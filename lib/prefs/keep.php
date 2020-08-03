<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function prefs_keep_list()
{
    return [
        'keep_versions' => [
            'name' => tra('Keep versions for'),
            'description' => tra('Do not delete versions younger than this number of days.'),
            'type' => 'text',
            'size' => '5',
            'units' => tra('days'),
            'default' => 1,
            'keywords' => 'wiki history',
        ],
    ];
}
