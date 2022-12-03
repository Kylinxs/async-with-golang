<?php

/**
 * @package tikiwiki
 */

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

// This script will send a 1x1 transparent gif image, close connection and reindex the file corresponding to the id url argument
// The goal is to process reindexation in a background job for which the user won't have to wait
//
// This trick has been found on the official php manual page comments of the register_shutdown_function function

require_once('tiki-setup.php');

// Reindex the file for search
if (($id = (int)$_GET['id']) > 0) {
    // Check feature
    if (
        $prefs['feature_file_galleries'] == 'y'
        && $prefs['feature_search'] == 'y'
        && $prefs['feature_search_fulltext'] != 'y'
        && $prefs['search_refresh_index_mode'] == 'normal'
        && $prefs['fgal_asynchronous_indexing'] == 'y'
    ) {
        $filegallib = TikiLib::lib('filegal');
        require_once('lib/search/refresh-functions.php');

        $info = $filegallib->get_file_info($id);

        if ($info['galleryId'] > 0) {
            $gal_info = $filegallib->get_file_gallery($info['galleryId']);

            // Check perms
            $tikilib->get_perm_object($info['galleryId'], 'file gallery', $gal_info, true);

            if (
                $tiki_p_admin_file_