<?php

/**
 * @package tikiwiki
 */

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

// If you put some traces in this script, and can't see them
// because the script automatically forwards to another URL
// with a call to header(), then you will not see the traces
// If you want to see the traces, set value below to true.
// WARNING: DO NOT COMMIT WITH TRUE!!!!
$dieInsteadOfForwardingWithHeader = false;
global $prefs;
require_once('lib/debug/Tracer.php');

$inputConfiguration = [
    [ 'staticKeyFilters' => [
        'page' => 'pagename',
        'returnto' => 'pagename',
        'watch' => 'digits',
    ] ],
];

$section = "wiki page";
$section_class = "tiki_wiki_page manage";   // This will be body class instead of $section
require_once('tiki-setup.php');
$wikilib = TikiLib::lib('wiki');
$structlib = TikiLib::lib('struct');
$notificationlib = TikiLib::lib('notification');
$editlib = TikiLib::lib('edit');

/**
 * @param $page
 * @param $page_info
 */
function guess_new_page_attributes_from_parent_pages($page, $page_info)
{
    global $prefs, $need_lang;
    $editlib = TikiLib::lib('edit');
    $tikilib = TikiLib::lib('tiki');
    $smarty = TikiLib::lib('smarty');

    if (! $page_info) {
        //
        // This is a new page being created. See if we can guess some of its attributes
        // (ex: language) based on those of its parent pages.
        //
        $new_page_inherited_attributes =
            $editlib->get_new_page_attributes_from_parent_pages($page, $page_info);
        if (
            $editlib->user_needs_to_specify_language_of_page_to_be_created($page, $page_info)
            && isset($new_page_inherited_attributes['lang'])
        ) {
            //
            // Language is not set yet, but it COULD be guessed from parent pages.
            // So, set it.
            //
            $_REQUEST['lang'] = $new_page_inherited_attributes['lang'];
        }
        if ($editlib->user_needs_to_specify_language_of_page_to_be_created($page, $page_info, $new_page_inherited_attributes)) {
            //
            // Language of new page was not defined, and could not be guessed from the
            // parent pages. User will have to specify it explicitly.
            //
            $langLib = TikiLib::lib('language');
            $languages = $langLib->list_languages(false, true);
            if (count($languages) === 1) {
                $_REQUEST['lang'] = $languages[0]['value'];
                $need_lang = false;
            } else {
                if ($prefs['wiki_default_language'] !== '') {
                    $_REQUEST['lang'] = $prefs['wiki_default_language'];
                    $need_lang = false;
                } else {
                    $smarty->assign('languages', $languages);
                    $smarty->assign('default_lang', $prefs['language']);
                    $need_lang = true;
                    $smarty->assign('_REQUEST', $_REQUEST);
                }
            }
        }
    }
}

/**
 * @param $page_id
 * @return bool
 */
function translationsToThisPageAreInProgress($page_id)
{
    $multilinguallib = TikiLib::lib('multilingual');

    $translations_in_progress = $multilinguallib->getTranslationsInProgressFlags($page_id);
    $answer = count($translations_in_progress) > 0;
    return $answer;
}

function execute_module_translation()
{
    $smarty = TikiLib::lib('smarty');
// will show the language of the available translations. Chnage to 'n' to show the page name
    $params['show_language'] = 'y';
// flag to indicate that the module is appearing within the notification area of the edit page
    $params['from_edit_page'] = 'y';
    $params['nobox'] = 'y';
    $module_reference = [
        'name' => 'translation',
            'params' => [ 'show_language' => $params['show_language'], 'from_edit_page' => $params['from_edit_page'], 'nobox' => $params['nobox'] ]
    ];

    $modlib = TikiLib::lib('mod');

    $out = $modlib->execute_module($module_reference);
    $smarty->assign('cont