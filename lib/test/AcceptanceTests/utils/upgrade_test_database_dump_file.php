<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

// Make sure script is run from a shell
if (PHP_SAPI !== 'cli') {
    die("Please run from a shell");
}

require_once __DIR__ . '/../../../../vendor_bundled/vendor/autoload.php';

//die ("WARNING: This script will destroy the current Tiki db. Comment out this line in the script to proceed.");

if ($argc != 2) {
    die("Missing argument. USAGE: $argv[0] <dump_filename>");
}

$test_TikiAcceptanceTestDBRestorer = new TikiAcceptanceTestDBRestorerSQLDumps();
$test_TikiAcceptanceTestDBRestorer->restoreDB($argv[1]);

$local_php = 'db/local.php';

require_once('installer/installlib.php');

// Force autoloading
if (! class_exists('ADOConnection')) {
    die('AdoDb not found.');
}

include $local_php;
$dbTiki = ADONewConnection($db_tiki);
$dbTiki->Connect($host_tiki, $user_tiki, $pass_tiki, $dbs_tiki);
$installer = Installer::getInstance();
$installer->update();

$test_TikiAcceptanceTestDBRestorer->createDumpFile($argv[1]);
