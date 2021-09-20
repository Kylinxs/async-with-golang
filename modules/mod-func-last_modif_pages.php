<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * @return array
 */
function module_last_modif_pages_info()
{
    return [
        'name' => tra('Latest Changes'),
        'description' => tra('List the specified number of pages, starting from the most recently modified.'),
        'prefs' => ["feature_wiki"],
        'params' => [
            'absurl' => [
                'name' => tra('Absolute URL'),
                'description' => tra('If set to "y", some of the links use an absolute URL instead of a relative one. This can avoid broken links if the module is to be sent in a newsletter, for example.') . " " . tr('Default: "n".')
            ],
            'url' => [
                'name' => tra('Link Target'),
                'description' => tra('Target URL of the "...more" link at the bottom of the module.') . " " . tr('Default:') . ' tiki-lastchanges.php'
            ],
            'maxlen' => [
                'name' => tra('Maximum Length'),
                'description' => tra('Maximum number of characters in page names allowed before truncating.'),
                'filter' => 'int'
            ],
            'show_namespace' => [
                    'name' => tra('Show Namespace'),
                    'description' => tra('Show namespace prefix in page names.') . ' ( y / n )',    // Do not translate y/n
                    'default' => 'y'
            ],
            'date' => [
                'name' => tra('Date'),
                'description' => tra('If set to "y", show page edit dates.') . ' ( y / n )',
            ],
            'user' => [
                'name' => tra('User'),
                'description' => tra('If set to "y", show who edited the pages.') . ' ( y / n )',
            ],
            'action' => [
                'name' => tra('Action'),
                'description' => tra('If set to "y", show action performed on the pages.') . ' ( y / n )',
            ],
            'comment' => [
                'name' => tra('Comment'),
                'description' => tra('If set to "y", show the descriptions of the change made on the pages.') . ' ( y / n )',
            ],
            'maxcomment' => [
                'name' => tra('Maximum Length for comments'),
                'description' => tra('Maximum number of characters in comments allowed before truncating.'),
                'filter' => 'int'
            ],
            'days' => [
                'name' => tra('Number of days'),
                'description' => tra('Number of day in past to look for modified pages. Defaults to "356"'),
                'filter' => 'int'
            ],
        ],
        'common_params' => ['nonums', 'rows']
    ];
}

/**
 * @param $mod_reference
 * @param $module_params
 */
function module_last_modif_pages($mod_reference, $module_params)
{
    $tikilib = TikiLib::lib('tiki');
    $smarty = TikiLib::lib('smarty');
    $histlib = TikiLib::lib('hist');
    $ranking = $histlib->get_last_changes($module_params['days'] ?? 365, 0, $mod_reference['rows'], 'lastModif_desc', '', true);

    $smarty->assign('modLastModif', $ranking["data"]);
    $smarty->assign('maxlen', isset($module_params["maxlen"]) ? $module_params["maxlen"] : 0);
    $smarty->assign('absurl', isset($module_params["absurl"]) ? $module_params["absurl"] : 'n');
    $smarty->assign('url', isset($module_params["url"]) ? $module_params["url"] : 'tiki-lastchanges.php');
    $smarty->assign('namespaceoption', isset($module_params['show_namespace']) ? $module_params['show_namespace'] : 'n');
    $smarty->assign('date', isset($module_params["date"]) ? $module_params["date"] : 'n');
    $smarty->assign('modif_user', isset($module_params["user"]) ? $module_params["user"] : 'n');
    $smarty->assign('action', isset($module_params["action"]) ? $module_params["action"] : 'n');
    $smarty->assign('comment', isset($module_params["comment"]) ? $module_params["comment"] : 'n');
    $smarty->assign('maxcomment', isset($module_params["maxcomment"]) ? $module_params["maxcomment"] : 0);
    // if one of the parameters exist and equal to "y"
    if ((isset($module_params["date"]) && ($module_params["date"] == 'y')) || (isset($module_params["user"]) && ($module_params["user"] == 'y')) || (isset($module_params["action"]) && ($module_params["action"] == 'y')) || (isset($module_params["comment"]) && ($module_params["comment"] == 'y'))) {
        $smarty->assign('modLastModifTable', 'y');
    } else {
        $smarty->assign('modLastModifTable', 'n');
    }
}
