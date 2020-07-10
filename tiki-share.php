<?php

/**
 * @package tikiwiki
 */

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

// To include a link in your tpl do
//<a href="tiki-share.php?url={$smarty.server.REQUEST_URI|escape:'url'}">{tr}Share this page{/tr}</a>

$section = 'share';
require_once('tiki-setup.php');
if (empty($_REQUEST['report'])) {
    $access->check_feature('feature_share');
    $access->check_permission('tiki_p_share');
} else {
    if ($_REQUEST['report'] == 'y') {
        $access->check_feature('feature_site_report', '', 'look');
        $access->check_permission('tiki_p_site_report');
    }
}

// email related:

$smarty->assign('do_email', (isset($_REQUEST['do_email']) ? $_REQUEST['do_email'] : true));
if (empty($_REQUEST['report']) || $_REQUEST['report'] != 'y') {
    // twitter/facebook related
    if (isset($prefs['feature_socialnetworks']) and $prefs['feature_socialnetworks'] == 'y') {
        require_once('lib/socialnetworkslib.php');
        $smarty->assign('twitterRegistered', $socialnetworkslib->twitterRegistered());
        $smarty->assign('facebookRegistered', $socialnetworkslib->facebookRegistered());

        $twitter_token = $tikilib->get_user_preference($user, 'twitter_token', '');

        $smarty->assign('twitter', ($twitter_token != ''));
        $facebook_token = $tikilib->get_user_preference($user, 'facebook_token', '');
        $smarty->assign('facebook', ($facebook_token != ''));
        $smarty->assign('do_tweet', (isset($_REQUEST['do_tweet']) ? $_REQUEST['do_tweet'] : true));
        $smarty->assign('do_fb', (isset($_REQUEST['do_fb']) ? $_REQUEST['do_fb'] : true));
        $smarty->assign('fblike', (isset($_REQUEST['fblike']) ? $_REQUEST['fblike'] : 1));
    } else {
        $smarty->assign('twitterRegistered', false);
        $smarty->assign('twitter', false);
        $smarty->assign('facebookRegistered', false);
        $smarty->assign('facebook', false);
    }

    // message related
    if (isset($prefs['feature_messages']) and $prefs['feature_messages'] == 'y') {
        $logslib = TikiLib::lib('logs');

        $smarty->assign('priority', (isset($_REQUEST['priority']) ? $_REQUEST['priority'] : 3));
        $smarty->assign('do_message', (isset($_REQUEST['do_message']) ? $_REQUEST['do_message'] : true));
        $send_msg = ($tiki_p_messages == 'y');

        if ($prefs['allowmsg_is_optional'] == 'y') {
            if ($tikilib->get_user_preference($user, 'allowMsgs', 'y') != 'y') {
                $send_msg = false;
            }
        }
        $smarty->assign('send_msg', $send_msg);
    } else {
        $smarty->assign('send_msg', false);
    }

    if (isset($prefs['feature_forums']) and $prefs['feature_forums'] == 'y') {
        $commentslib = TikiLib::lib('comments'); // not done in commentslib
        $sort_mode = $prefs['forums_ordering'];
        $channels = $commentslib->list_forums(0, -1, $sort_mode, '');
        Perms::bulk([ 'type' => 'forum' ], 'object', $channels['data'], 'forumId');
        $forums = [];
        $temp_max = count($channels['data']);
        for ($i = 0; $i < $temp_max; $i++) {
            $forumperms = Perms::get([ 'type' => 'forum', 'object' => $channels['data'][$i]['forumId'] ]);
            if (($forumperms->forum_post and $forumperms->forum_post_topic) or $forumperms->admin_forum) {
                $forums[] = $channels['data'][$i];
            }
        }
        $smarty->assign('forumId', (isset($_REQUEST['forumId']) ? $_REQUEST['forumId'] : 0));
    } else {
        $forums = [];
    }
    $smarty->assign('forums', $forums);
    $report = 'n';
} else {
    $report = 'y';
}
$smarty->assign('report', isset($_REQUEST['report']) ? $_REQUEST['report'] : '');

$errors = [];
$ok = true;

if (empty($_REQUEST['url']) && ! empty($_SERVER['HTTP_REFERER'])) {
    $u = parse_url($_SERVER['HTTP_REFERER']);

    if ($u['host'] != $_SERVER['SERVER_NAME']) {
        $smarty->assign('msg', tra('Incorrect param'));
        $smarty->display('error.tpl');
        die;
    }
    $_REQUEST['url'] = $_REQUEST['HTTP_REFERER'];
}

if (empty($_REQUEST['url'])) {
    $smarty->assign('msg', tra('missing parameters'));
    $smarty->display('error.tpl');
    die;
}

$_REQUEST['url'] = urldecode($_REQUEST['url']);

if (strstr($_REQUEST['url'], 'tiki-share.php')) {
    $_REQUEST['url'] = preg_replace('/.*tiki-share.php\?url=/', '', $_REQUEST['url']);
    header('location: tiki-share.php?url=' . $_REQUEST['url']);
}

$url_for_friend = $tikilib->httpPrefix(true) . $_REQUEST['url'];

if ($report != 'y') {
    if (isset($_REQUEST['shorturl'])) {
        $shorturl = $_REQUEST['shorturl'];
    } else {
        $shorturl = false;

        if (isset($prefs['feature_socialnetworks']) and $prefs['feature_socialnetworks'] == 'y') {
            $shorturl = $socialnetworkslib->bitlyShorten($user, $url_for_friend);
        }

        if ($shorturl == false && $prefs['feature_sefurl_routes'] == 'y' && $prefs['sefurl_short_url'] == 'y') {
             $route = \Tiki\CustomRoute\CustomRoute::getShortUrlRoute($url_for_friend, null);
             $shorturl = $route->getShortUrlLink();
        }

        if ($shorturl == false) {
            $shorturl = $url_for_friend;
        }
    }
    $smarty->assign('shorturl', $shorturl);
}

$smarty->assign('url', $_REQUEST['url']);
$smarty->assign('prefix', $tikilib->httpPrefix(true));
$smarty->assign_by_ref('url_for_friend', $url_for_friend);
$smarty->assign('back_url', isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');

if (! empty($_REQUEST['subject'])) {
    $subject = $_REQUEST['subject'];
    $smarty->assign('subject', $subject);
} else {
    if ($report == 'y') {
        $subject = tra('Report to the webmaster', $prefs['site_language']);
    } else {
        $smarty->assign('mail_site', $_SERVER['SERVER_NAME']);
        $subject = $smarty->fetch('mail/share_subject.tpl');
    }
}

$smarty->assign('subject', $subject);

if (isset($_REQUEST['send'])) {
    if (! empty($_REQUEST['comment'])) {
        $smarty->assign('comment', $_REQUEST['comment']);
    }

    if (! empty($_REQUEST['share_token_notification'])) {
        $smarty->assign('share_token_notification', $_REQUEST['share_token_notification']);
    }

    if (! empty($_REQUEST['share_access_rights'])) {
        $smarty->assign('share_access_rights', $_REQUEST['share_access_rights']);
    }

    if (! empty($_REQUEST['how_much_time_access'])) {
        $smarty->assign('how_much_time_access', $_REQUEST['how_much_time_access']);
    }

    check_ticket('share');
    if (empty($user) && $prefs['feature_antibot'] == 'y' && ! $captchalib->validate()) {
        $errors[] = $captchalib->getErrors();
    } else {
        if ($report == 'y') {
            $email = ! empty($prefs['feature_site_report_email']) ? $prefs['feature_site_report_email'] : (! empty($prefs['sender_email']) ? $prefs['sender_email'] : '');
            if (empty($email)) {
                $errors[] = tra("The mail can't be sent. Contact the administrator");
            }
            $_REQUEST['addresses'] = $email;
            $_REQUEST['do_email'] = 1;
        }
        if (isset($_REQUEST['do_email']) and $_REQUEST['do_email'] == 1) {
            // Fix for multi adresses with autocomplete funtionnality
            if (substr($_REQUEST['addresses'], -2) == ', ') {
                $_REQUEST['addresses'] = substr($_REQUEST['addresses'], 0, -2);
            }
            // Call checkAddresses with error = false to avoid double error reporting

            $adresses = checkAddresses($_REQUEST['addresses'], false);

            require_once 'lib/auth/tokens.php';
            if (
                $prefs['share_can_choose_how_much_time_access']
                && isset($_REQUEST['how_much_time_access'])
                && is_numeric($_REQUEST['how_much_time_access'])
                && $_REQUEST['how_much_time_access'] >= 1
            ) {
                $prefs['auth_token_access_maxhits'] = $_REQUEST['how_much_time_access'];

                /* To upload, you need 2 token: one to see the page and another */
                if (strpos($_REQUEST['url'], 'tiki-upload_file')) {
                    $prefs['auth_token_access_maxhits'] = $prefs['auth_token_access_maxhits'] * 2 + 1;
                }
            }

            $share_access_rights = isset($_POST['share_access']);
            if ($_REQUEST['share_token_notification'] == 'y') {
                // list all users to give an unique token for notification
                $tokenlib = AuthTokens::build($prefs);

                if (is_array($adresses)) {
                    $contactlib = TikiLib::lib('contact');
                    foreach ($adresses as $adresse) {
                        $tokenlist[] = $tokenlib->includeToken($url_for_friend, $share_access_rights ? $globalperms->getGroups() : ['Anonymous'], $adresse);
                        // if preference share_contact_add_non_existant_contact the add auomaticly to contact
                        if ($prefs['share_contact_add_non_existant_contact'] == 'y' && $prefs['feature_contacts'] == 'y') {
                            // check if email exist for at least one contact in
                            if (! $contactlib->exist_contact($adresse, $user)) {
                                $contacts = [['email' => $adresse]];
                                $contactlib->add_contacts($contacts, $user);
                            }
                        }
                    }
                }

                if (is_array($tokenlist)) {
                    foreach ($tokenlist as $i => $data) {
                        $query = parse_url($data);
                        parse_str($query['query'], $query_vars);
                        $detailtoken = $tokenlib->getToken($query_vars['TOKEN']);
                        // Delete old user watch if it's necessary => avoid bad mails
                        $tikilib->remove_user_watch_object('auth_token_called', $detailtoken['tokenId'], 'security');
                        $tikilib->add_user_watch($user, 'auth_token_called', $detailtoken['tokenId'], 'security', tra('Token called'), $data);
                    }
                }
            } else {
                if ($share_access_rights) {
                    $tokenlib = AuthTokens::build($prefs);
                    $url_for_friend = $tokenlib->includeToken($url_for_friend, $globalperms->getGroups(), $_REQUEST['addresses']);
                    $smarty->assign('share_access', true);
                }
                $tokenlist[0] = $url_for_friend;
            }

            $smarty->assign_by_ref('email', $_REQUEST['email']);

            if (! empty($_REQUEST['addresses'])) {
                $smarty->assign('addresses', $_REQUEST['addresses']);
            }

            if (! empty($_REQUEST['name'])) {
                $smarty->assign('name', $_REQUEST['name']);
            }
            $emailSent = sendMail($_REQUEST['email'], $_REQUEST['addresses'], $subject, $tokenlist);
            $smarty->assign('emailSent', $emailSent);
            $ok = $ok && $emailSent;
        }

        if ($report != 'y') {
            if (isset($_REQUEST['do_tweet']) and $_REQUEST['do_tweet'] == 1) {
                $tweet = substr($_R