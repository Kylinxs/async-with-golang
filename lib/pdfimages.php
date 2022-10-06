
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 *
 * A wrapper for pdfimages, part of the poppler.freedesktop.org set of tools
 *
 * Class PdfImagesLib
 */
class PdfImagesLib
{
    protected $binaryPath = 'pdfimages';
    protected $arguments;
    public $version;
    protected $source;
    protected $destination;

    /**
     * Sets the binary path of pdfimages
     * @param null $path|string The path to the pdfimages binary. Will default to Tiki preference location if not supplied.
     */
    public function setBinaryPath($path = null)
    {
        global $prefs;
        if ($path) {
            $this->binaryPath = escapeshellcmd($path);
        } elseif (! empty($prefs['ocr_pdfimages_path'])) {
            $this->binaryPath = escapeshellcmd($prefs['ocr_pdfimages_path']);
        } else {
            $this->binaryPath = 'pdfimages';
        }
    }

    /**
     * Sets the binary version of pdfimages.
     * No version will be set upon error.
     */
    public function setVersion()
    {
        $output = shell_exec(escapeshellarg($this->binaryPath) . ' -v 2>&1');
        preg_match('/[\d\.]+/', $output, $version);

        if (! empty($version[0])) {
            $this->version = $version[0];
        }
    }


    /**
     * Checks if pdfimages is installed
     *
     * @throws Exception If pdf images is not accessible on the local system.
     */
    public function isInstalled()
    {
        $this->setVersion();
        if (! $this->version) {
            throw new Exception('pdfimages binary not found');
        }
    }

    /**
     * Arguments to pass, you may mix-n-match from the following depending on your binary version.
     * f <int>       : first page to convert
     * l <int>       : last page to convert
     * png           : change the default output format to PNG
     * tiff          : change the default output format to TIFF
     * j             : write JPEG images as JPEG files
     * jp2           : write JPEG2000 images as JP2 files
     * jbig2         : write JBIG2 images as JBIG2 files
     * ccitt         : write CCITT images as CCITT files
     * all           : equivalent to -png -tiff -j -jp2 -jbig2 -ccitt
     * list          : print list of images instead of saving
     * opw <string>  : owner password (for encrypted files)
     * upw <string>  : user password (for encrypted files)
     * p             : include page numbers in output file names
     * q             : don't print any messages or errors
     * v             : print copyright and version info

     *
     * @param $argument string
     */
    public function setArgument($argument)
    {

        $this->arguments[] = $argument;
    }

    /**
     * @param $sourcePDF string The file path to the PDF to be processed
     * @param $destinationFolder string The directory path to the folder to place the extracted images
     *
     * @throws Exception If either path is not reachable
     */
    public function setFilePaths($sourcePDF, $destinationFolder)
    {
        if (! is_readable($sourcePDF)) {
            throw new Exception($this->source . 'not readable');
        }
        if (! is_writable($destinationFolder)) {
            throw new Exception($this->destination . ' is not writable');
        }

        $this->source = escapeshellarg($sourcePDF);
        $this->destination = escapeshellarg($destinationFolder);
    }

    /**
     * Execute the extraction of images with the binary app pdfimages
     * The arguments need to be previously set with an instance of PdfImagesLib
     * @throws Exception Thrown if pdfimages binary throws an error
     */
    public function run()
    {

        $this->isInstalled();

        if (empty($this->arguments)) {
            $arguments = ' ';
        } else {
            $arguments = ' -' . implode(' -', $this->arguments) . ' ';
        }

        $error = shell_exec($this->binaryPath . $arguments . $this->source . ' ' . $this->destination . ' 2>&1');
        if ($error) {
            throw new Exception($error);
        }
    }
}