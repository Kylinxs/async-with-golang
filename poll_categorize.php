
<?php

/**
 * @package tikiwiki
 */

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

if (basename($_SERVER['SCRIPT_NAME']) === basename(__FILE__)) {
    die('This script may only be included.');
}
//smarty is not there - we need setup
require_once('tiki-setup.php');

global $prefs;
if ($prefs['feature_polls'] == 'y') {
    #echo '<div>hier</div>';
    $polllib = TikiLib::lib('poll');
    $categlib = TikiLib::lib('categ');

    if (! isset($_REQUEST['poll_title'])) {
        $_REQUEST['poll_title'] = 'rate it!';
    }

    if (
        (isset($_REQUEST["poll_template"]) and $_REQUEST["poll_template"])
        || (isset($_REQUEST["olpoll"]) and $_REQUEST["olpoll"])
    ) {
        $catObjectId = $categlib->is_categorized($cat_type, $cat_objid);
        if (! $catObjectId) {
            $catObjectId = $categlib->add_categorized_object($cat_type, $cat_objid, $cat_desc, $cat_name, $cat_href);
            if (! $catObjectId) {
                Feedback::error(tr('Error categorizing poll object'));
            }
        }
        if (isset($_REQUEST["poll_template"]) and $_REQUEST["poll_template"]) {
            if ($polllib->has_object_polls($catObjectId) && $prefs['poll_multiple_per_object'] != 'y') {
                $polllib->remove_object_poll($cat_type, $cat_objid);
            }
            $pollid = $polllib->create_poll($_REQUEST["poll_template"], $cat_objid . ': ' . $_REQUEST['poll_title']);
            $result = $polllib->poll_categorize($catObjectId, $pollid, $_REQUEST['poll_title']);
        } else {
            $olpoll = $polllib->get_poll($_REQUEST["olpoll"]);
            $result = $polllib->poll_categorize($catObjectId, $_REQUEST["olpoll"], $olpoll['title']);
        }
        if ($result && $result->numRows()) {
            Feedback::success(tr('Poll associated with wiki page %0', htmlspecialchars($cat_objid)));
        } else {
            Feedback::error(tr('Poll not associated with wiki page %0', htmlspecialchars($cat_objid)));
        }
    }
}