<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * @return array
 */
function module_top_files_info()
{
    return [
        'name' => tra('Top Files'),
        'description' => tra('Displays the specified number of files with links to them, starting with the one with most hits.'),
        'prefs' => ['feature_file_galleries'],
        'params' => [],
        'common_params' => ['nonums', 'rows']
    ];
}

/**
 * @param $mod_reference
 * @param $module_params
 */
function module_top_files($mod_reference, $module_params)
{
    $smarty = TikiLib::lib('smarty');
    $filegallib = TikiLib::lib('filegal');
    $ranking = $filegallib->list_files(0, $mod_reference["rows"], 'hits_desc', '');

    $smarty->assign('modTopFiles', $ranking["data"]);
}
