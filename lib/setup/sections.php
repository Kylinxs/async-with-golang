<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

//this script may only be included - so its better to die if called directly.
if (basename($_SERVER['SCRIPT_NAME']) === basename(__FILE__)) {
    header('location: index.php');
    exit;
}

use Tiki\Sections;

$sections = Sections::getSections();

if (! isset($section)) {
    $section = '';
}

$sections_enabled = [];

foreach ($sections as $sec => $dat) {
    $feat = $dat['feature'];
    if ($feat === '' or ( isset($prefs[$feat]) and $prefs[$feat] == 'y' )) {
        $sections_enabled[$sec] = $dat;
    }
}

ksort($sections_enabled);
$smarty->assign_by_ref('sections_enabled', $sections_enabled);
if (! empty($section)) {
    $smarty->assign('section', $section);
}

if (! empty($section_class)) {
    $smarty->assign('section_class', $section_class);
} elseif (! empty($section)) {
    $section_class = 'tiki_' . str_replace(' ', '_', $section);
    $smarty->assign('section_class', $section_class);
}

function current_object()
{
    return Sections::currentObject();
}
