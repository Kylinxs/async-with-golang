<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER['SCRIPT_NAME'], basename(__FILE__)) !== false) {
    header('location: index.php');
    exit;
}

define('VENDOR_PATH', $tikipath . '/vendor_bundled/vendor/');
define('APP_PATH', VENDOR_PATH . 'jason-munro/cypht/');
define('WEB_ROOT', $tikiroot . 'vendor_bundled/vendor/jason-munro/cypht/');
define('DEBUG_MODE', false);

define('CACHE_ID', 'FoHc85ubt5miHBls6eJpOYAohGhDM61Vs%2Fm0BOxZ0N0%3D'); // Cypht uses for asset cache busting but we run the assets through Tiki pipeline, so no need to generate a unique key here
define('SITE_ID', 'Tiki-Integration');

require_once APP_PATH . 'lib/framework.php';
require_once __DIR__ . '/Tiki_Hm_Output_HTTP.php';
require_once __DIR__ . '/Tiki_Hm_Custom_Session.php';
require_once __DIR__ . '/Tiki_Hm_Tiki_Cache.php';
require_once __DIR__ . '/Tiki_Hm_Custom_Cache.php';
require_once __DIR__ . '/Tiki_Hm_Site_Config_File.php';
require_once __DIR__ . '/Tiki_Hm_User_Config.php';
require_once __DIR__ . '/Tiki_Hm_Sieve_Custom_Client.php';
require_once __DIR__ . '/Tiki_Hm_Sieve_Client_Factory.php';
