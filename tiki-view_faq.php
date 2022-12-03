
<?php

/**
 * @package tikiwiki
 */

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

$inputConfiguration = [
    ['staticKeyFilters' => [
        'faqId' => 'int',
        'sort_mode' => 'alpha',
        'find' => 'text',
        'sugg' => 'word',
        'suggested_question' => 'text',
        'suggested_answer' => 'purifier',
        'print' => 'word']
    ],
    [ 'catchAllUnset' => null ],
];

$section = 'faqs';
require_once('tiki-setup.php');
$faqlib = TikiLib::lib('faq');
if ($prefs['feature_categories'] == 'y') {
    $categlib = TikiLib::lib('categ');
}

$access->check_feature('feature_faqs');

if (empty($_REQUEST["faqId"])) {
    $smarty->assign('msg', tra("No FAQ indicated"));
    $smarty->display("error.tpl");
    die;
}

$tikilib->get_perm_object($_REQUEST['faqId'], 'faq');

$access->check_permission('tiki_p_view_faqs');

$faqlib->add_faq_hit($_REQUEST["faqId"]);
$smarty->assign('faqId', $_REQUEST["faqId"]);
$faq_info = $faqlib->get_faq($_REQUEST["faqId"]);
$smarty->assign('faq_info', $faq_info);
if (! isset($_REQUEST["sort_mode"])) {
    $sort_mode = 'position_asc,questionId_asc';
} else {
    $sort_mode = $_REQUEST["sort_mode"];
}
if (isset($_REQUEST["find"])) {
    $find = $_REQUEST["find"];
} else {
    $find = '';
}
$smarty->assign('find', $find);
$channels = $faqlib->list_faq_questions($_REQUEST["faqId"], 0, -1, 'position_asc,questionId_asc', $find);
$smarty->assign_by_ref('channels', $channels["data"]);
if (isset($_POST["sugg"])) {
    $access->checkCsrf();
    if ($tiki_p_suggest_faq == 'y') {
        if (empty($user) && $prefs['feature_antibot'] == 'y' && ! $captchalib->validate()) {
            Feedback::error(['mes' => $captchalib->getErrors()]);
            // Save the pending question and answer if antibot code is wrong
            $smarty->assign('pendingquestion', $_POST["suggested_question"]);
            $smarty->assign('pendinganswer', $_POST["suggested_answer"]);
        } else {
            if (! empty($_POST["suggested_question"])) {
                $faqlib->add_suggested_faq_question($_POST["faqId"], $_POST["suggested_question"], $_POST["suggested_answer"], $user);
            } else {
                Feedback::error(tra('You must suggest a question; please try again.'));
                // Save the pending answer if question is empty
                $smarty->assign('pendinganswer', $_POST["suggested_answer"]);
            }
        }
    }
}
$suggested = $faqlib->list_suggested_questions(0, -1, 'created_desc', '', $_REQUEST["faqId"]);
$smarty->assign_by_ref('suggested', $suggested["data"]);
$smarty->assign('suggested_cant', count($suggested["data"]));
include_once('tiki-section_options.php');
if ($prefs['feature_theme_control'] == 'y') {
    $cat_type = 'faq';
    $cat_objid = $_REQUEST["faqId"];
    include('tiki-tc.php');
}
// Display the template
$smarty->assign('mid', 'tiki-view_faq.tpl');
if (isset($_REQUEST['print'])) {
    $smarty->display('tiki-print.tpl');
    $smarty->assign('print', 'y');
} else {
    $smarty->display("tiki.tpl");
}