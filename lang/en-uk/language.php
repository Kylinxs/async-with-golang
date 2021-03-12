<?php
// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

// The original strings (English) are case-sensitive.

/* Note for translators about translation of text ending with punctuation
 *
 * The current list of concerned punctuation can be found in 'lib/init/tra.php'
 * On 2009-03-02, it is: (':', '!', ';', '.', ',', '?')
 * For clarity, we explain here only for colons: ':' but it is the same for the rest
 *
 * Short version: it is not a problem that string "Login:" has no translation. Only "Login" needs to be translated.
 *
 * Technical justification:
 * If a string ending with colon needs translating (like "{tr}Login:{/tr}")
 * then Tiki tries to translate 'Login' and ':' separately.
 * This allows to have only one translation for "{tr}Login{/tr}" and "{tr}Login:{/tr}"
 * and it still allows to translate ":" as " :" for languages that
 * need it (like French)
 * Note: the difference is invisible but " :" has an UTF-8 non-breaking-space, not a regular space, but the UTF-8 equivalent of the HTML &nbsp;.
 * This allows correctly displaying emails and JavaScript messages, not only web pages as would happen with &nbsp;.
 */
include('lang/en/language.php'); // Needed for providing a sensible default text for untranslated strings with context like : "edit_C(verb)"
$lang_current = array(
// "Global Setup" => "Global Setup",
// "Mainly front-facing, configured by site admins" => "Mainly front-facing, configured by site admins",
// "General Settings" => "General Settings",
// "Global site configuration, date formats, etc" => "Global site configuration, date formats, etc",
// "Features" => "Features",
// "Switches for major features" => "Switches for major features",
// "Wizards" => "Wizards",
// "Wizards to help you set up your site" => "Wizards to help you set up your site",
// "Look & Feel" => "Look & Feel",
// "Theme selection, layout settings and UI effect controls" => "Theme selection, layout settings and UI effect controls",
// "Registration & Log in" => "Registration & Log in",
// "User registration, remember me cookie settings and authentication methods" => "User registration, remember me cookie settings and authentication methods",
// "Modules" => "Modules",
// "Module appearance settings" => "Module appearance settings",
// "Categories" => "Categories",
// "Settings and features for categories" => "Settings and features for categories",
// "Search" => "Search",
// "Search configuration" => "Search configuration",
// "i18n" => "i18n",
"Internationalization and localization - multilingual features" => "Internationalisation and localisation - multilingual features",
// "Profiles" => "Profiles",
// "Repository configuration, browse and apply profiles" => "Repository configuration, browse and apply profiles",
// "Main Features" => "Main Features",
// "Core features - configured by site admins; instances and content created by site users" => "Core features - configured by site admins; instances and content created by site users",
// "Wiki" => "Wiki",
// "Wiki page settings and features" => "Wiki page settings and features",
// "Editing and Plugins" => "Editing and Plugins",
// "Text editing settings applicable to many areas. Plugin activation and plugin alias management" => "Text editing settings applicable to many areas. Plugin activation and plugin alias management",
// "Wysiwyg" => "Wysiwyg",
// "Options for WYSIWYG editor" => "Options for WYSIWYG editor",
// "File Galleries" => "File Galleries",
// "Defaults and configuration for file galleries" => "Defaults and configuration for file galleries",
// "Blogs" => "Blogs",
// "Settings for blogs" => "Settings for blogs",
// "Calendar" => "Calendar",
// "Settings and features for calendars" => "Settings and features for calendars",
// "Comments" => "Comments",
// "Comments settings" => "Comments settings",
// "Articles" => "Articles",
// "Settings and features for articles" => "Settings and features for articles",
// "Forums" => "Forums",
// "Settings and features for forums" => "Settings and features for forums",
// "Trackers" => "Trackers",
// "Settings and features for trackers" => "Settings and features for trackers",
// "Miscellaneous" => "Miscellaneous",
// "Other features - configured by site admins; instances and content created by site users" => "Other features - configured by site admins; instances and content created by site users",
// "Meta Tags" => "Meta Tags",
// "Information to include in the header of each page" => "Information to include in the header of each page",
// "Workspaces" => "Workspaces",
// "Configure workspace feature" => "Configure workspace feature",
// "Copyright" => "Copyright",
// "Site-wide copyright information" => "Site-wide copyright information",
// "Payment" => "Payment",
// "Payment settings" => "Payment settings",
// "Maps" => "Maps",
// "Settings and features for maps" => "Settings and features for maps",
// "Video" => "Video",
// "Video integration configuration" => "Video integration configuration",
// "Print Settings" => "Print Settings",
// "Settings and features for print versions and pdf generation" => "Settings and features for print versions and pdf generation",
// "Semantic Links" => "Semantic Links",
// "Manage semantic wiki links" => "Manage semantic wiki links",
// "Feeds" => "Feeds",
// "Outgoing RSS feed setup" => "Outgoing RSS feed setup",
// "Banners" => "Banners",
// "Site advertisements and notices" => "Site advertisements and notices",
// "Users & Community" => "Users & Community",
// "Configured or content created by site users" => "Configured or content created by site users",
// "User Settings" => "User Settings",
// "User related preferences like info and picture, features, messages and notification, files, etc" => "User related preferences like info and picture, features, messages and notification, files, etc",
// "Rating" => "Rating",
// "Rating settings" => "Rating settings",
// "Score" => "Score",
// "Values of actions for users rank score" => "Values of actions for users rank score",
// "Tags" => "Tags",
// "Settings and features for tags" => "Settings and features for tags",
// "Polls" => "Polls",
// "Settings and features for polls" => "Settings and features for polls",
// "Directory" => "Directory",
// "Settings and features for directory of links" => "Settings and features for directory of links",
// "FAQs" => "FAQs",
// "Settings and features for FAQs" => "Settings and features for FAQs",
// "RTC" => "RTC",
// "Real-time collaboration tools" => "Real-time collaboration tools",
// "Share" => "Share",
// "Configure share feature" => "Configure share feature",
// "Community" => "Community",
// "User specific features and settings" => "User specific features and settings",
// "Social networks" => "Social networks",
// "Configure social networks integration" => "Configure social networks integration",
// "Messages" => "Messages",
// "Message settings" => "Message settings",
// "Connect" => "Connect",
// "Tiki Connect - join in" => "Tiki Connect - join in",
// "Advanced" => "Advanced",
// "Backend management - only the site admins access" => "Backend management - only the site admins access",
// "InterTiki" => "InterTiki",
// "Set up links between Tiki servers" => "Set up links between Tiki servers",
// "Webservices" => "Webservices",
// "Register and manage web services" => "Register and manage web services",
// "SEF URLs" => "SEF URLs",
// "Search Engine Friendly URLs" => "Search Engine Friendly URLs",
// "Performance" => "Performance",
// "Server performance settings" => "Server performance settings",
// "Security" => "Security",
// "Site security settings" => "Site security 