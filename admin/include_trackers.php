
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER['SCRIPT_NAME'], basename(__FILE__)) !== false) {
    header('location: index.php');
    exit;
}

$trklib = TikiLib::lib('trk');

$find = '';
$offset = 0;
$sort_mode = 'created_desc';

//*** begin state-changing actions
// TODO avoid altering $_POST variable directly
if (isset($_POST['trkset']) && $access->checkCsrf()) {
    $tikilib->set_preference('t_use_db', $_POST['t_use_db']);
    if (substr($_POST['t_use_dir'], -1) != '\\' && substr($_POST['t_use_dir'], -1) != '/' && $_POST['t_use_dir'] != '') {
        $_POST['t_use_dir'] .= '/';
    }
    $tikilib->set_preference('t_use_dir', $_POST['t_use_dir']);
}

if (isset($_POST['action']) && isset($_POST['attId']) && $access->checkCsrf()) {
    $item = $trklib->get_item_attachment($_POST['attId']);
    if ($_POST['action'] == 'move2db') {
        $trklib->file_to_db($prefs['t_use_dir'] . $item['path'], $_POST['attId']);
    } elseif ($_POST['action'] == 'move2file') {
        $trklib->db_to_file($prefs['t_use_dir'] . md5($item['filename']), $_POST['attId']);
    }
}

if (isset($_POST['all2db']) && $access->checkCsrf()) {
    $attachments = $trklib->list_all_attachments();
    for ($i = 0; $i < $attachments['cant']; $i++) {
        if ($attachments['data'][$i]['path']) {
            $trklib->file_to_db($prefs['t_use_dir'] . $attachments['data'][$i]['path'], $attachments['data'][$i]['attId']);
        }
    }
} elseif (isset($_POST['all2file']) && $access->checkCsrf()) {
    $attachments = $trklib->list_all_attachments();
    for ($i = 0; $i < $attachments['cant']; $i++) {
        if (! $attachments['data'][$i]['path']) {
            $trklib->db_to_file($prefs['t_use_dir'] . md5($attachments['data'][$i]['filename']), $attachments['data'][$i]['attId']);
        }
    }
}
//*** end state-changing actions

if (! empty($_REQUEST['find'])) {
    $find = $_REQUEST['find'];
}
if (! empty($_REQUEST['offset'])) {
    $offset = $_REQUEST['offset'];
}
if (! empty($_REQUEST['sort_mode'])) {
    $sort_mode = $_REQUEST['sort_mode'];
}

$smarty->assign_by_ref('find', $find);
$smarty->assign_by_ref('offset', $offset);
$smarty->assign_by_ref('sort_mode', $sort_mode);

$attachments = $trklib->list_all_attachments($offset, $maxRecords, $sort_mode, $find);
$smarty->assign_by_ref('cant_pages', $attachments['cant']);
$headerlib->add_cssfile('themes/base_files/feature_css/admin.css');
$smarty->assign_by_ref('attachments', $attachments['data']);

$factory = new Tracker_Field_Factory(false);
$fieldPreferences = [];

foreach ($factory->getFieldTypes() as $type) {
    $fieldPreferences[] = array_shift($type['prefs']);
}

$smarty->assign('fieldPreferences', $fieldPreferences);