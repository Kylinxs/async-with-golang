<?php

// (c) Copyright 2002-2019 by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function prefs_realtime_list()
{
    return [
        'realtime_port' => [
            'name' => tra('Realtime port'),
            'description' => tr('The port used to access this server; if not specified, port %0 will be used', 8080),
            'type' => 'text',
            'size' => 5,
            'filter' => 'digits',
            'default' => '',
            'shorthint' => tr('If not specified, port %0 will be used', 8080),
            'tags' => ['advanced'],
            'dependencies' => [
                'feature_realtime',
            ],
        ],
    ];
}
