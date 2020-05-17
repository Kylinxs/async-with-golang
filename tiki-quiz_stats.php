<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

$section = 'quizzes';
require_once('tiki-setup.php');
$quizlib = TikiLib::lib('quiz');

$auto_query_args = ['sort_mode', 'offset', 'find'];

$access->check_feature('feature_quizzes');
$access->check_permission('tiki_p_view_quiz_stats');

if (! isset($_REQUEST["sort_mode"])) {
    $sort_mode = 'quizName_asc';
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
$channels = $quizlib->list_quiz_sum_stats($offset, $maxRecords, $sort_mode, $find);
$temp_max = count($channels["data"]);
for ($i = 0; $i < $temp_max; $i++) {
    if ($userlib->object_has_one_permission($channels["data"][$i]["quizId"], 'quiz')) {
        $channels["data"][$i]["individual"] = 'y';
        if ($userlib->object_has_permission($user, $channels["data"][$i]["quizId"], 'quiz', 'tiki_p_take_quiz')) {
            $channels["data"][$i]["individual_tiki_p_take_quiz"] = 'y';
        } else {
            $channels["data"][$i]["individual_tiki_p_take_quiz"] = 'n';
        }
        if ($userlib->object_has_permission($user, $channels["data"][$i]["quizId"], 'quiz', 'tiki_p_view_quiz_stats')) {
            $channels["data"][$i]["individual_tiki_p_view_quiz_stats"] = 'y';
        } else {
            $channels["data"][$i]["individual_tiki_p_view_quiz_stats"] = 'n';
        }
        if ($userlib->object_has_permission($user, $channels["data"][$i]["quizId"], 'quiz', 'tiki_p_view_user_stats')) {
            $channels["data"][$i]["individual_tiki_p_view