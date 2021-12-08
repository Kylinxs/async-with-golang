
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class BOMChecker_Scanner
{
    public const BOM_UTF8 = 'BOM-UTF8';
    public const BOM_UTF16 = 'BOM-UTF16';

    // Tiki source folder
    protected $sourceDir = __DIR__ . '/../../../';

    protected $excludeDir = [];
    protected $scanFiles = [];

    protected $scanExtensions = [
        'php',
        'tpl',
        'js',
    ];

    // The number of files scanned.
    protected $scannedFiles = 0;

    // The list of files detected with BOM
    protected $bomFiles = [
        self::BOM_UTF8 => [],
        self::BOM_UTF16 => [],
    ];

    // The list of files detected without BOM
    protected $withoutBomFiles = [];

    /**
     * @param string $scanDir The file directory to scan.
     * @param array $scanExtensions An array with the file extensions to scan for BOM.
     */
    public function __construct($scanDir = null, $scanExtensions = [], $excludeDir = [], $scanFiles = [])
    {
        if (! empty($scanDir) && is_dir($scanDir)) {
            $this->sourceDir = $scanDir;
        }

        $this->sourceDir = realpath($this->sourceDir);

        if (is_array($scanExtensions) && count($scanExtensions)) {
            $this->scanExtensions = $scanExtensions;
        }

        if (! empty($excludeDir)) {
            $this->excludeDir = $excludeDir;
        }

        if (! empty($scanFiles)) {
            $this->scanFiles = $scanFiles;
        }
    }

    /**
     * Scan the folder for BOM files
     * @return array
     *  An array with the path to the BOM detected files.
     */
    public function scan()
    {
        if (! empty($this->scanFiles)) {
            $this->checkListFiles($this->scanFiles);
        } else {
            $this->checkDir($this->sourceDir);
        }

        return $this->getBomFiles();
    }

    /**
     * Check directory path
     *
     * @param string $sourceDir
     * @return void
     */
    protected function checkDir($sourceDir)
    {
        if (! empty($this->excludeDir) && in_array($sourceDir, $this->excludeDir)) {
            return;
        }

        $sourceDir = $this->fixDirSlash($sourceDir);

        // Copy files and directories.
        $sourceDirHandler = opendir($sourceDir);

        while ($file = readdir($sourceDirHandler)) {
            // Skip ".", ".." .
            if ($file == '.' || $file == '..') {
                continue;
            }

            $sourcefilePath = $sourceDir . $file;

            if (is_dir($sourcefilePath)) {
                $this->checkDir($sourcefilePath);
            }

            if (
                ! is_file($sourcefilePath)
                || ! in_array($this->getFileExtension($sourcefilePath), $this->scanExtensions)
            ) {
                continue;
            }

            if (! $type = $this->checkUtfBom($sourcefilePath)) {
                $this->withoutBomFiles[] = $sourcefilePath;
                continue;
            }

            $this->bomFiles[$type][] = str_replace($this->sourceDir . '/', '', $sourcefilePath);
        }
    }

    /**
     * Check a list of files
     *
     * @param string $listFiles
     * @return void
     */
    protected function checkListFiles($listFiles)
    {
        if (empty($listFiles)) {
            return;
        }

        foreach ($listFiles as $file) {
            if (in_array($this->getFileExtension($file), $this->scanExtensions)) {
                if (! $type = $this->checkUtfBom($file)) {
                    $this->withoutBomFiles[] = $file;
                } else {
                    $this->bomFiles[$type][] = $file;
                }
            }
        }
    }

    /**
     * Check and change slash directory path
     *
     * @param string $dirPath
     * @return string
     */
    protected function fixDirSlash($dirPath)
    {
        $dirPath = str_replace('\\', '/', $dirPath);

        if (substr($dirPath, -1, 1) != '/') {
            $dirPath .= '/';
        }

        return $dirPath;
    }

    /**
     * Get file extension
     *
     * @param string $filePath
     * @return string
     */
    protected function getFileExtension($filePath)
    {
        $info = pathinfo($filePath);
        return isset($info['extension']) ? $info['extension'] : '';
    }

    /**
     * Check if UTF-8 / UTF-16 BOM codification file
     *
     * @param string $filePath
     * @return bool|string false if not found, a string with the type of BOM if found
     */
    protected function checkUtfBom($filePath)
    {
        $file = fopen($filePath, 'r');
        $data = fgets($file, 3);
        fclose($file);

        $this->scannedFiles++;

        if (substr($data, 0, 3) == "\xEF\xBB\xBF") {
            return self::BOM_UTF8;
        }

        if (
            (substr($data, 0, 2) == "\xFE\xFF") // UTF-16 big-endian BOM
            || (substr($data, 0, 2) == "\xFF\xFE") // UTF-16 little-endian BOM
        ) {
            return self::BOM_UTF16;
        }

        return false;
    }

    /**
     * Get the number of files scanned.
     *
     * @return int
     */
    public function getScannedFiles()
    {
        return $this->scannedFiles;
    }

    /**
     * Get the list of files detected with BOM.
     *
     * @return array
     */
    public function getBomFiles()
    {
        $allFiles = [];
        foreach ($this->bomFiles as $files) {
            $allFiles = array_merge($allFiles, $files);
        }

        return $allFiles;
    }

    /**
     * Get the list of files detected with BOM.
     *
     * @return array
     */
    public function getBomFilesByType($type = null)
    {
        if (! $type) {
            return $this->bomFiles;
        }

        return $this->bomFiles[$type] ?? [];
    }

    /**
     * Get the list of files detected without BOM.
     *
     * @return array
     */
    public function getWithoutBomFiles()
    {
        return $this->withoutBomFiles;
    }

    /**
     * Returs true if there is at least one file found with BOM
     *
     * @return bool
     */
    public function bomFilesFound()
    {
        foreach ($this->bomFiles as $result) {
            if (! empty($result)) {
                return true;
            }
        }

        return false;
    }
}