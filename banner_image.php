
<?php

/**
 * @package tikiwiki
 */

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

// application to display an image from the database with
// option to resize the image dynamically creating a thumbnail on the fly.
require_once('tiki-setup.php');
if (! isset($_REQUEST["id"])) {
    die;
}

$id = (int) $_REQUEST['id'];
$defaultCache = 'temp/public';



$access->check_feature('feature_banners');

$bannercachefile = $prefs['tmpDir'];

if ($tikidomain) {
    $bannercachefile .= "/$tikidomain";
}

$bannercachefile .= "/banner." . (int)$_REQUEST["id"];

if (is_file($bannercachefile) and (! isset($_REQUEST["reload"]))) {
    $size = getimagesize($bannercachefile);
    $type = $size['mime'];
} else {
    $bannerlib = TikiLib::lib('banner');
    $info = $bannerlib->get_banner($_REQUEST["id"]);
    if (! $info) {
        die;
    }
    $type = $info["imageType"];
    $data = $info["imageData"];
    if ($data) {
        file_put_contents($bannercachefile, $data);
    }
}

header("Content-Type: $type");
if (is_file($bannercachefile)) {
    readfile($bannercachefile);
} else {
    Feedback::error(tr('Banner #%0 image cache file not found', $_REQUEST['id']));
}