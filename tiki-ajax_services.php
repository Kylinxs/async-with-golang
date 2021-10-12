
<?php

/**
 * @package tikiwiki
 */

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

// To contain data services for ajax calls
//
// If controller and action are specified in the request, the controller class matching the
// controller key in the $contollerMap registry will be instantiated. The method matching the
// action name will be called. The input to the method is a JitFilter. The output of the method
// will be serialized and sent to the browser.
//
// Otherwise, the procedural script remains

$inputConfiguration = [[
    'staticKeyFilters' => [
        'action' => 'word',
        'controller' => 'text',
        'sort_mode' => 'word',      // templates/search/list/table.tpl:68
        'watch' => 'alpha',         // templates/comment/post.tpl:58
        'confirmForm' => 'alpha',   // \Services_Utilities::isConfirmPost
        'itemId' => 'int',          // often used in \ParserLib::parse_wiki_argvariable
    ],
]];

if (isset($_REQUEST['controller'], $_REQUEST['action']) || isset($_GET['controller'], $_GET['action'])) {
    $inputConfiguration[] = ['catchAllUnset' => null];
}

//Some times the filters spit out some errors, here we get the error into a var, so the ajax still works.
ob_start();
require_once('tiki-setup.php');
$errMsg = ob_get_clean();

if (isset($_REQUEST['controller'], $_REQUEST['action'])) {
    $controller = $_REQUEST['controller'];
    $extensionPackage = '';

    if (strpos($_REQUEST['controller'], ".") !== false) {
        $parts = explode(".", $_REQUEST['controller']);
        if (count($parts) == 3) {
            $extensionPackage = $parts[0] . "." . $parts[1];
            $controller = $parts[2];
        }
    }

    $action = $_REQUEST['action'];

    $broker = TikiLib::lib('service')->getBroker($extensionPackage);
    $broker->process($controller, $action, $jitRequest);
    exit;
}

if ($access->is_serializable_request() && isset($_REQUEST['listonly'])) {
    $access->check_feature('feature_jquery_autocomplete');

    $query = $jitRequest->q->text();

    $sep = '|';
    if (isset($_REQUEST['separator'])) {
        $sep = $_REQUEST['separator'];
    }
    $p = strrpos($query, $sep);
    if ($p !== false) {
        $query = substr($query, $p + 1);
    }

    if (empty($query)) {
        $access->output_serialized([]);
        return;
    }
    if ($_REQUEST['listonly'] == 'groups') {
        $listgroups = $userlib->get_groups(0, -1, 'groupName_asc', '', '', 'n');

        // TODO proper perms checking - this looks right but returns nothing for reg, and everything for admin
        // $listgroups['data'] = Perms::filter( array( 'type' => 'group' ), 'object', $listgroups['data'], array( 'object' => 'groupName' ), 'view_group' );

        $grs = [];
        foreach ($listgroups['data'] as $gr) {
            if (isset($query) && stripos($gr['groupName'], $query) !== false) {
                $grs[] = $gr['groupName'];
            }
        }
        $access->output_serialized($grs);
    } elseif ($_REQUEST['listonly'] == 'users') {
        $names_array = explode(',', str_replace(';', ',', $query));
        $last_name = trim(end(array_filter($names_array)));

        $listusers = $userlib->get_users_names(0, 100, 'login_asc', $last_name);

        $access->output_serialized($listusers);
    } elseif ($_REQUEST['listonly'] == 'usersandcontacts') {
        $email_array = explode(',', str_replace(';', ',', $query));
        $last_email = trim(end($email_array));

        $contactlib = TikiLib::lib('contact');
        $listcontact = $contactlib->list_contacts($user);
        $listusers = $userlib->get_users(0, 100, 'login_asc', '', '', false, '', $last_email);

        $contacts = [];
        foreach ($listcontact as $key => $contact) {
            if (isset($last_email) && (stripos($contact['firstName'], $last_email) !== false or stripos($contact['lastName'], $last_email) !== false or stripos($contact['email'], $last_email) !== false)) {
                if ($contact['email'] <> '') {
                    $contacts[] = $contact['email'];
                }
            }
        }
        foreach ($listusers['data'] as $key => $contact) {
            if (isset($last_email) && (stripos($contact['firstName'], $last_email) !== false or stripos($contact['login'], $last_email) !== false or stripos($contact['lastName'], $last_email) !== false or stripos($contact['email'], $query) !== false)) {
                if ($prefs['login_is_email'] == 'y') {
                    $contacts[] = $contact['login'];
                } else {
                    $contacts[] = $contact['email'];
                }
            }
        }
        $contacts = array_unique($contacts);
        sort($contacts);
        $access->output_serialized($contacts);
    } elseif ($_REQUEST['listonly'] == 'userrealnames') {
        $names_array = explode(',', str_replace(';', ',', $query));
        $last_name = trim(end($names_array));
        $groups = '';
        $listusers = $userlib->get_users_light(0, -1, 'login_asc', $last_name, $groups);
        $done = [];
        $finalusers = [];
        foreach ($listusers as $usrId => $usr) {
            if (isset($last_name)) {
                $longusr = $usr . ' (' . $usrId . ')';
                if (array_key_exists($usr, $done)) {
                    // disambiguate duplicates
                    if (stripos($longusr, $last_name) !== false) {
                        $oldkey = array_search($usr, $finalusers);
                        if ($oldkey !== false) {
                            $finalusers[$oldkey] = $done[$usr];
                        }
                    }
                    if (stripos($longusr, $last_name) !== false) {
                        $finalusers[] = $longusr;
                    }
                } else {
                    if (stripos($longusr, $last_name) !== false) {
                        $finalusers[] = $longusr;
                    }
                }
                $done[$usr] = $longusr;
            }
        }

        $access->output_serialized($finalusers);
    } elseif ($_REQUEST['listonly'] == 'tags') {
        $freetaglib = TikiLib::lib('freetag');

        $tags = $freetaglib->get_tags_containing($query);
        $access->output_serialized($tags);
    } elseif ($_REQUEST['listonly'] == 'icons') {
        $dir = 'img/icons';
        $max = isset($_REQUEST['max']) ? $_REQUEST['max'] : 10;
        $icons = [];
        $style_dir = $tikilib->get_style_path($prefs['style'], $prefs['style_option']);
        if ($style_dir && is_dir($style_dir . $dir)) {
            read_icon_dir($style_dir . $dir, $icons, $max, $query);
        }
        read_icon_dir($dir, $icons, $max, $query);
        $access->output_serialized($icons);
    } elseif ($_REQUEST['listonly'] == 'shipping' && $prefs['shipping_service'] == 'y') {
        global $shippinglib;
        require_once 'lib/shipping/shippinglib.php';

        $access->output_serialized($shippinglib->getRates($_REQUEST['from'], $_REQUEST['to'], $_REQUEST['packages']));
    } elseif ($_REQUEST['listonly'] == 'trackername') {
        $trackers = TikiLib::lib('trk')->get_trackers_containing($query);
        $access->output_serialized($trackers);
    } elseif ($_REQUEST['listonly'] == 'references') {
        $references = TikiLib::lib('references')->getLibContaining($query);
        $access->output_serialized($references);
    }
} elseif ($access->is_serializable_request() && isset($_REQUEST['zotero_tags'])) { // Handle Zotero Requests
    $access->check_feature([ 'zotero_enabled' ]);
    $zoterolib = TikiLib::lib('zotero');

    $references = $zoterolib->get_references($_REQUEST['zotero_tags']);

    if ($references === false) {
        $access->output_serialized(['type' => 'unauthorized', 'results' => []]);
    } else {
        $access->output_serialized(['type' => 'success', 'results' => $references]);
    }