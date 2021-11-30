
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

use Laminas\Text\Figlet\Figlet;

function wikiplugin_figlet_info(): array
{
    return [
        'name' => tra('Figlet'),
        'format' => 'html',
        'documentation' => 'PluginFiglet',
        'description' => tra('Generate FIGlet text banners'),
        'prefs' => ['wikiplugin_figlet'],
        'iconname' => 'heading',
        'introduced' => 24,
        'body' => tra('Content'),
        'params' => [
            'font' => [
                'required' => false,
                'name' => tra('Font face'),
                'description' => tra('Path to "fif" font file. Find more fonts here http://www.figlet.org/fontdb.cgi'),
                'filter' => 'text',
            ],
            'width' => [
                'required' => false,
                'name' => tra('Output width'),
                'description' => tra('Defines the maximum width of the output string in characters.'),
                'filter' => 'int',
            ]
        ]
    ];
}

/**
 * @param string $data
 * @param array  $params
 *
 * @return string html
 */
function wikiplugin_figlet(string $data, array $params): string
{
    if (empty($data)) {
        return '';
    }

    $string = new Figlet();
    /**
     * @TODO erase the default font
     */
    //If we have a font set up in $params
    if (! empty($params['font'])) {
        $string->setFont($params['font']);
    }
    //else keep the default font-face

    if (! empty($params['width'])) {
        $string->setOutputWidth($params['width']);
    }

    return "<pre>" . $string->render($data) . "</pre>";
}