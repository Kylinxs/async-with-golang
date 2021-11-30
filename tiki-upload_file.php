
<?php

/**
 * @package tikiwiki
 */

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

use Tiki\TikiInit;

$section = 'file_galleries';
$isUpload = false;

if (isset($_POST['upload'])) {
    $isUpload = true;
    unset($_POST['upload']);
    unset($_GET['upload']);
    unset($_REQUEST['upload']);
}

if (isset($_POST['PHPSESSID']) && $_POST['PHPSESSID'] != '') {
    session_id($_POST['PHPSESSID']);
}

require_once('tiki-setup.php');
if ($prefs['feature_categories'] == 'y') {
    $categlib = TikiLib::lib('categ');
}

$access->check_feature('feature_file_galleries');

$filegallib = TikiLib::lib('filegal');
if ($prefs['feature_groupalert'] == 'y') {
    $groupalertlib = TikiLib::lib('groupalert');
}
@ini_set('max_execution_time', 0);
$auto_query_args = ['galleryId', 'fileId', 'filegals_manager', 'view', 'simpleMode', 'insertion_syntax'];

if ($prefs['auth_token_access'] == 'y' && ! empty($token)) {
    $smarty->assign('token_id', $token);
}

$requestGalleryId = null;
if (isset($_REQUEST['galleryId']) && ! is_array($_REQUEST['galleryId'])) {
    $requestGalleryId = $_REQUEST['galleryId'];
    $_REQUEST['galleryId'] = [ $requestGalleryId ];
}

$fileInfo = null;
$fileId = null;
if (! empty($_REQUEST['fileId'])) {
    $fileId = $_REQUEST['fileId'];

    if (! ($fileInfo = $filegallib->get_file_info($fileId))) {
        $smarty->assign('msg', tra("Incorrect param"));
        $smarty->display('error.tpl');
        die;
    }
    if (empty($_REQUEST['galleryId'][0])) {
        $_REQUEST['galleryId'][0] = $fileInfo['galleryId'];
    } elseif ($_REQUEST['galleryId'][0] != $fileInfo['galleryId']) {
        $smarty->assign('msg', tra("Could not find the file requested"));
        $smarty->display('error.tpl');
        die;
    }
    include_once('lib/mime/mimetypes.php');
    global $mimetypes;
    asort($mimetypes);
    $smarty->assign_by_ref('mimetypes', $mimetypes);
    if (! empty($prefs['ocr_enable']) && $prefs['ocr_enable'] === 'y') {
        if (! empty($prefs['ocr_file_level']) && $prefs['ocr_file_level'] === 'y') {
            if (empty($prefs['ocr_limit_languages'])) {
                $ocr = TikiLib::lib('ocr');
                $langs = $ocr->getTesseractLangs();
            } else {
                $langs = $prefs['ocr_limit_languages'];
            }
            $selectedLangs = json_decode($fileInfo['ocr_lang']);
            // lets remove the language codes from the unselected list if they are already selected
            foreach ($selectedLangs as $lang) {
                unset($langs[array_search($lang, $langs)]);
            }
            $langLib = TikiLib::lib('language');

            if (! empty($selectedLangs)) {
                $smarty->assign('selectedLanguages', $langLib->findLanguageNames($selectedLangs));
            }
            $smarty->assign('languages', $langLib->findLanguageNames($langs));
        }
        if ($fileInfo['ocr_state']) {
            $smarty->assign('ocr_state', true);
        }
    }
    $subGalleries = $filegallib->getSubGalleries();

    $gals = [];
    foreach ($subGalleries['data'] as $gal) {
        if ($gal['type'] == 'direct') {
            continue;
        }
        $gals[] = [
            'label' => $gal['parentName'] . ' > ' . $gal['name'],
            'id' => $gal['id'],
            'perms' => $gal['perms'],
            'public' => $gal['public'],
            'user' => $gal['user'],
        ];
    }
    sort($gals);
    $smarty->assign_by_ref('all_galleries', $gals);
}

if (isset($_REQUEST['galleryId'][0])) {
    $gal_info = $filegallib->get_file_gallery((int)$_REQUEST['galleryId'][0]);
    if (empty($gal_info)) {
        $smarty->assign('msg', tra('Incorrect file gallery'));
        $smarty->display('error.tpl');
        die;
    }
    $tikilib->get_perm_object($_REQUEST['galleryId'][0], 'file gallery', $gal_info, true);
    $smarty->assign_by_ref('gal_info', $gal_info);
}

if (empty($fileId) && $tiki_p_upload_files != 'y' && $tiki_p_admin_file_galleries != 'y') {
    $smarty->assign('errortype', 401);
    $smarty->assign('msg', tra("Permission denied"));
    $smarty->display('error.tpl');
    die;
}
if (isset($_REQUEST['galleryId'][1])) {
    foreach ($_REQUEST['galleryId'] as $i => $gal) {
        if (! $i) {
            continue;
        }
        // TODO get the good gal_info
        $perms = $tikilib->get_perm_object($_REQUEST['galleryId'][$i], 'file gallery', isset($gal_info) ? $gal_info : '', false);
        $access->check_permission('tiki_p_upload_files');
    }
}
if (! empty($fileId)) {
    if (! empty($fileInfo['lockedby']) && $fileInfo['lockedby'] != $user && $tiki_p_admin_file_galleries != 'y') { // if locked must be the locker
        $smarty->assign('msg', tra(sprintf('The file has been locked by %s', $fileInfo['lockedby'])));
        $smarty->display('error.tpl');
        die;
    }
    if (! ((! empty($user) && ($user == $fileInfo['user'] || $user == $fileInfo['lockedby'])) || $tiki_p_edit_gallery_file == 'y')) { // must be the owner or the locker or have the perms
        $smarty->assign('errortype', 401);
        $smarty->assign('msg', tra("You do not have permission to edit this file"));
        $smarty->display('error.tpl');
        die;
    }
    if ($gal_info['backlinkPerms'] == 'y' && $filegallib->hasOnlyPrivateBacklinks($fileId)) {
        $smarty->assign('errortype', 401);
        $smarty->assign('msg', tra("You do not have permission to edit this file"));
        $smarty->display('error.tpl');
        die;
    }
    if (isset($_REQUEST['lockedby']) && $fileInfo['lockedby'] != $_REQUEST['lockedby']) {
        if (empty($fileInfo['lockedby'])) {
            $smarty->assign('msg', tra(sprintf('The file has been unlocked meanwhile')));
        } else {
            $smarty->assign('msg', tra(sprintf('The file has been locked by %s', $fileInfo['lockedby'])));
        }
        $smarty->display('error.tpl');
        die;
    }
    if ($gal_info['lockable'] == 'y' && empty($fileInfo['lockedby']) && $tiki_p_admin_file_galleries != 'y') {
        $smarty->assign('msg', tra('You must lock the file before editing it'));
        $smarty->display('error.tpl');
        die;
    }
}

$smarty->assign('show', 'n');
if (! empty($_REQUEST['galleryId'][0]) && $prefs['feature_groupalert'] == 'y') {
    $groupforalert = $groupalertlib->GetGroup('file gallery', (int)$_REQUEST['galleryId'][0]);
    if ($groupforalert != '') {
        $showeachuser = $groupalertlib->GetShowEachUser('file gallery', (int)$_REQUEST['galleryId'][0], $groupforalert);
        $listusertoalert = $userlib->get_users(0, -1, 'login_asc', '', '', false, $groupforalert, '');
        $smarty->assign_by_ref('listusertoalert', $listusertoalert['data']);
    }
    $smarty->assign_by_ref('groupforalert', $groupforalert);
    $smarty->assign_by_ref('showeachuser', $showeachuser);
}

if (empty($_REQUEST['returnUrl'])) {
    include('lib/filegals/max_upload_size.php');
}

// Process an upload here
if ($isUpload) {
    $shortLivedTokens = ($prefs['site_short_lived_csrf_tokens'] ?? 'n') === 'y';
    // multiple form submissions are possible but the same ticket is in each form if JS is enabled,
    // so save ticket info from first submission
    if ($shortLivedTokens && (int) $_POST['submission'] === 1 && ! empty($_POST['totalSubmissions']) && (int) $_POST['totalSubmissions'] > 1) {
        $_SESSION['tickets']['repeatTicket']['ticket'] = $_POST['ticket'];
        $_SESSION['tickets']['repeatTicket']['time'] = $_SESSION['tickets'][$_POST['ticket']];
    }
    if (
        ((! $shortLivedTokens || (int) $_POST['submission'] === 1) && $access->checkCsrf())
        // for subsequent submissions check ticket against saved ticket info from first submission
        || ((int) $_POST['submission'] > 1
            && (int) $_POST['submission'] <= (int) $_POST['totalSubmissions']
            // check that posted ticket matches saved ticket from first submission
            && $_POST['ticket'] === $_SESSION['tickets']['repeatTicket']['ticket']
            // check that the ticket from the first submission hasn't expired
            && ! empty($_SESSION['tickets']['repeatTicket']['time'])
            && $_SESSION['tickets']['repeatTicket']['time'] < time()
            && $_SESSION['tickets']['repeatTicket']['time'] > time()
            - $prefs['site_security_timeout']
        )
    ) {
        if ($shortLivedTokens && ! empty($_POST['totalSubmissions']) && (int) $_POST['submission'] === (int) $_POST['totalSubmissions']) {
            unset($_SESSION['tickets']['repeatTicket']);
        }
        $optionalRequestParams = [
            'fileId',
            'parentGalleryId',
            'name',
            'user',
            'description',
            'author',
            'comment',
            'returnUrl',
            'isbatch',
            'deleteAfter',
            'deleteAfter_unit',
            'hit_limit',
            'listtoalert',
            'insertion_syntax',
            'filetype',
            'imagesize',
            'image_max_size_x',
            'image_max_size_y',
            'ocr_state',
            'ocr_lang'
        ];

        $uploadParams = [
            'fileInfo' => $fileInfo,
            'galleryId' => $_REQUEST['galleryId'],
        ];

        foreach ($optionalRequestParams as $p) {
            if (isset($_REQUEST[ $p ])) {
                if ($p === 'parentGalleryId') {
                    $uploadParams[ 'galleryId' ] = [$_REQUEST[ $p ]];   // new parent gallery for file from edit properties form
                } else {
                    $uploadParams[ $p ] = $_REQUEST[ $p ];
                }
            }
        }

        if (! empty($prefs['ocr_enable']) && $prefs['ocr_enable'] === 'y' && empty($_POST['ocr_state'][0])) {
            $uploadParams['ocr_state'][0] = null;
        }
        if ($fileInfo = $filegallib->actionHandler('uploadFile', $uploadParams)) {
            $fileId = $fileInfo['fileId'];
        }
    }
}

$fileparts = pathinfo($fileInfo['filename']);
$fileInfo['extension'] = isset($fileparts['extension']) ? $fileparts['extension'] : '';
$smarty->assign_by_ref('fileInfo', $fileInfo);
$smarty->assign('editFileId', (int) $fileId);

// Get the list of galleries to display the select box in the template
$smarty->assign('galleryId', empty($_REQUEST['galleryId'][0]) ? '' : $_REQUEST['galleryId'][0]);

if (empty($fileId)) {
    if (isset($gal_info['type']) && $gal_info['type'] == 'user') {
        $galleries = $filegallib->getSubGalleries($requestGalleryId, true, 'userfiles');
    } else {
        $galleries = $filegallib->getSubGalleries($requestGalleryId, true, 'upload_files');
    }
    $smarty->assign_by_ref('galleries', $galleries["data"]);
    $smarty->assign('treeRootId', $galleries['parentId']);
}

if ($prefs['fgal_limit_hits_per_file'] == 'y') {
    $smarty->assign('hit_limit', $filegallib->get_download_limit($fileId));
}

if (! empty($fileInfo['fileId'])) {
    $smarty->assign('metarray', $filegallib->metadataAction($fileInfo['fileId']), 'get_array');
}

$is_iis = TikiInit::isIIS();
$smarty->assign('is_iis', $is_iis);

$cat_type = 'file';
$cat_objid = (int) $fileId;
include_once('categorize_list.php');

include_once('tiki-section_options.php');

// disallow robots to index page:
$smarty->assign('metatag_robots', 'NOINDEX, NOFOLLOW');

$smarty->assign('category_jail', TikiLib::lib('tiki')->get_jail(false));

// Display the template
if ($prefs['javascript_enabled'] != 'y' or ! $isUpload || ! empty($_REQUEST['fileId'])) {
    if ($prefs['file_galleries_use_jquery_upload'] !== 'y') {
        $headerlib->add_jsfile('vendor_bundled/vendor/jquery-form/form/jquery.form.js');
    }
    $smarty->assign('mid', 'tiki-upload_file.tpl');
    if (! empty($_REQUEST['filegals_manager'])) {
        $smarty->assign('filegals_manager', $_REQUEST['filegals_manager']);
        $smarty->assign('insertion_syntax', $jitRequest->insertion_syntax->text());
        $smarty->display("tiki_full.tpl");
    } else {
        $smarty->display("tiki.tpl");
    }
}