<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * @return array
 */
function module_last_submissions_info()
{
    return [
        'name' => tra('Newest Article Submissions'),
        'description' => tra('Lists the specified number of article submissions from newest to oldest.'),
        'prefs' => ["feature_submissions"],
        'params' => [],
        'common_params' => ['nonums', 'rows']
    ];
}

/**
 * @param $mod_reference
 * @param $module_params
 */
function module_last_submissions($mod_reference, $module_params)
{
    $smarty = TikiLib::lib('smarty');
    $artlib = TikiLib::lib('art');
    $ranking = $artlib->list_submissions(0, $mod_reference['rows'], 'created_desc', '', '');
    $smarty->assign('modLastSubmissions', $ranking["data"]);
}
