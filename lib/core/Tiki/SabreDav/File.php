<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\SabreDav;

use Sabre\DAV;
use TikiLib;
use Tiki\FileGallery\File as TikiFile;

class File extends DAV\File
{
    private $file;

    public function __construct($path_or_id)
    {
        if ((int)$path_or_id == 0) {
            $result = TikiLib::lib('filegal')->get_objectid_from_virtual_path($path);
            if (! $result || $result['type'] != 'file') {
                throw new DAV\Exception\NotFound(tr('The file with path: ' . $path_or_id . ' could not be found'));
            }
            $path_or_id = $result['id'];
        }
        $this->file = TikiFile::id($path_or_id);
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getName()
    {
        return $this->file->filename;
    }

    public function get()
    {
        return $this->file->getContents();
    }

    public function getSize()
    {
        return $this->file->filesize;
    }

    public function getETag()
    {
        $md5 = md5($this->file->hash . $this->file->lastModif);
        return '"' . $md5 . '-' . crc32($md5) . '"';
    }

    public function getContentType()
    {
        return $this->file->filetype;
    }

    public function getLastModified()
    {
        return $this->file->lastModif;
    }

    public function put($data)
    {
        Utilities::checkUploadPermission($this->file->galleryDefinition());

        $info = Utilities::parseContents($this->file->filename, $data);

        $this->file->replace($info['content'], $info['mime'], $this->file->name, $this->file->filename);
    }

    public function setName($name)
    {
        Utilities::checkUploadPermission($this->file->galleryDefinition());

        $this->file->replace($this->file->data, $this->file->filetype, $name, $name);
    }

    public function delete()
    {
        Utilities::checkDeleteFilePermission($this->file->galleryDefinition());

        $this->file->delete();
    }
}
