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
    $smarty->assign('content_of_update_translation_section', $out);
}

function possibly_set_pagedata_to_pretranslation_of_source_page()
{
    global $tracer;
    $multilinguallib = TikiLib::lib('multilingual');
    $smarty = TikiLib::lib('smarty');
    $editlib = TikiLib::lib('edit');

    if ($editlib->isNewTranslationMode()) {
        $source_page = $_REQUEST['source_page'];
        $possibly_pretranslated_content = $multilinguallib->partiallyPretranslateContentOfPage($source_page, $_REQUEST['lang']);
        $smarty->assign('pagedata', $possibly_pretranslated_content);
    }
}


$access->check_feature('feature_wiki');

if ($editlib->isNewTranslationMode() || $editlib->isUpdateTranslationMode()) {
    $translation_mode = 'y';
    $multilinguallib = TikiLib::lib('multilingual');
} else {
    $translation_mode = 'n';
}
$smarty->assign('translation_mode', $translation_mode);

// If page is blank (from quickedit module or wherever) tell user -- instead of editing the default page
// Dont get the page from default HomePage if not set (surely this would always be an error?)
if (empty($_REQUEST["page"])) {
    $smarty->assign('msg', tra("You must specify a page name, it will be created if it doesn't exist."));
    $smarty->display("error.tpl");
    die;
}

$max_pagename_length = TikiLib::lib('wiki')->max_pagename_length();
if (mb_strlen($_REQUEST["page"]) > $max_pagename_length) {
    //$_REQUEST["page"] = substr($_REQUEST["page"], 0, $max_pagename_length);
    $smarty->assign('msg', tra(tr("You have exceeded the number of characters allowed (158 max) for the page name field")));
    $smarty->display("error.tpl");
    die;
}

if (strtolower($_REQUEST["page"]) == 'sandbox' && $prefs['feature_sandbox'] !== 'y') {
    $smarty->assign('msg', tra("You can’t name a page 'Sandbox' because it is reserved for the Sandbox feature"));
    $smarty->display("error.tpl");
    die;
}

$page = $_REQUEST["page"];

if (isset($_REQUEST["description"])) {
    $max_pagedescription_length = 201;
    if (mb_strlen($_REQUEST["description"]) > $max_pagedescription_length) {
        $smarty->assign('msg', tra("The description of the page should not exceed 200 characters."));
        $smarty->display("error.tpl");
        die;
    }
}

// Copy namespace from structure parent page
if ($prefs['namespace_enabled'] === 'y') {
    if (isset($_REQUEST['current_page_id'])) {
        $s_page_info = $structlib->s_get_page_info($_REQUEST['current_page_id']);
        $s_suffix = '';
        if (isset($prefs['namespace_separator']) && ! empty($prefs['namespace_separator']) && strpos($s_page_info['pageName'], $prefs['namespace_separator']) !== false) {
            $split = explode($prefs['namespace_separator'], $s_page_info['pageName']);
            $s_suffix = reset($split);
        }
    }
}
if (! empty($s_suffix)) {
    $_REQUEST['namespace'] = $s_suffix;
}

if ($prefs['namespace_enabled'] == 'y' && isset($_REQUEST['namespace'])) {
    // Only prepend the namespace separator, if the page is missing a namespace
    $ns = $_REQUEST['namespace'] . $prefs['namespace_separator'];
    if (strpos($page, $ns, 0) === false) {
        $page = $ns . $page;
    }
}

$smarty->assign('page', $page);
$info = $tikilib->get_page_info($page);
$smarty->assign('quickedit', isset($_GET['quickedit']));

// 2010-01-26: Keep in active until translation refactoring is done.
if ($editlib->isNewTranslationMode() || $editlib->isUpdateTranslationMode()) {
     $editlib->prepareTranslationData();
}
$editlib->make_sure_page_to_be_created_is_not_an_alias($page, $info);
guess_new_page_attributes_from_parent_pages($page, $info);

if ($translation_mode === 'n' && isset($info['page_id']) ? translationsToThisPageAreInProgress($info['page_id']) : false) {
    $smarty->assign('prompt_for_edit_or_translate', 'y');
    include_once('modules/mod-func-translation.php');
    execute_module_translation();
} else {
    $smarty->assign('prompt_for_edit_or_translate', 'n');
}

// wysiwyg decision
include 'lib/setup/editmode.php';

$auto_query_args = ['wysiwyg','page_id','page', 'returnto', 'lang', 'hdr'];

$smarty->assign('page', $page);
// Permissions - first is it a new page to be inserted into structure?
if (isset($_REQUEST["current_page_id"]) && empty($info)) {
    if (empty($_REQUEST['page'])) {
        $smarty->assign('msg', tra("You must specify a page name, it will be created if it doesn't exist."));
        $smarty->display("error.tpl");
        die;
    }

    $structure_info = $structlib->s_get_structure_info($_REQUEST['current_page_id']);
    if (
        ($tiki_p_edit != 'y' && ! $tikilib->user_has_perm_on_object($user, $structure_info["pageName"], 'wiki page', 'tiki_p_edit'))
        ||
        (($tiki_p_edit_structures != 'y' &&
            ! $tikilib->user_has_perm_on_object($user, $structure_info["pageName"], 'wiki page', 'tiki_p_edit_structures')))
    ) {
        $smarty->assign('errortype', 401);
        $smarty->assign('msg', tra("You do not have permission to edit this page."));
        $smarty->display("error.tpl");
        die;
    }

    $smarty->assign('current_page_id', $_REQUEST["current_page_id"]);
    if (isset($_REQUEST["add_child"])) {
        $smarty->assign('add_child', "true");
    }
} else {
    $structure_info = [];
    $smarty->assign('current_page_id', 0);
    $smarty->assign('add_child', false);
}
$tikilib->get_perm_object($page, 'wiki page', $info, true);
if ($tiki_p_edit !== 'y' && (! empty($info) || empty($structure_info))) {
    if (empty($user)) {
        $cachelib = TikiLib::lib('cache');
        $cacheName = $tikilib->get_ip_address() . $tikilib->now;
        $cachelib->cacheItem($cacheName, http_build_query($_REQUEST, '', '&'), 'edit');
        $smarty->assign('urllogin', "tiki-editpage.php?cache=$cacheName");
    }
    $smarty->assign('errortype', 401);
    $smarty->assign('msg', tra("You do not have permission to edit this page."));
    $smarty->display("error.tpl");
    die;
}
// Anti-bot feature: if enabled, anon user must type in a code displayed in an image
if (isset($_REQUEST['save']) && (! $user || $user === 'anonymous') && $prefs['feature_antibot'] === 'y') {
    if (! $captchalib->validate()) {
        $smarty->assign('errortype', 'no_redirect_login');
        $smarty->assign('msg', $captchalib->getErrors());
        $smarty->display("error.tpl");
        die;
    }
}

$page_ref_id = '';
if (isset($_REQUEST["page_ref_id"])) {
    $page_ref_id = $_REQUEST["page_ref_id"];
}

$smarty->assign('page_ref_id', $page_ref_id);

/**
 * @param $a1
 * @param $a2
 * @return mixed
 */
function compare_import_versions($a1, $a2)
{
    return $a1["version"] - $a2["version"];
}

$serviceLib = TikiLib::lib('service');
if (isset($_REQUEST['cancel_edit'])) {
    if ($prefs['feature_warn_on_edit'] === 'y') {
        $serviceLib->internal('semaphore', 'unset', ['object_id' => $page]);
    }
    if (! empty($_REQUEST['returnto'])) {
        if (isURL($_REQUEST['returnto'])) {
            $url = $_REQUEST['returnto'];
        } else {
            // came from wikiplugin_include.php edit button
            $url = $wikilib->sefurl($_REQUEST['returnto']);
        }
    } else {
        $url = $wikilib->sefurl($page);
        if (! empty($_REQUEST['page_ref_id'])) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . 'page_ref_id=' . $_REQUEST['page_ref_id'];
        }
    }

    if ($prefs['feature_multilingual'] === 'y' && $prefs['feature_best_language'] === 'y' && isset($info['lang']) && $info['lang'] !== $prefs['language']) {
        $url .= (strpos($url, '?') === false ? '?' : '&') . 'no_bl=y';
    }

    if ($dieInsteadOfForwardingWithHeader) {
        die("-- tiki-editpage: Dying before first call to header(), so we can see traces. Forwarding to: \$url='$url'");
    }
    $access->redirect($url);
}
if (isset($_REQUEST['minor'])) {
    $_REQUEST['isminor'] = 'on';
    $_REQUEST['save'] = true;
}

if ($user && $prefs['feature_user_watches'] === 'y') {
    $isFormSubmit = isset($jitRequest['edit']);
    if ($tikilib->page_exists($page)) {
        $currentlyWatching = (bool) $tikilib->user_watches($user, 'wiki_page_changed', $page, 'wiki page');
        $default = $currentlyWatching;
    } else {
        // New pages get default watch checked for authors
        $currentlyWatching = false;
        $default = ($prefs['wiki_watch_author'] === 'y');
    }

    $requestedWatch = isset($_REQUEST['watch']) && $isFormSubmit;
    $smarty->assign('show_watch', 'y');
    $smarty->assign('watch_checked', ( ($default && ! $isFormSubmit) || $requestedWatch) ? 'y' : 'n');
} else {
    $currentlyWatching = false;
    $requestedWatch = false;
    $smarty->assign('show_watch_controls', 'n');
}

if (isset($_REQUEST['partial_save'])) {
    $_REQUEST['save'] = true;
}

if (isset($_REQUEST['hdr'])) {
    $smarty->assign('hdr', $_REQUEST['hdr']);
}

if (isset($_REQUEST['pos'])) {
    $smarty->assign('pos', $_REQUEST['pos']);
}

if (isset($_REQUEST['cell'])) {
    $smarty->assign('cell', $_REQUEST['cell']);
}

// We set empty wiki page name as default here if not set (before including Tiki modules)
if ($prefs['feature_warn_on_edit'] === 'y') {
    $editpageconflict = 'n';
    $beingEdited = 'n';
    $semUser = '';
    $u = $user ? $user : 'anonymous';
    if (! empty($page) && ($page !== 'sandbox' || $page === 'sandbox' && $tiki_p_admin === 'y')) {
        if (! isset($_REQUEST['save'])) {
            if (
                $serviceLib->internal('semaphore', 'is_set', ['object_id' => $page]) &&
                $serviceLib->internal('semaphore', 'get_user', ['object_id' => $page]) !== $u &&
                ! $serviceLib->internal('semaphore', 'is_set', ['object_id' => 'togetherjs ' . $page])
            ) {
                $editpageconflict = 'y';
            } elseif ($tiki_p_edit === 'y') {
                $serviceLib->internal('semaphore', 'set', ['object_id' => $page]);
            }
            $semUser = $serviceLib->internal('semaphore', 'get_user', ['object_id' => $page]);
            $beingedited = 'y';
        } else {
            $serviceLib->internal('semaphore', 'unset', ['object_id' => $page]);
            $serviceLib->internal('semaphore', 'unset', ['object_id' => 'togetherjs ' . $page]);
        }
    }
    if ($editpageconflict === 'y' && ! isset($_REQUEST["conflictoverride"])) {
        include_once('lib/smarty_tiki/modifier.userlink.php');
        include_once('lib/smarty_tiki/modifier.username.php');
        $msg = tr("This page is being edited by %0. Please check with the user before editing the page,    otherwise the changes will be stored as two separate versions in the history and you will have to manually merge them later.", smarty_modifier_username($semUser));
        $msg .= '<br /><br /><a href="tiki-editpage.php?page=';
        $msg .= urlencode($page);
        $msg .= '&conflictoverride=y">' . tra('Override lock and carry on with edit') . '</a>';
        $smarty->assign('msg', $msg);
        $smarty->assign('errortitle', tra('Page is currently being edited'));
        $smarty->display("error.tpl");
        die;
    }
}
$included_by = $wikilib->get_external_includes($page);
if (sizeof($included_by) > 0) {
    $smarty->assign_by_ref('included_by', $included_by);
}

$recursive_include = in_array($page, array_column($included_by, 'itemId'));
if ($recursive_include) {
    $smarty->assign('recursive_include', 'y');
}

$category_needed = false;
$contribution_needed = false;
if (isset($_REQUEST['lock_it']) && $_REQUEST['lock_it'] === 'on') {
    $lock_it = 'y';
} else {
    $lock_it = 'n';
}
if (isset($_REQUEST['comments_enabled']) && $_REQUEST['comments_enabled'] === 'on') {
    $comments_enabled = 'y';
} else {
    $comments_enabled = 'n';
}
$hash = [];
$hash['lock_it'] = $lock_it;
$hash['comments_enabled'] = $comments_enabled;

if (! empty($_REQUEST['contributions'])) {
    $hash['contributions'] = $_REQUEST['contributions'];
}
if (! empty($_REQUEST['contributors'])) {
    $hash['contributors'] = $_REQUEST['contributors'];
}
if (isset($_FILES['userfile1']) && is_uploaded_file($_FILES['userfile1']['tmp_name'])) {
    check_ticket('edit-page');
    require("lib/mail/mimelib.php");
    $fp = fopen($_FILES['userfile1']['tmp_name'], "rb");
    $data = '';
    while (! feof($fp)) {
        $data .= fread($fp, 8192 * 16);
    }
    fclose($fp);
    $name = $_FILES['userfile1']['name'];
    $mimelib = new mime();
    $output = $mimelib->decode($data);
    $parts = [];
    parse_output($output, $parts, 0);
    $last_part = '';
    $last_part_ver = 0;
    usort($parts, 'compare_import_versions');
    foreach ($parts as $part) {
        if ($part["version"] > $last_part_ver) {
            $last_part_ver = $part["version"];
            $last_part = $part["body"];
        }
        if (isset($part["pagename"])) {
            $pagename = urldecode($part["pagename"]);
            $version = urldecode($part["version"]);
            $author = urldecode($part["author"]);
            $lastmodified = $part["lastmodified"];
            if (isset($part["description"])) {
                $description = $part["description"];
            } else {
                $description = '';
            }
            $pageLang = isset($part["lang"]) ? $part["lang"] : "";
            $authorid = urldecode($part["author_id"]);
            if (isset($part["hits"])) {
                $hits = urldecode($part["hits"]);
            } else {
                $hits = 0;
            }
            $ex = substr($part["body"], 0, 25);
            //print(strlen($part["body"]));
            $msg = '';
            if (isset($_REQUEST['save']) && $prefs['feature_contribution'] === 'y' && $prefs['feature_contribution_mandatory'] === 'y' && (empty($_REQUEST['contributions']) || count($_REQUEST['contributions']) <= 0)) {
                $contribution_needed = true;
                $smarty->assign('contribution_needed', 'y');
            } else {
                $contribution_needed = false;
            }
            if (isset($_REQUEST['save']) && $prefs['feature_categories'] === 'y' && $prefs['feature_wiki_mandatory_category'] >= 0 && (empty($_REQUEST['cat_categories']) || count($_REQUEST['cat_categories']) <= 0)) {
                $category_needed = true;
                $smarty->assign('category_needed', 'y');
            } else {
                $category_needed = false;
            }
            if (isset($_REQUEST["save"]) && ! $category_needed && ! $contribution_needed) {
                if (strtolower($pagename) !== 'sandbox' || $tiki_p_admin === 'y') {
                    $description = TikiFilter::get('striptags')->filter($description);
                    if ($tikilib->page_exists($pagename)) {
                        if ($prefs['feature_multilingual'] === 'y') {
                            $info = $tikilib->get_page_info($pagename);
                            if ($info['lang'] !== $pageLang) {
                                $multilinguallib = TikiLib::lib('multilingual');
                                if ($multilinguallib->updateObjectLang('wiki page', $info['page_id'], $pageLang, true)) {
                                    $pageLang = $info['lang'];
                                    $smarty->assign('msg', tra("The language can't be changed as its set of translations has already this language"));
                                    $smarty->display("error.tpl");
                                    die;
                                }
                            }
                        }

                        $tikilib->update_page($pagename, $part["body"], tra('page imported'), $author, $authorid, $description, 0, $pageLang, false, $hash);
                    } else {
                        $tikilib->create_page($pagename, $hits, $part["body"], $lastmodified, tra('created from import'), $author, $authorid, $description, $pageLang, false, $hash);
                    }

                    // Handle the translation bits after actual creation/update
                    // This path is never used by minor updates
                    if ($prefs['feature_multilingual'] === 'y') {
                        $multilinguallib = TikiLib::lib('multilingual');
                        $tikilib->cache_page_info = [];

                        if ($editlib->isNewTranslationMode()) {
                            if ($editlib->aTranslationWasSavedAs('complete')) {
                                $editlib->saveCompleteTranslation();
                            } elseif ($editlib->aTranslationWasSavedAs('partial')) {
                                $editlib->savePartialTranslation();
                            }
                        } elseif ($editlib->isUpdateTranslationMode()) {
                            if ($editlib->aTranslationWasSavedAs('complete')) {
                                $editlib->saveCompleteTranslation();
                            } elseif ($editlib->aTranslationWasSavedAs('partial')) {
                                $editlib->savePartialTranslation();
                            }
                        } else {
                            $info = $tikilib->get_page_info($pagename);
                            $flags = [];
                            if (isset($_REQUEST['translation_critical'])) {
                                $flags[] = 'critical';
                            }
                            $multilinguallib->createTranslationBit('wiki page', $info['page_id'], $info['version'], $flags);
                        }
                    }
                }
            } else {
                $_REQUEST["edit"] = $last_part;
            }
        }
    }

    // If the watch state is not the same
    if ($requestedWatch !== $currentlyWatching) {
        if ($requestedWatch) {
            $tikilib->add_user_watch($user, 'wiki_page_changed', $page, 'wiki page', $page, $wikilib->sefurl($page));
        } else {
            $tikilib->remove_user_watch($user, 'wiki_page_changed', $page, 'wiki page');
        }
    }

    if (isset($_REQUEST["save"])) {                 // jb tiki 6 - this block of code seems to be redundant and unused - TOKIL
        unset($_REQUEST["save"]);
        if ($page_ref_id) {
            $url = "tiki-index.php?page_ref_id=$page_ref_id";
        } else {
            $url = $wikilib->sefurl($page);
        }
        if ($prefs['feature_best_language'] === 'y') {
            $url .= (strpos($url, '?') === false ? '?' : '&') . 'no_bl=y';
        }


        if ($prefs['flaggedrev_approval'] == 'y' && $tiki_p_wiki_approve == 'y') {
            $flaggedrevisionlib = TikiLib::lib('flaggedrevision');

            if ($flaggedrevisionlib->page_requires_approval($page)) {
                $url .= (strpos($url, '?') === false ? '?' : '&') . 'latest=1';
            }
        }
        if ($dieInsteadOfForwardingWithHeader) {
            die("-- tiki-editpage: Dying before second call to header(), so we can see traces. Forwarding to: '$url'");
        }
        $access->redirect($url);
    }
}

$smarty->assign('category_needed', $category_needed);
$smarty->assign('contribution_needed', $contribution_needed);
$wiki_up = "img/wiki_up";
if ($tikidomain) {
    $wiki_up .= "/$tikidomain";
}
// Upload pictures here
if (($prefs['feature_wiki_pictures'] === 'y') && (isset($tiki_p_upload_picture)) && ($tiki_p_upload_picture === 'y')) {
    $i = 1;
    while (isset($_FILES['picfile' . $i])) {
        if (is_uploaded_file($_FILES['picfile' . $i]['tmp_name'])) {
            $picname = $_FILES['picfile' . $i]['name'];
            if (preg_match('/\.(gif|png|jpe?g)$/i', $picname)) {
                if (@getimagesize($_FILES['picfile' . $i]['tmp_name'])) {
                    $filegallib = TikiLib::lib('filegal');
                    try {
                        $filegallib->assertUploadedFileIsSafe($_FILES['picfile' . $i]['tmp_name'], $_FILES['picfile' . $i]['name']);
                    } catch (Exception $e) {
                        // This is never being reached, because right now getimagesize fails for
                        // any unsafe file (SVG). Doesn't hurt, as support is already done for future
                        // safety issues.
                        $smarty->assign('msg', $e->getMessage());
                        $smarty->display("error.tpl");
                        die();
                    }
                    move_uploaded_file($_FILES['picfile' . $i]['tmp_name'], "$wiki_up/$picname");
                    chmod("$wiki_up/$picname", 0644); // seems necessary on some system (see move_uploaded_file doc on php.net)
                }
            }
        }
        $i++;
    }
}
if ($prefs['feature_wiki_attachments'] === 'y' && isset($_REQUEST["attach"]) && ($tiki_p_wiki_attach_files === 'y' || $tiki_p_wiki_admin_attachments === 'y')) {
    if (isset($_FILES['userfile2']) && is_uploaded_file($_FILES['userfile2']['tmp_name'])) {
        $ret = $tikilib->attach_file($_FILES['userfile2']['name'], $_FILES['userfile2']['tmp_name'], $prefs['w_use_db'] === 'y' ? 'db' : 'dir');
        if ($ret['ok']) {
            $wikilib->wiki_attach_file($page, $_FILES['userfile2']['name'], $_FILES['userfile2']['type'], $_FILES['userfile2']['size'], ($prefs['w_use_db'] === 'dir') ? '' : $ret['data'], $_REQUEST["attach_comment"], $user, $ret['fhash']);
        } else {
                $smarty->assign('msg', $ret['error']);
                $smarty->display("error.tpl");
                die();
        }
    }
}


// Suck another page and append to the end of current
$suck_url = isset($_REQUEST["suck_url"]) ? $_REQUEST["suck_url"] : '';

if (isset($_REQUEST["parsehtml"])) {
    $parsehtml = $_REQUEST["parsehtml"] === 'on' ? 'y' : 'n';
} else {
    // FIXME: If the user hasn't checked, we attempt to unparse anyway if ! is_html. Better not display the checkbox than ignoring its value
    $parsehtml = isset($info['is_html']) ? ($info['is_html'] ? 'n' : 'y') : 'n';
}
$smarty->assign('parsehtml', $parsehtml);

if (isset($_REQUEST['do_suck']) && strlen($suck_url) > 0) {
    // \note by zaufi
    //   This is ugly implementation of wiki HTML import.
    //   I think it should be plugable import/export converters with ability
    //   to choose from edit form what converter to use for operation.
    //   In case of import converter, it can try to guess what source
    //   file is (using mime type from remote server response).
    //   Of couse converters may have itsown configuration panel what should be
    //   pluged into wiki page edit form too... (like HTML importer may have
    //   flags 'strip HTML tags' and 'try to convert HTML to wiki' :)
    //   At least one export filter for wiki already coded :) -- PDF exporter...
    $sdta = $tikilib->httprequest($suck_url);
    if (error_get_last()) {
        $smarty->assign('msg', tra("Can't import remote HTML page"));
        $smarty->display("error.tpl");
        die;
    }
    // Need to parse HTML?
    if ($parsehtml === 'y') {
        $sdta = $editlib->parse_html($sdta);
    }
    $_REQUEST['edit'] = $jitRequest['edit'] . $sdta;
}
// if "UserPage" complete with the user name
if ($prefs['feature_wiki_userpage'] === 'y' && $tiki_p_admin !== 'y' && $page === $prefs['feature_wiki_userpage_prefix']) {
    $page .= $user;
    $_REQUEST['page'] = $page;
}

if (strtolower($_REQUEST["page"]) === 'sandbox' && $prefs['feature_sandbox'] !== 'y') {
    $smarty->assign('msg', tra("The Sandbox is disabled"));
    $smarty->display("error.tpl");
    die;
}

if (! isset($_REQUEST["comment"])) {
    $_REQUEST["comment"] = '';
}

// Get page data
if (isset($info['wiki_cache'])) {
    $prefs['wiki_cache'] = $info['wiki_cache'];
    $smarty->assign('wiki_cache', $prefs['wiki_cache']);
}

if (isset($info["flag"]) ? $info["flag"] === 'L' : false && ! $wikilib->is_editable($page, $user, $info)) {
    $smarty->assign('msg', tra("The page cannot be edited because it is locked"));
    $smarty->display("error.tpl");
    die;
}

$smarty->assign('editable', 'y');
$smarty->assign('show_page', 'n');
$smarty->assign('comments_show', 'n');

$smarty->assign_by_ref('data', $info);
$smarty->assign('footnote', '');
$smarty->assign('has_footnote', 'n');
$parserlib = TikiLib::lib('parser');
if ($prefs['feature_wiki_footnotes'] === 'y') {
    if ($user) {
        $x = $wikilib->get_footnote($user, $page);
        $footnote = $wikilib->get_footnote($user, $page);
        $smarty->assign('footnote', $footnote);
        if ($footnote) {
            $smarty->assign('has_footnote', 'y');
        }
        $smarty->assign('parsed_footnote', $parserlib->parse_data($footnote));
        if (isset($_REQUEST['footnote'])) {
            check_ticket('edit-page');
            $smarty->assign('parsed_footnote', $parserlib->parse_data($_REQUEST['footnote']));
            $smarty->assign('footnote', $_REQUEST['footnote']);
            $smarty->assign('has_footnote', 'y');
            if (empty($_REQUEST['footnote'])) {
                $wikilib->remove_footnote($user, $page);
            } else {
                $wikilib->replace_footnote($user, $page, $_REQUEST['footnote']);
            }
        }
    }
}
if (isset($_REQUEST["customTip"]) && ! isset($_REQUEST['preview']) && ! isset($_REQUEST['save'])) {
    $smarty->assign('customTip', $_REQUEST['customTip']);
    if (isset($_REQUEST["customTipTitle"])) {
        $smarty->assign('customTipTitle', tra($_REQUEST["customTipTitle"]));
    } else {
        $smarty->assign('customTipTitle', tra('Tip'));
    }
}
if (isset($_REQUEST["wikiHeaderTpl"]) && ! isset($_REQUEST['preview']) && ! isset($_REQUEST['save'])) {
    $smarty->assign('wikiHeaderTpl', $smarty->fetch("wiki:{$_REQUEST['wikiHeaderTpl']}"));
}
if ((isset($_REQUEST["template_name"]) || isset($_REQUEST["templateId"])) && ! isset($_REQUEST['preview']) && ! isset($_REQUEST['save'])) {
    $templateLang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : null;

    if (isset($_REQUEST["templateId"])) {
        $templateId = $_REQUEST["templateId"];
    } else {
        $multilinguallib = TikiLib::lib('multilingual');
        $templateId = $multilinguallib->getTemplateIDInLanguage('wiki', $_REQUEST["template_name"], $templateLang);
    }
    $template_data = TikiLib::lib('template')->get_template($templateId, $templateLang);
    $_REQUEST["edit"] = $template_data["content"] . "\n" . $_REQUEST["edit"];
    $smarty->assign("templateId", $templateId);
}

if (isset($_REQUEST["ratingId"]) && $_REQUEST["ratingId"] > 0) {
    $smarty->assign("poll_template", $_REQUEST["ratingId"]);
} else {
    $smarty->assign("poll_template", 0);
}

if (isset($_REQUEST["edit"])) {
    $edit_data = $_REQUEST["edit"];
} else {
    if (isset($info['draft'])) {
        $edit_data = $info['draft']['data'];
    } elseif (isset($info["data"])) {
        if ((isset($_REQUEST['hdr']) || (! empty($_REQUEST['pos']) && isset($_REQUEST['cell']))) && $prefs['wiki_edit_section'] === 'y') {
            if (isset($_REQUEST['hdr'])) {
                if ($_REQUEST['hdr'] === "0") {
                    list($real_start, $real_len) = $tikilib->get_wiki_section($info['data'], 1);
                    $real_len = $real_start;
                    $real_start = 0;
                } else {
                    list($real_start, $real_len) = $tikilib->get_wiki_section($info['data'], $_REQUEST['hdr']);
                }
            } else {
                include_once('lib/wiki-plugins/wikiplugin_split.php');
                list($real_start, $real_len) = wikiplugin_split_cell($info['data'], $_REQUEST['pos'], $_REQUEST['cell']);
            }
            $edit_data = substr($info['data'], $real_start, $real_len);
        } else {
            $edit_data = $info['data'];
        }
    } else {
        $edit_data = '';
    }
}

$likepages = '';
$smarty->assign_by_ref('likepages', $likepages);
if ($prefs['feature_likePages'] === 'y' and $edit_data === '' && ! $tikilib->page_exists($page)) {
    $likepages = $wikilib->get_like_pages($page);
}

if (isset($prefs['wiki_feature_copyrights']) && $prefs['wiki_feature_copyrights'] === 'y') {
    if (isset($_REQUEST['copyrightTitle'])) {
        $smarty->assign('copyrightTitle', $_REQUEST["copyrightTitle"]);
    }
    if (isset($_REQUEST['copyrightYear'])) {
        $smarty->assign('copyrightYear', $_REQUEST["copyrightYear"]);
    }
    if (isset($_REQUEST['copyrightAuthors'])) {
        $smarty->assign('copyrightAuthors', $_REQUEST["copyrightAuthors"]);
    }
    if (isset($_REQUEST['copyrightHolder'])) {
        $smarty->assign('copyrightHolder', $_REQUEST["copyrightHolder"]);
    }
}

/* Local reference handling */
if (isset($prefs['feature_references']) && $prefs['feature_references'] === 'y') {
    if ($prefs['wikiplugin_addreference'] == 'y') {
        $referenceslib = TikiLib::lib('references');
        $page_id = TikiLib::lib('tiki')->get_page_id_from_name($page);
        if ($page_id) {
            $smarty->assign('showBiblioSection', '1');

            $references = $referenceslib->list_references($page_id);
            $lib_references = $referenceslib->list_lib_references();
            $perms = Perms::get(['wiki page', $page]);

            if ($perms->use_references) {
                $use_references = 1;
            } else {
                $use_references = 0;
            }

            if ($perms->edit_references) {
                $edit_references = 1;
            } else {
                $edit_references = 0;
            }

            $assoc_references = $referenceslib->list_assoc_references($page_id);

            $page_info = TikiLib::lib('tiki')->get_page_info($page);
            $listOfCodes = \Tiki\WikiPlugin\Reference::extractBibliographicCodesFromText($page_info['data']);
            $listOfCodes = array_unique($listOfCodes);

            $key_exists = [];
            foreach ($listOfCodes as $m) {
                if (array_key_exists($m, $assoc_references['data'])) {
                    $key_exists[$m] = 1;
                }
            }
            foreach ($references['data'] as $key => $ref) {
                if (array_key_exists($ref['biblio_code'], $key_exists)) {
                    $references['data'][$key]['is_present'] = 1;
                } else {
                    $references['data'][$key]['is_present'] = 0;
                }
            }

            $smarty->assign('key_exists', $key_exists);
            $smarty->assign('referencesCant', $references['cant']);
            $smarty->assign('references', $references['data']);

            if ($references['cant'] < 1 && $lib_references['cant'] < 1) {
                $smarty->assign('display', 'none');
            } else {
                $smarty->assign('display', 'block');
            }
            $smarty->assign('ajaxURL', $GLOBALS['base_url']);

            $smarty->assign('libReferencesCant', $lib_references['cant']);
            $smarty->assign('libReferences', $lib_references['data']);
            $smarty->assign('use_references', $use_references);
            $smarty->assign('edit_references', $edit_references);
        } else {
            $smarty->assign('showBiblioSection', '0');
        }
    }
}
/* Local reference handling */


if (isset($_REQUEST["comment"])) {
    $smarty->assign_by_ref('commentdata', $_REQUEST["comment"]);
} elseif (isset($info['draft'])) {
    $smarty->assign_by_ref('commentdata', $info['draft']['data']);
} else {
    $smarty->assign('commentdata', '');
}
if (isset($info["description"])) {
    if (isset($info['draft'])) {
        $info['description'] = $info['draft']['description'];
    }
    $smarty->assign('description', $info["description"]);
    $description = $info["description"];
} else {
    $smarty->assign('description', '');
    $description = '';
}
if (isset($_REQUEST["description"])) {
    $smarty->assign_by_ref('description', $_REQUEST["description"]);
    $description = $_REQUEST["description"];
}

$wiki_authors_style = '';
if ($prefs['wiki_authors_style_by_page'] === 'y') {
    if (isset($_REQUEST['wiki_authors_style']) && $tiki_p_admin_wiki === 'y') {
        $wiki_authors_style = $_REQUEST['wiki_authors_style'];
    } elseif (! empty($info['wiki_authors_style'])) {
        $wiki_authors_style = $info['wiki_authors_style'];
    }
    $smarty->assign('wiki_authors_style', $wiki_authors_style);
}

if ($is_html) {
    $smarty->assign('allowhtml', 'y');
} else {
    if (! empty($_REQUEST['preview'])) {
        $edit_data = str_replace('<x>', '', $edit_data);
    }
    $smarty->assign('allowhtml', 'n');
}
if (empty($_REQUEST['lock_it']) && ! empty($info['flag']) && $info['flag'] === 'L') {
    $lock_it = 'y';
}
$smarty->assign_by_ref('lock_it', $lock_it);
if ($prefs['wiki_comments_allow_per_page'] !== 'n') {
    if (! isset($_REQUEST['save']) && ! isset($_REQUEST['preview'])) {
        if (! empty($info) && ! empty($info['comments_enabled'])) {
            $comments_enabled = $info['comments_enabled'];
        } else {
            if ($prefs['wiki_comments_allow_per_page'] === 'y') {
                $comments_enabled = 'y';
            } else {
                $comments_enabled = 'n';
            }
        }
    }
    $smarty->assign_by_ref('comments_enabled', $comments_enabled);
}
if (isset($_REQUEST["lang"])) {
    if ($prefs['feature_multilingual'] === 'y' && isset($info["lang"]) && $info['lang'] !== $_REQUEST["lang"]) {
        $multilinguallib = TikiLib::lib('multilingual');
        if ($multilinguallib->updateObjectLang('wiki page', $info['page_id'], $_REQUEST["lang"], true)) {
            $pageLang = $info['lang'];
            $smarty->assign('msg', tra("The language can't be changed as its set of translations has already this language"));
            $smarty->display("error.tpl");
            die;
        }
    }
    $pageLang = $_REQUEST["lang"];
} elseif (isset($info["lang"])) {
    $pageLang = $info["lang"];
} elseif (isset($edit_lang)) {
    $pageLang = $edit_lang;
} else {
    $pageLang = "";
}

$smarty->assign('lang', $pageLang);
if ($prefs['feature_urgent_translation'] === 'y') {
    $urgent_allowed = true;
    $smarty->assign('urgent_allowed', $urgent_allowed);
}
if (isset($_REQUEST['translation_critical'])) {
    $smarty->assign('translation_critical', 1);
} else {
    $smarty->assign('translation_critical', 0);
}

// Parse (or not) $edit_data into $parsed
// Handles switching editor modes
if (! isset($_REQUEST['preview']) && ! isset($_REQUEST['save'])) {
    if (isset($_REQUEST['mode_normal']) && $_REQUEST['mode_normal'] === 'y') {
        // Convert page content, as we are switching from the WYSIWYG editor to the regular editor
        if (! $is_html) {
            // we come from WYSIWYG-Wiki
            $parsed = $edit_data;
        } else {
            // we come from WYSIWYG-HTML
            $parsed = $editlib->parseToWiki($edit_data);
        }
        $is_html = false;
        $info['is_html'] = false;
        $info['wysiwyg'] = false;
        $smarty->assign('allowhtml', 'n');
    } elseif (isset($_REQUEST['mode_wysiwyg']) && $_REQUEST['mode_wysiwyg'] === 'y') {
        // We are switching from the regular editor to the WYSIWYG editor
        if (! $is_html && $prefs['wysiwyg_htmltowiki'] === 'y') {
            // we switch to WYSIWYG-Wiki
            $parsed = $edit_data;
        } else {
            // we switch to WYSIWYG-HTML (regular WYSIWYG)
            $parsed = $editlib->parseToWysiwyg($edit_data, true); // Convert page content
            $is_html = true;
            $smarty->assign('allowhtml', 'y');
        }
        $info['is_html'] = $is_html;
        $info['wysiwyg'] = true;
    } elseif ($_SESSION['wysiwyg'] === 'y') {
        if (empty($info['is_html'])) {
            if ($edit_data == 'ajax error') {
                unset($_REQUEST['save']);   // don't save an ajax error
            }
        } else {
            $parsed = $parserlib->parse_data(
                $edit_data,
                [
                    'absolute_links' => true,
                    'noheaderinc' => true,
                    'suppress_icons' => true,
                    'wysiwyg' => true,
                    'is_html' => true,
                    'process_wiki_paragraphs' => false
                 ]
            );
        }
    }
    if (isset($info['pageName'])) {
        $tikilib->check_duplicate_alias($edit_data, $info['pageName']);
    }
}

if (empty($parsed)) {
        $parsed = $edit_data;
}
$smarty->assign('pagedata', $parsed);

// apply the optional post edit filters before preview
if (isset($_REQUEST["preview"])) {
    if ($_SESSION['wysiwyg'] === 'y' && $prefs['wysiwyg_wiki_parsed'] === 'y') {
        $parsed = $editlib->partialParseWysiwygToWiki($parsed);
        $parsed = $parserlib->parse_data($parsed, ['absolute_links' => true, 'noheaderinc' => true, 'suppress_icons' => true, 'preview_mode' => true, 'is_html' => $is_html]);
    } else {
        $parsed = $parserlib->parse_data($parsed, ['is_html' => $is_html, 'preview_mode' => true]);
    }
    // If we are in preview mode then preview it!
    $smarty->assign('preview', 1);
} else {
    $parsed = '';
    $smarty->assign('preview', 0);
}

$smarty->assign_by_ref('parsed', $parsed);

/**
 * @param $obj
 * @param $parts
 * @param $i
 */
function parse_output(&$obj, &$parts, $i)
{
    if (! empty($obj['parts'])) {
        foreach ($obj['parts'] as $index => $part) {
            parse_output($part, $parts, $index);
        }
    } elseif ($obj['type'] === 'application/x-tikiwiki') {
        $aux["body"] = $obj['body'];
        $ccc = $obj['header']["content-type"];
        $items = explode(';', $ccc);
        foreach ($items as $item) {
            $portions = explode('=', $item);
            if (isset($portions[0]) && isset($portions[1])) {
                $aux[trim($portions[0])] = trim($portions[1]);
            }
        }
        $parts[] = $aux;
    }
}
// Pro
// Check if the page has changed
$pageAlias = '';
$cat_type = 'wiki page';
$cat_objid = $_REQUEST["page"];
if (
    isset($_REQUEST['save'])
     && $prefs['feature_contribution'] === 'y'
     && $prefs['feature_contribution_mandatory'] === 'y'
     && (empty($_REQUEST['contributions']) || count($_REQUEST['contributions']) <= 0)
) {
    $contribution_needed = true;
    $smarty->assign('contribution_needed', 'y');
} else {
    $contribution_needed = false;
}
if (
    isset($_REQUEST['save'])
    && $prefs['feature_categories'] === 'y'
    && $prefs['feature_wiki_mandatory_category'] >= 0
    && (empty($_REQUEST['cat_categories']) || count($_REQUEST['cat_categories']) <= 0)
) {
    $category_needed = true;
    $smarty->assign('category_needed', 'y');
} else {
    $category_needed = false;
}
if (isset($_REQUEST['save']) && $prefs['wiki_mandatory_edit_summary'] === 'y' && empty($_REQUEST['comment'])) {
    $summary_needed = true;
    $smarty->assign('summary_needed', 'y');
} else {
    $summary_needed = false;
}
if ($prefs['wiki_mandatory_edit_summary'] === 'y') {
    $headerlib->add_jq_onready(
        '$("input[name=save],input[name=minor]").click(function(){
            if (!$("#comment").val()) {
                var s = prompt("' . tra('Describe the change you made') . '", "");
                if (!s) return false;
                $("#comment").val(s);
            }
            return true;
        })'
    );
}

if ($prefs['site_layout_per_object'] == 'y') {
    $attributelib = TikiLib::lib('attribute');

    if (isset($jitPost['object_layout'])) {
        $attributelib->set_attribute('wiki page', $page, 'tiki.object.layout', $jitPost->object_layout->word());
    }

    $attributes = $at