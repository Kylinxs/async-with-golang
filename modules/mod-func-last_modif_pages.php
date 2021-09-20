<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * @return array
 */
function module_last_modif_pages_info()
{
    return [
        'name' => tra('Latest Changes'),
        'description' => tra('List the specified number of pages, starting from the most recently modified.'),
        'prefs' => ["feature_wiki"],
        'params' => [
            'absurl' => [
                'name' => tra('Absolute URL'),
                'description' => tra('If set to "y", some of the links use an absolute URL instead of a relative one. This can avoid broken links if the module is to be sent in a newsletter, for example.') . " " . tr('Default: "n".')
            ],
            'url' => [
                'name' => tra('Link Target'),
                'description' => tra('Target URL of the "...more" link at the bottom of the module.') . " " . tr('Default:') . ' tiki-lastchanges.php'
            ],
            'maxlen' => [
                'name' => tra('Maximum Length'),
                'description' => tra('Maximum number of characters in page names allowed before truncating.'),
                'filter' => 'int'
            ],
            'show_namespace' => [
                    'name' => tra('Show Namespace'),
                    'description' => tra('Show namespace prefix in page names.') . ' ( y / n )',    // Do not translate y/n
                    'default' => 'y'
            ],
            'date' => [
                'name' => tra('Date'),
                'description' => tra('If set to "y", show page edit dates.') . ' ( y / n )',
            ],
            'user' => [
                'name' => tra('User'),
                'description' => tra('If set to "y", show who edited the pages.') . ' ( y / n )',
            ],
            'action' => [
                'name' => tra('Action'),
                'description' => tra('If set to "y", show action performed on the pages.') . ' ( y / n )',
            ],
            'comment' => [
                'name' => tra('Comment'),
                'description' => tra('If set to "y", show the descriptions of the change made on the pages.') . ' ( y / n )',
            ],
            'maxcomment' => [
                'name' => tra('Maximum Length for comments'),
                'description' => tra('Maximum number of characters in comments allowed before truncating.'),
                'filter' => 'int'
            ],
            'days' => [
                'name' => tra('Number of days'),
                'description' => tra('Number of day in past to look for modified pages. Defaults to "356"'),
                'filter' => 'int'
            ],
        ],
        'common_params' => ['nonums', 'rows']
    ];
}

/**
 * @param $mod_reference
 * @param $module_params
 */
function