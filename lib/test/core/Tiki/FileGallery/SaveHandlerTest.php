
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * @group unit
 *
 */

use Tiki\FileGallery\File;
use Tiki\FileGallery\FileDraft;
use Tiki\FileGallery\SaveHandler;

class Tiki_FileGallery_Manipulator_SaveHandlerTest extends TikiTestCase
{
    protected function setUp(): void
    {
        global $prefs;
        $this->oldPrefs = $prefs;
        $prefs['fgal_use_db'] = 'y';
        $prefs['feature_file_galleries_save_draft'] = 'n';
        $prefs['fgal_keep_fileId'] = 'y';
        parent::setUp();
        $this->cleanup();
    }

    protected function tearDown(): void
    {
        global $prefs;
        $prefs = $this->oldPrefs;
        $this->cleanup();
    }

    public function testFileCreation()
    {
        $file = new File(['filename' => 'test.zip', 'data' => 'test123', 'galleryId' => 222]);
        $filegallib = TikiLib::lib('filegal');

        $filesTable = $filegallib->table('tiki_files');
        $this->assertFalse($filesTable->fetchOne('fileId', ['filename' => $file->filename]));

        $saveHandler = new SaveHandler($file);
        $fileId = $saveHandler->save();

        $this->assertNotEmpty($fileId);
        $this->assertEquals($fileId, $filesTable->fetchOne('fileId', ['filename' => $file->filename]));
        $this->assertEquals($file->data, $filesTable->fetchOne('data', ['filename' => $file->filename]));
    }

    public function testFileUpdatePlain()
    {
        global $prefs;

        $prefs['feature_file_galleries_save_draft'] = 'n';

        $filegallib = TikiLib::lib('filegal');
        $filesTable = $filegallib->table('tiki_files');

        $fileId = $this->createGalleryAndFile(['archives' => -1]);
        $file = File::id($fileId);

        $file->setParam('data', 'changed');
        $file->setParam('filename', 'test.txt');

        $saveHandler = new SaveHandler($file);
        $fileId = $saveHandler->save();

        $this->assertEquals('test.txt', $filesTable->fetchOne('filename', ['fileId' => $fileId]));
        $this->assertEquals('changed', $filesTable->fetchOne('data', ['fileId' => $fileId]));
    }

    public function testFileArchiveNewId()
    {
        global $prefs;

        $prefs['feature_file_galleries_save_draft'] = 'n';
        $prefs['fgal_keep_fileId'] = 'n';

        $filegallib = TikiLib::lib('filegal');
        $filesTable = $filegallib->table('tiki_files');

        $origFileId = $this->createGalleryAndFile(['archives' => 0]);
        $file = File::id($origFileId);

        $file->setParam('data', 'changed');

        $saveHandler = new SaveHandler($file);
        $fileId = $saveHandler->save();

        $this->assertEquals('test123', $filesTable->fetchOne('data', ['fileId' => $origFileId]));
        $this->assertEquals('changed', $filesTable->fetchOne('data', ['fileId' => $fileId]));
        $this->assertNotEquals($fileId, $origFileId);
    }

    public function testFileArchiveKeepId()
    {
        global $prefs;

        $prefs['feature_file_galleries_save_draft'] = 'n';
        $prefs['fgal_keep_fileId'] = 'y';

        $filegallib = TikiLib::lib('filegal');
        $filesTable = $filegallib->table('tiki_files');

        $origFileId = $this->createGalleryAndFile(['archives' => 0]);
        $file = File::id($origFileId);

        $file->setParam('data', 'changed');

        $saveHandler = new SaveHandler($file);
        $fileId = $saveHandler->save();

        $this->assertEquals('changed', $filesTable->fetchOne('data', ['fileId' => $origFileId]));
        $this->assertEquals('test123', $filesTable->fetchOne('data', ['archiveId' => $fileId]));
    }

    public function testFileDraftAndValidation()
    {
        global $prefs;

        $prefs['feature_file_galleries_save_draft'] = 'y';

        $filegallib = TikiLib::lib('filegal');
        $filesTable = $filegallib->table('tiki_files');
        $draftsTable = $filegallib->table('tiki_file_drafts');

        $fileId = $this->createGalleryAndFile(['archives' => 0]);
        $file = File::id($fileId);

        $this->assertEmpty($draftsTable->fetchOne('data', ['fileId' => $fileId]));

        $file->setParam('data', 'changed');

        $saveHandler = new SaveHandler($file);
        $saveHandler->save();

        $this->assertEquals('changed', $draftsTable->fetchOne('data', ['fileId' => $fileId]));
        $this->assertEquals('test123', $filesTable->fetchOne('data', ['fileId' => $fileId]));

        $filegallib->validate_draft($fileId);

        $this->assertEquals('changed', $filesTable->fetchOne('data', ['fileId' => $fileId]));
        $this->assertEmpty($draftsTable->fetchOne('data', ['fileId' => $fileId]));
    }

    private function createGalleryAndFile($params)
    {
        global $user;

        $galleryId = TikiLib::lib('filegal')->table('tiki_file_galleries')->insert(
            array_merge([
            'name' => 'Test Gallery',
            'type' => 'default'
            ], $params)
        );
        return TikiLib::lib('filegal')->table('tiki_files')->insert([
        'filename' => 'test.zip',
        'data' => 'test123',
        'galleryId' => $galleryId,
        'created' => time(),
        'user' => $user
        ]);
    }

    private function cleanup()
    {
        $filegallib = TikiLib::lib('filegal');
        $filegallib->table('tiki_files')->deleteMultiple([]);
        $filegallib->table('tiki_file_drafts')->deleteMultiple([]);
        $filegallib->table('tiki_file_galleries')->deleteMultiple(
            ['parentId' => $filegallib->table('tiki_file_galleries')->greaterThan(0)]
        );
        $filegallib->clearLoadedGalleryDefinitions();
    }
}