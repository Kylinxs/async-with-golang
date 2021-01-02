
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Services_Encryption_ControllerTest extends PHPUnit\Framework\TestCase
{
    protected $subject;

    protected function setUp(): void
    {
        global $prefs, $user;
        $user = '';
        $prefs['feature_user_encryption'] = 'y';

        $perms = new Perms();
        $perms->setResolverFactories(
            [
                new Perms_ResolverFactory_StaticFactory('root', new Perms_Resolver_Default(true)),
            ]
        );
        Perms::set($perms);

        $this->subject = new Services_Encryption_Controller();
        $this->subject->setUp();
    }

    public function testCreateSimpleKey()
    {
        $input = new JitFilter([
            'name' => 'test key',
            'shares' => 1,
            'algo' => 'aes-256-ctr',
        ]);
        $result = $this->subject->action_save_key($input);
        $this->assertGreaterThan(0, $result['keyId']);
        $this->assertEquals(1, count($result['shares']));
        $this->assertNotEmpty($result['shares'][0]);
        TikiLib::lib('tiki')->table('tiki_encryption_keys')->delete(['keyId' => $result['keyId']]);
    }

    public function testFailToCreateKeyWithoutShares()
    {
        $input = new JitFilter([
            'name' => 'test key',
            'shares' => 0,
            'algo' => 'aes-256-ctr',
        ]);
        $this->expectException(Services_Exception_Denied::class);
        $this->expectExceptionMessage('minimum');
        $this->subject->action_save_key($input);
    }

    public function testCreateKeySharedWithTikiUsers()
    {
        TikiLib::lib('user')->add_user('user1', 'pass1234', 'test1@example.org');
        TikiLib::lib('user')->add_user('user2', 'pass1234', 'test2@example.org');
        $input = new JitFilter([
            'name' => 'test key',
            'users' => 'user1, user2',
            'algo' => 'aes-256-ctr',
        ]);
        $result = $this->subject->action_save_key($input);
        $prefs = TikiLib::lib('tiki')->table('tiki_user_preferences')->fetchAll(
            ['user', 'value'],
            ['prefName' => 'pe.sk.' . $result['keyId']],
            -1,
            -1,
            'user'
        );
        $this->assertEquals(2, count($prefs));
        $this->assertEquals('user1', $prefs[0]['user']);
        $this->assertEquals($result['shares'][0], $prefs[0]['value']);
        $this->assertEquals('user2', $prefs[1]['user']);
        $this->assertEquals($result['shares'][1], $prefs[1]['value']);
        TikiLib::lib('tiki')->table('tiki_encryption_keys')->delete(['keyId' => $result['keyId']]);
        TikiLib::lib('user')->remove_user('user1');
        TikiLib::lib('user')->remove_user('user2');
    }

    public function testEncryptSharedKeyAfterUserLogin()
    {
        $this->shareKeyWithUser(function ($result) {
            $prefs = TikiLib::lib('tiki')->table('tiki_user_preferences')->fetchAll(
                ['user', 'value'],
                ['prefName' => TikiLib::lib('tiki')->table('tiki_user_preferences')->expr('$$ LIKE ?', ['d%.sk.' . $result['keyId']])]
            );
            $this->assertEquals(1, count($prefs));
            $this->assertEquals('user1', $prefs[0]['user']);
            $this->assertNotEquals($result['shares'][0], $prefs[0]['value']);
        });
    }

    public function testRetrieveSharedSecretStoredEncrypted()
    {
        $this->shareKeyWithUser(function ($result) {
            $share = $this->subject->action_get_share_for_key(new JitFilter(['keyId' => $result['keyId']]));
            $this->assertEquals($result['shares'][0], $share);
        });
    }

    public function testRetrieveActualKeyStoredEncrypted()
    {
        $this->shareKeyWithUser(function ($result) {
            $key = $this->subject->action_decrypt_key(new JitFilter(['keyId' => $result['keyId']]));
            $this->assertEquals($result['key'], $key);
        });
    }

    public function testFailDecryptionWhenMissingSharedKey()
    {
        $this->shareKeyWithUser(function ($result) {
            unset($_SESSION['cryptphrase']);
            $this->expectException(Services_Exception_Denied::class);
            $this->expectExceptionMessage('key not found');
            $this->subject->action_decrypt_key(new JitFilter(['keyId' => $result['keyId']]));
        });
    }

    public function testDecryptWithSuppliedSharedKey()
    {
        $this->shareKeyWithUser(function ($result) {
            unset($_SESSION['cryptphrase']);
            $key = $this->subject->action_decrypt_key(new JitFilter([
                'keyId' => $result['keyId'],
                'existing' => $result['shares'][0],
                'algo' => 'aes-256-ctr',
            ]));
            $this->assertEquals($result['key'], $key);
        });
    }

    public function testFailureWithMissingKey()
    {
        $this->expectException(Services_Exception_NotFound::class);
        $this->expectExceptionMessage('Key not found');
        $this->subject->action_decrypt_key(new JitFilter([]));
    }

    private function shareKeyWithUser($cb)
    {
        global $user;
        TikiLib::lib('user')->add_user('user1', 'pass1234', 'test1@example.org');
        $input = new JitFilter([
            'name' => 'test key',
            'users' => 'user1',
            'algo' => 'aes-256-ctr',
        ]);
        $result = $this->subject->action_save_key($input);
        $user = 'user1';
        $cryptlib = TikiLib::lib('crypt');
        $cryptlib->onUserLogin('pass1234');
        try {
            $cb($result);
        } finally {
            TikiLib::lib('tiki')->table('tiki_encryption_keys')->delete(['keyId' => $result['keyId']]);
            TikiLib::lib('user')->remove_user('user1');
            $user = '';
        }
    }
}