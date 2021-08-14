
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\SabreDav;

use Sabre\DAV;
use Sabre\DAV\Locks\Backend\File as FileBackend;
use Sabre\DAV\Locks\LockInfo;
use TikiLib;

class LocksBackend extends FileBackend
{
  /**
   * Returns a list of Sabre\DAV\Locks\LockInfo objects
   *
   * This method should return all the locks for a particular uri, including
   * locks that might be set on a parent uri.
   *
   * If returnChildLocks is set to true, this method should also look for
   * any locks in the subtree of the uri for locks.
   *
   * Works on the basis of File backend which allows us to have locks on URIs
   * of nonexistent files or wiki pages and adds what other locks are available
   * in Tiki.
   *
   * @param string $uri
   * @param bool $returnChildLocks
   * @return array
   */
    public function getLocks($uri, $returnChildLocks)
    {
        $locks = parent::getLocks($uri, $returnChildLocks);
      // check wiki page locks
        if (preg_match('#^Wiki Pages/#', $uri)) {
            $lockedPages = TikiLib::lib('wiki')->get_locked();
            foreach ($lockedPages as $page) {
                $existing = array_filter($locks, function ($lockInfo) use ($page) {
                    return $lockInfo->uri == 'Wiki Pages/' . $page['pageName'];
                });
                if (! $existing) {
                      $lockInfo = new LockInfo();
                      $lockInfo->owner = $page['lockedby'];
                      $lockInfo->token = DAV\UUIDUtil::getUUID();
                      $lockInfo->timeout = 0;
                      $lockInfo->created = $page['lastModif'];
                      $lockInfo->uri = 'Wiki Pages/' . $page['pageName'];
                      $locks[] = $lockInfo;
                }
            }
            return $locks;
        }
      // since only file locks are supported, we don't need to search for parent uri locks
        try {
            $file = new File($uri);
            if ($file->getFile()->lockedby) {
                $existing = array_filter($locks, function ($lockInfo) use ($uri) {
                    return $lockInfo->uri == $uri;
                });
                if (! $existing) {
                      $lockInfo = new LockInfo();
                      $lockInfo->owner = $file->getFile()->lockedby;
                      $lockInfo->token = DAV\UUIDUtil::getUUID();
                      $lockInfo->timeout = 0;
                      $lockInfo->created = $file->getFile()->lastModif;
                      $lockInfo->uri = $uri;
                      $locks[] = $lockInfo;
                }
            }
        } catch (DAV\Exception\NotFound $e) {
          # ignore missing file or unsupported file gallery locks
        }
        if ($returnChildLocks) {
            try {
                $directory = new Directory($uri);
                foreach ($directory->getChildren() as $child) {
                    if (get_class($child) == 'File') {
                        if ($child->getFile()->lockedby) {
                              $childUri = TikiLib::lib('filegal')->get_full_virtual_path($child->getFile()->fileId);
                              $existing = array_filter($locks, function ($lockInfo) use ($childUri) {
                                return $lockInfo->uri == $childUri;
                              });
                            if (! $existing) {
                                $lockInfo = new LockInfo();
                                $lockInfo->owner = $child->getFile()->lockedby;
                                $lockInfo->token = DAV\UUIDUtil::getUUID();
                                $lockInfo->timeout = 0;
                                $lockInfo->created = $child->getFile()->lastModif;
                                $lockInfo->uri = $childUri;
                                $locks[] = $lockInfo;
                            }
                        }
                    } else {
                        $galUri = TikiLib::lib('filegal')->get_full_virtual_path($child->getGalleryId());
                        $locks = array_merge($locks, $this->getLocks($galUri, $returnChildLocks));
                    }
                }
            } catch (DAV\Exception\NotFound $e) {
              # ignore missing file gallery
            }
        }
        return $locks;
    }

  /**
   * Locks a uri
   *
   * @param string $uri
   * @param LockInfo $lockInfo
   * @return bool
   */
    public function lock($uri, LockInfo $lockInfo)
    {
        parent::lock($uri, $lockInfo);
        try {
            if ($m = preg_match('#^Wiki Pages/(.*)$#', $uri)) {
                if (TikiLib::lib('tiki')->page_exists($m[1])) {
                    TikiLib::lib('wiki')->lock_page($m[1]);
                }
            } else {
                $file = new File($uri);
                TikiLib::lib('filegal')->lock_file($file->getFile()->fileId, $lockInfo->owner);
            }
        } catch (DAV\Exception\NotFound $e) {
          # ignore missing file or unsupported file gallery locks
        }
        return true;
    }

  /**
   * Removes a lock from a uri
   *
   * @param string $uri
   * @param LockInfo $lockInfo
   * @return bool
   */
    public function unlock($uri, LockInfo $lockInfo)
    {
        parent::unlock($uri, $lockInfo);
        try {
            if ($m = preg_match('#^Wiki Pages/(.*)$#', $uri)) {
                if (TikiLib::lib('tiki')->page_exists($m[1])) {
                    TikiLib::lib('wiki')->unlock_page($m[1]);
                }
            } else {
                $file = new File($uri);
                TikiLib::lib('filegal')->unlock_file($file->getFile()->fileId);
            }
        } catch (DAV\Exception\NotFound $e) {
          # ignore missing file or unsupported file gallery locks
        }
        return true;
    }
}