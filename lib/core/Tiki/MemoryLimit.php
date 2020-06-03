<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Tiki_MemoryLimit
{
    private $initialLimit;

    public function __construct($targetLimit)
    {
        $this->initialLimit = ini_get('memory_limit');
        $this->applyMinimalLimit($targetLimit);
    }

    public function __destruct()
    {
        // Restore initial limit
        $this->applySafeLimit($this->initialLimit);
    }

    private function applyMinimalLimit($target)
    {
        $initial = self::toBytes($this->initialLimit);
        if ($initial > -1 && $initial < self::toBytes($target)) {
            ini_set('memory_limit', $target);
        };
    }

    private function applySafeLimit($targetMemory)
    {
        $usage = memory_get_usage();
        $target = self::toBytes($targetMemory);

        if ($usage < $target) {
            ini_set('memory_limit', $targetMemory);
        }
    }

    private static function toBytes($limitString)
    {
        $limitString = trim($limitString);
        $bytes = (int) $limitString;
        $lastCharacter = strtolower($limitString[strlen($limitString) - 1]);
        $units = ['k' => 1, 'm' => 2, 'g' => 3];
        if (array_key_exists($lastCharacter, $units)) {
            $bytes = $bytes * (1024 ** $units[$lastCharacter]);
        }
        return $bytes;
    }
}
