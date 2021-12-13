<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function prefs_warn_list()
{
    return [
        'warn_on_edit_time' => [
            'name' => tra('Edit idle timeout'),
            'description' => tra('Select the amount of time (in minutes) after which a user\'s edit session will expire. If the user does not save or preview their work, it will be lost. Tiki will display a "countdown time" in the user\'s browser and display an alert when only a minute remains.'),
            'units' => tra('minutes'),
            'type' => 'list',
            'options' => [
                '1' => '1',
                '2' => '2',
                '5' => '5',
                '10' => '10',
                '15' => '15',
                '30' => '30',
            ],
            'default' => 2,
        ],
    ];
}
