
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function wikiplugin_sign_info()
{
    return [
        'name' => tr('Sign'),
        'documentation' => 'PluginSign',
        'description' => tr('Sign and date your contribution to a page'),
        'prefs' => ['wikiplugin_sign'],
        'tags' => ['basic'],
        'inline' => true,
        'format' => 'html',
        'iconname' => 'pencil',
        'introduced' => 10,
        'params' => [
            'user' => [
                'required' => false,
                'name' => tr('User'),
                'description' => tr('Auto-generated, the username.'),
                'since' => '10.0',
                'default' => '',
                'filter' => 'text',
                'advanced' => true,
            ],
            'datetime' => [
                'required' => false,
                'name' => tr('Date and time'),
                'description' => tr('Auto-generated, the timestamp'),
                'since' => '10.0',
                'default' => '',
                'filter' => 'text',
                'advanced' => true,
            ],
        ],
    ];
}

function wikiplugin_sign_rewrite($data, $params, $context)
{
    if (empty($params['user']) && empty($params['datetime'])) {
        global $user;
        $date = gmdate(DateTime::W3C);
        return "{sign user=\"$user\" datetime=\"$date\"}";
    }

    return false;
}

function wikiplugin_sign($data, $params, $offset)
{
    if (empty($params['datetime'])) {
        return false;
    }

    $user = isset($params['user']) ? $params['user'] : '';

    $time = strtotime($params['datetime']);

    if ($time === false) {
        return false;
    }

    $smarty = TikiLib::lib('smarty');
    $smarty->assign(
        'sign',
        [
            'user' => $user,
            'datetime' => $params['datetime'],
            'time' => $time,
        ]
    );

    $tip = $smarty->fetch('wiki-plugins/wikiplugin_sign.tpl');

    $smarty->loadPlugin('smarty_function_icon');
    $icon = smarty_function_icon(['name' => 'pencil', 'title' => '', 'iclass' => 'sign-icon'], $smarty->getEmptyInternalTemplate());

    TikiLib::lib('header')-> add_jq_onready(
        '$(function () {
            $("[data-bs-toggle=tooltip]").tooltip({
                delay: { "show": 0, "hide": 1000 }
            })
        });'
    );

    return "<span class='sign' data-animation='true' data-bs-toggle='tooltip' data-bs-html='true' data-bs-placement='auto' title='$tip'>$icon</span>";
}