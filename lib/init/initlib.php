<?php

/**
 * Tiki initialization functions and classes
 *
 * @package TikiWiki
 * @subpackage lib\init
 * @copyright (c) Copyright by authors of the Tiki Wiki CMS Groupware Project. All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * @licence Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
 */

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\Yaml\Yaml;
use Tiki\Package\ExtensionManager as PackageExtensionManager;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;

// $Id$

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER['SCRIPT_NAME'], basename(__FILE__)) !== false) {
    header('location: index.php');
    exit;
}

if (! file_exists(__DIR__ . '/../../vendor_bundled/vendor/autoload.php')) {
    $error = "Your Tiki is not completely installed because Composer has not been run to fetch package dependencies.\n" .
        "You need to run 'sh setup.sh' from the command line.\n" .
        "See https://doc.tiki.org/Composer for details.\n";

    if (http_response_code() === false) { // if running in cli
        $error = "\033[31m" . $error . "\e[0m\n";
    }
    echo $error;
    exit(1);
}

require_once __DIR__ . '/../../vendor_bundled/vendor/autoload.php'; // vendor libs bundled into tiki

// vendor libs managed by the user using composer (if any)
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    // In some cases, the vendor folder may contain the files from the old vendor folder before migrating to
    // vendor_bundled. In these cases eg. when unzipping a Tiki => 17.x on top of an existing Tiki <= 16.x instance,
    // loading the autoload from the vendor folder will cause issues.
    // We check for some core libraries (ZendFramework, Smarty and Adodb), if they are all present in the
    // vendor folder we will consider that there is a old vendor folder, and skip loading the autoload.php unless
    // there is a file called do_not_clean.txt inside the vendor folder (we will only check the file exists)
    if (
        file_exists(__DIR__ . '/../../vendor/do_not_clean.txt')
        || ! ( // check the existence of critical files denoting a legacy vendor folder
            (file_exists(__DIR__ . '/../../vendor/zendframework/zend-config/src/Config.php') //ZF2
                || file_exists(__DIR__ . '/../../vendor/bombayworks/zendframework1/library/Zend/Config.php')) //ZF1
            && (file_exists(__DIR__ . '/../../vendor/smarty/smarty/libs/Smarty.class.php') //Smarty
                || file_exists(__DIR__ . '/../../vendor/smarty/smarty/distribution/libs/Smarty.class.php')) //Smarty
            && file_exists(__DIR__ . '/../../vendor/adodb/adodb/adodb.inc.php') //Adodb
        )
    ) {
        $autoloader = require_once __DIR__ . '/../../vendor/autoload.php';
        // Autoload extension packages libs
        foreach (\Tiki\Package\ExtensionManager::getEnabledPackageExtensions(false) as $package) {
            if (is_dir($package['path'] . '/lib/') && strpos($package['path'], 'vendor_custom') === false) {
                $autoloader->addPsr4(str_replace('/', '\\', $package['name']) . '\\', $package['path'] . '/lib/');
            }
        }
    }
}

// vendor libraries managed by the user, packaged (if any)
if (is_dir(__DIR__ . '/../../vendor_custom')) {
    foreach (new DirectoryIterator(__DIR__ . '/../