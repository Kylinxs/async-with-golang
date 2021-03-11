<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

$section = 'cms';
require_once('tiki-setup.php');
$artlib = TikiLib::lib('art');

$access->check_feature('feature_articles');
$access->check_permission('tiki_p_admin_cms');

if (! isset($_REQUEST["topicid"])) {
    $smarty->assign('msg', tra("No topic id specified"));
    $smarty->display("error.tpl");
    die;
}

$topic_info = $artlib->get_topic($_REQUEST["topicid"]);
if ($topic_info == DB_ERROR) {
    $smarty->assign('msg', tra("Invalid topic id specified"));
    $smarty->display("error.tpl");
    die;
}
$smarty->assign_by_ref('topic_info', $topic_info);
$errors = false;
if (isset($_REQUEST["edittopic"])) {
    if (isset($_FILES['userfile1']) && is_uploaded_file($_FILES['userfile1']['tmp_name'])) {
        $filegallib = TikiLib::lib('filegal');
        try {
            $filegallib->assertUploadedFileIsSafe($_FILES['userfile1']['tmp_name'], $_FILES['userfile1']['name']);
        } catch (Exception $e) {
 