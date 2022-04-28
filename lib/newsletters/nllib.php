<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER["SCRIPT_NAME"], basename(__FILE__)) !== false) {
    header("location: index.php");
    exit;
}

include_once('lib/webmail/tikimaillib.php');

use Laminas\Mail\Exception\ExceptionInterface as ZendMailException;
use SlmMail\Exception\ExceptionInterface as SlmMailException;

class NlLib extends TikiLib
{
    public function replace_newsletter(
        $nlId,
        $name,
        $description,
        $allowUserSub,
        $allowAnySub,
        $unsubMsg,
        $validateAddr,
        $allowTxt,
        $frequency,
        $author,
        $allowArticleClip = 'y',
        $autoArticleClip = 'n',
        $articleClipRange = null,
        $articleClipTypes = '',
        $emptyClipBlocksSend = 'n'
    ) {

        if ($nlId) {
            $query = "update `tiki_newsletters` set `name`=?,
                                `description`=?,
                                `allowUserSub`=?,
                                `allowTxt`=?,
                                `allowAnySub`=?,
                                `unsubMsg`=?,
                                `validateAddr`=?,
                                `frequency`=?,
                                `allowArticleClip`=?,
                                `autoArticleClip`=?,
                                `articleClipRange`=?,
                                `articleClipTypes`=?,
                                `emptyClipBlocksSend`=?
                                where `nlId`=?";
            $result = $this->query(
                $query,
                [
                        $name,
                        $description,
                        $allowUserSub,
                        $allowTxt,
                        $allowAnySub,
                        $unsubMsg,
                        $validateAddr,
                        $frequency,
                        $allowArticleClip,
                        $autoArticleClip,
                        $articleClipRange,
                        $articleClipTypes,
                        $emptyClipBlocksSend,
                        (int) $nlId
                ]
            );
        } else {
            $query = "insert into `tiki_newsletters`(
                                `name`,
                                `description`,
                                `created`,
                                `lastSent`,
                                `editions`,
                                `users`,
                                `allowUserSub`,
                                `allowTxt`,
                                `allowAnySub`,
                                `unsubMsg`,
                                `validateAddr`,
                                `frequency`,
                                `author`,
                                `allowArticleClip`,
                                `autoArticleClip`,
                                `articleClipRange`,
                                `articleClipTypes`
                                ) ";
            $query .= " values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $result = $this->query(
                $query,
                [
                        $name,
                        $description,
                        (int) $this->now,
                        0,
                        0,
                        0,
                        $allowUserSub,
                        $allowTxt,
                        $allowAnySub,
                        $unsubMsg,
                        $validateAddr,
                        null,
                        $author,
                        $allowArticleClip,
                        $autoArticleClip,
                        $articleClipRange,
                        $articleClipTypes
                ]
            );
            $queryid = "select max(`nlId`) from `tiki_newsletters` where `created`=?";
            $nlId = $this->getOne($queryid, [(int) $this->now]);
        }
        return $nlId;
    }

    public function replace_edition($nlId, $subject, $data, $users, $editionId = 0, $draft = false, $datatxt = '', $files = [], $wysiwyg = null, $is_html = null)
    {
        if ($draft == false) {
            if ($editionId > 0 && $this->getOne('select `sent` from `tiki_sent_newsletters` where `editionId`=?', [ (int) $editionId ]) == -1) {
                // save and send a draft
                $query = "update `tiki_sent_newsletters` set `subject`=?, `data`=?, `sent`=?, `users`=? , `datatxt`=?, `wysiwyg`=?, `is_html`=? ";
                $query .= "where editionId=? and nlId=?";
                $result = $this->query($query, [$subject, $data, (int) $this->now, $users, $datatxt, $wysiwyg, $is_html, (int) $editionId, (int) $nlId]);
                $query = "update `tiki_newsletters` set `editions`= `editions`+ 1 where `nlId`=? ";
                $result = $this->query($query, [(int) $nlId]);
                $query = "delete from `tiki_sent_newsletters_files` where `editionId`=?";
                $result = $this->query($query, [(int) $editionId]);
            } else {
                 // save and send an edition
                $query = "insert into `tiki_sent_newsletters`(`nlId`,`subject`,`data`,`sent`,`users` ,`datatxt`, `wysiwyg`, `is_html`) values(?,?,?,?,?,?,?,?)";
                $result = $this->query($query, [(int) $nlId, $subject, $data, (int) $this->now, $users, $datatxt, $wysiwyg, $is_html]);
                $query = "update `tiki_newsletters` set `editions`= `editions`+ 1 where `nlId`=?";
                $result = $this->query($query, [(int) $nlId]);
                $editionId = $this->getOne('select max(`editionId`) from `tiki_sent_newsletters`');
            }
        } else {
            if ($editionId > 0 && $this->getOne('select `sent` from `tiki_sent_newsletters` where `editionId`=?', [(int) $editionId ]) == -1) {
                // save an existing draft
                $query = "update `tiki_sent_newsletters` set `subject`=?, `data`=?, `datatxt`=?, `wysiwyg`=?, `is_html`=? ";
                $query .= "where editionId=? and nlId=?";
                $result = $this->query($query, [$subject, $data, $datatxt, $wysiwyg, $is_html, (int) $editionId, (int) $nlId]);
                $query = "delete from `tiki_sent_newsletters_files` where `editionId`=?";
                $result = $this->query($query, [(int) $editionId]);
            } else {
                // save a new draft
                $query = "insert into `tiki_sent_newsletters`(`nlId`,`subject`,`data`,`sent`,`users`,`datatxt`, `wysiwyg`, `is_html`) values(?,?,?,?,?,?,?,?)";
                $result = $this->query($query, [(int) $nlId, $subject, $data, -1, 0, $datatxt, $wysiwyg, $is_html]);
                $editionId = $this->getOne('select max(`editionId`) from `tiki_sent_newsletters`');
            }
        }
        foreach ($files as $file) {
            $query = "insert into `tiki_sent_newsletters_files` (`editionId`,`name`,`type`,`size`,`filename`) values (?,?,?,?,?)";
            $result = $this->query($query, [(int) $editionId, $file['name'], $file['type'], (int) $file['size'], $file['filename']]);
        }
        return $editionId;
    }

    /* get only the email subscribers */
    public function get_subscribers($nlId, $isEmail = 'y')
    {
        $query = "select `email` from `tiki_newsletter_subscriptions` where `valid`=? and `nlId`=? and isUser !=?";
        $result = $this->query($query, ['y', (int) $nlId, $isEmail]);
        $ret = [];
        while ($res = $result->fetchRow()) {
            $ret[] = $res["email"];
        }
        return $ret;
    }

    public function get_all_subscribers($nlId, $genUnsub)
    {
        global $prefs, $user;
        $userlib = TikiLib::lib('user');
        $return = [];
        $all_users = [];
        $group_users = [];
        $included_users = [];
        $page_included_emails = [];

        // Get list of the root groups (groups explicitly subscribed to this newsletter)
        //
        $groups = [];
        $query = "select `groupName`,`include_groups` from `tiki_newsletter_groups` where `nlId`=?";
        $result = $this->fetchAll($query, [(int) $nlId]);
        foreach ($result as $res) {
            $groups[] = $res['groupName'];

            if ($res['include_groups'] == 'y') {
                $groups = array_merge($groups, $userlib->get_including_groups($res["groupName"], 'y'));
            }
        }

        // If some groups are subscribed to this newsletter, get the list of users from those groups to be able to add them as subscribers
        // + Generate a random code (to allow users to unsubscribe) for users who don't already have one
        //
        if (count($groups) > 0) {
            $mid = " and (" . implode(" or ", array_fill(0, count($groups), "`groupName`=?")) . ")";
            $query = "select distinct uu.`login`, uu.`email` from `users_users` uu, `users_usergroups` ug where uu.`userId`=ug.`userId` " . $mid;
            $result = $this->query($query, $groups);
            while ($res = $result->fetchRow()) {
                if (empty($res['email'])) {
                    if ($prefs['login_is_email'] == 'y' && $user != 'admin') {
                        $res['email'] = $res['login'];
                    } else {
                        continue;
                    }
                }
                $res['email'] = strtolower($res['email']);
                $all_users[$res['email']] = [
                    'nlId' => (int) $nlId,
                    'email' => $res['email'],
                    'code' => $this->genRandomString($res['login']),
                    'valid' => 'y',
                    'subscribed' => $this->now,
                    'isUser' => 'g',
                    'db_email' => $res['login'],
                    'included' => 'n'
                ];
                $group_users[] = $res['login'];
            }
        }
        unset($groups);

        // Add subscribers that comes from included newsletters (only if their email is not already in the current list)
        //   Those users need to be saved in database for the current newsletter, in order to allow them to unsubscribe to this newsletter only
        //   (This implies to generate a new unsubscription code for the current newsletter)
        //
        $incnl = $this->list_newsletter_included($nlId);
        foreach ($incnl as $incid => $incname) {
            $incall = $this->get_all_subscribers($incid, $genUnsub);
            foreach ($incall as $res) {
                if (empty($all_users[$res['email']])) {
                    $res['code'] = $this->genRandomString($res['db_email']);
                    $res['included'] = 'y';
                    $all_users[$res['email']] = $res;
                    $included_users[] = $res['db_email'];
                }
            }
        }

        // Retrieve current subscribers of the list (into $all_users array)
        // Do not keep subscribers that are:
        //   - not valid (valid = n)
        //   - or that comes from a tiki group (isUser = g)
        //     except those who explicitely unsubscribed themselves (valid = x), in order to keep this information and not add this user again later
        //     except those who are still in a subscribed group ($group_users)
        //   - or an included newsletter (included = y)
        //     except those who explicitely unsubscribed themselves (valid = x), in order to keep this information and not add this user again later
        //     except those who are still in an included newsletter ($included_users)
        //
        //   Note: users from included newsletters or groups (see above) are replaced by current subscribers to keep their code of unsubscription
        //
        $query = "select * from `tiki_newsletter_subscriptions` where `nlId`=?";
        $result = $this->query($query, [(int) $nlId]);
        while ($res = $result->fetchRow()) {
            // if the user registered an email address, put it in lowercase to have consistent
            // comparison with other sources of email addresses. Username are case sensitive.
            if (( $res['isUser'] == 'n' )) {
                   $res['email'] = strtolower($res['email']);
            };
            if (
                ( $res['included'] != 'y' || $res['valid'] == 'x' ) && ((
                    $res['valid'] != 'n' && ( $res['isUser'] != 'g' || $res['valid'] == 'x' ) )
                    || ( $res['isUser'] == 'g' && in_array($res['email'], $group_users) )
                )
                || ( $res['included'] == 'y' && in_array($res['email'], $included_users) )
            ) {
                $res['db_email'] = $res['email'];

                // Update e-mails of tiki users (directly included or included via a group)
                // When the e-mail already exists for another subscriber, keep the other subscriber
                //   (e.g. to keep information of users that subscribed themselves)
                //
                if ($res['isUser'] == 'y' || $res['isUser'] == 'g') {
                    $res['email'] = strtolower($userlib->get_user_email($res['db_email']));
                }

                // Add new subscribers to $all_users, or replace the information that was already there from group users
                //   In case of valid users from included newsletters, update everything except the unsubscribe code
                if ($res['included'] == 'y' && $res['valid'] == 'y') {
                    $all_users[$res['email']]['code'] = $res['code'];
                } else {
                    $all_users[$res['email']] = $res;
                }
            }
        }

        $page_emails = $this->list_newsletter_pages($nlId);
        if ($page_emails['cant'] > 0) {
            foreach ($page_emails['data'] as $page) {
                $emails = $this->get_emails_from_page($page['wikiPageName']);
                if (! is_array($emails)) {
                    continue;
                }
                foreach ($emails as $email) {
                    if (! empty($email)) {
                        $res = [
                            'valid' => $page['validateAddrs'] == 'y' ? 'n' : 'y',
                            'subscribed' => $this->now,
                            'isUser' => 'n',
                            'db_email' => $email,
                            'email' => $email,
                            'included' => 'n',
                        ];

                        if ($page['addToList'] == 'y') {
                            $res['code'] = $this->genRandomString($email);
                            $all_users[$email] = $res;
                        }
                        $page_included_emails[$email] = $res;
                    }
                }
            }
        }

        // Update database if requested
        //
        if ($genUnsub) {
            $this->query('DELETE FROM `tiki_newsletter_subscriptions` WHERE `nlId`=?', [(int) $nlId]);
            $query = "INSERT INTO `tiki_newsletter_subscriptions` (`nlId`,`email`,`code`,`valid`,`subscribed`,`isUser`,`included`) VALUES (?,?,?,?,?,?,?)";
            foreach ($all_users as $res) {
                $this->query(
                    $query,
                    [
                        (int) $nlId,
                        $res['db_email'],
                        $res['code'],
                        $res['valid'],
                        $res['subscribed'],
                        $res['isUser'],
                        $res['included']
                    ]
                );
            }
        }

        // Only send the newsletter to valid and confirmed emails (valid=y)
        foreach ($all_users as $r) {
            if ($r['valid'] == 'y') {
                $return[] = $r;
            }
        }

        $return = array_merge($return, $page_included_emails);

        return $return;
    }

    /**
     * Removes newsletters subscriptions
     *
     * @param integer $nlId
     * @param string  $email
     * @param boolean $isUser
     *
     * @return TikiDb_Pdo_Result|TikiDb_Adodb_Result
     * @access public
     */
    public function remove_newsletter_subscription($nlId, $email, $isUser)
    {
        $query = "delete from `tiki_newsletter_subscriptions` where `nlId`=? and `email`=? and `isUser`=?";
        return $this->query($query, [(int) $nlId, $email, $isUser], -1, -1, false);
    }

    /**
     * Removes newsletters subscriptions with only the code as parameter
     *
     * @param string $code
     *
     * @return TikiDb_Pdo_Result|TikiDb_Adodb_Result
     * @access public
     */
    public function remove_newsletter_subscription_code($code)
    {
        $query = 'delete from `tiki_newsletter_subscriptions` where `code`=?';
        return $this->query($query, [$code], -1, -1, false);
    }

    /**
     * @param $nlId
     * @param $group
     *
     * @return TikiDb_Pdo_Result|TikiDb_Adodb_Result
     */
    public function remove_newsletter_group($nlId, $group)
    {
        $query = "delete from `tiki_newsletter_groups` where `nlId`=? and `groupName`=?";
        return $this->query($query, [(int) $nlId,$group], -1, -1, false);
    }

    /**
     * @param $nlId
     * @param $includedId
     *
     * @return TikiDb_Pdo_Result|TikiDb_Adodb_Result
     */
    public function remove_newsletter_included($nlId, $includedId)
    {
        $query = "delete from `tiki_newsletter_included` where `nlId`=? and `includedId`=?";
        return $this->query($query, [(int) $nlId,$includedId], -1, -1, false);
    }

    /**
     * @param        $nlId
     * @param        $add
     * @param string $isUser
     * @param string $validateAddr
     * @param string $addEmail
     *
     * @return bool
     * @throws Exception
     */
    public function newsletter_subscribe($nlId, $add, $isUser = 'n', $validateAddr = '', $addEmail = '')
    {
        global $user, $prefs;
        $userlib = TikiLib::lib('user');
        $tikilib = TikiLib::lib('tiki');
        $smarty = TikiLib::lib('smarty');
        if (empty($add)) {
            return false;
        }
        if ($isUser == "y" && $addEmail == "y") {
            $add = $userlib->get_user_email($add);
            $isUser = "n";
        }
        $query = "select * from `tiki_newsletter_subscriptions` where `nlId`=? and `email`=? and `isUser`=?";
        $result = $this->query($query, [(int) $nlId, $add, $isUser]);
        if ($res = $result->fetchRow()) {
            if ($res['valid'] == 'y') {
                return false; /* already subscribed and valid - keep the same valid status */
            }
        }
        $code = $this->genRandomString($add);
        $info = $this->get_newsletter($nlId);
        if ($info["validateAddr"] == 'y' && $validateAddr != 'n') {
            if ($isUser == "y") {
                $email = $userlib->get_user_email($add);
            } else {
                $email = $add;
            }
            /* if already has validated don't ask again */
            // Generate a code and store it and send an email  with the
            // URL to confirm the subscription put valid as 'n'

            if (empty($res)) {
                $query = "insert into `tiki_newsletter_subscriptions`(`nlId`,`email`,`code`,`valid`,`subscribed`,`isUser`,`included`) values(?,?,?,?,?,?,?)";
                $bindvars = [(int) $nlId,$add,$code,'n',(int) $this->now,$isUser,'n'];
            } else {
                // if already sub'ed but not validated then update code and timestamp (a.k.a. `subscribed`) and resend mail
                $query = "UPDATE `tiki_newsletter_subscriptions` SET `code`=?,`subscribed`=? WHERE `nlId`=? AND `email`=? AND `isUser`=? AND `valid`='n' AND `included`='n'";
                $bindvars = [$code,(int) $this->now,(int) $nlId,$add,$isUser];
            }
            $result = $this->query($query, $bindvars);
            // Now send an email to the address with the confirmation instructions
            $smarty->assign('info', $info);
            $smarty->assign('mail_date', $this->now);
            $smarty->assign('mail_user', $user);
            $smarty->assign('code', $code);
            $foo = parse_url($_SERVER["REQUEST_URI"]);
            $smarty->assign('mail_machine', $tikilib->httpPrefix(true) . dirname($foo["path"]) . '/');
            $smarty->assign('server_name', $_SERVER["SERVER_NAME"]);
            $mail_data = $smarty->fetch('mail/confirm_newsletter_subscription.tpl');
            if (! isset($_SERVER["SERVER_NAME"])) {
                $_SERVER["SERVER_NAME"] = $_SERVER["HTTP_HOST"];
            }
            include_once 'lib/mail/maillib.php';
            $zmail = tiki_get_admin_mail();
            $zmail->setSubject(tra('Newsletter subscription information at') . ' ' . $_SERVER["SERVER_NAME"]);
            $textPart = new Laminas\Mime\Part($mail_data);
            $textPart->setType(Laminas\Mime\Mime::TYPE_TEXT);
            //////////////////////////////////////////////////////////////////////////////////
            //                                      //
            // [BUG FIX] hollmeer 2012-11-04:                       //
            // ADDED html part code to fix a bug; if html-part not set, code stalls!    //
            // must be added in all functions in the file!                  //
            //                                      //
            $mail_data_html = "";
            $noDuplicateTextPart = false;
            try {
                $mail_data_html = $smarty->fetch('mail/confirm_newsletter_subscription_html.tpl');
            } catch (Exception $e) {
                // html-template missing; ignore and use text-template below
                // which means $textPart and $htmlPart will be the same, so ensure only one is used
                $noDuplicateTextPart = true;
            }
            if ($mail_data_html != '') {
                //ensure body tags in html part
                if (stristr($mail_data_html, '</body>') === false) {
                    $mail_data_html = "<body>" . nl2br($mail_data_html) . "</body>";
                }
            } else {
                //no html-template, so just use text-template
                if (stristr($mail_data, '</body>') === false) {
                    $mail_data_html = "<body>" . nl2br($mail_data) . "</body>";
                } else {
                    $mail_data_html = $mail_data;
                }
            }
            $htmlPart = new Laminas\Mime\Part($mail_data_html);
            $htmlPart->setType(Laminas\Mime\Mime::TYPE_HTML);

            $emailBody = new \Laminas\Mime\Message();
            if ($noDuplicateTextPart) {
                $emailBody->setParts([$htmlPart]);
            } else {
                $emailBody->setParts([$htmlPart, $textPart]);
            }

            $zmail->setBody($emailBody);
            //                                      //
            //////////////////////////////////////////////////////////////////////////////////
            $zmail->addTo($email);
            try {
                tiki_send_email($zmail);

                return true;
            } catch (ZendMailException | SlmMailException $e) {
                return false;
            }
        } else {
            if (! empty($res) && $res["valid"] == 'n') {
                $query = "update `tiki_newsletter_subscriptions` set `valid` = 'y' where `nlId` = ? and `email` = ? and `isUser` = ?";
                $result = $this->query($query, [(int) $nlId, $add, $isUser]);
                return $result && $result->numRows();
            }
            $query = "insert into `tiki_newsletter_subscriptions`(`nlId`,`email`,`code`,`valid`,`subscribed`,`isUser`,`included`) values(?,?,?,?,?,?,?)";
            $result = $this->query($query, [(int) $nlId, $add, $code, 'y', (int) $this->now, $isUser, 'n']);
            return $result && $result->numRows();
        }
        /*$this->update_users($nlId);*/
        return false;
    }

    public function confirm_subscription($code)
    {
        global $prefs;
        $userlib = TikiLib::lib('user');
        $tikilib = TikiLib::lib('tiki');
        $smarty = TikiLib::lib('smarty');
        $foo = parse_url($_SERVER["REQUEST_URI"]);
        $url_subscribe = $tikilib->httpPrefix(true) . $foo["path"];
        $query = "select * from `tiki_newsletter_subscriptions` where `code`=?";
        $result = $this->query($query, [$code]);

        if (! $result->numRows()) {
            return false;
        }

        $res = $result->fetchRow();
        $info = $this->get_newsletter($res["nlId"]);
        $smarty->assign('info', $info);
        $query = "update `tiki_newsletter_subscriptions` set `valid`=? where `code`=?";
        $result = $this->query($query, ['y', $code]);
        // Now send a welcome email
        $smarty->assign('mail_date', $this->now);
        if ($res["isUser"] == "y") {
            $user = $res["email"];
            $email = $userlib->get_user_email($user);
        } else {
            $email = $res["email"];
            $user = $userlib->get_user_by_email($email); //global $user is not necessary defined as the user is not necessary logged in
        }
        $smarty->assign('mail_user', $user);
        $smarty->assign('code', $res["code"]);
        $smarty->assign('url_subscribe', $url_subscribe);
        if (! isset($_SERVER["SERVER_NAME"])) {
            $_SERVER["SERVER_NAME"] = $_SERVER["HTTP_HOST"];
        }
        include_once 'lib/mail/maillib.php';
        $zmail = tiki_get_admin_mail();
        $lg = ! $user ? $prefs['site_language'] : $this->get_user_preference($user, "language", $prefs['site_language']);
        $mail_data = $smarty->fetchLang($lg, 'mail/newsletter_welcome_subject.tpl');
        $zmail->setSubject(sprintf($mail_data, $info["name"], $_SERVER["SERVER_NAME"]));
        $mail_data = $smarty->fetchLang($lg, 'mail/newsletter_welcome.tpl');
        $textPart = new Laminas\Mime\Part($mail_data);
        $textPart->setType(Laminas\Mime\Mime::TYPE_TEXT);
        //////////////////////////////////////////////////////////////////////////////////
        //                                      //
        // [BUG FIX] hollmeer 2012-11-04:                       //
        // ADDED html part code to fix a bug; if html-part not set, code stalls!    //
        // must be added in all functions in the file!                  //
        //                                      //
        $mail_data_html = "";
        $noDuplicateTextPart = false;
        try {
            $mail_data_html = $smarty->fetchLang($lg, 'mail/newsletter_welcome_html.tpl');
        } catch (Exception $e) {
            // html-template missing; ignore and use text-template below
            // which means $textPart and $htmlPart will be the same, so ensure only one is used
            $noDuplicateTextPart = true;
        }
        if ($mail_data_html != '') {
            //ensure body tags in html part
            if (stristr($mail_data_html, '</body>') === false) {
                $mail_data_html = "<body>" . nl2br($mail_data_html) . "</body>";
            }
        } else {
            //no html-template, so just use text-template
            if (stristr($mail_data, '</body>') === false) {
                $mail_data_html = "<body>" . nl2br($mail_data) . "</body>";
            } else {
                $mail_data_html = $mail_data;
            }
        }
        $htmlPart = new Laminas\Mime\Part($mail_data_html);
        $htmlPart->setType(Laminas\Mime\Mime::TYPE_HTML);

        $emailBody = new \Laminas\Mime\Message();
        if ($noDuplicateTextPart) {
            $emailBody->setParts([$htmlPart]);
        } else {
            $emailBody->setParts([$htmlPart, $textPart]);
        }

        $zmail->setBody($emailBody);
        //                                      //
        //////////////////////////////////////////////////////////////////////////////////
        $zmail->addTo($email);

        try {
            tiki_send_email($zmail);

            return $this->get_newsletter($res["nlId"]);
        } catch (ZendMailException | SlmMailException $e) {
            return false;
        }
    }

    public function unsubscribe($code, $mailit = false)
    {
        global $prefs;
        $userlib = TikiLib::lib('user');
        $tikilib = TikiLib::lib('tiki');
        $smarty = TikiLib::lib('smarty');
        $foo = parse_url($_SERVER["REQUEST_URI"]);
        $url_subscribe = $tikilib->httpPrefix(true) . $foo["path"];
        $query = "select * from `tiki_newsletter_subscriptions` where `code`=?";
        $result = $this->query($query, [$code]);

        if (! $result->numRows()) {
            return false;
        }

        $res = $result->fetchRow();
        $info = $this->get_newsletter($res["nlId"]);
        $smarty->assign('info', $info);
        $smarty->assign('code', $res["code"]);
        if ($res["isUser"] == 'g' || $res["included"] == 'y') {
            $query = "update `tiki_newsletter_subscriptions` set `valid`='x' where `code`=?";
        } else {
            $query = "delete from `tiki_newsletter_subscriptions` where `code`=?";
        }
        $result = $this->query($query, [$code], -1, -1, false);
        // Now send a bye bye email
        $smarty->assign('mail_date', $this->now);
        if ($res["isUser"] == "y") {
            $user = $res["email"];
            $email = $userlib->get_user_email($user);
        } else {
            $email = $res["email"];
            $user = $userlib->get_user_by_email($email); //global $user is not necessary defined as the user is not necessary logged in
        }
        $smarty->assign('mail_user', $user);
        $smarty->assign('url_subscribe', $url_subscribe);
        $lg = ! $user ? $prefs['site_language'] : $this->get_user_preference($user, "language", $prefs['site_language']);
        if (! isset($_SERVER["SERVER_NAME"])) {
            $_SERVER["SERVER_NAME"] = $_SERVER["HTTP_HOST"];
        }
        if ($mailit) {
            include_once 'lib/mail/maillib.php';
            $zmail = tiki_get_admin_mail();
            $mail_data = $smarty->fetchLang($lg, 'mail/newsletter_byebye_subject.tpl');
            $zmail->setSubject(sprintf($mail_data, $info["name"], $_SERVER["SERVER_NAME"]));
            $mail_data = $smarty->fetchLang($lg, 'mail/newsletter_byebye.tpl');
            $textPart = new Laminas\Mime\Part($mail_data);
            $textPart->setType(Laminas\Mime\Mime::TYPE_TEXT);
            //////////////////////////////////////////////////////////////////////////////////
            //                                      //
            // [BUG FIX] hollmeer 2012-11-04:                       //
            // ADDED html part code to fix a bug; if html-part not set, code stalls!    //
            // must be added in all functions in the file!                  //
            //                                      //
            $mail_data_html = "";
            try {
                $mail_data_html = $smarty->fetch('mail/newsletter_byebye_subject_html.tpl');
            } catch (Exception $e) {
                // html-template missing; ignore and use text-template below
            }
            if ($mail_data_html != '') {
                //ensure body tags in html part
                if (stristr($mail_data_html, '</body>') === false) {
                    $mail_data_html = "<body>" . nl2br($mail_data_html) . "</body>";
                }
            } else {
                //no html-template, so just use text-template
                if (stristr($mail_data, '</body>') === false) {
                    $mail_data_html = "<body>" . nl2br($mail_data) . "</body>";
                } else {
                    $mail_data_html = $mail_data;
                }
            }
            $htmlPart = new Laminas\Mime\Part($mail_data_html);
            $htmlPart->setType(Laminas\Mime\Mime::TYPE_HTML);

            $emailBody = new \Laminas\Mime\Message();
            $emailBody->setParts([$htmlPart, $textPart]);

            $zmail->setBody($emailBody);
            //                                      //
            //////////////////////////////////////////////////////////////////////////////////
            $zmail->addTo($email);

            try {
                tiki_send_email($zmail);
            } catch (ZendMailException | SlmMailException $e) {
            }
        }
        /*$this->update_users($res["nlId"]);*/
        return $this->get_newsletter($res["nlId"]);
    }

    /**
     * @param        $nlId
     * @param string $validateAddr
     * @param string $addEmail
     *
     * @return bool
     * @throws Exception
     */
    public function add_all_users($nlId, $validateAddr = '', $addEmail = '')
    {
        $query = "select `email`, `login`from `users_users`";
        $result = $this->query($query, []);
        $success = true;
        while ($res = $result->fetchRow()) {
            if ($addEmail == "y") {
                $add = $res["email"];
                $isUser = "n";
            } else {
                $add = $res["login"];
                $isUser = "y";
            }
            if (! empty($add)) {
                $eachResult = $this->newsletter_subscribe($nlId, $add, $isUser, $validateAddr, $addEmail);
                if (! $eachResult) {
                    $success = false;
                }
            } else {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * @param        $nlId
     * @param        $group
     * @param string $include_groups
     *
     * @return TikiDb_Pdo_Result|TikiDb_Adodb_Result
     */
    public function add_group($nlId, $group, $include_groups = 'n')
    {
        $query = "delete from `tiki_newsletter_groups` where `nlId`=? and `groupName`=?";
        $this->query($query, [(int) $nlId, $group], -1, -1, false);
        $code = $this->genRandomString($group);
        $query = "insert into `tiki_newsletter_groups`(`nlId`,`groupName`,`code`,`include_groups`) values(?,?,?,?)";
        return $this->query($query, [(int) $nlId, $group, $code, $include_groups]);
    }

    /**
     * @param $nlId
     * @param $includedId
     *
     * @return bool
     * @throws Exception
     */
    public function add_included($nlId, $includedId)
    {
        // do not include $includedId subscribers if $includedId newsletter includes $nlId subscribers
        // to avoid fatal recursive errors in get_all_subscribers() method
        $includedIdIncludes = $this->list_newsletter_included($includedId);
        if (array_key_exists($nlId, $includedIdIncludes)) {
            Feedback::warning(tr('Cannot add subscribers from a newsletter that includes this newsletter\'s subscribers'));
            return false;
        } else {
            $query = "delete from `tiki_newsletter_included` where `nlId`=? and `includedId`=?";
            $this->query($query, [(int) $nlId, (int) $includedId], -1, -1, false);
            $query = "insert into `tiki_newsletter_included` (`nlId`,`includedId`) values(?,?)";
            $result = $this->query($query, [(int) $nlId, (int) $includedId]);
            return $result && $result->numRows() > 0;
        }
    }

    /**
     * @param        $nlId
     * @param        $group
     * @param string $validateAddr
     * @param string $addEmail
     *
     * @return bool
     * @throws Exception
     */
    public function add_group_users($nlId, $group, $validateAddr = '', $addEmail = '')
    {
        $groups = array_merge([$group], $this->get_groups_all($group));
        $mid = implode(" or ", array_fill(0, count($groups), "`groupName`=?"));
        $query = "select `login`,`email`  from `users_users` uu, `users_usergroups` ug where uu.`userId`=ug.`userId` and ($mid)";
        $result = $this->query($query, $groups);
        $ret = [];
        while ($res = $result->fetchRow()) {
            if ($addEmail == "y") {
                $ret[] = $res['email'];
            } else {
                $ret[] = $res['login'];
            }
        }
        $ret = array_unique($ret);
        $isUser = $addEmail == "y" ? "n" : "y";
        $success = true;
        foreach ($ret as $o) {
            $eachResult = $this->newsletter_subscribe($nlId, $o, $isUser, $validateAddr, $addEmail);
            if (! $eachResult) {
                $success = false;
            }
        }
        return $success;
    }

    public function get_newsletter($nlId)
    {
        $query = "select * from `tiki_newsletters` where `nlId`=?";
        $result = $this->query($query, [(int) $nlId]);
        if (! $result->numRows()) {
            return false;
        }
        $res = $result->fetchRow();
        return $res;
    }

    public function get_edition($editionId)
    {
        $query = "select * from `tiki_sent_newsletters` where `editionId`=?";
        $result = $this->query($query, [(int) $editionId]);
        if (! $result->numRows()) {
            return false;
        }
        $res = $result->fetchRow();
        $res['files'] = $this->get_edition_files($editionId);
        return $res;
    }

    public function get_edition_files($editionId)
    {
        global $prefs;
        $res = [];
        $query = "select * from `tiki_sent_newsletters_files` where `editionId`=?";
        $result = $this->query($query, [(int) $editionId]);
        $res = [];
        while ($f = $result->fetchRow()) {
            $f['error'] = 0;
            $res[] = $f;
        }
        return $res;
    }

    public function update_users($nlId)
    {
        $users = $this->getOne("select count(*) from `tiki_newsletter_subscriptions` where `nlId`=? and `valid`!=?", [(int) $nlId, 'x']);
        $query = "update `tiki_newsletters` set `users`=? where `nlId`=?";
        $result = $this->query($query, [$users, (int) $nlId]);
    }

    /* perms = a or between perms */
    public function list_newsletters($offset, $maxRecords, $sort_mode, $find, $update = '', $perms = '', $full = 'y')
    {
        global $user, $tikilib;
        $bindvars = [];
        if ($find) {
            $findesc = '%' . $find . '%';
            $mid = " where (tn.`name` like ? or tn.`description` like ?)";
            $bindvars[] = $findesc;
            $bindvars[] = $findesc;
        } else {
            $mid = '';
        }

        $query = "select tn.nlId, tn.`name`, tn.`description`, tn.`users`, tn.`editions`, tn.`author`, max(tsn.`sent`) as lastSent 
        from `tiki_newsletters` tn 
        left join `tiki_sent_newsletters` tsn on (tn.`nlId` = tsn.`nlId`) $mid 
        group by tn.`nlId`, tn.`name`, tn.`description`, tn.`users`, tn.`editions`, tn.`author`
        order by " . $this->convertSortmode("$sort_mode");
        $result = $this->query($query, $bindvars, $maxRecords, $offset);
        $query_cant = "select count(*) from  `tiki_newsletters` as tn $mid";
        $cant = $this->getOne($query_cant, $bindvars);
        $ret = [];

        while ($res = $result->fetchRow()) {
            $objperms = Perms::get('newsletter', $res['nlId']);
            $res['tiki_p_admin_newsletters'] = $objperms->admin_newsletters ? 'y' : 'n';
            $res['tiki_p_send_newsletters'] = $objperms->send_newsletters ? 'y' : 'n';
            $res['tiki_p_subscribe_newsletters'] = $objperms->subscribe_newsletters ? 'y' : 'n';

            if (! empty($perms)) {
                $hasPerm = false;
                if (is_array($perms)) {
                    foreach ($perms as $perm) {
                        if ($res[$perm] == 'y') {
                            $hasPerm = true;
                            break;
                        }
                    }
                } else {
                    $hasPerm = $res[$perms];
                }
                if (! $hasPerm) {
                    continue;
                }
            }
            if ($full != 'n') {
                $ok = count($this->get_all_subscribers($res['nlId'], ""));
                $notok = $this->getOne("select count(*) from `tiki_newsletter_subscriptions` where `valid`=? and `nlId`=?", ['n', (int) $res['nlId']]);
                $res["users"] = $ok + $notok;
                $res["confirmed"] = $ok;
                $res['drafts'] = $this->getOne("select count(*) from `tiki_sent_newsletters` where `nlId`=? and `sent`=-1", [(int) $res['nlId']]);
            }
            $ret[] = $res;
        }
        $retval = [];
        $retval["data"] = $ret;
        $retval["cant"] = $cant;
        return $retval;
    }

    public function list_avail_newsletters()
    {
        $res = [];
        $query = "select `nlId`, `name` from `tiki_newsletters` where `allowUserSub`='y'";
        $bindvars = [];
        $result = $this->query($query, $bindvars);
        while ($rez = $result->fetchRow()) {
            $res[] = $rez;
        }
        return $res;
    }

    public function list_editions($nlId, $offset, $maxRecords, $sort_mode, $find, $drafts = false, $perm = '')
    {
        global $tikilib, $user;
        $bindvars = [];
        $mid = "";

        if ($nlId) {
            $mid .= " and tn.`nlId`=" . (int)$nlId;
            $tiki_p_admin_newsletters = $tikilib->user_has_perm_on_object($user, $nlId, 'newsletter', 'tiki_p_admin_newsletters') ? 'y' : 'n';
            $tiki_p_send_newsletters = $tikilib->user_has_perm_on_object($user, $nlId, 'newsletter', 'tiki_p_send_newsletters') ? 'y' : 'n';
            $tiki_p_subscribe_newsletters = $tikilib->user_has_perm_on_object($user, $nlId, 'newsletter', 'tiki_p_subscribe_newsletters') ? 'y' : 'n';
        }

        if ($find) {
            $findesc = '%' . $find . '%';
            $mid .= " and (`subject` like ? or `data` like ?)";
            $bindvars[] = $findesc;
            $bindvars[] = $findesc;
        }

        $mid .= ($drafts ? ' and tsn.`sent`=-1' : ' and tsn.`sent`<>-1');

        $query = "select tsn.`editionId`,tn.`nlId`,`subject`,`data`,tsn.`users`,`sent`,`name`,tsn.`wysiwyg` from `tiki_newsletters` tn, `tiki_sent_newsletters` tsn ";
        $query .= " where tn.`nlId`=tsn.`nlId` $mid order by " . $this->convertSortMode("$sort_mode");
        $result = $this->query($query, $bindvars, $maxRecords, $offset);
        $ret = [];
        $query_cant = "select count(*) from `tiki_newsletters` tn, `tiki_sent_newsletters` tsn where tn.`nlId`=tsn.`nlId` $mid";
        $cant = $this->getOne($query_cant, $bindvars);

        while ($res = $result->fetchRow()) {
            if ($nlId) {
                if ($tiki_p_admin_newsletters != 'y' && $perm && $$perm == 'n') {
                    continue;
                }
                $res['tiki_p_admin_newsletters'] = $tiki_p_admin_newsletters;
                $res['tiki_p_send_newsletters'] = $tiki_p_send_newsletters;
                $res['tiki_p_subscribe_newsletters'] = $tiki_p_subscribe_newsletters;
            } else {
                $res['tiki_p_admin_newsletters'] = $tikilib->user_has_perm_on_object($user, $res['nlId'], 'newsletter', 'tiki_p_admin_newsletters') ? 'y' : 'n';
                $res['tiki_p_send_newsletters'] = $tikilib->user_has_perm_on_object($user, $res['nlId'], 'newsletter', 'tiki_p_send_newsletters') ? 'y' : 'n';
                $res['tiki_p_subscribe_newsletters'] = $tikilib->user_has_perm_on_object($user, $res['nlId'], 'newsletter', 'tiki_p_subscribe_newsletters') ? 'y' : 'n';
                if ($perm && $res[$perm] == 'n') {
                    continue;
                }
            }
            $ret[] = $res;
        }

        $retval = [];
        $retval["data"] = $ret;
        $retval["cant"] = $cant;
        return $retval;
    }

    public function list_newsletter_subscriptions($nlId, $offset, $maxRecords, $sort_mode, $find)
    {
        $bindvars = [(int) $nlId];
        if ($find) {
            $findesc = '%' . $find . '%';
            $mid = " where `nlId`=? and (`valid` != 'y' or (`isUser` != 'g' and `included` != 'y')) and `email` like ?";
            $bindvars[] = $findesc;
        } else {
            // show all except valid by group or include newsletters
            $mid = " where `nlId`=?  and (`valid` != 'y' or (`isUser` != 'g' and `included` != 'y')) ";
        }

        $query = "select * from `tiki_newsletter_subscriptions` $mid order by " . $this->convertSortMode("$sort_mode") . ", email asc";
        $query_cant = "select count(*) from tiki_newsletter_subscriptions $mid";
        $result = $this->query($query, $bindvars, $maxRecords, $offset);
        $cant = $this->getOne($query_cant, $bindvars);
        $ret = [];

        while ($res = $result->fetchRow()) {
            $ret[] = $res;
        }
        $retval = [];
        $retval["data"] = $ret;
        $retval["cant"] = $cant;
        return $retval;
    }

    public function list_newsletter_groups($nlId, $offset = -1, $maxRecords = -1, $sort_mode = 'groupName_asc', $find = '')
    {
        $bindvars = [(int) $nlId];
        if ($find) {
            $findesc = '%' . $find . '%';
            $mid = " where `nlId`=? and `groupName` like ?";
            $bindvars[] = $findesc;
        } else {
            $mid = " where `nlId`=? ";
        }

        $query = "select * from `tiki_newsletter_groups` $mid order by " . $this->convertSortMode("$sort_mode");
        $query_cant = "select count(*) from `tiki_newsletter_groups` $mid";
        $result = $this->query($query, $bindvars, $maxRecords, $offset);
        $cant = $this->getOne($query_cant, $bindvars);
        $ret = [];

        $userlib = TikiLib::lib('user');
        while ($res = $result->fetchRow())