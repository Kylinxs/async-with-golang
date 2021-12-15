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
"Global Setup" => "Usanidi wa Ulimwenguni",
"Mainly front-facing, configured by site admins" => "Inatazama mbele, iliyosanidiwa na wasimamizi wa tovuti",
"General Settings" => "Mipangilio ya Jumla",
"Global site configuration, date formats, etc." => "Usanidi wa tovuti kwa jumla, muundo wa tarehe, ...",
"Features" => "Vipengele",
"Switches for major features" => "Utekelezaji wa vipengele vikuu",
"Registration & Log in" => "Usajili & Ingia",
"Wizards" => "Wachawi",
"Wizards to help you set up your site" => "Wachawi kukusaidia kusanidi tovuti yako",
"Look & Feel" => "Mandhari & Uonekanaji",
"Theme selection, layout settings and UI effect controls" => "Uchaguzi wa mandhari, mipangilio ya mpangilio na udhibiti wa athari za UI",
// "Registration & Log in" => "Registration & Log in",
"User registration, remember me cookie settings and authentication methods" => "Usajili wa mtumiaji, unakumbuka mipangilio ya cookie na mbinu za uthibitisho",
"Modules" => "Moduli",
"Module appearance settings" => "Mipangilio ya mwonekano ya Moduli",
"Categories" => "Jamii",
"Settings and features for categories" => "Mipangilio na vipengele vya makundi",
"Search" => "Tafuta",
"Search configuration" => "Usanidi wa utafutaji",
"i18n" => "i18n",
"Internationalization and localization - multilingual features" => "Ujisishaji na ujanibishaji - vipengele vya lugha nyingi",
"Profiles" => "Wasifu",
"Repository configuration, browse and apply profiles" => "Usanidi ya hifadhi, kuvinjari na kutumia maelezo",
"Main Features" => "Sifa kuu",
"Core features - configured by site admins; instances and content created by site users" => "Vipengele vya msingi - vilivyoundwa na wasimamizi wa tovuti; matukio na maudhui yaliyoundwa na watumiaji wa tovuti",
"Wiki" => "Wiki",
"Wiki page settings and features" => "Usanidi na vipengele ya kurasa za Wiki",
"Editing and Plugins" => "Uhariri na Plugins",
"Text editing settings applicable to many areas. Plugin activation and plugin alias management" => "Mipangilio ya uhariri wa maandishi inayotumika kwa maeneo mengi. Utekelezaji wa Plugin na usimamizi wa viungo vya programu",
"Wysiwyg" => "Wysiwyg",
"Options for WYSIWYG editor" => "Machaguo ya Kihariri cha WYSIWYG",
"File Galleries" => "Nyumba ya sanaa ya mafaili",
"Defaults and configuration for file galleries" => "Chaguo-msingi na Usanidi wa nyumba za faili",
"Blogs" => "Blogu",
"Settings for blogs" => "Mipangilio ya blogu",
"Calendar" => "Kalenda",
"Settings and features for calendars" => "Mipangilio na vipengele vya kalenda",
"Comments" => "Maoni",
"Comments settings" => "Mipangilio ya maoni",
"Articles" => "Makala",
"Settings and features for articles" => "Mipangilio na vipengele vya makala",
"Forums" => "Vikao",
"Settings and features for forums" => "Mipangilio na vipengele vya Mabaraza",
"Trackers" => "Trackers",
"Settings and features for trackers" => "Mipangilio na vipengele vya trackers",
"Miscellaneous" => "Kumba",
// "Other features - configured by site admins; instances and content created by site users" => "Other features - configured by site admins; instances and content created by site users",
"Meta Tags" => "Lebo za Meta",
"Information to include in the header of each page" => "Taarifa ya kujumuisha katika kichwa cha kila ukurasa",
"Workspaces" => "Nafasi za kazi",
"Configure workspace feature" => "Sanidi kipengele cha nafasi ya kazi",
"Copyright" => "Hakimiliki",
"Site-wide copyright information" => "Maelezo ya hakimiliki ya tovuti",
"Payment" => "Malipo",
// "Payment settings" => "Payment settings",
"Maps" => "Ramani",
"Settings and features for maps" => "Mipangilio na vipengee vya ramani",
// "Video" => "Video",
"Video integration configuration" => "Usanidi wa ushirikiano wa video",
"Print Settings" => "Mipangilio ya Magazeti",
"Settings and features for print versions and pdf generation" => "Mipangilio na vipengele vya matoleo ya magazeti na kizazi cha pdf",
// "Semantic Links" => "Semantic Links",
"Manage semantic wiki links" => "Dhibiti viungo vya wiki",
"Feeds" => "mtiririko",
"Outgoing RSS feed setup" => "Mapangilio yanayomalizwa muda na RSS feeds",
"Banners" => "Mabango",
// "Site advertisements and notices" => "Site advertisements and notices",
// "Users & Community" => "Users & Community",
// "Configured or content created by site users" => "Configured or content created by site users",
"User Settings" => "Mipangilio ya Mtumiaji",
"User related preferences like info and picture, features, messages and notification, files, etc" => "Mapendekezo yanayohusiana na mtumiaji kama habari na picha, vipengele, ujumbe na taarifa, faili, nk",
"Rating" => "Upimaji",
// "Rating settings" => "Rating settings",
"Score" => "Mfumo wa tathmini",
"Values of actions for users rank score" => "Thamani ya matendo kwa watumiaji cheo alama",
"Tags" => "Vitambulisho",
"Settings and features for tags" => "Mipangilio na vipengele vya vitambulisho",
"Polls" => "Uchaguzi",
"Settings and features for polls" => "Mipangilio na vipengele vya Uchaguzi",
"Directory" => "Orodha",
"Settings and features for directory of links" => "Mipangilio na vipengele vya directory ya viungo",
"FAQs" => "FAQs",
"Settings and features for FAQs" => "Mipangilio na vipengele vya Maswali yanayoulizwa sana (FAQs)",
// "RTC" => "RTC",
"Real-time collaboration tools" => "Vifaa vya kushirikiana wakati halisi",
"Share" => "Kushiriki",
"Configure share feature" => "Sanidi kipengele cha kushiriki",
"Community" => "Jamii",
"User specific features and settings" => "Vipengele maalum na mipangilio ya mtumiaji",
"Social networks" => "Mitandao ya kijamii",
"Configure social networks integration" => "Sanidi mitandao ya ushirikiano wa kijamii",
"Messages" => "Ujumbe",
"Message settings" => "Mipangilio ya ujumbe",
"Connect" => "Unganisha",
"Tiki Connect - join in!" => "Tiki Connect - Jiunge!",
"Advanced" => "Kikubwa",
// "Backend management - only the site admins access" => "Backend management - only the site admins access",
"InterTiki" => "InterTiki",
"Set up links between Tiki servers" => "Sanidi viungo kati ya seva za Tiki",
"Webservices" => "Huduma za Mtandao",
"Register and manage web services" => "Jisajili na udhibiti huduma za wavuti",
// "SEF URLs" => "SEF URLs",
"Search Engine Friendly URLs" => "URL za Injini za Kutafuta",
"Performance" => "Utendaji",
"Server performance settings" => "Mipangilio ya seva ya utendaji",
"Security" => "Usalama",
"Site security settings" => "Vipimo vya usalama wa tovuti",
"Statistics" => "Takwimu",
"Configure statistics reporting for your site usage" => "Sanidi ripoti za takwimu kwa matumizi ya tovuti yako",
"Packages" => " Vifurushi",
"External packages installation and management" => "Ufungashaji wa nje na usimamizi wa nje",
"General" => "Jenerali",
"Log in" => "Uhusiano",
// "Meta-Tags" => "Meta-Tags",
"Webmail" => "Webmail",
// "Webmail settings" => "Webmail settings",
// "SEF URL" => "