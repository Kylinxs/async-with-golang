
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Test\Files;

use Comments;
use Exception;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Tiki\Files\CheckAttachmentGallery;
use TikiLib;
use TrackerLib;
use WikiLib;

class CheckAttachmentGalleryTest extends TestCase
{
    protected $file_root;
    protected $files_dir;
    protected $default_file_content = 'this is a test';

    protected function setUp(): void
    {
        global $prefs;

        $prefs['feature_user_watches'] = 'n';
        $this->refreshDirectory();
        // Remove existing attachments
        $this->removeAttachmentsFromDb();
    }

    protected function tearDown(): void
    {
        $this->removeAttachmentsFromDb();
    }

    /**
     * @dataProvider getTypes
     * @param $type
     */
    public function testAttachmentEmptyAttachmentsNoProblemOnDB($type)
    {
        $to_assert = [
            'usesDatabase' => true,
            'path' => [''],
            'mixedLocation' => false,
            'count' => 0,
            'countFilesDb' => 0,
            'countFilesDisk' => 0,
            'issueCount' => 0,
            'missing' => [],
            'mismatch' => [],
            'unknown' => [],
        ];

        $this->configToStoreFiles($type, true);
        $checkInstance = new CheckAttachmentGallery($type);

        $result = $checkInstance->analyse();

        $this->assertEquals($to_assert, $result);
    }

    /**
     * @dataProvider getTypes()
     *
     * @param $type
     */
    public function testAttachmentEmptyAttachmentsNoProblemOnDisk($type)
    {
        $to_assert = [
            'usesDatabase' => false,
            'path' => [$this->files_dir],
            'mixedLocation' => false,
            'count' => 0,
            'countFilesDb' => 0,
            'countFilesDisk' => 0,
            'issueCount' => 0,
            'missing' => [],
            'mismatch' => [],
            'unknown' => [],
        ];

        $this->configToStoreFiles($type, false);
        $checkInstance = new CheckAttachmentGallery($type);

        $result = $checkInstance->analyse();

        $this->assertEquals($to_assert, $result);
    }

    /**
     * @dataProvider getTypes
     * @param $type
     * @throws Exception
     */
    public function testAttachmentWithOneFileOnDisk($type)
    {
        $this->configToStoreFiles($type, false);

        $this->insertAttachment(['name' => uniqid(), 'type' => $type]);

        $checkInstance = new CheckAttachmentGallery($type);
        $result = $checkInstance->analyse();

        $this->assertFalse($result['usesDatabase']);
        $this->assertFalse($result['mixedLocation']);
        $this->assertEquals(1, $result['count']);
        $this->assertEquals(0, $result['issueCount']);
    }

    /**
     * @dataProvider getTypes()
     * @param $type
     * @throws Exception
     */
    public function testAttachmentWithOneFileOnDb($type)
    {
        $this->configToStoreFiles($type, true);
        $checkInstance = new CheckAttachmentGallery($type);
        $this->insertAttachment([
            'name' => 'testAttachmentWithOneFileOnDb',
            'type' => $type,
            'db' => true
        ]);
        $result = $checkInstance->analyse();

        $this->assertFalse($result['mixedLocation']);
        $this->assertEquals(1, $result['count']);
        $this->assertEquals(0, $result['issueCount']);
    }

    /**
     * @dataProvider getTypes
     * @param $type
     */
    public function testAttachmentUnknownFile($type)
    {
        global $tikilib;

        $this->configToStoreFiles($type, false);
        $filename = md5($tikilib->now);
        $this->createFileOnDisk($filename, 'Invalid file');

        $checkInstance = new CheckAttachmentGallery($type);

        $result = $checkInstance->analyse();

        $this->assertFalse($result['usesDatabase']);
        $this->assertFalse($result['mixedLocation']);
        $this->assertEquals(0, $result['count'], 'Count'); // Attachment not registered in db
        $this->assertEquals(1, $result['issueCount'], 'Issue Count');
        $this->assertEquals(
            [
                [
                    'name' => $filename,
                    'path' => $this->files_dir,
                    'size' => strlen('Invalid file')
                ]
            ],
            $result['unknown']
        );
    }

    /**
     * @dataProvider getTypes
     * @param $type
     * @throws Exception
     */
    public function testAttachmentMismatchFile($type)
    {
        global $tikilib;

        $this->configToStoreFiles($type, false);

        $filename = md5($tikilib->now);

        $id = $this->insertAttachment([
            'name' => $filename,
            'fhash' => $filename,
            'type' => $type,
            'size' => 1 // the size will create the mismatch
        ]);

        $checkInstance = new CheckAttachmentGallery($type);

        $result = $checkInstance->analyse();

        $this->assertFalse($result['usesDatabase']);
        $this->assertFalse($result['mixedLocation']);
        $this->assertEquals(1, $result['count'], 'Count');
        $this->assertEquals(1, $result['issueCount'], 'Issue Count');
        $this->assertEquals(
            [
                [
                    'name' => $filename,
                    'path' => $this->files_dir,
                    'size' => 1,
                    'id' => $id
                ]
            ],
            $result['mismatch']
        );
    }

    /**
     * Test that a file that should be stored in filesystem is missing
     *
     * @dataProvider getTypes
     * @param $type
     * @throws Exception
     */
    public function testAttachmentMissingFile($type)
    {
        global $tikilib;

        $this->configToStoreFiles($type, false);

        $filename = md5($tikilib->now);
        $id = $this->insertAttachment([
            'name' => $filename,
            'fhash' => $filename,
            'type' => $type,
        ]);

        if (file_exists($this->files_dir . $filename)) {
            unlink($this->files_dir . $filename);
        }

        $checkInstance = new CheckAttachmentGallery($type);

        $result = $checkInstance->analyse();

        $this->assertFalse($result['usesDatabase']);
        $this->assertFalse($result['mixedLocation']);
        $this->assertEquals(1, $result['count'], 'Count');
        $this->assertEquals(1, $result['issueCount'], 'Issue Count');
        $this->assertEquals([
            [
                'name' => $filename,
                'path' => $this->files_dir,
                'size' => (int)strlen($this->default_file_content),
                'id' => $id,
            ]
        ], $result['missing']);
    }

    /**
     * Configures the preferences to set the storage in the disk for a specific type
     * @param      $type
     * @param bool $use_db
     */
    protected function configToStoreFiles($type, $use_db = false)
    {
        global $prefs;
        $prefs[$type . '_use_db'] = $use_db ? 'y' : 'n';
        $prefs[$type . '_use_dir'] = $use_db ? '' : $this->files_dir;
    }

    /**
     * Inserts a TXT file attachment for a specific type
     * @param $file
     * @return mixed
     * @throws Exception
     */
    protected function insertAttachment($file)
    {
        global $tikilib;

        $id = null;
        $base_name = $file['name'];
        $useDB = isset($file['db']) && $file['db'];

        $string = $this->default_file_content;
        $size = $file['size'] ?? strlen($string);
        $type = $file['type'];

        $lib = $this->getLib($type);
        $dir = $useDB ? '' : $this->files_dir;
        $fhash = null;

        if (! $useDB) {
            $fhash = $file['fhash'] ?? md5($base_name . $tikilib->now);
            $this->createFileOnDisk($fhash, $string);
            $string = null;
        }

        switch ($type) {
            case 'w':
                $id = $lib->wiki_attach_file('test', $base_name, '.txt', (int)$size, $string, 0, 0, $fhash, time());
                break;
            case 't':
                $id = $lib->replace_item_attachment(null, $base_name, '.txt', (int)$size, $string, 0, 0, $fhash, '', '', 0, 0, [], []);
                break;
            case 'f':
                $id = $lib->forum_attach_file(0, 0, $base_name, '.txt', (int)$size, $string, $fhash, $dir, 0);
                break;
        }

        return $id;
    }

    /**
     * Function to create a local file with a specific content
     * @param $file_name
     * @param $content
     */
    protected function createFileOnDisk($file_name, $content)
    {
        $full_path = $this->files_dir . $file_name;
        $myfile = fopen($full_path, "w") or die("Unable to open file!");
        fwrite($myfile, $content);
        fclose($myfile);
    }

    /**
     * Clears and prepares the DB to run the tests
     * @throws Exception
     */
    protected function removeAttachmentsFromDb()
    {
        $types = ['f', 't', 'w'];

        foreach ($types as $type) {
            $lib = $this->getLib($type);
            $attachments = $lib->list_all_attachments();

            foreach ($attachments['data'] as $attachment) {
                $this->removeAttachment($type, $attachment['attId']);
            }
        }
    }

    /**
     * Gets the lib related to a specific attachment type
     *
     * @param $type
     *
     * @return WikiLib|TrackerLib|Comments
     * @throws Exception
     */
    protected function getLib($type)
    {
        switch ($type) {
            case 'w':
                return TikiLib::lib('wiki');
            case 't':
                return TikiLib::lib('trk');
            case 'f':
                return TikiLib::lib('comments');
        }
    }

    /**
     * Gets the method responsible for removing an attachment based on the type
     * @param $type
     * @param $id
     * @throws Exception
     */
    protected function removeAttachment($type, $id)
    {
        $lib = $this->getLib($type);

        switch ($type) {
            case 'w':
                $lib->remove_wiki_attachment($id);
                break;
            case 't':
                $lib->remove_item_attachment($id);
                break;
            case 'f':
                $lib->remove_thread_attachment($id);
                break;
        }
    }

    /**
     * Refreshes mock directory to store files for test purposes
     */
    protected function refreshDirectory()
    {
        $this->file_root = vfsStream::setup(uniqid('', true), null);
        $this->files_dir = $this->file_root->url() . '/test/';
        mkdir($this->files_dir);
    }

    /**
     * Data provider to test all attachment galleries
     *
     * @return array
     */
    public function getTypes()
    {
        return [
            'forum' => ['f'],
            'tracker' => ['t'],
            'wiki' => ['w'],
        ];
    }
}