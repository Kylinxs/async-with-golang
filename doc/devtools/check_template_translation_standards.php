
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

use Symfony\Component\Console\Input\ArgvInput;

if (isset($_SERVER['REQUEST_METHOD'])) {
    die('Only available through command-line.');
}

$dir = __DIR__;
require_once $dir . '/../../tiki-filter-base.php';
require __DIR__ . '/svntools.php';

$input = new ArgvInput();
$file = $input->getParameterOption(['--file']);
$all = $input->hasParameterOption(['--all']);
$templates = getAllTemplateFiles($dir);

if (empty($file) && empty($all)) {
    error('Params not found. ' . PHP_EOL . 'Valid params: --file [file], --all');
    die();
}

if (! empty($file)) {
    $file = $dir . '/' . $file;
    if (! in_array($file, $templates) || ! file_exists($file)) {
        error('File not found or is not .tpl');
        die();
    }
    $message = '';
    $check = check($file);
    if (isset($check)) {
        $message .= $check . PHP_EOL;
    }
    if (! empty($message)) {
        info(color('File has ":" or "," outside of translation in the following lines in red:', 'yellow'));
        info(trim($message, PHP_EOL));
        exit(1);
    } else {
        info(basename($file) . ' ' . color('OK', 'green'));
    }
}

if (! empty($all)) {
    $message = '';
    foreach ($templates as $file) {
        $check = check($file);
        if (isset($check)) {
            $message .= $check . PHP_EOL;
        }
    }
    if (! empty($message)) {
        info(color('The following files have ":" or "," outside of translation in the following lines in red:', 'yellow'));
        info(trim($message, PHP_EOL));
        exit(1);
    } else {
        important('All template files OK');
    }
}

function getAllTemplateFiles($currentDir)
{
    $templateDir = new RecursiveDirectoryIterator($currentDir . '/../../templates');
    $ite = new RecursiveIteratorIterator($templateDir);
    $files = new RegexIterator($ite, '/.*tpl/', RegexIterator::GET_MATCH);
    $templateList = [];
    foreach ($files as $file) {
        if (file_exists($file[0])) {
            $templateList = array_merge($templateList, $file);
        }
    }
    return $templateList;
}

function check($file)
{
    if (file_exists($file)) {
        $message = realpath($file);
        $lineNumber = '';
        if ($fileHandler = fopen($file, "r")) {
            $i = 0;
            while ($line = fgets($fileHandler)) {
                $i++;
                if (strpos($line, '{/tr}:') !== false || strpos($line, '{/tr},') !== false) {
                    $lineNumber .= $i . ",";
                }
            }
            fclose($fileHandler);
        }
        if (! empty($lineNumber)) {
            return color($message . ':', 'blue') . color(substr($lineNumber, 0, -1), 'red');
        }
    }
}