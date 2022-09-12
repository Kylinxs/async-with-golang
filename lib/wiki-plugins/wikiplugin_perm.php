<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function wikiplugin_perm_info()
{
    return [
        'name' => tra('Permissions'),
        'documentation' => 'PluginPerm',
        'description' => tra('Display content based on permission settings'),
        'body' => tr('Wiki text to display if conditions are met. The body may contain %0{ELSE}%1. Text after the
            marker will be displayed to users not matching the conditions.', '<code>', '</code>'),
        'prefs' => ['wikiplugin_perm'],
        'filter' => 'wikicontent',
        'iconname' => 'permission',
        'introduced' => 5,
        'params' => [
            'perms' => [
                'required' => false,
                'name' => tra('Possible Permissions'),
                'description' => tra('Pipe-separated list of permissions, one of which is needed to view the default text.') .
                    ' ' . tra('Example:') . ' <code>tiki_p_rename|tiki_p_edit</code>',
                'since' => '5.0',
                'filter' => 'text',
                'separator' => '|',
                'default' => '',
            ],
            'notperms' => [
                'required' => false,
                'name' => tra('Forbidden Permissions'),
                'description' => tra('Pipe-separated list of permissions, any of which will cause the default text not to show.') .
                    ' ' . tra('Example:') . ' <code>tiki_p_rename|tiki_p_edit</code>',
                'since' => '5.0',
                'filter' => 'text',
                'separator' => '|',
                'default' => '',
            ],
            'global' => [
                'required' => false,
                'name' => tra('Global'),
                'description' => tra('Indicate whether the permissions are global or local to the object'),
                'since' => '5.0',
                'filter' => 'text',
                'default' => '',
                'options' => [
                    ['text' => '', 'value' => ''],
                    ['text' => tra('Yes'), 'value' => '1'],
                    ['text' => tra('No'), 'value' => '0']
                ],
            ],
            'object' => [
                'required' => false,
                'name' => tra('Object ID'),
                'description' => tra('Name or ID of the object to test if not global or the current object'),
                'since' => '21.3',
                'filter' => 'text',
                'default' => '',
            ],
            'type' => [
                'required' => false,
                'name' => tra('Type'),
                'description' => tra('Type of object referred to in Object ID'),
                'since' => '21.3',
                'filter' => 'wordspace',
                'default' => '',
            ],
        ]
    ];
}

function wikiplugin_perm($data, $params)
{
    global $user;
    $userlib = TikiLib::lib('user');
    if (! empty($params['perms'])) {
        $perms = $params['perms'];
    }
    if (! empty($params['notperms'])) {
        $notperms = $params['notperms'];
    }

    if (! $perms && ! $notperms) {
        Feedback::error(tr('One of either parameter %0perms%1 or %0notperms%1 are required.', '<code>', '</code>'));
        return '';
    }
    if ($params['global']) {
        $global = true;
    } else {
        $global = false;
    }

    if (! empty($params['object']) && ! empty($params['type'])) {
        $objectPerms = Perms::get([ 'type' => $params['type'], 'object' => $params['object'] ]);
    } else {
        $objectPerms = null;
    }

    if (strpos($data, '{ELSE}')) {
        $dataelse = substr($data, strpos($data, '{ELSE}') + 6);
        $data = substr($data, 0, strpos($data, '{ELSE}'));
    } else {
        $dataelse = '';
    }

    if (! empty($perms)) {
        $ok = false;
        foreach ($perms as $perm) {
            if ($global) {
                if ($userlib->user_has_permission($user, $perm)) {
                    $ok = true;
                    break;
                }
            } elseif ($objectPerms) {
                $ok = $objectPerms->$perm;
                if ($ok) {
                    break;
                }
            } else {
                global $$perm;
                if ($$perm == 'y') {
                    $ok = true;
                    break;
                }
            }
        }
    }
    if (! empty($notperms)) {
        $ok = true;
        foreach ($notperms as $perm) {
            if ($global) {
                if ($userlib->user_has_permission($user, $perm)) {
                    $ok = false;
                    break;
                }
            } elseif ($objectPerms) {
                $ok = ! $objectPerms->$perm;
                if (! $ok) {
                    break;
                }
            } else {
                global $$perm;
                if ($$perm == 'y') {
                    $ok = false;
                    break;
                }
            }
        }
    }

    if ($ok) {
        return $data;
    } else {
        return $dataelse;
    }
}
