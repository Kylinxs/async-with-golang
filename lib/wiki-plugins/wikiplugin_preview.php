<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

use Tiki\Lib\Alchemy\AlchemyLib;

/**
 * Plugin definition for preview
 *
 * @return array
 */
function wikiplugin_preview_info()
{
    return [
        'name' => tr('Preview Files'),
        'documentation' => 'PluginPreviewFiles',
        'description' => tr('Enabled to generate preview of images or video files'),
        'prefs' => ['wikiplugin_preview'],
        'iconname' => 'file',
        'introduced' => 18,
        'tags' => ['experimental'],
        'packages_required' => ['media-alchemyst/media-alchemyst' => 'MediaAlchemyst\Alchemyst'],
        'format' => 'html',
        'params' => [
            'fileId' => [
                'required' => true,
                'name' => tr('fileId'),
                'description' => tr('Id of the file in the file gallery'),
                'since' => '18.0',
                'filter' => 'int',
            ],
            'animation' => [
                'required' => false,
                'name' => tr('Animation'),
                'description' => tr('Output should be a static image (<code>0</code>) or an animation (<code>1</code>)'),
                'since' => '18.0',
                'filter' => 'int',
            ],
            'width' => [
                'required' => false,
                'name' => tr('Width'),
                'description' => tr('Width of the result in pixels'),
                'since' => '18.0',
                'filter' => 'int',
            ],
            'height' => [
                'required' => false,
                'name' => tr('Height'),
                'description' => tr('Height of the result in pixels'),
                'since' => '18.0',
                'filter' => 'int',
            ],
            'download' => [
                'required' => false,
                'name' => tr('Download'),
                'description' => tr('Show download link to the original file'),
                'since' => '19.0',
                'filter' => 'int',
            ],
            'range' => [
                'required' => false,
                'name' => tr('Range'),
                'description' => tr('Page range preview in the format <integer>-<integer>. Example for the preview page from 2 to 4: "2-4"'),
                'since' => '21.0',
            ],
        ],
    ];
}

/**
 * Plugin definition for Preview
 *
 * @param $data
 * @param $params
 * @return string|void
 */
function wikiplugin_preview($data, $params)
{
    global $user, $prefs, $tikipath, $tikidomain;

    if (! AlchemyLib::isLibraryAvailable()) {
        return;
    }

    $fileId = isset($params['fileId']) ? (int)$params['fileId'] : 0;
    $animation = isset($params['animation']) ? (int)$params['animation'] : 0;
    $width = isset($params['width']) ? (int)$params['width'] : null;
    $height = isset($params['height']) ? (int)$params['height'] : null;
    $range = isset($params['range']) ? $params['range'] : null;

    $smartyLib = TikiLib::lib('smarty');

    $fileGalleryLib = TikiLib::lib('filegal');
    $userLib = TikiLib::lib('user');
    $file = \Tiki\FileGallery\File::id($fileId);
    if (! $file->exists() || ! $userLib->user_has_perm_on_object($user, $file->fileId, 'file', 'tiki_p_download_files')) {
        return;
    }

    $requestUniqueIdentifier = md5(serialize([$data, $params]));

    if (! isset($_REQUEST[$requestUniqueIdentifier])) {
        // generate the html output
        $urlParts = parse_url($_SERVER['REQUEST_URI']);
        $path = isset($urlParts['path']) ? $urlParts['path'] : '/';
        if (isset($urlParts['query'])) {
            parse_str($urlParts['query'], $pageParams);
        } else {
            $pageParams = [];
        }
        if (isset($_GET['page'])) {
            $pageParams['page'] = $_GET['page'];
        }
        $pageParams[$requestUniqueIdentifier] = '1';
        $pageParamStr = http_build_query($pageParams, null, '&');

        $fileLink = $path . '?' . $pageParamStr;

        $smartyLib->assign('param', $params);
        $files = [];

        if (! empty($range)) {
            $rangeFormat = preg_match("/^\d+\-\d+$/", $range);

            if ($rangeFormat !== 1) {
                Feedback::error(tr('File ID %0 preview contains an invalid range parameter.', $fileId));
            } else {
                $pageIterator = explode('-', $range)[0];
                $lastPage = explode('-', $range)[1];

                while ($pageIterator <= $lastPage) {
                    $files[] = $fileLink . "&previewPage=" . $pageIterator;
                    $pageIterator++;
                }
            }
        } else {
            $files[] = $fileLink;
        }

        $smartyLib->assign('files', $files);

        if ((isset($params['download']) && $params['download'] === 1)) {
            $tikilib = TikiLib::lib('tiki');
            $smartyLib->assign('original_file_download_link', $tikilib->tikiUrl() . 'tiki-download_file.php?fileId=' . $fileId, true);
        }

        return $smartyLib->fetch('wiki-plugins/wikiplugin_preview.tpl');
    }

    $filePath = $file->getWrapper()->getReadableFile();
    $fileMd5 = $file->getWrapper()->getChecksum();

    while (ob_get_level() > 1) {
        ob_end_clean();
    } // Be sure output buffering is turned off

    /** @var Cachelib $cacheLib */
    $cacheLib = TikiLib::lib('cache');

    $cacheName = $fileMd5 . $requestUniqueIdentifier;
    $previewPage = isset($_GET['previewPage']) ? $_GET['previewPage'] : null;

    if ($previewPage) {
        $cacheName .= "_" . $previewPage;
    }

    $cacheType = 'wp_preview_' . $fileId . '_';

    $buildContent = true;
    $content = null;
    $contentType = null;

    $content_temp = $cacheLib->getCached($cacheName, $cacheType);
    if ($content_temp && $content_temp !== serialize(false) && $content_temp != "") {
        $buildContent = false;
        $pos = strpos($content_temp, ';');
        $contentType = substr($content_temp, 0, $pos);
        $content = substr($content_temp, $pos + 1);
    }
    unset($content_temp);

    if ($buildContent) {
        $newFileExtension = $animation ? '.gif' : '.png';
        $newFilePath = $tikipath . DIRECTORY_SEPARATOR
            . 'temp' . DIRECTORY_SEPARATOR
            . 'cache' . DIRECTORY_SEPARATOR
            . $tikidomain . DIRECTORY_SEPARATOR
            . 'target_' . $cacheType . $cacheName . $newFileExtension;

        // This will allow apps executed by Alchemy (like when converting doc to pdf) to have a writable home
        // save existing ENV
        $envHomeDefined = isset($_ENV) && array_key_exists('HOME', $_ENV);
        if ($envHomeDefined) {
            $envHomeCopy = $_ENV['HOME'];
        }
        // set a proper home folder
        $_ENV['HOME'] = $tikipath . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $tikidomain;

        AlchemyLib::hintMimeTypeByFilePath($filePath, $file->filetype);

        $alchemy = new AlchemyLib();
        $contentType = $alchemy->convertToImage($filePath, $newFilePath, $width, $height, $animation, $previewPage);

        // Restore the environment
        if ($envHomeDefined) {
            $_ENV['HOME'] = $envHomeCopy;
        } else {
            unset($_ENV['HOME']);
        }

        if (file_exists($newFilePath)) {
            $content = file_get_contents($newFilePath);
        }
        unlink($newFilePath);

        if (empty($content)) {
            exit;
        }

        $cacheLib->cacheItem($cacheName, $contentType . ';' . $content, $cacheType);
    }

    session_write_close(); // close the session in case of large transcode/downloads to enable further browsing

    // Compression of the stream may corrupt files on windows
    ini_set('zlib.output_compression', 'Off');

    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false);
    header('Content-Length: ' . strlen($content));
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: inline; filename="' . $fileMd5 . '";');
    header('Connection: close');
    header('Content-Transfer-Encoding: binary');
    echo $content;
    exit;
}
