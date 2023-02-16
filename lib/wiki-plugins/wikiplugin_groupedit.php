
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function wikiplugin_groupedit_info()
{
    return [
        'name' => tra('Limited Group Edit'),
        'documentation' => 'PluginGroupEdit',
        'description' => tra('Display a list of the groups for users that have the edit limited group permission to edit the group information.'),
        'prefs' => ['wikiplugin_groupedit'],
        'iconname' => 'groupedit',
        'introduced' => 1,
        'params' => [
        ],
    ];
}

function wikiplugin_groupedit($data, $params)
{
    $smarty = TikiLib::lib('smarty');
    $userlib = TikiLib::lib('user');

    $template = 'tiki-edit_groups.tpl';
    $groups = $userlib->get_groups();
    $smarty->assign('groups', $groups["data"]);
    $smarty->assign('uri', $_SERVER['REQUEST_URI']);

    $ret = $smarty->fetch($template);
    return '~np~' . $ret . '~/np~';
}