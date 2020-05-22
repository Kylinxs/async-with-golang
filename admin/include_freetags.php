<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

if (basename($_SERVER['SCRIPT_NAME']) === basename(__FILE__)) {
    die('This script may only be included.');
}

require_once('tiki-setup.php');

if (isset($_POST["cleanup"]) && $access->checkCsrf()) {
    $freetaglib = TikiLib::lib('freetag');
    $result = $freetaglib->cleanup_tags();
    if ($result) {
        Feedback::success(tr('Tags successfully cleaned up.'));
    } else {
        Feedback::error(tr('Tag cleanup failed.'));
    }
}
