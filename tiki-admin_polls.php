
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
$access->check_feature('feature_polls');
$access->check_permission('tiki_p_admin_polls');

$polllib = TikiLib::lib('poll');

$auto_query_args = ['pollId', 'sort_mode', 'offset', 'find'];

//Use 12- or 24-hour clock for $publishDate time selector based on admin and user preferences
$userprefslib = TikiLib::lib('userprefs');
$smarty->assign('use_24hr_clock', $userprefslib->get_user_clock_pref($user));

if (! isset($_REQUEST["pollId"])) {
    $_REQUEST["pollId"] = 0;
}
$smarty->assign('pollId', $_REQUEST["pollId"]);
if (isset($_REQUEST["setlast"]) && $access->checkCsrf()) {
    $result = $polllib->set_last_poll();
    if ($result) {
        if ($result->numRows()) {
            Feedback::success(tr('Last poll set as current'));
        } else {
            Feedback::note(tr('No changes made, last poll already set as current'));
        }
    } else {
        Feedback::error(tr('Last poll failed to set as current'));
    }
}
if (isset($_REQUEST["closeall"]) && $access->checkCsrf()) {
    $result = $polllib->close_all_polls();
    if ($result) {
        if ($numRows = $result->numRows()) {
            $msg = $numRows == 1 ? tr('One poll closed') : tr('%0 polls closed', $numRows);
            Feedback::success($msg);
        } else {
            Feedback::note(tr('No changes made, polls already closed'));
        }
    } else {
        Feedback::error(tr('Polls not closed'));
    }
}
if (isset($_REQUEST["activeall"]) && $access->checkCsrf()) {
    $result = $polllib->active_all_polls();
    if ($result) {
        if ($numRows = $result->numRows()) {
            $msg = $numRows == 1 ? tr('One poll activated') : tr('%0 polls activated', $numRows);
            Feedback::success($msg);
        } else {
            Feedback::note(tr('No changes made, polls already activated'));
        }
    } else {
        Feedback::error(tr('Polls not activated'));
    }
}
if (isset($_REQUEST["remove"]) && $access->checkCsrf(true)) {
    $result = $polllib->remove_poll($_REQUEST["remove"]);
    if ($result && $result->numRows()) {
        Feedback::success(tr('Poll deleted'));
    } else {
        Feedback::error(tr('Poll not deleted'));
    }
}
if ((isset($_REQUEST["save"]) || isset($_REQUEST["add"])) && $access->checkCsrf()) {
    if ($prefs['feature_jscalendar'] == 'y' && ! empty($_REQUEST['pollPublishDate'])) {
        $publishDate = (int) $_REQUEST['pollPublishDate'];
    } else {
        //Convert 12-hour clock hours to 24-hour scale to compute time
        if (! empty($_REQUEST['Time_Meridian'])) {
            $_REQUEST['Time_Hour'] = date('H', strtotime($_REQUEST['Time_Hour'] . ':00 ' . $_REQUEST['Time_Meridian']));
        }
        $publishDate = $tikilib->make_time($_REQUEST["Time_Hour"], $_REQUEST["Time_Minute"], 0, $_REQUEST["Date_Month"], $_REQUEST["Date_Day"], $_REQUEST["Date_Year"]);
    }
    if (! isset($_REQUEST['voteConsiderationSpan'])) {
        $_REQUEST['voteConsiderationSpan'] = 0;
    }
    $pid = $polllib->replace_poll($_REQUEST["pollId"], $_REQUEST["title"], $_REQUEST["active"], $publishDate, $_REQUEST['voteConsiderationSpan']);
    $position = 0;
    if (isset($_REQUEST['options']) && is_array($_REQUEST['options'])) {
        //TODO insert options into poll
        $optionSuccess = 0;
        foreach ($_REQUEST['options'] as $i => $option) {
            //continue;
            if ($option == "") {
                if (isset($_REQUEST['optionsId']) && isset($_REQUEST['optionsId'][$i])) {
                    $result = $polllib->remove_poll_option($_REQUEST['optionsId'][$i]);
                }
                if ($result && $result->numRows()) {
                    $optionSuccess++;
                    // unset $result to avoid false counts
                    unset($result);
                }
            } else {
                $oid = isset($_REQUEST['optionsId']) && isset($_REQUEST['optionsId'][$i]) ? $_REQUEST['optionsId'][$i] : null;
                $result = $polllib->replace_poll_option($pid, $oid, $option, $position++);
                if ($result && $result->numRows()) {
                    $optionSuccess++;
                    // unset $result to avoid false counts
                    unset($result);
                }
            }
        }
        if ($pid) {
            if ($optionSuccess) {
                $msg = $optionSuccess === 1
                    ? tr('Poll saved with one option added or changed (including only changing the option position)')
                    : tr(
                        'Poll saved with %0 options added or changed (including only changing the option position)',
                        $optionSuccess
                    );
                Feedback::success($msg);
            } else {
                $msg = tr('Poll saved with no options added or changed');
            }
            Feedback::success($msg);
        } else {
            Feedback::error(tr('Poll not saved'));
        }
    }
    $cat_type = 'poll';
    $cat_objid = $pid;
    $cat_desc = substr($_REQUEST["title"], 0, 200);
    $cat_name = $_REQUEST["title"] ? $_REQUEST["title"] : '';
    $cat_href = "tiki-poll_results.php?pollId=" . $cat_objid;
    include_once("categorize.php");
}
if (
    isset($_REQUEST['addPoll'])
    && ! empty($_REQUEST['poll_template'])
    && ! empty($_REQUEST['pages'])
    && $access->checkCsrf()
) {
    $wikilib = TikiLib::lib('wiki');
    $categlib = TikiLib::lib('categ');
    $cat_type = 'wiki page';
    foreach ($_REQUEST['pages'] as $cat_objid) {
        if (! $catObjectId = $categlib->is_categorized($cat_type, $cat_objid)) {
            $info = $tikilib->get_page_info($cat_objid);
            $cat_desc = $info['description'];
            $cat_href = 'tiki-index.php?page=' . urlencode($cat_objid);
            $cat_name = ! empty($_REQUEST["title"]) ? $_REQUEST["title"] : '';
        }
        include('poll_categorize.php');
        if (isset($_REQUEST['locked']) && $_REQUEST['locked'] == 'on' && $prefs['feature_wiki_usrlock'] == 'y') {
            $result = $wikilib->lock_page($cat_objid);
            if ($result) {
                if ($result->numRows()) {
                    Feedback::success(tr('Page %0 locked', $cat_objid));
                } else {
                    Feedback::note(tr('Page %0 already locked', $cat_objid));
                }
            } else {
                Feedback::error(tr('Page %0 not locked', $cat_objid));
            }
        }
    }
}
if ($_REQUEST["pollId"]) {
    $info = $polllib->get_poll($_REQUEST["pollId"]);
    $options = $polllib->list_poll_options($_REQUEST["pollId"]);
    $cookietab = 1;
} else {
    $info = [];
    $info["title"] = '';
    $info["active"] = 'y';
    $info["publishDate"] = $tikilib->now;
    $info['voteConsiderationSpan'] = 0;
    $options = [];
}

$smarty->assign('info', $info);
$smarty->assign('options', $options);
if (! isset($_REQUEST["sort_mode"])) {
    $sort_mode = 'publishDate_desc';
} else {
    $sort_mode = $_REQUEST["sort_mode"];
}
if (! isset($_REQUEST["offset"])) {
    $offset = 0;
} else {
    $offset = $_REQUEST["offset"];
}
$smarty->assign_by_ref('offset', $offset);
if (isset($_REQUEST["find"])) {
    $find = $_REQUEST["find"];
} else {
    $find = '';
}
$smarty->assign('find', $find);
$smarty->assign_by_ref('sort_mode', $sort_mode);
$channels = $polllib->list_polls($offset, $maxRecords, $sort_mode, $find);
$smarty->assign_by_ref('cant_pages', $channels["cant"]);
if ($prefs['poll_list_categories'] == 'y') {
    foreach ($channels['data'] as $key => $channel) {
        $channels['data'][$key]['categories'] = $polllib->get_poll_categories($channel['pollId']);
    }
}
if ($prefs['poll_list_objects'] == 'y') {
    foreach ($channels['data'] as $key => $channel) {
        $channels['data'][$key]['objects'] = $polllib->get_poll_objects($channel['pollId']);
    }
}
$smarty->assign_by_ref('channels', $channels["data"]);
$listPages = $tikilib->list_pageNames();
$smarty->assign_by_ref('listPages', $listPages['data']);
$cat_type = 'poll';
$cat_objid = $_REQUEST["pollId"];
include_once("categorize_list.php");
// disallow robots to index page:
$smarty->assign('metatag_robots', 'NOINDEX, NOFOLLOW');
// Display the template
$smarty->assign('mid', 'tiki-admin_polls.tpl');
$smarty->display("tiki.tpl");