
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Files;

use TikiLib;

class CheckAttachmentGallery extends AbstractCheckGallery
{
    /*
     * Holds current attachment type
     * t    -   Tracker Attachments
     * w    -   Wiki Attachments
     * f    -   Forum Attachments
     */
    private $type;

    /**
     * @param string $type The attachment type to check
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    public function analyse()
    {
        $usesDatabase = $this->areFilesStoredInDatabase();
        $attachmentsPath = $this->getPathOnDisk();

        $filesInDbCount = 0;
        $filesInDiskCount = 0;

        $filesToCheckOnDisk = [];

        $attachments = $this->getAttachments();
        $filesCountTotal = count($attachments['data']);

        foreach ($attachments['data'] as $attachment) {
            if ($attachment['path']) {
                $filesInDiskCount++;
                $filesToCheckOnDisk[] = [
                    'id' => $attachment['attId'],
                    'name' => $attachment['path'],
                    'path' => $attachmentsPath,
                    'size' => $attachment['filesize'],
                ];
            } else {
                $filesInDbCount++;
            }
        }

        $filesOnDisk = $this->listFilesInDirectory($attachmentsPath);

        list($missing, $mismatch, $unknown) = $this->matchFileList($filesToCheckOnDisk, $filesOnDisk, []);

        return [
            'usesDatabase' => $usesDatabase,
            'path' => [$attachmentsPath],
            'mixedLocation' => ($filesInDbCount !== 0 && $filesInDiskCount !== 0) ? true : false,
            'count' => $filesCountTotal,
            'countFilesDb' => $filesInDbCount,
            'countFilesDisk' => $filesInDiskCount,
            'issueCount' => count($missing) + count($mismatch) + count($unknown),
            'missing' => $missing,
            'mismatch' => $mismatch,
            'unknown' => $unknown,
        ];
    }

    /**
     * Gets attachments based on their type. "list_all_attachments" is common to all of these libs
     * @return array
     * @throws \Exception
     */
    private function getAttachments()
    {
        switch ($this->type) {
            case 't':
                $lib = TikiLib::lib('trk');
                break;
            case 'w':
                $lib = TikiLib::lib('wiki');
                break;
            case 'f':
                $lib = TikiLib::lib('comments');
                break;
        }

        return $lib->list_all_attachments();
    }

    /**
     * Checks if the configuration is to store files in DB or disk
     *
     * @return bool
     */
    protected function areFilesStoredInDatabase()
    {
        global $prefs;
        return isset($prefs[$this->type . '_use_db']) ? $prefs[$this->type . '_use_db'] === 'y' : false;
    }

    /**
     * Returns where to store files on disk
     *
     * @return string
     */
    protected function getPathOnDisk()
    {
        global $prefs;
        return isset($prefs[$this->type . '_use_dir']) ? $prefs[$this->type . '_use_dir'] : false;
    }
}