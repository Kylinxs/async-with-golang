
<?php

/**
 * @package tikiwiki
 */

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

$section = 'mytiki';
require_once('tiki-setup.php');

$access->check_feature(['user_assigned_modules']);
$access->check_user($user);
$access->check_permission('tiki_p_configure_modules');

$usermoduleslib = TikiLib::lib('usermodules');

if (isset($_REQUEST["recreate"]) && $access->checkCsrf()) {
    $result = $usermoduleslib->create_user_assigned_modules($user);
    if ($result && $result->numRows()) {
        Feedback::success(tr('Default user modules restored'));
    } else {
        Feedback::error(tr('Default user modules not restored'));
    }
}

// Initial setup of default modules - no ticket required even though this changes the database. Origin is checked.
if (! $usermoduleslib->user_has_assigned_modules($user) && $access->checkCsrf(tr('As the first step in assigning user modules, click "Confirm action" to create the default module assignments'))) {
    $result = $usermoduleslib->create_user_assigned_modules($user);
    if ($result && $result->numRows()) {
        Feedback::success(tr('Default user modules assigned'));
    } else {
        Feedback::error(tr('Default user modules not assigned'));
    }
}

if (isset($_REQUEST["unassign"]) && $access->checkCsrf()) {
    $result = $usermoduleslib->unassign_user_module($_REQUEST["unassign"], $user);
    if ($result && $result->numRows()) {
        Feedback::success(tr('User module unassigned'));
    } else {
        Feedback::error(tr('User module not unassigned'));
    }
}

if (isset($_REQUEST["assign"]) && $access->checkCsrf()) {
    $result = $usermoduleslib->assign_user_module($_REQUEST["module"], $_REQUEST["position"], $_REQUEST["order"], $user);
    if ($result && $result->numRows()) {
        Feedback::success(tr('User module assigned'));
    } else {
        Feedback::error(tr('User module not assigned'));
    }
}

if (isset($_REQUEST["up"]) && $access->checkCsrf()) {
    $result = $usermoduleslib->up_user_module($_REQUEST["up"], $user);
    if ($result && $result->numRows()) {
        Feedback::success(tr('User module display order moved up. Displayed order may not change if other modules now have the same order rank.'));
    } else {
        Feedback::error(tr('User module display order not moved up'));
    }
}

if (isset($_REQUEST["down"]) && $access->checkCsrf()) {
    $result = $usermoduleslib->down_user_module($_REQUEST["down"], $user);
    if ($result && $result->numRows()) {
        Feedback::success(tr('User module display order moved down. Displayed order may not change if other modules now have the same order rank.'));
    } else {
        Feedback::error(tr('User module display order not moved down'));
    }
}

if (isset($_REQUEST["left"]) && $access->checkCsrf()) {
    $result = $usermoduleslib->set_column_user_module($_REQUEST["left"], $user, 'left');
    if ($result && $result->numRows()) {
        Feedback::success(tr('User module moved to left column'));
    } else {
        Feedback::error(tr('User module not moved to left column'));
    }
}

if (isset($_REQUEST["right"]) && $access->checkCsrf()) {
    $result = $usermoduleslib->set_column_user_module($_REQUEST["right"], $user, 'right');
    if ($result && $result->numRows()) {
        Feedback::success(tr('User module moved to right column'));
    } else {
        Feedback::error(tr('User module not moved to right column'));
    }
}

$orders = [];
for ($i = 1; $i < 50; $i++) {
    $orders[] = $i;
}
$smarty->assign_by_ref('orders', $orders);
$assignables = $usermoduleslib->get_user_assignable_modules($user);
if (count($assignables) > 0) {
    $smarty->assign('canassign', 'y');
} else {
    $smarty->assign('canassign', 'n');
}
$modules = $usermoduleslib->get_user_assigned_modules($user);
$smarty->assign('modules_l', $usermoduleslib->get_user_assigned_modules_pos($user, 'left'));
$smarty->assign('modules_r', $usermoduleslib->get_user_assigned_modules_pos($user, 'right'));
$smarty->assign_by_ref('assignables', $assignables);
$smarty->assign_by_ref('modules', $modules);
include_once('tiki-mytiki_shared.php');
$smarty->assign('mid', 'tiki-user_assigned_modules.tpl');
$smarty->display("tiki.tpl");