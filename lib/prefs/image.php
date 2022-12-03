<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function prefs_image_list()
{
    return [
        'image_responsive_class' => [
            'name' => tra('Default for img-fluid class used in the IMG plugin'),
            'description' => tra('Default option for whether an image produced with the IMG plugin has the img-fluid class - a plugin parameter allows this to be overridden'),
            'type' => 'flag',
            'default' => 'y',
        ],
    ];
}
