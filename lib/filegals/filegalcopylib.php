<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * Class FilegalCopyLib
 *
 * Container for functions involved in files:copy and files:move console commands
 *
 */
class FilegalCopyLib extends FileGalLib
{
    /**
     * Processes a list of files to be copied/moved to a directory in the filesystem
     *
     * @param array $files
     * @param string $destinationPath
     * @param bool $move
     * @return array                    feedback messages
     */

    public function processCopy($files, $destinationPath, $move = false)
    {

        $feedback = [];
        $operation = ($move) ? "Move" : "Copy";

        // cycle through all files to copy
        foreach ($files as $file) {
            $result = $this->copyFile($file, $destinationPath, $move);
            if (isset($result['error'])) {
                if ($move) {
                    $feedback[] = '<span class="text-danger">' . tr('Move was not successful for "%0"', $file['filename']) . '<br>(' . $result['error'] . ')</span>';
                } else {
                    $feedback[] = '<span class="text-danger">' . tr('Copy was not successful for "%0"', $file['filename']) . '<br>(' . $result['error'] . ')</span>';
                }
            } else {
                if ($move) {
                    $feedback[] = tra('Move was successful') . ': ' . $file['filename'];
                } else {
                    $feedback[] = tra('Copy was successful') . ': ' . $file['filename'];
                }
            }
        }
        return $feedback;
    }

    /**
     *  Takes a file from a file gallery and copies/moves it to a local path
     *
     * @param array $file
     * @param string $destinationPath
     * @param string $sourcePath
     * @param bool $move
     * @return array                    [fileName[,error]]
     */
    public function copyFile($file, $destinationPath, $move = false)
    {
        $file = \Tiki\FileGallery\File::id($file['fileId']);

        $source = $file->getWrapper()->getReadableFile();
        if (! copy($source, $destinationPath . $file->filename)) {
            if (! is_writable($destinationPath)) {
                return ['error' => tra('Cannot write to this path: ') . $destinationPath];
            } else {
                return ['error' => tra('Cannot read this file: ') . $source];
            }
        }

        if ($move) {
            if ($this->remove_file($file->getParams(), '', true) === false) {
                return ['error' => tra('Cannot remove file from gallery')];
            }
        }

        return [
            'fileName' => $file->filename,
        ];
    }
}
