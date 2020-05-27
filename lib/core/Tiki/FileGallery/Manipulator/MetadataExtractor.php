<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\FileGallery\Manipulator;

use TikiLib;

class MetadataExtractor extends Manipulator
{
    public function run($args = [])
    {
        global $prefs, $user;

        $file = $this->file;
        $filegallib = TikiLib::lib('filegal');

        $metadata = $filegallib->extractMetadataJson($file->getWrapper()->getReadableFile());
        $file->setParam('metadata', $metadata);

        if ($file->name === $file->filename && ! $file->galleryDefinition()->isDirect()) {
            $name = TikiLib::lib('filegal')::getTitleFromFilename($file->name);
        } else {
            $name = $file->name;
        }
        $file->setParam('name', trim(strip_tags($name)));
        $file->setParam('description', strip_tags($file->description));
        $file->setParam('filename', $this->truncateFilename($file->filename));

        $search_data = '';
        if ($prefs['fgal_enable_auto_indexing'] != 'n') {
            $search_data = $filegallib->get_search_text_for_data($file);
        }
        $file->setParam('search_data', $search_data);

        if (empty($file->created)) {
            $created = $filegallib->now;
            $lastModif = $filegallib->now;
        } else {
            $created = $file->created;
            $lastModif = $filegallib->now;
        }
        $file->setParam('created', $created);
        $file->setParam('lastModif', $lastModif);
        $file->setParam('lastModifUser', $user);

        $file->setParam('filetype', $filegallib->fixMime($file->filetype, $file->filename));
        if (! $file->exists() && $prefs['ocr_enable'] === 'y') {
            $ocrLib = TikiLib::lib('ocr');
            $ocr_state = $ocrLib::OCR_STATUS_SKIP;
            if ($file->getParam('ocr_state') || $prefs['ocr_every_file'] === 'y') {
                $ocrLib->setMimeTypes();
                if (in_array($file->filetype, $ocrLib->ocrMime)) {
                    $ocr_state = $ocrLib::OCR_STATUS_PENDING;
                }
            }
            $file->setParam('ocr_state', $ocr_state);
        }
    }

    private function truncateFilename($filename)
    {
        if (strlen($filename) > 80) {
            $filename = substr($filename, 0, 38) . '...' . substr($filename, strlen($filename) - 38);
        }
        return $filename;
    }
}
