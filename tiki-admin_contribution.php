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
$access->check_feature('feature_contribution');

$contributionlib = TikiLib::lib('contribution');
$access->check_permission(['tiki_p_admin_contribution']);

if (isset($_REQUEST['setting']) && $access->checkCsrf()) {
    $result = false;
    if (
        isset($_REQUEST['feature_contribution_mandatory'])
        && $_REQUEST['feature_contribution_mandatory'] == "on"
    ) {
        $result = $tikilib->set_preference('feature_contribution_mandatory', 'y');
    } else {
        $result = $tikilib->set_preference('feature_contribution_mandatory', 'n');
    }
    if (
        isset($_REQUEST['feature_contribution_mandatory_forum'])
        && $_REQUEST['feature_contribution_mandatory_forum'] == "on"
    ) {
        $result = $tikilib->set_preference('feature_contribution_mandatory_forum', 'y');
    } else {
        $result = $tikilib->set_preference('feature_contribution_mandatory_forum', 'n');
    }
    if (
        isset($_REQUEST['feature_contribution_mandatory_comment'])
        && $_REQUEST['feature_contribution_mandatory_comment'] == "on"
    ) {
        $result = $tikilib->set_preference('feature_contribution_mandatory_comment', 'y');
    } else {
        $result = $tikilib->set_preference('feature_contribution_mandatory_comment', 'n');
    }
    if (
        isset($_REQUEST['feature_contribution_mandatory_blog'])
        && $_REQUEST['feature_contribution_mandatory_blog'] == "on"
    ) {
        $result = $tikilib->set_preference('feature_contribution_mandatory_blog', 'y');
    } else {
        $result = $tikilib->set_preference('feat