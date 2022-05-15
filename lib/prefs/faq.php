<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function prefs_faq_list()
{
    return [
        'faq_comments_per_page' => [
            'name' => tra('Default number of comments per page'),
            'description' => tra('Maximum number of comments to display on each page. Users may override this number.'),
            'type' => 'text',
            'units' => tra('comments'),
            'size' => '5',
            'default' => 10,
        ],
        'faq_comments_default_ordering' => [
            'name' => tra('Default order of comments'),
            'description' => tra('In which order to list the comments on the page.'),
            'type' => 'list',
            'options' => [
                'commentDate_desc' => tra('Newest first'),
                'commentDate_asc' => tra('Oldest first'),
                'points_desc' => tra('Points'),
            ],
            'default' => 'points_desc',
        ],
        'faq_prefix' => [
            'name' => tra('Prefix for answers'),
            'description' => tra('The prefix for that Tiki should display for each FAQ answer.'),
            'type' => 'list',
            'options' => [
                'none' => tra('None'),
                'QA' => tra('Q and A'),
                'question_id' => tra('Question ID'),
            ],
            'default' => 'QA',
        ],
        'faq_feature_copyrights' => [
            'name' => tra('FAQ copyright'),
            'description' => tra('Apply copyright management preferences to this feature.'),
            'type' => 'flag',
            'dependencies' => [
                'feature_faqs',
            ],
            'default' => 'n',
        ],
    ];
}
