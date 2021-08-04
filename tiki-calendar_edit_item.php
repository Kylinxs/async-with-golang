<?php

/**
 * @package tikiwiki
 */

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

$section = 'calendar';
require_once('tiki-setup.php');

$access->check_feature('feature_calendar');

$calendarlib = TikiLib::lib('calendar');
$categlib = TikiLib::lib('categ');
include_once('lib/newsletters/nllib.php');
if ($prefs['feature_groupalert'] == 'y') {
    $groupalertlib = TikiLib::lib('groupalert');
}
$auto_query_args = ['calitemId', 'viewcalitemId'];

$daysnames = ['SU' => 'Sunday','MO' => 'Monday','TU' => 'Tuesday','WE' => 'Wednesday','TH' => 'Thursday','FR' => 'Friday','SA' => 'Saturday'];
$daysnames_abr = array_keys($daysnames);
$monthnames = ["",'January','February','March','April','May','June','July','August','September','October','November','December'];
$smarty->assign('daysnames', $daysnames);
$smarty->assign('daysnames_abr', $daysnames_abr);
$smarty->assign('monthnames', $monthnames);

$smarty->assign('edit', false);
$smarty->assign('recurrent', '');
$hour_minmax = '';
$recurrence = [
    'id'                => '',
    'weekly'            => '',
    'weekdays'          => [],
    'monthly'           => '',
    'dayOfMonth'        => '',
    'yearly'            => '',
    'dateOfYear_day'    => '',
    'dateOfYear_month'  => '',
    'startPeriod'       => '',
    'nbRecurrences'     => '',
    'endPeriod'         => ''
];
$smarty->assign('recurrence', $recurrence);

$caladd = [];
$addable = [];  // calendars this user can add to
$rawcals = $calendarlib->list_calendars();
if ($rawcals['cant'] == 0 && $tiki_p_admin_calendar == 'y') {
    $smarty->assign('msg', tra('You need to <a href="tiki-admin_calendars.php?cookietab=2">create a calendar</a>'));
    $smarty->display("error.tpl");
    die;
}

$rawcals['data'] = Perms::filter([ 'type' => 'calendar' ], 'object', $rawcals['data'], [ 'object' => 'calendarId' ], 'view_calendar');

foreach ($rawcals["data"] as $cal_data) {
    $cal_id = $cal_data['calendarId'];
    $calperms = Perms::get([ 'type' => 'calendar', 'object' => $cal_id ]);
    if ($cal_data["personal"] == "y") {
        if ($user) {
            $cal_data["tiki_p_view_calendar"] = 'y';
            $cal_data["tiki_p_view_events"] = 'y';
            $cal_data["tiki_p_add_events"] = 'y';
            $cal_data["tiki_p_change_events"] = 'y';
        } else {
            $cal_data["tiki_p_view_calendar"] = 'n';
            $cal_data["tiki_p_view_events"] = 'y';
            $cal_data["tiki_p_add_events"] = 'n';
            $cal_data["tiki_p_change_events"] = 'n';
        }
    } else {
        $cal_data["tiki_p_view_calendar"] = $calperms->view_calendar ? "y" : "n";
        $cal_data["tiki_p_view_events"] = $calperms->view_events ? "y" : "n";
        $cal_data["tiki_p_add_events"] = $calperms->add_events ? "y" : "n";
        $cal_data["tiki_p_change_events"] = $calperms->change_events ? "y" : "n";
    }
    $caladd["$cal_id"] = $cal_data;
    if ($cal_data['tiki_p_add_events'] == 'y') {
        $calID = $cal_id;
        $addable[] = $calID;
    }
}
$smarty->assign('listcals', $caladd);
if (isset($_REQUEST['saveas'])) {
    $smarty->assign('saveas', true);
}
if (! isset($_REQUEST["calendarId"])) {
    if (isset($_REQUEST['calitemId'])) {
        $calID = $calendarlib->get_calendarid($_REQUEST['calitemId']);
    } elseif (isset($_REQUEST['viewcalitemId'])) {
        $calID = $calendarlib->get_calendarid($_REQUEST['viewcalitemId']);
    }
} elseif (isset($_REQUEST['calendarId'])) {
    $calID = $_REQUEST['calendarId'];
} elseif (isset($_REQUEST['save']) && isset($_REQUEST['save']['calendarId'])) {
    $calID = $_REQUEST['save']['calendarId'];
}

if ($prefs['feature_groupalert'] == 'y' && ! empty($calID)) {
    $groupforalert = $groupalertlib->GetGroup('calendar', $calID);
    $showeachuser = '';
    if ($groupforalert != '') {
        $showeachuser = $groupalertlib->GetShowEachUser('calendar', $calID, $groupforalert);
        $listusertoalert = $userlib->get_users(0, -1, 'login_asc', '', '', false, $groupforalert, '');
        $smarty->assign_by_ref('listusertoalert', $listusertoalert['data']);
    }
    $smarty->assign_by_ref('groupforalert', $groupforalert);
    $smarty->assign_by_ref('showeachuser', $showeachuser);
}


$calitemId = ! empty($_REQUEST['save']['calitemId']) ? $_REQUEST['save']['calitemId'] : (! empty($_REQUEST['calitemId']) ? $_REQUEST['calitemId'] : (! empty($_REQUEST['viewcalitemId']) ? $_REQUEST['viewcalitemId'] : 0));
if (! empty($calitemId)) {
    $calitem = $calendarlib->get_item($calitemId);
    // reset perms depending on the calendaritem (which inherits perms from its parent calendar)
    $tikilib->get_perm_object($calitemId, 'calendaritem', $calitem, true, $calID);
    if ($calitem['user'] == $user) {
        $smarty->assign('tiki_p_change_events', 'y');
        $tiki_p_change_events = 'y';
        if (! empty($_REQUEST['save']['calendarId'])) {
            $caladd[$_REQUEST['save']['calendarId']]['tiki_p_change_events'] = $caladd[$_REQUEST['save']['calendarId']]['tiki_p_add_events'];
        }
        $caladd[$calitem['calendarId']]['tiki_p_change_events'] = 'y';
    }

    if ($tiki_p_change_events !== 'y') {
        $_REQUEST['viewcalitemId'] = $calitemId;
        unset($_REQUEST['calitemId']);
    }
}

if (isset($_REQUEST['save']) && ! isset($_REQUEST['preview']) && ! isset($_REQUEST['act'])) {
    $_REQUEST['changeCal'] = true;
}

$displayTimezone = TikiLib::lib('tiki')->get_display_timezone();

if (isset($_REQUEST['act']) || isset($_REQUEST['preview']) || isset($_REQUEST['changeCal'])) {
    $save = new JitFilter(array_merge($calitem ?? [], $_POST['save']));
    $save['allday'] = empty($_POST['allday']) ? 0 : 1;

    if (! isset($save['date_start']) && ! isset($save['date_end'])) {
        $save['date_start'] = strtotime($_POST['start_date_Year'] . '-' . $_POST['start_date_Month'] . '-' . $_POST['start_date_Day'] .
            ' ' . $_POST['start_Hour'] . ':' . $_POST['start_Minute'] . ':00');
        $save['date_end'] = strtotime($_POST['end_date_Year'] . '-' . $_POST['end_date_Month'] . '-' . $_POST['end_date_Day'] .
            ' ' . $_POST['end_Hour'] . ':' . $_POST['end_Minute'] . ':00');
        echo date('Y-m-d H:i', $save['end']);
    }

    if (! empty($save['name'])) {
        $save['name'] = $save->name->string();
    }

    if (! empty($save['description'])) {
        $save['description'] = $tikilib->convertAbsoluteLinksToRelative($save->description->html());
    }

    // Take care of timestamps dates coming from jscalendar
    if (isset($save['date_start']) || isset($save['date_end'])) {
        if (isset($_REQUEST['tzoffset'])) {
            $save['date_start'] = TikiDate::convertWithTimezone($_REQUEST, $save['date_start']) - TikiDate::tzServerOffset($displayTimezone, $save['date_start']);
            $save['date_end'] = TikiDate::convertWithTimezone($_REQUEST, $save['date_end']) - TikiDate::tzServerOffset($displayTimezone, $save['date_end']);
            if (! empty($_POST['startPeriod'])) {
                // get timezone date at 12:00am - reason: when this is later displayed, it could be the wrong date if stored at UTC
                // real solution here is to save the start date as a date object, not a timestamp to avoid timezone conversion issues...
                $_POST['startPeriod'] = TikiDate::convertWithTimezone($_REQUEST, $_POST['startPeriod']) - TikiDate::tzServerOffset($displayTimezone, $_POST['startPeriod']);
                $_POST['startPeriod'] = TikiDate::getStartDay($_POST['startPeriod'], $displayTimezone);
            }
            if (! empty($_POST['endPeriod'])) {
                // get timezone date at 12:00am
                $_POST['endPeriod'] = TikiDate::convertWithTimezone($_REQUEST, $_POST['endPeriod']) - TikiDate::tzServerOffset($displayTimezone, $_POST['endPeriod']);
                $_POST['endPeriod'] = TikiDate::getStartDay($_POST['endPeriod'], $displayTimezone);
            }
        }
    }

    $save['start'] = $save['date_start'];

    if ($save['end_or_duration'] == 'duration') {
        $save['duration'] = max(0, $_REQUEST['duration_Hour'] * 60 * 60 + $_REQUEST['duration_Minute'] * 60);
        $save['end'] = $save['start'] + $save['duration'];
    } else {
        $save['end'] = $save['date_end'];
        $save['duration'] = max(0, $save['end'] - $save['start']);
    }

    if (! empty($save['participant_roles'])) {
        $participants = [];
        foreach ($save['participant_roles'] as $username => $role) {
            $participants[] = [
                'username' => $username,
                'role' => $role,
                'partstat' => $save['participant_partstat'][$username] ?? null
            ];
        }
        $save['participants'] = $participants;
    } else {
        $save['participants'] = [];
    }
}

$impossibleDates = false;
if (isset($save['start']) && isset($save['end'])) {
    if (($save['end'] - $save['start']) < 0) {
        $impossibleDates = true;
    }
}

if (isset($_POST['act'])) {
    // Check antibot code if anonymous and allowed
    if (empty($user) && $prefs['feature_antibot'] == 'y' && (! $captchalib->validate())) {
        $smarty->assign('msg', $captchalib->getErrors());
        $smarty->assign('errortype', 'no_redirect_login');
        $smarty->display("error.tpl");
        die;
    }
    if (empty($save['user'])) {
        $save['user'] = $user;
    }
    $newcalid = $save['calendarId'];
    if (
        (empty($save['calitemId']) and $caladd["$newcalid"]['tiki_p_add_events'] == 'y') ||
            (! empty($save['calitemId']) and $caladd["$newcalid"]['tiki_p_change_events'] == 'y')
    ) {
