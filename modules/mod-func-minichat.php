<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * @return array
 */
function module_minichat_info()
{
    return [
        'name' => tra('Minichat'),
        'description' => tra('Small live chat box'),
        'prefs' => ["feature_minichat"],
        'params' => [
            'channels' => [
                'name' => tra('Channels'),
                'description' => tra('List of chat channels. Channel names are separated by a comma (",").') . ' ' . tra('Example value') . ': ' . tra('english') . ',' . tra('french') . '. ' . tra('By default, a single channel named "default" exists.'),
                'filter' => 'striptags'
            ]
        ],
        'common_params' => ['rows']
    ];
}

/**
 * @param $mod_reference
 * @param $module_params
 */
function module_minichat($mod_reference, $module_params)
{
    $smarty = TikiLib::lib('smarty');
    global $prefs;
    if (isset($module_params["channels"])) {
        $channels = explode(',', $module_params["channels"]);
    } else {
        $channels = ['default'];
    }

    if (isset($_SESSION['minichat_channels'])) {
        $channels = $_SESSION['minichat_channels'];
    }

    $jscode = '';
    foreach ($channels as $k => $channel) {
        $channel = '#' . preg_replace('/[^a-zA-Z0-9\-\_]/i', '', $channel);
        $channel = substr($channel, 0, 30);
        $channels[$k] = $channel;

        $jscode .= "minichat_addchannel('" . $channel . "');\n";
    }

    $smarty->assign('jscode', $jscode);
}
