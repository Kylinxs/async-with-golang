<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function prefs_sefurl_list()
{
    return [

        'sefurl_short_url' => [
            'name' => tr('Short URL'),
            'description' => tr('Provides the ability to create a short url, easy to share.'),
            'type' => 'flag',
            'default' => 'n',
            'keywords' => 'short url',
            'dependencies' => [
                'feature_sefurl_routes',
            ],
        ],
        'sefurl_short_url_base_url' => [
            'name' => tr('Short URL base URL'),
            'description' => tra('The base URL that is used when generating short URLs, including the HTTP prefix, example: "http://www.example.com". By default will use the URL of the current website.'),
            'type' => 'text',
            'size' => '300',
            'default' => '',
            'keywords' => 'short url',
            'dependencies' => [
                'sefurl_short_url',
            ],
        ],
    ];
}
