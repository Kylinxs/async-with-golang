<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * @return array
 */
function module_short_url_info()
{
    return [
        'name' => tra('Short URL'),
        'description' => tra('Creates or shows a short url for the visiting page.'),
        'prefs' => ['feature_sefurl_routes', 'sefurl_short_url'],
    ];
}
