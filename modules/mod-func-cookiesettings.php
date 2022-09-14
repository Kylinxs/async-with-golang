
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * @return array
 */
function module_cookiesettings_info()
{
    return [
        'name'        => tra('Cookie Consent Settings'),
        'description' => tra('Allows users to change their cookie consent preferences.'),
        'prefs'       => ['cookie_consent_feature'],
        'params'      => [
            'mode'      => [
                'name'        => tra('Mode'),
                'description' => tra('Display mode, text, icon or both. Default "icon"'),
                'filter'      => 'word',
            ],
            'text'      => [
                'name'        => tra('Text'),
                'description' => tra('Text to show on the link and tooltip. Default "Cookie Consent Settings"'),
                'filter'      => 'text',
            ],
            'icon'      => [
                'name'        => tra('Icon'),
                'description' => tra('Icon to show on the link. Default "cog"'),
                'filter'      => 'word',
            ],
            'iconsize'  => [
                'name'        => tra('Icon Size'),
                'description' => tra('Size of icon. Default "2"'),
                'filter'      => 'word',
            ],
            'class'     => [
                'name'        => tra('Class'),
                'description' => tra('Class of the container div. Default "p-2 bg-dark"'),
                'filter'      => 'word',
            ],
            'textclass' => [
                'name'        => tra('Text Class'),
                'description' => tra('Class of the text or icon. Default "text-light"'),
                'filter'      => 'word',
            ],
            'corner'    => [
                'name'        => tra('Position'),
                'description' => tra('Position on the page, topleft, topright, bottomleft, bottomright or none. Default "bottomleft"'),
                'filter'      => 'word',
            ],
        ],
    ];
}

/**
 * @param $mod_reference
 * @param $module_params
 */
function module_cookiesettings($mod_reference, &$module_params)
{
    $module_params = array_merge([
        'mode'      => 'icon',
        'text'      => 'Cookie Consent Settings',
        'icon'      => 'cog',
        'iconsize'  => 2,
        'class'     => 'p-2 bg-dark',
        'textclass' => 'text-light',
        'corner'    => 'bottomleft',
    ], $module_params);

    $module_params['class'] .= ' ' . $module_params['corner'];
}