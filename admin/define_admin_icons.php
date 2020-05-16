
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER["SCRIPT_NAME"], basename(__FILE__)) !== false) {
    header("location: index.php");
    exit;
}

global $prefs, $admin_icons;

if ($prefs['theme_unified_admin_backend'] === 'y') {
    $admin_icons = [
        'general'           => [
            'title'       => tr('Global Setup'),
            'description' => tr('Mainly front-facing, configured by site admins.'),
            'icon'        => 'admin_section_general',
            'children'    => [
                'general'  => [
                    'title'       => tr('General Settings'),
                    'description' => tr('Global site configuration, date formats, etc.'),
                    'help'        => 'General-Admin',
                ],
                'features' => [
                    'title'       => tr('Features'),
                    'description' => tr('Switches for major features'),
                    'help'        => 'Features-Admin',
                ],
                'wizard' => [
                    'title'       => tr('Wizards'),
                    'description' => tr('Wizards to help you set up your site'),
                    'help'        => 'Admin-Wizards',
                    'url'         => 'tiki-wizard_admin.php',
                ],
                'look'     => [
                    'title'       => tr('Look & Feel'),
                    'description' => tr('Theme selection, layout settings and UI effect controls'),
                    'help'        => 'Look-and-Feel',
                ],
                'login'       => [
                    'title'       => tr('Registration & Log in'),
                    'description' => tr('User registration, remember me cookie settings and authentication methods'),
                    'help'        => 'Login-Config',
                ],
                'module'   => [
                    'title'       => tr('Modules'),
                    'description' => tr('Module appearance settings'),
                    'help'        => 'Module',
                ],
                'category' => [
                    'title'       => tr('Categories'),
                    'disabled'    => $prefs['feature_categories'] != 'y',
                    'description' => tr('Settings and features for categories'),
                    'help'        => 'Categories-Admin',
                ],
                'search'   => [
                    'title'       => tr('Search'),
                    'description' => tr('Search configuration'),
                    'help'        => 'Search',
                    'disabled'    => $prefs['feature_search'] !== 'y' && $prefs['feature_search_fulltext'] !== 'y',
                ],
                'i18n'     => [
                    'title'       => tr('i18n'),
                    'description' => tr('Internationalization and localization - multilingual features'),
                    'help'        => 'i18n',
                ],
                'profiles' => [
                    'title'       => tr('Profiles'),
                    'description' => tr('Repository configuration, browse and apply profiles'),
                    'help'        => 'Profiles',
                ],
            ],
        ],
        'content-main'      => [
            'title'       => tr('Main Features'),
            'description' => tr('Core features - configured by site admins; instances and content created by site users.'),
            'icon'        => 'admin_section_content',
            'children'    => [
                'wiki'     => [
                    'title'       => tr('Wiki'),
                    'disabled'    => $prefs['feature_wiki'] != 'y',
                    'description' => tr('Wiki page settings and features'),
                    'help'        => 'Wiki-Config',
                ],
                'textarea'  => [
                    'title'       => tr('Editing and Plugins'),
                    'description' => tr('Text editing settings applicable to many areas. Plugin activation and plugin alias management'),
                    'help'        => 'Text-area',
                ],
                'wysiwyg'   => [
                    'title'       => tr('WYSIWYG'),
                    'disabled'    => $prefs['feature_wysiwyg'] != 'y',
                    'description' => tr('Options for WYSIWYG editor'),
                    'help'        => 'WYSIWYG',
                ],
                'fgal'     => [
                    'title'       => tr('File Galleries'),
                    'disabled'    => $prefs['feature_file_galleries'] != 'y',
                    'description' => tr('Defaults and configuration for file galleries'),
                    'help'        => 'File-Gallery',
                ],
                'blogs'    => [
                    'title'       => tr('Blogs'),
                    'disabled'    => $prefs['feature_blogs'] != 'y',
                    'description' => tr('Settings for blogs'),
                    'help'        => 'Blog',
                ],
                'calendar' => [
                    'title'       => tr('Calendar'),
                    'disabled'    => $prefs['feature_calendar'] != 'y',
                    'description' => tr('Settings and features for calendars'),
                    'help'        => 'Calendar',
                ],
                'comments' => [
                    'title'       => tr('Comments'),
                    'description' => tr('Comments settings'),
                    'help'        => 'Comments',
                ],
                'articles' => [
                    'title'       => tr('Articles'),
                    'disabled'    => $prefs['feature_articles'] != 'y',
                    'description' => tr('Settings and features for articles'),
                    'help'        => 'Articles',
                ],
                'forums'   => [
                    'title'       => tr('Forums'),
                    'disabled'    => $prefs['feature_forums'] != 'y',
                    'description' => tr('Settings and features for forums'),
                    'help'        => 'Forums-Admin',
                ],
                'trackers' => [
                    'title'       => tr('Trackers'),
                    'disabled'    => $prefs['feature_trackers'] != 'y',
                    'description' => tr('Settings and features for trackers'),
                    'help'        => 'Trackers-Admin',
                ],
            ],
        ],
        'content-secondary' => [
            'title'       => tr('Miscellaneous'),
            'description' => tr('Other features - configured by site admins; instances and content created by site users.'),
            'icon'        => 'admin_section_other',
            'children'    => [
                'metatags'    => [
                    'title'       => tr('Meta Tags'),
                    'description' => tr('Information to include in the header of each page'),
                    'help'        => 'Meta-Tags',
                ],
                'workspace' => [
                    'title'       => tr('Workspaces'),
                    'disabled'    => $prefs['workspace_ui'] != 'y' && $prefs['feature_areas'] != 'y',
                    'description' => tr('Configure workspace feature'),
                    'help'        => 'Workspace',
                ],
                'copyright' => [
                    'title'       => tr('Copyright'),
                    'disabled'    => $prefs['feature_copyright'] != 'y',
                    'description' => tr('Site-wide copyright information'),
                    'help'        => 'Copyright',
                ],
                'payment'   => [
                    'title'       => tr('Payment'),
                    'disabled'    => $prefs['payment_feature'] != 'y',
                    'description' => tr('Payment settings'),
                    'help'        => 'Payment',
                ],
                'maps'      => [
                    'title'       => tr('Maps'),
                    'description' => tr('Settings and features for maps'),
                    'help'        => 'Maps',
                    'disabled'    => false,
                ],
                'video'     => [
                    'title'       => tr('Video'),
                    'disabled'    => $prefs['feature_kaltura'] != 'y',
                    'description' => tr('Video integration configuration'),
                    'help'        => 'Video-Admin',
                ],
                'print'     => [
                    'title'       => tr('Print Settings'),
                    'description' => tr('Settings and features for print versions and pdf generation'),
                    'help'        => 'Print-Setting-Admin',
                ],
                'semantic'  => [
                    'title'       => tr('Semantic Links'),
                    'disabled'    => $prefs['feature_semantic'] != 'y',
                    'description' => tr('Manage semantic wiki links'),
                    'help'        => 'Semantic-Admin',
                ],
                'rss'       => [
                    'title'       => tr('Feeds'),
                    'help'        => 'Feeds-User',
                    'description' => tr('Outgoing RSS feed setup'),
                ],
                'ads'       => [
                    'title'       => tr('Banners'),
                    'disabled'    => $prefs['feature_banners'] != 'y',
                    'description' => tr('Site advertisements and notices'),
                    'help'        => 'Banner-Admin',
                ],
            ],
        ],
        'community'         => [
            'title'       => tr('Users & Community'),
            'description' => tr('Configured or content created by site users.'),
            'icon'        => 'admin_section_community',
            'children'    => [
                'user'           => [
                    'title'       => tr('User Settings'),
                    'description' => tr('User related preferences like info and picture, features, messages and notification, files, etc'),
                    'help'        => 'User Settings',
                ],
                'rating'         => [
                    'title'       => tr('Rating'),
                    'help'        => 'Rating',
                    'description' => tr('Rating settings'),
                    'disabled'    => $prefs['wiki_simple_ratings'] !== 'y' && $prefs['wiki_comments_simple_ratings'] !== 'y'
                        && $prefs['comments_vote'] !== 'y'
                        && $prefs['rating_advanced'] !== 'y'
                        && $prefs['trackerfield_rating'] !== 'y'
                        && $prefs['article_user_rating'] !== 'y'
                        && $prefs['rating_results_detailed'] !== 'y'
                        && $prefs['rating_smileys'] !== 'y',
                ],
                'score'          => [
                    'title'       => tr('Score'),
                    'disabled'    => $prefs['feature_score'] != 'y',
                    'description' => tr('Values of actions for users rank score'),
                    'help'        => 'Score',
                ],
                'freetags'       => [
                    'title'       => tr('Tags'),
                    'disabled'    => $prefs['feature_freetags'] != 'y',
                    'description' => tr('Settings and features for tags'),
                    'help'        => 'Tags',
                ],
                'polls'          => [
                    'title'       => tr('Polls'),
                    'disabled'    => $prefs['feature_polls'] != 'y',
                    'description' => tr('Settings and features for polls'),
                    'help'        => 'Polls',
                ],
                'directory'      => [
                    'title'       => tr('Directory'),
                    'disabled'    => $prefs['feature_directory'] != 'y',
                    'description' => tr('Settings and features for directory of links'),
                    'help'        => 'Directory',
                ],
                'faqs'           => [
                    'title'       => tr('FAQs'),
                    'disabled'    => $prefs['feature_faqs'] != 'y',
                    'description' => tr('Settings and features for FAQs'),
                    'help'        => 'FAQ',
                ],
                'rtc'            => [
                    'title'       => tr('RTC'),
                    'description' => tr('Real-time collaboration tools'),
                    'help'        => 'RTC',
                ],
                'share'          => [
                    'title'       => tr('Share'),
                    'disabled'    => $prefs['feature_share'] != 'y',
                    'description' => tr('Configure share feature'),
                    'help'        => 'Share',
                ],
                'community'      => [
                    'title'       => tr('Community'),
                    'description' => tr('User specific features and settings'),
                    'help'        => 'Community',
                ],
                'socialnetworks' => [
                    'title'       => tr('Social networks'),
                    'disabled'    => $prefs['feature_socialnetworks'] != 'y',
                    'description' => tr('Configure social networks integration'),
                    'help'        => 'Social-Networks',
                ],
                'messages'       => [
                    'title'       => tr('Messages'),
                    'disabled'    => $prefs['feature_messages'] != 'y',
                    'description' => tr('Message settings'),
                    'help'        => 'Inter-User-Messages',
                ],
                'connect'     => [
                    'title'       => tr('Connect'),
                    'description' => tr('Tiki Connect - join in!'),
                    'help'        => 'Connect',
                ],
            ],
        ],
        'backend'           => [
            'title'       => tr('Advanced'),
            'description' => tr('Backend management - only the site admins access.'),
            'icon'        => 'cogs',
            'children'    => [
                'intertiki'   => [
                    'title'       => tr('InterTiki'),
                    'disabled'    => $prefs['feature_intertiki'] != 'y',
                    'description' => tr('Set up links between Tiki servers'),
                    'help'        => 'InterTiki',
                ],
                'webservices' => [
                    'title'       => tr('Webservices'),
                    'disabled'    => $prefs['feature_webservices'] != 'y',
                    'description' => tr('Register and manage web services'),
                    'help'        => 'WebServices',
                ],
                'sefurl'      => [
                    'title'       => tr('SEF URLs'),
                    'disabled'    => $prefs['feature_sefurl'] != 'y' && $prefs['feature_canonical_url'] != 'y',
                    'description' => tr('Search Engine Friendly URLs'),
                    'help'        => 'Search-Engine-Friendly-URL',
                ],
                'mautic'      => [
                    'title'       => tr('Marketing Automation'),
                    'disabled'    => $prefs['site_mautic_enable'] != 'y',
                    'description' => tr('Add Mautic Marketing Automation To Your Website'),
                    'help'        => 'Mautic',
                ],
                'performance' => [
                    'title'       => tr('Performance'),
                    'description' => tr('Server performance settings'),
                    'help'        => 'Performance',
                ],
                'security'    => [
                    'title'       => tr('Security'),
                    'description' => tr('Site security settings'),
                    'help'        => 'Security',
                ],
                'stats'       => [
                    'title'       => tr('Statistics'),
                    //      'disabled' => $prefs['feature_stats'] != 'y',
                    'description' => tr('Configure statistics reporting for your site usage'),
                    'help'        => 'Statistics-Admin',
                ],
                'packages'    => [
                    'title'       => tr('Packages'),
                    'description' => tr('External packages installation and management'),
                    'help'        => 'Packages',
                ],
                'orphanprefs' => [
                    'title'       => tr('Orphan Preferences'),
                    'description' => tr('Orphan (leftover) preferences'),
                    'help'        => 'Orphan preferences',
                ],
            ],
        ],
    ];
} else {
    $admin_icons = [
        "general" => [
            'title' => tr('General'),
            'description' => tr('Global site configuration, date formats, etc.'),
            'help' => 'General-Admin',
        ],
        "features" => [
            'title' => tr('Features'),
            'description' => tr('Switches for major features'),
            'help' => 'Features-Admin',
        ],
        "login" => [
            'title' => tr('Log in'),
            'description' => tr('User registration, remember me cookie settings and authentication methods'),
            'help' => 'Login-Config',
        ],
        "user" => [
            'title' => tr('User Settings'),
            'description' => tr('User related preferences like info and picture, features, messages and notification, files, etc'),
            'help' => 'User-Settings',
        ],
        "profiles" => [
            'title' => tr('Profiles'),
            'description' => tr('Repository configuration, browse and apply profiles'),
            'help' => 'Profiles',
        ],
        "look" => [
            'title' => tr('Look & Feel'),
            'description' => tr('Theme selection, layout settings and UI effect controls'),
            'help' => 'Look-and-Feel',
        ],
        "textarea" => [
            'title' => tr('Editing and Plugins'),
            'description' => tr('Text editing settings applicable to many areas. Plugin activation and plugin alias management'),
            'help' => 'Text-area',
        ],
        "module" => [
            'title' => tr('Modules'),
            'description' => tr('Module appearance settings'),
            'help' => 'Module',
        ],
        "i18n" => [
            'title' => tr('i18n'),
            'description' => tr('Internationalization and localization - multilingual features'),
            'help' => 'i18n',
        ],
        "metatags" => [
            'title' => tr('Meta Tags'),
            'description' => tr('Information to include in the header of each page'),
            'help' => 'Meta Tags',
        ],
        "maps" => [
            'title' => tr('Maps'),
            'description' => tr('Settings and features for maps'),
            'help' => 'Maps',
            'disabled' => false,
        ],
        "performance" => [
            'title' => tr('Performance'),
            'description' => tr('Server performance settings'),
            'help' => 'Performance',
        ],
        "security" => [
            'title' => tr('Security'),
            'description' => tr('Site security settings'),
            'help' => 'Security',
        ],
        "comments" => [
            'title' => tr('Comments'),
            'description' => tr('Comments settings'),
            'help' => 'Comments',
        ],
        "rss" => [
            'title' => tr('Feeds'),
            'help' => 'Feeds User',
            'description' => tr('Outgoing RSS feed setup'),
        ],
        "connect" => [
            'title' => tr('Connect'),
            'help' => 'Connect',
            'description' => tr('Tiki Connect - join in!'),
        ],
        "rating" => [
            'title' => tr('Rating'),
            'help' => 'Rating',
            'description' => tr('Rating settings'),
            'disabled' => $prefs['wiki_simple_ratings'] !== 'y' &&
                            $prefs['wiki_comments_simple_ratings'] !== 'y' &&
                            $prefs['comments_vote'] !== 'y' &&
                            $prefs['rating_advanced'] !== 'y' &&
                            $prefs['trackerfield_rating'] !== 'y' &&
                            $prefs['article_user_rating'] !== 'y' &&
                            $prefs['rating_results_detailed'] !== 'y' &&
                            $prefs['rating_smileys'] !== 'y',
        ],
        "search" => [
            'title' => tr('Search'),
            'description' => tr('Search configuration'),
            'help' => 'Search',
            'disabled' => $prefs['feature_search'] !== 'y' &&
                            $prefs['feature_search_fulltext'] !== 'y',
        ],
        "wiki" => [
            'title' => tr('Wiki'),
            'disabled' => $prefs['feature_wiki'] != 'y',
            'description' => tr('Wiki page settings and features'),
            'help' => 'Wiki-Config',
        ],
        "fgal" => [
            'title' => tr('File Galleries'),
            'disabled' => $prefs['feature_file_galleries'] != 'y',
            'description' => tr('Defaults and configuration for file galleries'),
            'help' => 'File Gallery',
        ],
        "blogs" => [
            'title' => tr('Blogs'),
            'disabled' => $prefs['feature_blogs'] != 'y',
            'description' => tr('Settings for blogs'),
            'help' => 'Blog',
        ],
        "articles" => [
            'title' => tr('Articles'),
            'disabled' => $prefs['feature_articles'] != 'y',
            'description' => tr('Settings and features for articles'),
            'help' => 'Articles',
        ],
        "forums" => [
            'title' => tr('Forums'),
            'disabled' => $prefs['feature_forums'] != 'y',
            'description' => tr('Settings and features for forums'),
            'help' => 'Forums-Admin',
        ],
        "trackers" => [
            'title' => tr('Trackers'),
            'disabled' => $prefs['feature_trackers'] != 'y',
            'description' => tr('Settings and features for trackers'),
            'help' => 'Trackers-Admin',
        ],
        "polls" => [
            'title' => tr('Polls'),
            'disabled' => $prefs['feature_polls'] != 'y',
            'description' => tr('Settings and features for polls'),
            'help' => 'Polls',
        ],
        "calendar" => [
            'title' => tr('Calendar'),
            'disabled' => $prefs['feature_calendar'] != 'y',
            'description' => tr('Settings and features for calendars'),
            'help' => 'Calendar',
        ],
        "category" => [
            'title' => tr('Categories'),
            'disabled' => $prefs['feature_categories'] != 'y',
            'description' => tr('Settings and features for categories'),
            'help' => 'Categories-Admin',
        ],
        "workspace" => [
            'title' => tr('Workspaces'),
            'disabled' => $prefs['workspace_ui'] != 'y' && $prefs['feature_areas'] != 'y',
            'description' => tr('Configure workspace feature'),
            'help' => 'Workspace',
        ],
        "score" => [
            'title' => tr('Score'),
            'disabled' => $prefs['feature_score'] != 'y',
            'description' => tr('Values of actions for users rank score'),
            'help' => 'Score',
        ],
        "freetags" => [
            'title' => tr('Tags'),
            'disabled' => $prefs['feature_freetags'] != 'y',
            'description' => tr('Settings and features for tags'),
            'help' => 'Tags',
        ],
        "faqs" => [
            'title' => tr('FAQs'),
            'disabled' => $prefs['feature_faqs'] != 'y',
            'description' => tr('Settings and features for FAQs'),
            'help' => 'FAQ',
        ],
        "directory" => [
            'title' => tr('Directory'),
            'disabled' => $prefs['feature_directory'] != 'y',
            'description' => tr('Settings and features for directory of links'),
            'help' => 'Directory',
        ],
        "copyright" => [
            'title' => tr('Copyright'),
            'disabled' => $prefs['feature_copyright'] != 'y',
            'description' => tr('Site-wide copyright information'),
            'help' => 'Copyright',
        ],
        "messages" => [
            'title' => tr('Messages'),
            'disabled' => $prefs['feature_messages'] != 'y',
            'description' => tr('Message settings'),
            'help' => 'Inter-User-Messages',
        ],
        "webmail" => [
            'title' => tr('Webmail'),
            'disabled' => $prefs['feature_webmail'] != 'y',
            'description' => tr('Webmail settings'),
            'help' => 'Webmail',
            'url' => 'tiki-webmail.php?page=settings'
        ],
        "wysiwyg" => [
            'title' => tr('Wysiwyg'),
            'disabled' => $prefs['feature_wysiwyg'] != 'y',
            'description' => tr('Options for WYSIWYG editor'),
            'help' => 'Wysiwyg',
        ],
        "ads" => [
            'title' => tr('Banners'),
            'disabled' => $prefs['feature_banners'] != 'y',
            'description' => tr('Site advertisements and notices'),
            'help' => 'Banner-Admin',
        ],
        "intertiki" => [
            'title' => tr('InterTiki'),
            'disabled' => $prefs['feature_intertiki'] != 'y',
            'description' => tr('Set up links between Tiki servers'),
            'help' => 'InterTiki',
        ],
        "semantic" => [
            'title' => tr('Semantic Links'),
            'disabled' => $prefs['feature_semantic'] != 'y',
            'description' => tr('Manage semantic wiki links'),
            'help' => 'Semantic-Admin',
        ],
        "webservices" => [
            'title' => tr('Webservices'),
            'disabled' => $prefs['feature_webservices'] != 'y',
            'description' => tr('Register and manage web services'),
            'help' => 'WebServices',
        ],
        "sefurl" => [
            'title' => tr('SEF URL'),
            'disabled' => $prefs['feature_sefurl'] != 'y' && $prefs['feature_canonical_url'] != 'y',
            'description' => tr('Search Engine Friendly URLs'),
            'help' => 'Search-Engine-Friendly-URL',
        ],
        "video" => [
            'title' => tr('Video'),
            'disabled' => $prefs['feature_kaltura'] != 'y',
            'description' => tr('Video integration configuration'),
            'help' => 'Video-Admin',
        ],
        "payment" => [
            'title' => tr('Payment'),
            'disabled' => $prefs['payment_feature'] != 'y',
            'description' => tr('Payment settings'),
            'help' => 'Payment',
        ],
        "socialnetworks" => [
            'title' => tr('Social networks'),
            'disabled' => $prefs['feature_socialnetworks'] != 'y',
            'description' => tr('Configure social networks integration'),
            'help' => 'Social-Networks',
        ],
        "community" => [
            'title' => tr('Community'),
            'description' => tr('User specific features and settings'),
            'help' => 'Community',
        ],
        "share" => [
            'title' => tr('Share'),
            'disabled' => $prefs['feature_share'] != 'y',
            'description' => tr('Configure share feature'),
            'help' => 'Share',
        ],
        "stats" => [
            'title' => tr('Statistics'),
    //      'disabled' => $prefs['feature_stats'] != 'y',
            'description' => tr('Configure statistics reporting for your site usage'),
            'help' => 'Statistics-Admin',
        ],
        "print" => [
            'title' => tr('Print Settings'),
            'description' => tr('Settings and features for print versions and pdf generation'),
            'help' => 'Print-Setting-Admin',
        ],
        "packages" => [
            'title' => tr('Packages'),
            'description' => tr('External packages installation and management'),
            'help' => 'Packages',
        ],
        "rtc" => [
            'title' => tr('RTC'),
            'description' => tr('Real-time collaboration tools'),
            'help' => 'RTC',
        ],
        "orphanprefs" => [
            'title' => tr('Orphan Preferences'),
            'description' => tr('Orphan (leftover) preferences'),
            'help' => 'Orphan preferences',
        ],
    ];
}