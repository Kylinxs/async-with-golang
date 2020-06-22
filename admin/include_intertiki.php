<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

// This script may only be included - so its better to die if called directly.
if (strpos($_SERVER['SCRIPT_NAME'], basename(__FILE__)) !== false) {
    header('location: index.php');
    exit;
}
if (! isset($_REQUEST['interlist'])) {
    $_REQUEST['interlist'] = [];
}
if (! isset($_REQUEST['known_hosts'])) {
    $_REQUEST['known_hosts'] = [];
}

//*** begin state-changing actions
//TODO Avoid altering $_POST variable directly in this section
if (isset($_POST['del']) && $access->checkCsrf(true)) {
    foreach ($prefs['interlist'] as $k => $i) {
        if ($k == $_POST['del']) {
            unset($_POST['interlist'][$k]);
        }
    }
    simple_set_value('interlist');
    //to refresh interlist dropdown - not sure if there's a better way to do this
    $access->redirect($_SERVER['REQUEST_URI'], '', 200);
}
if (isset($_POST['delk']) && $access->checkCsrf(true)) {
    foreach ($prefs['known_hosts'] as $k => $i) {
        if ($k == $_POST['delk']) {
            unset($_POST['known_hosts'][$k]);
        }
    }
    simple_set_value('known_hosts');
}
if (isset($_POST['new']) && is_array($_POST['new']) && $_POST['new']['name'] && $access->checkCsrf()) {
    $_POST['interlist']["{$_POST['new']['name']}"] = $_POST['new'];
    simple_set_value('interlist');
}

if (isset($_POST['newhost']) && is_array($_POST['newhost']) && $_POST['newhost']['key'] && $access->checkCsrf()) {
    $_POST['known_hosts']["{$_POST['newhost']['key']}"] = $_POST['newhost'];
    simple_set_value('known_hosts');
}
if (! empty($_POST['known_hosts']) && $access->checkCsrf()) {
    foreach ($_POST['known_hosts'] as $k => $v) {
        if (isset($_POST['known_hosts'][$k]['allowusersregister'])) {
            $_POST['known_hosts'][$k]['allowusersregister'] = 'y';
        }
        if (
            empty($_POST['known_hosts'][$k]['name'])
            && empty($_POST['known_hosts'][$k]['key'])
            && empty($_POST['known_hosts'][$k]['ip'])
            && empty($_POST['known_hosts'][$k]['contact'])
        ) {
            unset($_POST['known_hosts'][$k]);
        }
    }
    simple_set_value('known_hosts');
}
