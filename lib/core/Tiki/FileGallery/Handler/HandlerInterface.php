<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\FileGallery\Handler;

interface HandlerInterface
{
  /**
   * Returns a FileWrapper\WrapperInterface for accessing and modifying the file contents
   * and metadata.
   */
    public function getFileWrapper($file);

  /**
   * Deletes a file from the underlying storage.
   */
    public function delete($file);

  /**
   * Ensures unique filename is available for new files if underlying storage requires it.
   */
    public function uniquePath($file);

  /**
   * Is the underlying storage location writable.
   */
    public function isWritable();
}
