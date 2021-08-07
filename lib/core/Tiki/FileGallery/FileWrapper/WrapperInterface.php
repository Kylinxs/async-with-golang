<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\FileGallery\FileWrapper;

interface WrapperInterface
{
    /**
     * Returns a path to a readable file path to read the content from.
     * Can be used by external tools who use a file path as the input.
     */
    public function getReadableFile();

    /**
     * Returns the content of the file as a string.
     */
    public function getContents();

  /**
   * Returns the file checksum.
   */
    public function getChecksum();

  /**
   * Get file size in bytes.
   */
    public function getSize();

  /**
   * Is the file available on the local filesystem?
   */
    public function isFileLocal();

  /**
   * Replace file contents.
   */
    public function replaceContents($data);

  /**
   * Get Tiki database storable content of this file.
   * Implementations can use data, path, filesize, etc. db columns.
   * If needed, more columns can be added.
   */
    public function getStorableContent();
}
