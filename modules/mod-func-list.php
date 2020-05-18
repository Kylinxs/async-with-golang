<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

if (strpos($_SERVER["SCRIPT_NAME"], basename(__FILE__)) !== false) {
    header("location: index.php");
    exit;
}

/**
 * @return array
 */
function module_list_info()
{
    return [
        'name' => tra('List'),
        'description' => tra('List objects from the Unified Index'),
        'prefs' => ['feature_search'],
        'params' => [
            'body' => [
                'required' => false,
                'name' => tra('Body'),
                'description' => tra('Definition of the list as used in plugin list.'),
                'filter' => 'text',
                'type' => 'textarea',
                'default' => '',
            ],
            'searchable_only' => [
                'required' => false,
                'name' => tra('Searchable Only Results'),
                'description' => tra('Only include results marked as searchable in the index.'),
                'filter' => 'digits',
                'default' => '1',
                'options' => [
                    ['text' => tra(''), 'value' => ''],
                    ['text' => tra('Yes'), 'value' => '1'],
                    ['text' => tra('No'), 'value' => '0'],
                ],
            ],
        ],
    ];
}

/**
 * @param $mod_reference
 * @param $module_params
 */
function module_list($mod_reference, $module_params)
{
    // nothing to do here?
}
