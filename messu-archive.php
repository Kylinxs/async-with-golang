
<?php

/**
 * @package tikiwiki
 */

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$



$section = 'user_messages';
$inputConfiguration = [[
    'staticKeyFilters' => [
        'unarchive' => 'alpha',
        'delete'    => 'digits',
        'download'  => 'alpha',
        'filter'    => 'alpha',
        'find'      => 'text',
        'flag'      => 'alnumdash',
        'flagval'   => 'alnumdash',
        'offset'    => 'digits',
        'priority'  => 'digits',
        'sort_mode' => 'alnumdash',
        'type'      => 'alpha'
    ],
    'staticKeyFiltersForArrays' => [
        'msg'       => 'digits',
    ],
    'catchAllUnset' => null,
]];

require_once('tiki-setup.php');
$messulib = TikiLib::lib('message');
$access->check_user($user);
$access->check_feature('feature_messages');
$access->check_permission('tiki_p_messages');
$maxRecords = $messulib->get_user_preference($user, 'maxRecords', 20);

//set defaults
$sort_mode = 'date_desc';
$offset = 0;
$find = '';
$flag = '';
$flagval = '';
$priority = '';

// Delete messages if the delete button was pressed
if (isset($_POST["delete"])) {
    if (isset($_POST["msg"]) && $access->checkCsrf(true)) {
        $i = 0;
        foreach (array_keys($_POST["msg"]) as $msg) {
            $result = $messulib->delete_message($user, $msg, 'archive');
            $i = $i + $result->numRows();
        }
        if ($i) {
            $msg = $i === 1 ? tr('%0 archived message was deleted', $i) : tr('%0 archived messages were deleted', $i);
            Feedback::success($msg);
        } else {
            Feedback::error(tra('No messages were deleted'));
        }
    } elseif (! isset($_POST["msg"])) {
        Feedback::error(tra('No messages were selected to delete'));
    }
}

// Download messages if the download button was pressed
if (isset($_REQUEST["download"])) {
    // if message ids are handed over, use them:
    if (isset($_REQUEST["msg"])) {
        foreach (array_keys($_REQUEST["msg"]) as $msg) {
            $tmp = $messulib->get_message($user, $msg, 'archive');
            $items[] = $tmp;
        }
    } else {
        $items = $messulib->get_messages($user, 'archive', '', '', '');
    }
    $smarty->assign_by_ref('items', $items);
    header("Content-Disposition: attachment; filename=tiki-msg-archive-" . time() . ".txt ");
    $smarty->display('messu-download.tpl', null, null, null, 'application/download');
    die;
}


// Archive messages if the archive button was pressed
if (isset($_POST["unarchive"])) {
    if (isset($_POST["msg"]) && $access->checkCsrf()) {
        $tmp = $messulib->count_messages($user, 'archive');
        $i = 0;
        foreach (array_keys($_POST["msg"]) as $msg) {
            /*if (($prefs['messu_archive_size'] > 0) && ($tmp + $i >= $prefs['messu_archive_size'])) {
                $smarty->assign('msg', tra("Archive is full. Delete some messages from archive first."));
                $smarty->display("error.tpl");
                die;
            }*/
            $result = $messulib->unarchive_message($user, $msg);
            $i = $i + $result->numRows();
        }
        if ($i) {
            $msg = $i === 1 ? tr('%0 message was unarchived', $i) : tr('%0 messages were unarchived', $i);
            Feedback::success($msg);
        } else {
            Feedback::error(tra('No messages were unarchived'));
        }
    } elseif (! isset($_POST["msg"])) {
        Feedback::error(tra('No messages were selected to unarchive'));
    }
}

if (isset($_REQUEST['filter'])) {
    if ($_REQUEST['flags'] != '') {
        $parts = explode('_', $_REQUEST['flags']);
        $flag = $parts[0];
        $flagval = $parts[1];
    }
} else {
    if (isset($_REQUEST["flag"])) {
        $flag = $_REQUEST["flag"];
    }
    if (isset($_REQUEST["flagval"])) {
        $flagval = $_REQUEST["flagval"];
    }
}

if (isset($_REQUEST["sort_mode"])) {
    $sort_mode = $_REQUEST["sort_mode"];
}
if (isset($_REQUEST["offset"])) {
    $offset = $_REQUEST["offset"];
}
if (isset($_REQUEST["find"])) {
    $find = $_REQUEST["find"];
}
if (isset($_REQUEST["priority"])) {
    $priority = $_REQUEST["priority"];
}

$smarty->assign_by_ref('flag', $flag);
$smarty->assign_by_ref('flagval', $flagval);
$smarty->assign_by_ref('priority', $priority);
$smarty->assign_by_ref('offset', $offset);
$smarty->assign_by_ref('sort_mode', $sort_mode);
$smarty->assign('find', $find);
// What are we paginating: items
$items = $messulib->list_user_messages(
    $user,
    $offset,
    $maxRecords,
    $sort_mode,
    $find,
    $flag,
    $flagval,
    $priority,
    'archive'
);
$smarty->assign_by_ref('cant_pages', $items["cant"]);
$smarty->assign_by_ref('items', $items["data"]);
$cellsize = 200;
$percentage = 1;
if ($prefs['messu_archive_size'] > 0) {
    $current_number = $messulib->count_messages($user, 'archive');
    $smarty->assign('messu_archive_number', $current_number);
    $smarty->assign('messu_archive_size', $prefs['messu_archive_size']);
    $percentage = ($current_number / $prefs['messu_archive_size']) * 100;
    $cellsize = round($percentage / 100 * 200);
    if ($current_number > $prefs['messu_archive_size']) {
        $cellsize = 200;
    }
    if ($cellsize < 1) {
        $cellsize = 1;
    }
    $percentage = round($percentage);
}
$smarty->assign('cellsize', $cellsize);
$smarty->assign('percentage', $percentage);
include_once('tiki-section_options.php');
include_once('tiki-mytiki_shared.php');
$smarty->assign('mid', 'messu-archive.tpl');
$smarty->display("tiki.tpl");