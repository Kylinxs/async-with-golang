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
        $result = $tikilib->set_preference('feature_contribution_mandatory_blog', 'n');
    }
    if (
        isset($_REQUEST['feature_contribution_display_in_comment'])
        && $_REQUEST['feature_contribution_display_in_comment'] == "on"
    ) {
        $result = $tikilib->set_preference('feature_contribution_display_in_comment', 'y');
    } else {
        $result = $tikilib->set_preference('feature_contribution_display_in_comment', 'n');
    }
    if (isset($_REQUEST['feature_contributor_wiki']) && $_REQUEST['feature_contributor_wiki'] == "on") {
        $result = $tikilib->set_preference('feature_contributor_wiki', 'y');
    } else {
        $result = $tikilib->set_preference('feature_contributor_wiki', 'n');
    }
    if ($result) {
        Feedback::success(tr('Contribution settings saved'));
    } else {
        Feedback::error(tr('Contribution settings not saved'));
    }
}
if (isset($_REQUEST['add']) && isset($_REQUEST['new_contribution_name']) && $access->checkCsrf()) {
    $result = $contributionlib->add_contribution(
        $_REQUEST['new_contribution_name'],
        isset($_REQUEST['description']) ? $_REQUEST['description'] : ''
    );
    if ($result && $result->numRows()) {
        Feedback::success(tr('Contribution added'));
    } else {
        Feedback::error(tr('Contribution not added'));
    }
}
if (
    isset($_REQUEST['replace'])
    && isset($_REQUEST['name'])
    && isset($_REQUEST['contributionId'])
    && $access->checkCsrf()
) {
    $result = $contributionlib->replace_contribution(
        $_REQUEST['contributionId'],
        $_REQUEST['name'],
        isset($_REQUEST['description']) ? $_REQUEST['description'] : ''
    );
    if ($result && $result->numRows()) {
        Feedback::success(tr('Contribution modified'));
    } else {
        Feedback::error(tr('Contribution not modified'));
    }
    unset($_REQUEST['contributionId']);
}
if (isset($_REQUEST['remove']) && $access->checkCsrf(true)) {
    $result = $contributionlib->remove_contribution($_REQUEST['remove']);
    if ($result && $result->numRows()) {
        Feedback::success(tr('Contribution removed'));
    } else {
        Feedback::error(tr('Contribution not removed'));
    }
}
if (isset($_REQUEST['contributionId'])) {
    $contribution = $contributionlib->get_contribution($_REQUEST['contributionId']);
    $smarty->assign('contribution', $contribution);
}
$contributions = $contributionlib->list_contributions();
$smarty->assign_by_ref('contributions', $contributions['data']);
$smarty->assign('mid', 'tiki-admin_contribution.tpl');
$smarty->display("tiki.tpl");
