#!/usr/bin/php
<?php
// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

if (isset($_SERVER['REQUEST_METHOD'])) {
    die('Only available through command-line.');
}

$tikiBase = realpath(__DIR__ . '/../..');

$composerJsonFile = "$tikiBase/vendor_bundled/composer.json";
$composerJsonFileBackup = str_replace('/composer.', '/composer_https.', $composerJsonFile);
$composerLockFile = "$tikiBase/vendor_bundled/composer.lock";
$composerLockFileBackup = str_replace('/composer.', '/composer_https.', $composerLockFile);

if ($_SERVER['argc'] <= 1 || isset($_SERVER['argv']['help'])) {
    echo 'Changes composer files to use http only for use behind firewalls etc.
  Options:
    execute : Change composer to use http mode
    revert : Revert changes to use normal https mode
';
    return;
} elseif ($_SERVER['argv'][1] === 'revert') {
    revert();
} elseif ($_SERVER['argv'][1] === 'execute') {
    execute();
}


function execute()
{
    global $tikiBase, $composerJsonFile, $composerJsonFileBackup, $composerLockFile, $composerLockFileBackup;

    $repoUrlHttps = 'https://composer.tiki.org';
    $repoUrlHttp = 'http://composer.tiki.org';

    if (! is_writable("$tikiBase/vendor_bundled/") || ! is_writable($composerJsonFile) || ! is_writable($composerLockFile)) {
        echo "Error: Cannot write to $tikiBase/vendor_bundled/ or the composer files\n";
        return;
    }

    echo "Backing up original files\n";

    // back up both files
    if (! file_exists($composerJsonFileBackup)) {
        copy($composerJsonFile, $composerJsonFileBackup);
    } else {
        echo "Error: composer.json backup file already exists\n";
        return;
    }
    if (! file_exists($composerLockFileBackup)) {
        copy($composerLockFile, $composerLockFileBackup);
    } else {
        echo "Error: composer.lock backup file already exists\n";
        return;
    }

    echo "Processing composer.json\n";

    $json = json_decode(file_get_contents($composerJsonFile), true);

    $json['config']['secure-http'] = false;
    $json['repositories'][0]['url'] = str_replace($repoUrlHttps, $repoUrlHttp, $json['repositories'][0]['url']);

    file_put_contents($composerJsonFile, json_encode($json, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

    echo "Processing composer.lock\n";

    $lock = file_get_contents($composerLockFile);
    $lock = str_replace($repoUrlHttps, $repoUrlHttp, $lock);
    file_put_contents($composerLockFile, $lock);

    echo "Done\n";
}

function revert()
{
    global $tikiBase, $composerJsonFile, $composerJsonFileBackup, $composerLockFile, $composerLockFileBackup;

    if (! is_writable("$tikiBase/vendor_bundled/") || ! is_writable($composerJsonFile) || ! is_writable($composerLockFile)) {
        echo "Error: Cannot write to $tikiBase/vendor_bundled/ or the composer files\n";
        return;
    }

    // check for back files
    if (! file_exists($composerJsonFileBackup)) {
        echo "Error: composer.json backup file not found\n";
        return;
    }
    if (! file_exists($composerLockFileBackup)) {
        echo "Error: composer.lock backup file not found\n";
        return;
    }

    echo "Restoring backup files\n";

    unlink($composerJsonFile);
    rename($composerJsonFileBackup, $composerJsonFile);
    unlink($composerLockFile);
    rename($composerLockFileBackup, $composerLockFile);

    echo "Done\n";
}
