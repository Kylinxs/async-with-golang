<?php

/**
 * @package tikiwiki
 */

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

$auto_query_args = ['page_ref_id', 'offset', 'find_objects'];
require_once('tiki-setup.php');

$structlib = TikiLib::lib('struct');
$access->check_feature(['feature_wiki','feature_wiki_structure']);
if (! isset($_REQUEST["page_ref_id"])) {
    $smarty->assign('msg', tra("No structure indicated"));
    $smarty->display("error.tpl");
    die;
}

$page_info = $structlib->s_get_page_info($_REQUEST["page_ref_id"]);

$access->check_permission('tiki_p_view', tra('View this wiki page'), 'wiki page', $page_info['pageName']);

if (isset($_REQUEST['move_to'])) {
    check_ticket('edit-structure');
    $structlib->move_to_structure($_REQUEST['page_ref_id'], $_REQUEST['structure_id'], $_REQUEST['begin']);
}

$structure_info = $structlib->s_get_structure_info($_REQUEST["page_ref_id"]);

$smarty->assign('page_ref_id', $_REQUEST["page_ref_id"]);
$smarty->assign('structure_id', $structure_info["page_ref_id"]);
$smarty->assign('structure_name', $structure_info["pageName"]);

$perms = Perms::get((['type' => 'wiki page', 'object' => $structure_info["pageName"]]));
$tikilib->get_perm_object($structure_info["pageName"], 'wiki page', $page_info);    // global perms still needed for logic in categorize.tpl

if (! $perms->view) {
    $smarty->assign('errortype', 401);
    $smarty->assign('msg', tra('You do not have permission to view this page.'));
    $smarty->display("error.tpl");
    die;
}

if ($perms->edit_structures) {
    if ($prefs['lock_wiki_structures'] === 'y') {
        $lockedby = TikiLib::lib('attribute')->get_attribute('wiki structure', $structure_info['pageName'], 'tiki.object.lock');
        if ($lockedby && $lockedby === $user && $perms->lock_structures || ! $lockedby || $perms->admin_structures) {
            $editable = 'y';
        } else {
            $editable = 'n';
        }
    } else {
        $editable = 'y';
    }
} else {
    $editable = 'n';
}
$smarty->assign('editable', $editable);


$alert_categorized = [];
$alert_in_st = [];
$alert_to_remove_cats = [];
$alert_to_remove_extra_cats = [];

// needed here for filtering later in the search results
$subtree = $structlib->get_subtree($structure_info["page_ref_id"]);

// start security hardened section
if ($editable === 'y') {
    $smarty->assign('remove', 'n');

    if (isset($_REQUEST["remove"])) {
        check_ticket('edit-structure');
        $smarty->assign('remove', 'y');
        $remove_info = $structlib->s_get_page_info($_REQUEST["remove"]);
          $structs = $structlib->get_page_structures($remove_info['pageName'], $structure);
        //If page is member of more than one structure, do not give option to remove page
        $single_struct = (count($structs) == 1);
        if ($single_struct && $perms->remove) {
            $smarty->assign('page_removable', 'y');
        } else {
            $smarty->assign('page_removable', 'n');
        }
        $smarty->assign('removepage', $_REQUEST["remove"]);
        $smarty->assign('removePageName', $remove_info["pageName"]);
    }

    if (isset($_REQUEST["rremove"])) {
        $access->check_authenticity();
        $structlib->s_remove_page($_REQUEST["rremove"], false, empty($_REQUEST['page']) ? '' : $_REQUEST['page']);
        $_REQUEST["page_ref_id"] = $page_info["parent_id"];
    }
    # TODO : Case where the index page of the structure is removed seems to be unexpected, leaving a corrupted structure
    if (isset($_REQUEST["sremove"])) {
        $access->check_authenticity();
        $page = $page_info["pageName"];
        $delete = $tikilib->user_has_perm_on_object($user, $page_info['pageName'], 'wiki page', 'tiki_p_remove');
        $structlib->s_remove_page($_REQUEST["sremove"], $delete, empty($_REQUEST['page']) ? '' : $_REQUEST['page']);
        $_REQUEST["page_ref_id"] = $page_info["parent_id"];
    }

    if ($prefs['feature_user_watches'] == 'y' && $tiki_p_watch_structure == 'y' && $user && ! empty($_REQUEST['watch_object']) && ! empty($_REQUEST['watch_action'])) {
        check_ticket('edit-structure');
        if ($_REQUEST['watch_action'] == 'add' && ! e