<?php

/**
 * @package tikiwiki
 */

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

$section = 'trackers';

if (isset($_REQUEST["trackerId"]) && ! is_numeric($_REQUEST["trackerId"])) {
    $params = explode("-", $_REQUEST['trackerId']);
    $_REQUEST["trackerId"] = $_GET['trackerId'] = $params[0];
}

require_once('t