<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER['SCRIPT_NAME'], basename(__FILE__)) !== false) {
    header('location: index.php');
    exit;
}

class ArtLib extends TikiLib
{
    //Special parsing for multipage articles
    public function get_number_of_pages($data)
    {
        $parts = explode('...page...', $data);
        return count($parts);
    }

    public function get_page($data, $i)
    {
        // Get slides
        $parts = explode('...page...', $data);

        if (! isset($parts[$i - 1])) {
            $i = 1;
        }
        $ret = $parts[$i - 1];
        if (substr($parts[$i - 1], 1, 5) == '<br/>') {
            $ret = substr($parts[$i - 1], 6);
        }

        if (substr($parts[$i - 1], 1, 6) == '<br />') {
            $ret = substr($parts[$i - 1], 7);
        }

        return $ret;
    }

    public function approve_submission($subId)
    {
        $data = $this->get_submission($subId);

        if (! $data) {
            return false;
        }

        if (! $data['image_x']) {
            $data['image_x'] = 0;
        }

        if (! $data['image_y']) {
            $data['image_y'] = 0;
        }

        $articleId = $this->replace_article(
            $data['title'],
            $data['authorName'],
            $data['topicId'],
            $data['useImage'],
            $data['image_name'],
            $data['image_size'],
            $data['image_type'],
            $data['image_data'],
            $data['heading'],
            $data['body'],
            $data['publishDate'],
            $data['expireDate'],
            $data['author'],
            0,
            $data['image_x'],
            $data['image_y'],
            $data['type'],
            $data['topline'],
            $data['subtitle'],
            $data['linkto'],
            $data['image_caption'],
            $data['lang'],
            $data['rating'],
            $data['isfloat']
        );
        $this->transfer_attributes_from_submission($subId, $articleId);

        $query = "update `tiki_objects` set `type`= ?, `itemId`= ?, `href`=? where `itemId` = ? and `type`= ?";
        $this->query($query, ['article', (int)$articleId, "tiki-read_article.php?articleId=$articleId", (int)$subId, 'submission']);
        $query = 'update `tiki_objects` set `href`=?, `type`=? where `href`=?';
        $this->query($query, ["'tiki-read_article.php?articleId=$articleId", 'article', "tiki-edit_submission.php?subId=$subId"]);

        $this->remove_submission($subId);
    }

    public function add_article_hit($articleId)
    {
        if (StatsLib::is_stats_hit()) {
            $query = "update `tiki_articles` set `nbreads`=`nbreads`+1 where `articleId`=?";
            $this->query($query, [$articleId]);
        }
    }

    public function remove_article($articleId, $article_data = '')
    {
        global $user, $prefs;
        $smarty = TikiLib::lib('smarty');
        $tikilib = TikiLib::lib('tiki');

        if ($articleId) {
            if (empty($article_data)) {
                $article_data = $this->get_article($articleId);
            }
            $query = 'delete from `tiki_articles` where `articleId`=?';

            $result = $this->query($query, [$articleId]);
            $this->remove_object('article', $articleId);

            $multilinguallib = TikiLib::lib('multilingual');
            $multilinguallib->detachTranslation('article', $articleId);

            TikiLib::events()->trigger(
                'tiki.article.delete',
                [
                    'type' => 'article',
                    'object' => $articleId,
                    'user' => $user,
                ]
            );

            // TODO refactor
            $nots = $tikilib->get_event_watches('article_deleted', '*');
            if (! empty($article_data['topicId'])) {
                $nots2 = $tikilib->get_event_watches('topic_article_deleted', $article_data['topicId']);
            } else {
                $nots2 = [];
            }
            $smarty->assign('mail_action', 'Delete');

            $nots3 = [];
            foreach ($nots as $n) {
                $nots3[] = $n['email'];
            }
            foreach ($nots2 as $n) {
                if (! in_array($n['email'], $nots3)) {
                    $nots[] = $n;
                }
            }

            if ($prefs['user_article_watch_editor'] != "y") {
                for ($i = count($nots) - 1; $i >= 0; --$i) {
                    if ($nots[$i]['user'] == $user) {
                        unset($nots[$i]);
                        break;
                    }
                }
            }

            if (! isset($_SERVER['SERVER_NAME'])) {
                $_SERVER['SERVER_NAME'] = $_SERVER["HTTP_HOST"];
            }

            if ($prefs['feature_user_watches'] == 'y' && $prefs['feature_daily_report_watches'] == 'y') {
                $reportsManager = Reports_Factory::build('Reports_Manager');
                $reportsManager->addToCache(
                    $nots,
                    [
                        'event'             => 'article_deleted',
                        'articleId'         => $articleId,
                        'articleTitle'      => $article_data['title'],
                        'authorName'        => $article_data['authorName'],
                        'user'              => $user
                    ]
                );
            }

            if (count($nots) || (! empty($emails) && is_array($emails))) {
                include_once('lib/notifications/notificationemaillib.php');

                $smarty->assign('mail_site', $_SERVER['SERVER_NAME']);
                $smarty->assign('mail_title', 'articleId=' . $articleId);
                $smarty->assign('mail_postid', $articleId);
                $smarty->assign('mail_user', $user);
                $smarty->assign('mail_current_data', $article_data['heading'] . "\n----------------------\n" . $article_data['body']);

                // the strings below are used to localize messages in the template file
                //get_strings tr('New article post:') tr('Edited article post:') tr('Deleted article post:')
                sendEmailNotification($nots, 'watch', 'user_watch_article_post_subject.tpl', $_SERVER['SERVER_NAME'], 'user_watch_article_post.tpl');
            }

            return true;
        }
    }

    public function remove_submission($subId)
    {
        if ($subId) {
            $query = 'delete from `tiki_submissions` where `subId`=?';
            $result = $this->query($query, [(int) $subId]);
            $this->remove_object('submission', $subId);
            return true;
        }
    }

    public function delete_expired_submissions($maxrows = 1000)
    {
        $tiki_submissions = TikiDb::get()->table('tiki_submissions');

        $expired = $tiki_submissions->fetchColumn(
            'subId',
            ['expireDate' => $tiki_submissions->lesserThan($this->now)],
            $maxrows
        );

        $transaction = $this->begin();

        foreach ($expired as $subId) {
            $tiki_submissions->delete(['subId' => $subId]);

            $this->remove_object('submission', $subId);
        }

        $transaction->commit();


        return true;
    }

    public function replace_submission($title, $authorName, $topicId, $useImage, $imgname, $imgsize, $imgtype, $imgdata, $heading, $body, $publishDate, $expireDate, $user, $subId, $image_x, $image_y, $type, $topline, $subtitle