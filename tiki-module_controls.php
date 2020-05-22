<?php

/**
 * @package tikiwiki
 */

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

require_once('tiki-setup.php');
$usermoduleslib = TikiLib::lib('usermodules');
$smarty = TikiLib::lib('smarty');
global $tiki_p_configure_modules, $prefs, $user;
$actions = [
    'mc_unassign' => [
        'method' => 'unassign_user_module',
        'success' => tr('User module unassigned'),
        'error' => tr('User module not unassigned'),
    ],
    'mc_up' => [
        'method' => 'swap_up_user_module',
        'success' => tr('User module moved up'),
        'error' => tr('User module not moved up'),
    ],
    'mc_down' => [
        'method' => 'swap_down_user_module',
        'success' => tr('User module moved down'),
        'error' => tr('User module not moved down'),
    ],
    'mc_move' => [
        'method' => 'move_module',
        'success' => tr('User module moved to opposite side'),
        'error' => tr('User module not moved to opposite side'),
    ],
];
$actions = array_intersect_key($actions, $_REQUEST);
$check_req = count($actions);
if ($tiki_p_configure_modules != 'y' && $check_req) {
    Feedback::errorPage(['mes' => tr('You do not have permission to use this feature'), 'errortype' => 401]);
}
if ($prefs['user_assigned_modules'] != 'y' && $check_req) {
    Feedback::errorPage(tr('This feature is disabled') . ': user_assigned_modules');
}
if (! $user && $check_req) {
    Feedback::errorPage(tr('You must log in to use this feature'));
}
$request_uri = $url = isset($_SERVER["REQUEST_URI"]) ? $_SERVER['REQUEST_URI'] : '';
$access = TikiLib::lib('access');
foreach ($actions as $action => $settings) {
    if (isset($_REQUEST[$action]) && $access->checkCsrf()) {
        // Assign default user modules if user has not yet configured modules
        if (! $usermoduleslib->user_has_assigned_modules($user)) {
            $usermoduleslib->create_user_assigned_modules($user);
        }
        $method = $settings['method'];
        $result = $usermoduleslib->$method($_REQUEST[$action], $user);
        /** @var TikiDb_Pdo_Result|TikiDb_Adodb_Result $result */
        if ($result && $result->numRows()) {
            Feedback::success($settings['success']);
        } else {
            Feedback::error($settings['error']);
        }
    }
    // Remove module movemet paramaters from an URL
    // \todo What if 'mc_xxx' arg was not at the end? (if smbd fix URL by hands...)
    //       should I handle this very special (hack?) case?
    $url = preg_replace('/(.*)(\?|&){1}(mc_up|mc_down|mc_move|mc_unassign)=[^&]*/', '\1', $url);
}

// Fix locaton if parameter was removed...
if ($url != $request_uri || (isset($_POST['redirect']) && $_POST['redirect'])) {
    $access->redirect($url);
}
$smarty->assign('current_location', $url);
$smarty->assign('mpchar', (strpos($url, '?') ? '&' : '?'));
