<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Package\Extension\Api;

use Tiki\Package\Extension\Api;

class Events extends Api
{
    protected static $eventMap = [];

    public function isInstalled($folder)
    {
        $installed1 = array_keys(self::$parents);
        if (strpos($folder, '/') !== false && strpos($folder, '_') === false) {
            $folder = str_replace('/', '_', $folder);
        }
        if (parent::isInstalled($folder) && in_array($folder, $installed1)) {
            return true;
        } else {
            return false;
        }
    }

    public static function setEventMap($folder, $eventMap)
    {
        if (strpos($folder, '/') !== false && strpos($folder, '_') === false) {
            $folder = str_replace('/', '_', $folder);
        }
        foreach ($eventMap as $event) {
            $event->folder = $folder;
            self::$eventMap[] = $event;
        }
        return true;
    }

    public static function bindEvents($events)
    {
        foreach (self::$eventMap as $event) {
            $events->bind($event->event, [$event->lib, $event->function], (array) $event->extra_args);
        }
    }
}
