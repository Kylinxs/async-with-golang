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
include_once('lib/directory/dirlib.php');
$access->check_feature('feature_directory');
$access->check_permission('tiki_p_validate_links');
if (isset($_REQUEST["validate"]) && isset($_REQUEST['sites'])) {
    check_ticket('dir-validate');
    foreach (array_keys($_REQUEST["sites"]) as $siteId) {
        $dirlib->dir_validate_site($siteId);
    }
}
if (isset($_REQUEST["remove"])) {
    $access->check_authenticity();
    $dirlib->dir_remove_site($_REQUEST["remove"]);
}
if (isset($_REQUEST["del"]) && isset($_REQUEST['sites'])) {
    check_ticket('dir-validate');
    foreach (array_keys($_REQUEST["sites"]) as $siteId) {
        $dirlib->dir_remove_site($siteId);
    }
}
// Listing: invalid sites
// Pagination resolution
if (! isset($_REQUEST["sort_mode"])) {
    $sort_mode = 'created_desc';
} else {
    $sort_mode = $_REQUEST["sort_mode"];
}
if (! isset($_REQUES