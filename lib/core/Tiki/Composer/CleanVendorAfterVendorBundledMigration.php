
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Composer;

use Composer\Script\Event;
use Composer\Util\FileSystem;
use Symfony\Component\Finder\Finder;

/**
 * After Migrate the vendors to vendors_bundled, we should clean the vendor folder
 * We don't want to that by deleting all files in the vendor folder, instead we will try
 * to do sensitive decisions about what to delete
 *
 * All the process is skipped exists a file called "do_not_clean.txt" in the vendor folder
 *
 * Class CleanVendorAfterVendorBundledMigration
 * @package Tiki\Composer
 */
class CleanVendorAfterVendorBundledMigration
{
    // To calculate the md5 hash for the old vendor folder, on a linux server, you can use (inside the old vendor folder):
    //
    // $ STRING=$(ls -d */* | grep -v "^composer/" | grep -v "^bin/" | LC_COLLATE=C sort -fu | tr '\n' ':' | sed 's/:$//')
    // $ echo -n $STRING | md5sum
    //
    public const PRE_MIGRATION_OLD_VENDOR_FOLDER_MD5_HASH = '6997e3dc0e3ad453ab8ea9798653a0fa'; // version 17 before change
    public const VENDOR_FOLDER_MD5_HASH_16_X = '40473ceff65c1045ccd10ebd5e5e3110'; // version 16.3
    public const VENDOR_FOLDER_MD5_HASH_15_X = '273278571219f62e2d658e510684d763'; // version 15.6
    public const VENDOR_FOLDER_MD5_HASH_14_X = 'fbf3913809c5575aee178a1c2437a48a'; // version 14.4
    public const VENDOR_FOLDER_MD5_HASH_13_X = '507a38862ece4a36a6787850e7e732be'; // version 13.2
    public const VENDOR_FOLDER_MD5_HASH_12_X = '466948d920571e4065b5ddde9b0d72da'; // version 12.13

    /**
     * @param Event $event
     */
    public static function cleanLinks(Event $event)
    {
        self::cleanBinLinks();
    }

    /**
     * @param Event $event
     */
    public static function clean(Event $event)
    {

        /*
         * 0) Make sure old bin links are removed so they can be created by composer
         * 1) If a file called do_not_clean.txt exists in the vendor folder stop
         * 2) If there is a vendor/autoload.php, check the hash of the folder structure, if different from at the time
         *    of the vendor_bundle migration, ignore
         * 2.1) Even if the hash do not match, check if 3 of the tiki bundled packages are installed, if that is the
         *    case warn the user as it might be a problem and disable autoload
         * 3) If we arrive here, clean all folders and autoload.php in the old (pre migration) vendor folder
         */

        $io = $event->getIO();
        $fs = new FileSystem();

        $rootFolder = realpath(__DIR__ . '/../../../../');
        $oldVendorFolder = realpath($rootFolder . '/vendor');

        // 0) Make sure we can install known bin files (they might be still linked to the old vendor folder
        self::cleanBinLinks();

        // if we cant find the vendor dir no sense in progressing
        if ($oldVendorFolder === false || ! is_dir($oldVendorFolder)) {
            return;
        }

        // 1) If a file called do_not_clean.txt exists in the vendor folder stop
        if (file_exists($oldVendorFolder . '/do_not_clean.txt')) {
            $io->write('');
            $io->write('File vendor/do_not_clean.txt is present, no attempt to clean the vendor folder will be done!');
            $io->write('');

            return;
        }

        // 2) If there is a vendor/autoload.php, check the hash of the folder structure, if different from at the time
        //    of the vendor_bundle migration, ignore
        if (file_exists($oldVendorFolder . '/autoload.php')) {
            $finder = new Finder();
            $finder->in($oldVendorFolder)->exclude(['composer', 'bin'])->depth(2);

            $packages = [];
            foreach ($finder as $file) {
                $packages[] = $file->getRelativePath();
            }

            $packages = array_unique($packages);
            natcasesort($packages);
            $packagesString = implode(':', array_values($packages));

            $md5checksum = md5($packagesString);

            if (
                ! in_array(
                    $md5checksum,
                    [
                    self::PRE_MIGRATION_OLD_VENDOR_FOLDER_MD5_HASH,
                    self::VENDOR_FOLDER_MD5_HASH_16_X,
                    self::VENDOR_FOLDER_MD5_HASH_15_X,
                    self::VENDOR_FOLDER_MD5_HASH_14_X,
                    self::VENDOR_FOLDER_MD5_HASH_13_X,
                    self::VENDOR_FOLDER_MD5_HASH_12_X,
                    ]
                )
            ) {
                // * 2.1) Even if the hash do not match, check if 3 of the tiki bundled packages are installed, if that is the
                //        case warn the user as it might be a problem and disable autoload
                if (
                    (file_exists($oldVendorFolder . '/zendframework/zend-config/src/Config.php') //ZF2
                        || file_exists($oldVendorFolder . '/bombayworks/zendframework1/library/Zend/Config.php')) //ZF1
                    && (file_exists($oldVendorFolder . '/smarty/smarty/libs/Smarty.class.php') //Smarty
                        || file_exists($oldVendorFolder . '/smarty/smarty/distribution/libs/Smarty.class.php')) //Smarty
                    && file_exists($oldVendorFolder . '/adodb/adodb/adodb.inc.php') //Adodb
                ) {
                    rename($oldVendorFolder . '/autoload.php', $oldVendorFolder . '/autoload-disabled.php');
                    self::cleanTemplates($rootFolder, $fs);

                    $message = <<<'EOD'
Your vendor folder contains multiple packages that were normally bundled with Tiki. Since version 17 those libraries
were migrated from the folder "vendor" to the folder "vendor_bundled".

It looks like your instance still has these libraries in the vendor folder, to avoid issues your "vendor/autoload.php"
was renamed to "vendor/autoload-disabled.php".

If you are sure that you want to use the libraries in addition to the ones bundled with tiki, please rename 
"vendor/autoload-disabled.php" back to "vendor/autoload.php" and place a file with the name "do_not_clean.txt" in the vendor folder.

Tiki will not load your "vendor/autoload.php" when is detected as being a stale folder unless a file called
"vendor/do_not_clean.txt" exists. A "vendor/do_not_clean.txt" will prevent, in future runs of composer, the automatic disabling of
"vendor/autoload.php".

Most probably you did not add your own custom libraries in addition to the ones bundled with tiki, so you can empty your "vendor" directory
with: "rm -rf vendor/*".
If you have your own custom libraries, you should remove all the other ones from the "vendor" directory.
EOD;

                    file_put_contents($oldVendorFolder . '/autoload-disabled-README.txt', $message . "\n");

                    $io->write('');
                    $io->write('!!!! Warning !!!!');
                    $io->write('');
                    $io->write($message);
                    $io->write('');
                    $io->write('A copy of this information was written also into "vendor/autoload-disabled-README.txt"');
                    $io->write('');
                }
                return;
            }
        } elseif (file_exists($oldVendorFolder . '/autoload-disabled.php')) {
            // we already disabled autoload, in previous runs, do nothing.
            return;
        }

        // 3) If we arrive here, clean all folders and autoload.php in the old (pre migration) vendor folder

        $fs->remove($oldVendorFolder . '/autoload.php');

        $vendorDirsCleaned = false;
        $vendorDirs = glob($oldVendorFolder . '/*', GLOB_ONLYDIR);
        foreach ($vendorDirs as $dir) {
            if (is_dir($dir)) {
                $fs->remove($dir);
                $vendorDirsCleaned = true;
            }
        }

        if ($vendorDirsCleaned) {
            self::cleanTemplates($rootFolder, $fs);
        }
    }

    /**
     * Cleans links from the bin folder to the legacy vendor folder
     */
    protected static function cleanBinLinks()
    {
        $fs = new FileSystem();

        $rootFolder = realpath(__DIR__ . '/../../../../');
        $oldVendorFolder = realpath($rootFolder . '/vendor');

        // 0) Make sure we can install known bin files (they might be still linked to the old vendor folder
        $binFiles = ['lessc', 'minifycss', 'minifyjs', 'dbunit', 'phpunit'];

        foreach ($binFiles as $file) {
            $filePath = $rootFolder . '/bin/' . $file;
            if (is_link($filePath)) {
                $linkDestination = readlink($filePath);
                $fileRealPath = realpath($filePath);
                if (
                    strncmp($linkDestination, '../vendor/', strlen('../vendor/')) === 0 // relative link to vendor folder
                    || $filePath === false // target don't exists, so link is broken
                    || strncmp(
                        $fileRealPath,
                        $oldVendorFolder,
                        strlen($oldVendorFolder)
                    ) === 0 // still pointing to old vendor folder
                ) {
                    $fs->unlink($filePath);
                }
            }
        }
    }

    /**
     * Clean templates, if there was a change to the vendors
     * @param string $rootFolder
     * @param FileSystem $fs
     */
    protected static function cleanTemplates($rootFolder, $fs)
    {
        // there are some cached templates that will stop tiki to work after the migration
        $loopDirs = array_merge(
            [$rootFolder . '/temp/templates_c'],
            glob($rootFolder . '/temp/templates_c/*', GLOB_ONLYDIR)
        );
        foreach ($loopDirs as $dir) {
            $cachedTemplates = glob($dir . '/*.tpl.php');
            foreach ($cachedTemplates as $template) {
                $fs->remove($template);
            }
        }
    }
}