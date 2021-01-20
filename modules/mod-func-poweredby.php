
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * @return array
 */
function module_poweredby_info()
{
    return [
        'name' => tra('Powered By'),
        'description' => tra('Powered by Tiki, and others'),
        'params' => [
            'tiki' => [
                'name' => tra('Tiki'),
                'description' => tra('Shows "Powered by Tiki Wiki CMS Groupware" message') . ' (y/n)',
                'filter' => 'alpha',
            ],
            'version' => [
                'name' => tra('Version'),
                'description' => tra('Tiki version info') . ' (y/n)',
                'filter' => 'alpha',
            ],
            'credits' => [
                'name' => tra('Credits'),
                'description' => tra('Shows theme credits (contents of credits.tpl)') . ' (y/n)',
                'filter' => 'alpha',
                        ],
            'icons' => [
                'name' => tra('Icons'),
                'description' => tra('Shows various "powered by" icons') . ' (y/n)',
                'filter' => 'alpha',
            ],
        ],
    ];
}

/**
 * @param $mod_reference
 * @param $module_params
 */
function module_poweredby($mod_reference, $module_params)
{
}