<?php

/**
 * @package tikiwiki
 */

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

require_once 'tiki-setup.php';
$creditslib = TikiLib::lib('credits');

if ($prefs['feature_credits'] == 'n' and ! empty($user)) {
    $smarty->assign('msg', tra('You do not have the permission that is needed to use this feature'));
    $smarty->display('error.tpl');
    die;
}

require_once('admin/include_credits.php');

list($creditTypes, $staticCreditTypes) = creditTypes();

$smarty->assign('userfilter', $user);

$editing = $userlib->get_user_info($user);

$credits = userPlansAndCredits();
list($start_date, $end_date) = getStartDateFromRequest();

$req_type = $_REQUEST['action_type'];
$smarty->assign('act_type', $req_type);

consumptionData();

$smarty->assign('mid', 'tiki-user_credits.tpl');
$smarty->display('tiki.tpl');
