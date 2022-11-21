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

$auto_query_args = [];

$access->check_feature('feature_kaltura');
$access->check_permission(['tiki_p_upload_videos']);
//get_strings tra('Upload Media')

$smarty->assign('mid', 'tiki-kaltura_upload.tpl');
$smarty->display("tiki.tpl");
