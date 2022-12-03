<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function wikiplugin_profilesymbolvalue_info()
{
    return [
        'name' => tra('Profile Symbol Value'),
        'documentation' => 'PluginProfileSymbolValue',
        'description' => tra('Display the profile symbol related with profile and a reference and optionally with domain or package'),
        'prefs' => ['wikiplugin_profilesymbolvalue'],
        'introduced' => 20,
        'tags' => [ 'basic' ],
        'params' => [
            'domain' => [
                'required' => false,
                'description' => tra('Domain'),
                'since' => '20',
                'filter' => 'text',
            ],
            'profile' => [
                'required' => true,
                'description' => tra('Profile name'),
                'since' => '20',
                'filter' => 'text',
            ],
            'reference' => [
                'name' => tra('Reference key'),
                'required' => true,
                'description' => tra('Reference name'),
                'since' => '20',
                'filter' => 'text',
            ],
            'package' => [
                'name' => tra('Package'),
                'description' => tra('Package extension name'),
                'required' => false,
                'since' => '20',
                'filter' => 'text',
            ],
        ],
    ];
}

function wikiplugin_profilesymbolvalue($data, $params)
{
    $domain = isset($params['domain']) ? $params['domain'] : '';
    $profile = isset($params['profile']) ? $params['profile'] : '';
    $ref = isset($params['reference']) ? $params['reference'] : '';
    $package = isset($params['package']) ? $params['package'] : '';

    $smarty = TikiLib::lib('smarty');
    $smarty->loadPlugin('smarty_function_profilesymbolvalue');
    return smarty_function_profilesymbolvalue([
        'domain' => $domain,
        'profile' => $profile,
        'ref' => $ref,
        'package' => $package
    ], $smarty->getEmptyInternalTemplate());
}
