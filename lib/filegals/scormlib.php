<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class ScormLib
{
    public function handle_file_creation($args)
    {
        if ($metadata = $this->getRequestMetadata($args)) {
            $this->createItem(
                $metadata,
                [
                    'scormPackage' => $args['object'],
                ]
            );
        }
    }

    public function handle_file_update($args)
    {
        if (isset($args['initialFileId']) && $metadata = $this->getRequestMetadata($args)) {
            $relationlib = TikiLib::lib('relation');
            $items = $relationlib->get_relations_to('file', $args['initialFileId'], 'tiki.file.attach');

            $transaction = TikiDb::get()->begin();

            foreach ($items as $item) {
                if ($item['type'] == 'trackeritem') {
                    $this->updateItem(
                        $item['itemId'],
                        $metadata,
                        [
                            'scormPackage' => $args['object'],
                        ]
                    );
                }
            }

            $transaction->commit();
        }
    }

    private function getRequestMetadata($args)
    {
        $metadata = null;

        $file = \Tiki\FileGallery\File::id($args['object']);

        if (
            $this->isZipFile($args)
            && $zip = $this->getZipFile($file)
        ) {
            if ($manifest = $this->getScormManifest($zip)