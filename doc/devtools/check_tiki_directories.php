
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

if (PHP_SAPI !== 'cli') {
    die('Only available through command-line.');
}

require dirname(__FILE__) . '/svntools.php';

$tikiRootFolder = realpath(__DIR__ . '/../../');

$excludeDir = [
    '.', // Directories that are hidden (Ex: .composer)
    '_custom',
    'temp/',
    'vendor/',
    'vendor_bundled/',
    'node_modules/'
];

$fixIndex = in_array('fix-index', $argv);

// get latest file "stat" info
clearstatcache();

$emptyDirectoriesMessage = '';
$missingIndexMessage = '';
$missingIndexMessageFixed = '';
$missingHtaccessMessage = '';

$it = new RecursiveDirectoryIterator($tikiRootFolder);

foreach (new RecursiveIteratorIterator($it) as $file) {
    $filePath = $file->getRealpath();
    $fileName = $file->getFilename();

    if ($fileName == '..' || ! $file->isDir()) {
        continue;
    }

    if (toExclude($excludeDir, $filePath)) {
        continue;
    }

    if (isEmptyDir($filePath)) {
        $emptyDirectoriesMessage .= color($filePath, 'blue') . PHP_EOL;
        continue;
    }

    if (empty(glob($filePath . '/[iI][nN][dD][eE][xX].[pP][hH][pP]'))) { // index.php case-insensitive
        $missingIndexMessage .= color($filePath, 'blue') . PHP_EOL;

        if ($fixIndex) {
            $projectFolder = str_replace('doc', '', dirname(__DIR__));
            $path = str_replace($projectFolder, '', $filePath);
            $path = str_replace('-', '', $path);
            $indexPath = preg_replace('/(\w+)/', '../', $path);
            $indexPath = str_replace('//', '/', $indexPath);

            $indexContent = '<?php' . PHP_EOL . PHP_EOL . '// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project' . PHP_EOL;
            $indexContent .= '//' . PHP_EOL . '// All Rights Reserved. See copyright.txt for details and a complete list of authors.' . PHP_EOL;
            $indexContent .= '// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.' . PHP_EOL;
            $indexContent .= '// $Id$' . PHP_EOL;
            $indexContent .= PHP_EOL . '// This redirects to the sites root to prevent directory browsing' . PHP_EOL;
            $indexContent .= 'header("location: ' . $indexPath . 'index.php");' . PHP_EOL;
            $indexContent .= 'die;' . PHP_EOL;

            file_put_contents($filePath . '/index.php', $indexContent);
            $missingIndexMessageFixed .= color($filePath, 'blue') . PHP_EOL;
        }

        if (! folderHasHtaccess($filePath)) {
            $missingHtaccessMessage .= color($filePath, 'blue') . PHP_EOL;
        }
    }
}

if (! empty($emptyDirectoriesMessage) || ! empty($missingIndexMessage) || ! empty($missingHtaccessMessage)) {
    if (! empty($emptyDirectoriesMessage)) {
        echo color('The following directories are empty:', 'yellow') . PHP_EOL;
        info($emptyDirectoriesMessage);
    }
    if (! empty($missingIndexMessage)) {
        echo color('index.php file is missing in the following directories:', 'yellow') . PHP_EOL;
        info($missingIndexMessage);

        if (! empty($missingIndexMessageFixed)) {
            echo color('index.php file was fixed in the following directories:', 'green') . PHP_EOL;
            info($missingIndexMessageFixed);
        }
    }
    if (! empty($missingHtaccessMessage)) {
        echo color('.htaccess file is missing in the following directories:', 'yellow') . PHP_EOL;
        info($missingHtaccessMessage);
    }
    exit(1);
} else {
    important('All directories OK');
}

/**
 * Check if folder is empty
 *
 * @param $dir
 * @return boolean
 */
function isEmptyDir($dir)
{
    return (($files = scandir($dir)) && count($files) <= 2);
}

/**
 * Check if folder is marked to be excluded
 *
 * @param $dir
 * @param $path
 * @return boolean
 */
function toExclude($dir, $path)
{
    global $tikiRootFolder;

    $path = str_replace($tikiRootFolder, '', $path);
    $path = rtrim($path, '/') . '/';

    return (str_replace($dir, '', $path) != $path);
}

function folderHasHtaccess($dir): bool
{
    global $tikiRootFolder;

    $hasHtaccess = file_exists($dir . '/.htaccess');

    // We want to ensure that first level folders have .htaccess
    if (! $hasHtaccess && dirname($dir) !== $tikiRootFolder && $dir != $tikiRootFolder) {
        return folderHasHtaccess(dirname($dir));
    }

    return $hasHtaccess;
}