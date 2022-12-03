<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\FileGallery\Handler;

use Tiki\FileGallery\FileWrapper\PhysicalFile;

class FileSystem implements HandlerInterface
{
    private $directory;
    private $preserveFilename = false;

    public function __construct($directory)
    {
        $this->directory = $directory;
        $this->directory = rtrim($directory, '/\\');
    }

    public function getFileWrapper($file)
    {
        return new PhysicalFile($this->directory, $file->path);
    }

    public function delete($file)
    {
        $full = "{$this->directory}/$file->path";

        if ($file->path && is_writable($full)) {
            unlink($full);
        }
    }

    public function setPreserveFilename($preserveFilename)
    {
        $this->preserveFilename = ($preserveFilename == true);
    }

    private function uniqueNameObfuscated($name)
    {
        $name = md5($name);
        while (file_exists($this->directory . '/' . $name)) {
            $name = md5(uniqid($name));
        }
        return $name;
    }

    /**
     * Given a file name, if a file exists in disk with the same name, then
     * append a numeric counter to it. Eg.:
     *  - flower.jpg     -> flower_1.jpg
     *  - flower.tar.xz  -> flower_1.tar.xz
     *  - flower         -> flower_1
     *  - .flower        -> .flower_1
     *  - .flower.jpg    -> .flower_1.jpg
     *  - .flower.tar.gz -> .flower_1.tar.gz
     */
    private function uniqueNameIncremental($name)
    {
        $counter = 1;
        $result = $name;
        while (file_exists($this->directory . '/' . $result)) {
            $result = preg_replace('/^(\.*[^\.]*)(\.)?/', "\$1_{$counter}\$2", $name);
            $counter++;
        }
        return $result;
    }

    public function uniquePath($file)
    {
        if (! empty($file->path) && $file->isReplacing) {
            return $file->path;
        }

        if ($this->preserveFilename) {
            $name = $this->uniqueNameIncremental($file->name);
        } else {
            $name = $this->uniqueNameObfuscated($file->name);
        }

        return $name;
    }

    public function isWritable()
    {
        return is_writable($this->directory);
    }
}
