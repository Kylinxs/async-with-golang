
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
include_once('tiki-setup.php');

$access->check_user($user);
$access->check_feature('feature_user_watches');

if ($prefs['feature_user_watches_translations']) {
    $langLib = TikiLib::lib('language');
    $languages = $langLib->list_languages();
    $smarty->assign_by_ref('languages', $languages);
}

$notificationlib = TikiLib::lib('notification');

$notification_types = $notificationlib->get_global_watch_types(true);

$smarty->assign('add_options', $notification_types);
if (isset($_POST['langwatch'])) {
    foreach ($languages as $lang) {
        if ($_POST['langwatch'] == $lang['value']) {
            $langwatch = $lang;
            break;
        }
    }
} else {
    $langwatch = null;
}

if ($prefs['feature_categories']) {
    $categlib = TikiLib::lib('categ');
    $categories = $categlib->getCategories(null, true, false);
} else {
    $categories = [];
}

if (isset($_REQUEST['categwatch'])) {
    $selected_categ = null;
    foreach ($categories as $categ) {
        if ($_REQUEST['categwatch'] == $categ['categId']) {
            $selected_categ = $categ;
            break;
        }
    }
}
// request from unsubscribe email link, like in templates/mail/user_watch_map_changed.tpl
// TODO would be better to provide a token with the unsubscribe link that could be matched to the database
// TODO so that the user doesn't have to log in to unsubscribe - similar to unsubscribe in tiki-newsletter.php
if (isset($_REQUEST['id'])) {
    if ($tiki_p_admin_notifications != 'y' && $user != $tikilib->get_user_notification($_REQUEST['id'])) {
        Feedback::errorPage(['mes' => tr('Permission denied'), 'errortype' => 401]);
    }
    if ($access->checkCsrf()) {
        $result = $tikilib->remove_user_watch_by_id($_REQUEST['id']);
    }
    if ($result && $result->numRows()) {
        Feedback::success(tr('Unsubscribed from user watch email notification'));
    } else {
        Feedback::error(tr('Unsubscribe failed'));
    }
}

if (isset($_REQUEST["add"]) && $access->checkCsrf()) {
    if (isset($_REQUEST['event'])) {
        if (! isset($notification_types[$_REQUEST['event']])) {
            Feedback::errorPage(tr('Unknown watch type'));
        }
        $watch_object = '*';
        $watch_type = $notification_types[$_REQUEST['event']]['type'];
        $watch_label = $notification_types[$_REQUEST['event']]['label'];
        $watch_url = $notification_types[$_REQUEST['event']]['url'];
        //special values
        switch ($_REQUEST['event']) {
            case 'wiki_page_in_lang_created':
                $watch_object = $langwatch['value'];
                $watch_label = tra('Language watch') . ": {$lang['name']}";
                break;

            case 'category_changed_in_lang':
                if ($selected_categ && $langwatch) {
                    $watch_object = $selected_categ['categId'];
                    $watch_type = $langwatch['value'];
                    $watch_label = tr('Category watch: %0, Language: %1', $selected_categ['name'], $langwatch['name']);
                    $watch_url = "tiki-browse_categories.php?lang={$lang['value']}&parentId={$selected_categ['categId']}";
                }
        }
        $result = $tikilib->add_user_watch(
            $user,
            $_REQUEST['event'],
            $watch_object,
            $watch_type,
            $watch_label,
            $watch_url
        );
        if ($result) {
            Feedback::success(tr('User watch added'));
        } else {
            Feedback::error(tr('User watch not added'));
        }
        $_REQUEST['event'] = '';
    } else {
        // Don't see where this case is used in the code
        $errors = 0;
        foreach ($_REQUEST['cat_categories'] as $cat) {
            if ($cat > 0) {
                $result = $tikilib->add_user_watch($user, 'new_in_category', $cat, 'category', "tiki-browse_category.php?parentId=$cat");
                $errors += $result ? 0 : 1;
            } else {
                $tikilib->remove_user_watch($user, 'new_in_category', '*');
                /** @var  TikiDb_Pdo_Result|TikiDb_Adodb_Result $result */
                $result = $tikilib->add_user_watch($user, 'new_in_category', '*', 'category', "tiki-browse_category.php");
                $errors += $result && $result->numRows() ? 0 : 1;
            }
            if ($errors) {
                Feedback::error('Errors encountered in adding category user watches');
            } else {
                Feedback::success('Category user watches added');
            }
        }
    }
}
// no confirmation needed as it is easy to add back a watch
if (isset($_REQUEST["delete"]) && isset($_REQUEST['checked']) && $access->checkCsrf()) {
    $checked = is_array($_REQUEST['checked']) ? $_REQUEST['checked'] : [$_REQUEST['checked']];
    /* CSRL doesn't work if param as passed not in the uri */
    foreach ($checked as $item) {
        $result = $tikilib->remove_user_watch_by_id($item);
        if ($result && $result->numRows()) {
            Feedback::success(tr('User watch deleted'));
        } else {
            Feedback::error(tr('User watch not deleted'));
        }
    }
}
$notification_types = $notificationlib->get_global_watch_types();
$rawEvents = $tikilib->get_watches_events();
$events = [];
foreach ($rawEvents as $event) {
    if (array_key_exists($event, $notification_types)) {
        $events[$event] = $notification_types[$event]['label'];
    } else {
        $events[$event] = $event; // Fallback to raw event name if no description found
    }
}
$smarty->assign('events', $events);
// if not set event type then all
if (! isset($_REQUEST['event'])) {
    $_REQUEST['event'] = '';
}
// get all the information for the event
$watches = $tikilib->get_user_watches($user, $_REQUEST['event']);
foreach ($watches as $key => $watch) {
    if (array_key_exists($watch['event'], $notification_types)) {
        $watches[$key]['label'] = $notification_types[$watch['event']]['label'];
    }
}
$smarty->assign('watches', $watches);
// this was never needed here, was it ? -- luci
//include_once ('tiki-mytiki_shared.php');
if ($prefs['feature_categories']) {
    $watches = $tikilib->get_user_watches($user, 'new_in_category');
    $nb = count($categories);
    foreach ($watches as $watch) {
        if ($watch['object'] == '*') {
            $smarty->assign('all', 'y');
            break;
        }
        for ($i = 0; $i < $nb; ++$i) {
            if ($watch['object'] == $categories[$i]['categId']) {
                $categories[$i]['incat'] = 'y';
                break;
            }
        }
    }
    $smarty->assign('categories', $categories);
}
if ($prefs['feature_messages'] == 'y' && $tiki_p_messages == 'y') {
    $unread = $tikilib->user_unread_messages($user);
    $smarty->assign('unread', $unread);
}
$eok = $userlib->get_user_email($user);
$smarty->assign('email_ok', empty($eok) ? 'n' : 'y');
$reportsUsers = Reports_Factory::build('Reports_Users');
$reportsUsersUser = $reportsUsers->get($user);
$smarty->assign_by_ref('report_preferences', $reportsUsersUser);

$smarty->assign('user_calendar_watch_editor', $tikilib->get_user_preference($user, 'user_calendar_watch_editor'));
$smarty->assign('user_article_watch_editor', $tikilib->get_user_preference($user, 'user_article_watch_editor'));
$smarty->assign('user_wiki_watch_editor', $tikilib->get_user_preference($user, 'user_wiki_watch_editor'));
$smarty->assign('user_blog_watch_editor', $tikilib->get_user_preference($user, 'user_blog_watch_editor'));
$smarty->assign('user_tracker_watch_editor', $tikilib->get_user_preference($user, 'user_tracker_watch_editor'));
$smarty->assign('user_comment_watch_editor', $tikilib->get_user_preference($user, 'user_comment_watch_editor'));
$smarty->assign('user_category_watch_editor', $tikilib->get_user_preference($user, 'user_category_watch_editor'));
$smarty->assign('user_plugin_approval_watch_editor', $tikilib->get_user_preference($user, 'user_plugin_approval_watch_editor'));

$smarty->assign('mid', 'tiki-user_watches.tpl');
$smarty->display("tiki.tpl");