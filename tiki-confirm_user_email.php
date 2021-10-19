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

global $tiki_p_admin_users;

// Admins can validate users even if preference is not active.
if ($tiki_p_admin_users !== 'y' && (isset($prefs['email_due']) && $prefs['email_due'] < 0 ) && $prefs['validateUsers'] != 'y') {
    Feedback::errorPage(tr('This feature is disabled') . ': validateUsers');
}

if (isset($_REQUEST['user']) && isset($_REQUEST['pass']) && $access->checkCsrf()) {
    if ($userlib->confirm_email($_REQUEST['user'], $_REQUEST['pass'])) {
        if (empty($user)) {
            $_SESSION["$user_cookie_site"] = $user = $_REQUEST['user'];
        }
        $msg = tr('User %0 validated by the admin', htmlspecialchars($_REQUEST['user']));
        Feedback::success($msg);
        $redirect = '';
        if (! empty($_SERVER['HTTP_REFERER'])) {
            $referer = parse_url($_SERVER['HTTP_REFERER']);
            if (! empty($referer['path'])) {
                $redirect = isset($referer['query']) ? $referer['path'] . '?' . $referer['query'] : $referer['path'];
            }
        }
        if (empty($redirect)) {
            $redirect = 'tiki-information.php?msg=' . urlencode($msg);
        }
        $access->redirect($redirect);
        die;
    }
}

Feedback::errorPage(tr('Problem. Try to log in again to receive new confirmation instructions.'));
