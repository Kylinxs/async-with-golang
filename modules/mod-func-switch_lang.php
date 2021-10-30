
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * @return array
 */
function module_switch_lang_info()
{
    return [
        'name' => tra('Switch Language'),
        'description' => tra('Displays a language picker to change the language of the site.'),
        'prefs' => ['feature_multilingual', 'change_language'],
        'params' => [
            'mode' => [
                'name' => tra('Display mode'),
                'description' => tra('Changes how the list of languages is displayed. Possible values are droplist, flags and words. Defaults to droplist.'),
                'filter' => 'alpha',
            ],
        ],
    ];
}

/**
 * @param $mod_reference
 * @param $module_params
 */
function module_switch_lang($mod_reference, $module_params)
{
    global $prefs, $user, $flagmapping;

    $smarty = TikiLib::lib('smarty');
    $tikilib = TikiLib::lib('tiki');

    $frontendLang = $prefs['language'];
    if (! empty($user)) {
        $userlib = TikiLib::lib('user');
        $userLang = $userlib->get_user_preference($user, 'language');
        $frontendLang = ! empty($userLang) ? $userLang : $prefs['site_language'];
    }

    // tiki-setup has already set the $language variable
    //Create a list of languages
    $languages = [];
    $langLib = TikiLib::lib('language');
    $languages = $langLib->list_languages(false, 'n');
    $mode = isset($module_params["mode"]) ? $module_params["mode"] : "droplist";
    $smarty->assign('mode', $mode);
    if ($mode == 'flags' || $mode == 'words' || $mode == 'abrv') {
        include('lang/flagmapping.php');
        global $pageRenderer;
        //$trads = $multilinguallib->getTranslations('wiki page', $page_id, $page, $prefs['language']);

        for ($i = 0, $icount_languages = count($languages); $i < $icount_languages; $i++) {
            if (isset($flagmapping[$languages[$i]['value']])) {
                $languages[$i]['flag'] = $flagmapping[$languages[$i]['value']][0];
            } else {
                $languages[$i]['flag'] = '';
            }
            if (isset($pageRenderer) && ! empty($pageRenderer->trads)) {
                $languages[$i]['class'] = ' unavailable';
                for ($t = 0, $tcount_pageR = count($pageRenderer->trads); $t < $tcount_pageR; $t++) {
                    if ($pageRenderer->trads[$t]['lang'] == $languages[$i]['value']) {
                        $languages[$i]['class'] = ' available';
                    }
                }
            } else {
                $languages[$i]['class'] = '';
            }
            if ($languages[$i]['value'] == $frontendLang) {
                $languages[$i]['class'] .= ' highlight';
            }
        }
    }
    $smarty->assign_by_ref('languages', $languages);
    $smarty->assign_by_ref('frontendLang', $frontendLang);
}