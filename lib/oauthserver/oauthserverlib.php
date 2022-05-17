
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER["SCRIPT_NAME"], basename(__FILE__)) !== false) {
    header("location: index.php");
    exit;
}

include_once __DIR__ . '/TikiCryptKey.php';
include_once __DIR__ . '/entities/UserEntity.php';
include_once __DIR__ . '/repositories/AccessTokenRepository.php';
include_once __DIR__ . '/repositories/AuthCodeRepository.php';
include_once __DIR__ . '/repositories/ClientRepository.php';
include_once __DIR__ . '/repositories/RefreshTokenRepository.php';
include_once __DIR__ . '/repositories/ScopeRepository.php';

use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\ImplicitGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\ResourceServer;

class OAuthServerLib extends \TikiLib
{
    private $server;

    public function getEncryptionKey()
    {
        global $prefs;
        $tikilib = TikiLib::lib('tiki');

        if (empty($prefs['oauthserver_encryption_key'])) {
            $encryptionKey = $tikilib->generate_unique_sequence(32, true);
            $tikilib->set_preference('oauthserver_encryption_key', $encryptionKey);
        }

        return $prefs['oauthserver_encryption_key'];
    }

    public function getPublicKey()
    {
        global $prefs;
        $tikilib = TikiLib::lib('tiki');

        if (empty($prefs['oauthserver_public_key'])) {
            $keys = $this->generateKeys();
            $tikilib->set_preference('oauthserver_public_key', $keys['public']);
            $tikilib->set_preference('oauthserver_private_key', $keys['private']);
        }

        return $prefs['oauthserver_public_key'];
    }

    public function getPrivateKey()
    {
        global $prefs;
        $tikilib = TikiLib::lib('tiki');

        if (empty($prefs['oauthserver_private_key'])) {
            $keys = $this->generateKeys();
            $tikilib->set_preference('oauthserver_public_key', $keys['public']);
            $tikilib->set_preference('oauthserver_private_key', $keys['private']);
        }

        return $prefs['oauthserver_private_key'];
    }

    public function getClientRepository()
    {
        $database = TikiLib::lib('db');
        return new ClientRepository($database);
    }

    public function getAccessTokenRepository()
    {
        return new AccessTokenRepository();
    }

    public function getServer($skip_keypair = false)
    {
        if (empty($this->server)) {
            $this->server = new AuthorizationServer(
                $this->getClientRepository(),
                $this->getAccessTokenRepository(),
                new ScopeRepository(),
                $skip_keypair ? new TikiCryptKey(null) : $this->getPrivateKey(),
                $this->getEncryptionKey()
            );
        }
        return $this->server;
    }

    public function getUserEntity()
    {
        global $user;
        $entity = new UserEntity();
        $entity->setIdentifier($user);
        return $entity;
    }

    public function determineServerGrant($skip_keypair = false)
    {
        global $user;
        $server = $this->getServer($skip_keypair);

        if (! empty($user)) {
            // TODO: this is legacy, see if xmpp/converse really need it
            $grant = new ImplicitGrant(new \DateInterval('PT1H'), '?');
            $server->enableGrantType($grant);
        }

        // end user/app auth flow
        $grant = new AuthCodeGrant(
            new AuthCodeRepository(),
            new RefreshTokenRepository(),
            new \DateInterval('PT10M')
        );
        $grant->setRefreshTokenTTL(new \DateInterval('P1M'));
        $server->enableGrantType($grant);

        // end user/app refresh token flow
        $grant = new RefreshTokenGrant(new RefreshTokenRepository());
        $grant->setRefreshTokenTTL(new \DateInterval('P1M'));
        $server->enableGrantType(
            $grant,
            new \DateInterval('PT1H')
        );

        // server-to-server client credentials flow
        $server->enableGrantType(
            new ClientCredentialsGrant(),
            new \DateInterval('PT1H')
        );

        return $this;
    }

    public function getClient($client_id)
    {
        return $this->getClientRepository()->get($client_id);
    }

    public function createClient($data)
    {
        $repo = $this->getClientRepository();

        if (empty($data['client_id'])) {
            $data['client_id'] = $repo::generateSecret(32);
        }

        if (empty($data['client_secret'])) {
            $data['client_secret'] = $repo::generateSecret(64);
        }

        $entity = ClientRepository::build($data);
        return $repo->create($entity);
    }

    public function checkAuthToken($request)
    {
        $util = new Services_OAuthServer_Utilities();
        $request = $util->tiki2Psr7Request($request);
        $server = new ResourceServer(
            $this->getAccessTokenRepository(),
            new TikiCryptKey($this->getPublicKey())
        );
        try {
            $request = $server->validateAuthenticatedRequest($request);
            return $request->getAttribute('oauth_access_token_id');
        } catch (OAuthServerException $e) {
            TikiLib::lib('access')->display_error(null, $e->getMessage() . ' ' . $e->getHint(), 403);
        }
    }

    private function generateKeys()
    {
        $error_message = tr('OAuth server is not configured correctly: missing public/private key pair and autogeneration failed.');
        if (! function_exists('openssl_pkey_new')) {
            throw new Exception($error_message);
        }
        $private_key = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => 2048,
        ]);
        if (! $private_key) {
            throw new Exception($error_message);
        }
        $details = openssl_pkey_get_details($private_key);
        if (! $details) {
            throw new Exception($error_message);
        }
        $public_key_pem = $details['key'] ?? null;
        $result = openssl_pkey_export($private_key, $private_key_pem);
        if (! $result || empty($public_key_pem) || empty($private_key_pem)) {
            throw new Exception($error_message);
        }
        return [
            'public' => $public_key_pem,
            'private' => $private_key_pem,
        ];
    }
}