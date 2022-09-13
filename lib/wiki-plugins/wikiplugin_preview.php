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
    if (! $file->exists() || ! $userLib->user_has_perm_on_objec