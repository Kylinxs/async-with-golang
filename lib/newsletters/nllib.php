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
                $query = "insert into `tik