
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Standards\TikiIgnore\Helpers;

trait IgnoreListTrait
{
    protected $ignoreSniffList;
    protected $ignoreList;
    protected $relativePathOffset;

    protected function loadIgnoreList($ignoreSniffList)
    {
        if (! is_array($ignoreSniffList)) {
            $ignoreSniffList = [$ignoreSniffList];
        }

        $this->relativePathOffset = strlen(dirname(__FILE__, 7)) + 1;

        $this->ignoreSniffList = $ignoreSniffList;
        $this->ignoreList = [];

        if (file_exists(dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'ignore_list.json')) {
            $ignoreList = json_decode(file_get_contents(dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'ignore_list.json'), true);
            foreach ($ignoreSniffList as $sniff) {
                if (! empty($ignoreList[$sniff])) {
                    $this->ignoreList[$sniff] = $ignoreList[$sniff];
                }
            }
        }
    }

    protected function inIgnoreList($sniff, $file, $key)
    {
        $relativeFile = substr($file, $this->relativePathOffset);
        if (empty($this->ignoreList[$sniff][$relativeFile][$key])) {
            return false;
        }
        return true;
    }

    protected function ignoreToken($sniff, $phpcsFile, &$tokens, $stackPtr)
    {
        $line = $tokens[$stackPtr]['line'];
        if (empty($phpcsFile->tokenizer->ignoredLines[$line])) {
            $phpcsFile->tokenizer->ignoredLines[$line] = [];
        }
        $phpcsFile->tokenizer->ignoredLines[$line][$sniff] = true;
    }
}