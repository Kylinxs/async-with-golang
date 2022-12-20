<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function prefs_sitead_list()
{
    return [
        'sitead_publish' => [
            'name' => tra('Publish'),
            'type' => 'flag',
            'description' => tra('Make the banner visible to all site visitors.'),
            'dependencies' => [
                'feature_sitead',
            ],
            'hint' => tra('Activate must be turned on for Publish to take effect.'),
            'default' => 'n',
        ],
    ];
}
