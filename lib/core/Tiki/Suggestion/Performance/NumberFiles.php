<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Suggestion\Performance;

use Tiki\Suggestion\SuggestionRulesInterface as SuggestionRules;
use TikiLib;

class NumberFiles implements SuggestionRules
{
    private static $totalFiles = 200;

    public function parser()
    {
        global $prefs;
        $message = '';
        if ($prefs['fgal_use_db'] === 'y') {
            $filegallib = TikiLib::lib('filegal');
            $files = $filegallib->list_files();
            $totalFiles = ! empty($files['cant']) ? $files['cant'] : 0;
            if ($totalFiles >= self::$totalFiles) {
                $message = tra('You are using a lot of files, you can move out from the database to file system.');
            }
        }
        return $message;
    }
}
