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
require_once('tiki-setup.php');
$messulib = TikiLib::lib('message');
$access->check_user($user);
$access->check_feature('feature_messages');
$access->check_permission('tiki_p_messages');

if ((! isset($_REQUEST['msgId']) || $_REQUEST['msgId'] == 0) && empty($_POST['msgdel'])) {
    $smarty->assign('legend', tra("No more messages"));
    $smarty->assign('mid', 'messu-read_sent.tpl');
    $smarty->display("tiki.tpl");
    die;
}

if (isset($_POST['action']) && $access->checkCsrf()) {
    $messulib->flag_message($user, $_POST['msgId'], $_POST['action'], $_POST['actionval'], 'sent');
}
if (isset($_POST['msgdel']) && $access->checkCsrf(true)) {
    $result = $messulib->delete_message($user, $_POST['msgdel'], 'sent');
    if ($result->numRows()) {
        Feedback::success(tr('Sent message deleted'));
    } else {
        Feedback::error(tr('Sent message not deleted'));
    }
    header('location: messu-sent.php');
    exit;
}

$smarty->assign('sort_mode', $_REQUEST['sort_mode']);
$smarty->assign('find', $_REQUEST['find']);
$smarty->assign('flag', $_REQUEST['flag']);
$smarty->assign('offset', $_REQUEST['offset']);
$smarty->assign('flagval', $_REQUEST['flagval']);
$smarty->assign('priority', $_REQUEST['priority']);
$smarty->assign('legend', '');

// Using the sort_mode, flag, flagval and find get the next and prev messages
$smarty->assign('msgId', $_REQUEST['msgId']);
$next = $messulib->get_next_message($user, $_REQUEST['msgId'], $_REQUEST['sort_mode'], $_REQUEST['find'], $_REQUEST['flag'], $_REQUEST['flagval'], $_REQUEST['priority'], 'sent');
$prev = $messulib->get_prev_message($user, $_REQUEST['msgId'], $_REQUEST['sort_mode'], $_REQUEST['find'], $_REQUEST['flag'], $_REQUEST['flagval'], $_REQUEST['priority'], 'sent');
$smarty->assign('next', $next);
$smarty->assign('prev', $prev);
// Mark the message as read
$messulib->flag_message($user, $_REQUEST['msgId'], 'isRead', 'y', 'sent');
// Get the message and assign its data to template vars
$msg = $messulib->get_message($user, $_REQUEST['msgId'], 'sent');
$smarty->assign('msg', $msg);
include_once('tiki-section_options.php');
include_once('tiki-mytiki_shared.php');
$smarty->assign('mid', 'messu-read_sent.tpl');
$smarty->display("tiki.tpl");
