<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

use Tiki\Package\VendorHelper;

function prefs_pwa_list()
{
    return [
        'pwa_feature' => [
            'name' => tra('Progressive Web Application Mode'),
            'description' => tra('Allow Tiki to be used offline and be installed in a mobile device.'),
            'help' => 'Enable-Progressive-Web-Application-Mode',
            'warning' => tra('Experimental feature.<br>Only Wiki pages and Trackers are available offline for now.'),
            'type' => 'flag',
            'tags' => ['experimental'],
            'default' => 'n',
            'packages_required' => ['npm-asset/dexie' => VendorHelper::getAvailableVendorPath('dexie', 'npm-asset/dexie/dist/dexie.js')],

        ],
        'pwa_cache_links' => [
            'name' => tra('Links to cache in PWA Mode'),
            'description' => tra('List of links to be cached when PWA is enabled'),
            'help' => 'Example: tiki-index.php?page=HomePage',
            'warning' => tra('Experimental feature.'),
            'type' => 'textarea',
            'dependencies' => ['pwa_feature'],
            'size' => 6,
            'default' => '',
        ]
    ];
}
