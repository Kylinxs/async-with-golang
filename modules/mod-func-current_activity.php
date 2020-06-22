<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER['SCRIPT_NAME'], basename(__FILE__)) !== false) {
    header('location: index.php');
    exit;
}

/**
 * @return array
 */
function module_current_activity_info()
{
    return [
        'name' => tra('Current Activity'),
        'description' => tra('Display users who are currently editing a page or a tracker.'),
        'prefs' => [],
        'documentation' => 'Module current_activity',
        'params' => [],
    ];
}

/**
 * @param $mod_reference
 * @param $module_params
 */
function module_current_activity($mod_reference, $module_params)
{
    $tikilib = TikiLib::lib('tiki');
    $smarty = TikiLib::lib('smarty');
    $count = ! isset($module_params['content']) || $module_params['content'] != 'list';
    $list = ! isset($module_params['content']) || $module_params['content'] != 'count';
    $smarty->assign('count', $count);
    $smarty->assign('list', $list);

    if ($count) {
        $logged_users = $tikilib->count_sessions();
        $smarty->assign('logged_users', $logged_users);
    }
    $result = $tikilib->query("SELECT * FROM tiki_semaphores");
    $results = [];
    while ($row = $result->fetchRow()) {
        $results[] = $row;
    }
    $data = [];
    foreach ($results as $res) {
        $activity = $res["semName"];
        if (isset($data[$activity])) {
            array_push($data[$activity]["users"], $res["user"]);
        } else {
            $data[$activity] = ["type" => $res["objectType"], "users" => [$res["user"]]];
        }
    }

    $smarty->assign('results', $data);
    $smarty->assign('tpl_module_title', tra('Current activity'));
}
