<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * @return array
 */
function module_credits_info()
{
    return [
        'name' => tra('Tiki User Credits'),
        'description' => tra('Shows the credits a user has.'),
        'prefs' => ['feature_credits'],
        'params' => [],
    ];
}
