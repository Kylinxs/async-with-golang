
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * @return array
 */
function module_top_file_galleries_info()
{
    return [
        'name' => tra('Top File Galleries'),
        'description' => tra('Displays the specified number of file galleries with links to them, starting with the one with most hits.'),
        'prefs' => ['feature_file_galleries'],
        'params' => [],
        'common_params' => ['nonums', 'rows']
    ];
}

/**
 * @param $mod_reference
 * @param $module_params
 */
function module_top_file_galleries($mod_reference, $module_params)
{
    global $prefs;
    $filegallib = TikiLib::lib('filegal');
    $ranking = $filegallib->get_files(0, $mod_reference["rows"], 'hits_desc', null, $prefs['fgal_root_id'], false, true, false, false, false, false, false);
    $smarty = TikiLib::lib('smarty');
    $smarty->assign('modTopFileGalleries', $ranking["data"]);
}