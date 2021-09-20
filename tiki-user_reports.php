<?php

/**
 * @package tikiwiki
 */

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

include_once('tiki-setup.php');

$access->check_user($user);
$access->check_feature('feature_daily_report_watches');

$reportsManager = Reports_Factory::build('Reports_Manager');

//Enable User Reports
if (isset($_POST['report_preferences']) && $_POST['use_daily_reports'] == "true" && $access->checkCsrf()) {
    $interval = filter_input(INPUT_POST, 'interval', FILTER_SANITIZE_STRING);
    $view = filter_input(INPUT_POST, 'view', FILTER_SANITIZE_STRING);
    $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
    $always_email = filter_input(INPUT_POST, 'always_email', FILTER_SANITIZE_NUMBER_INT);
    if ($always_email != 1) {
        $always_email = 0;
    }

    $result = $reportsManager->save($user, $interval, $view, $type, $always_email);
    if ((is_numeric($result) && $result > 0) || (is_object($result) && $result->numRows())) {
        Feedback::success(tr('User report preferences saved'));
    } else {
        Feedback::error(tr('User report preferences not saved'));
    }
    header('Location: tiki-user_watches.php');
    die;
}
//Disable User Reports
if (isset($_POST['report_preferences']) && $_POST['use_daily_reports'] != "true") {
    $result = $reportsManager->delete($user);
    if ($result && $result->numRows()) {
        Feedback::success(tr('User reports disabled'));
    } else {
        Feedback::error(tr('User report not disabled'));
    }
    header('Location: tiki-user_watches.php');
    die;
}
