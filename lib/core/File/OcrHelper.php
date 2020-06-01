
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\File;

use Tiki\TikiInit;
use Tiki\Process\Process;
use Tiki\FileGallery\File;
use Tiki\Lib\Alchemy\AlchemyLib;
use Tiki\Lib\Unoconv\UnoconvLib;
use Unoconv\Exception\RuntimeException;

class OcrHelper
{
    /**
     * Extract text from multiple file types for OCR purposes
     * @param $fileId
     * @return bool|string
     * @throws \Exception
     */
    public static function extractText($fileId)
    {
        $file = File::id($fileId);

        if (empty($file)) {
            return false;
        }

        if (substr(PHP_OS, 0, 3) == 'WIN') {
            return false;
        }

        global $mimetypes, $tikidomain;
        include_once('lib/mime/mimetypes.php');

        $tempReadableFilePath = $file->getWrapper()->getReadableFile();
        $pdfTempFile = null;
        $tempFileName = implode(DIRECTORY_SEPARATOR, ['temp', 'cache', $tikidomain, uniqid()]);
        $mimetypeMatched = false;

        if (FileHelper::isOfficeDocument($file->filetype)) {
            $mimetypeMatched = true;
            try {
                $unoconv = new UnoconvLib();
                $unoconv->convertFile($tempReadableFilePath, $tempFileName, 'txt');
            } catch (RuntimeException $e) {
                // Unoconv was unable to extract text without converting to PDF first
                $pdfTempFile = PDFHelper::convertToPDF($fileId);
            }
        }

        if ($file->filetype == $mimetypes['pdf'] || (! empty($pdfTempFile) && file_exists($pdfTempFile))) {
            $mimetypeMatched = true;
            $tempReadableFilePath = $pdfTempFile ?: $tempReadableFilePath;
            $process = new Process(['which', 'pdftotext']);
            $process->run();
            $pdfToTextPath = preg_replace('/\s+/', ' ', trim($process->getOutput()));

            if (empty($pdfToTextPath) || TikiInit::isWindows()) {
                throw new \Exception('Text cannot be extracted because pdftotext library is unavailable or is not supported.');
            }

            $process = new Process([$pdfToTextPath, $tempReadableFilePath, $tempFileName]);
            $process->run();
        }

        if (! $mimetypeMatched) {
            throw new \Exception('Mime-type not supported for OCR text extraction');
        }

        if ($pdfTempFile) {
            unlink($pdfTempFile);
        }

        $ocrContent = file_get_contents($tempFileName);
        unlink($tempFileName);

        return $ocrContent;
    }

    /**
     * Convert an image to another image file format
     * @param $fileId
     * @param $newPathWithExtension
     * @return bool|string|null
     * @TODO - This function should probably be moved into a more generic place.
     */
    public static function convertImage($fileId, $newPathWithExtension)
    {
        $file = File::id($fileId);

        if (empty($file)) {
            return false;
        }

        $alchemy = new AlchemyLib();
        $temporaryFile = $file->getWrapper()->getReadableFile();

        return $alchemy->convertToImage($temporaryFile, $newPathWithExtension);
    }
}