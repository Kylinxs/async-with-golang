<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function wikiplugin_footnotearea_info()
{
    return [
        'name' => tra('Footnote Area'),
        'documentation' => 'PluginFootnoteArea',
        'description' => tra('Create automatically numbered footnotes (together with PluginFootnote)'),
        'prefs' => ['wikiplugin_footnote'],
        'iconname' => 'superscript',
        'format' => 'html',
        'introduced' => 3,
        'params' => [
            'class' => [
                'required' => false,
                'name' => tra('Class'),
                'description' => tra('Filter footnotearea by footnote class'),
                'since' => '17.0',
                'default' => '',
                'filter' => 'alnum',
                'accepted' => tra('Valid CSS class'),
            ],
            'sameasstyle' => [
                'required' => false,
                'name' => tra('SameAs Style'),
                'description' => tra('Numbering style for sameas referencing.'),
                'since' => '17.0',
                'default' => 'disc',
                'filter' => 'text',
                'accepted' => tra('Valid Tiki ((Number Style))'),
            ],
        ],
    ];
}

/**
 * @param $data
 * @param $params
 * @param $offset
 * @param $context
 * @return string
 * @throws Exception
 */
function wikiplugin_footnotearea($data, $params, $offset, $context)
{
    $footnotes = $context->footnotes;
    $smarty = TikiLib::lib('smarty');

    if (isset($params['sameasstyle'])) {
        $smarty->assign('sameType', $params['sameasstyle']);
    } else {
        $smarty->assign('sameType', 'disc');
    }

    $html = '';

    if (isset($params['class'])) {                                       // if class was given
        if (isset($footnotes['lists'][$params['class']])) {        // if the class exists
            $html = genFootnoteArea($footnotes['lists'][$params['class']]);
            unset($footnotes['lists'][$params['class']]['entry']);
        }
    } else {
        $html = genFootnoteArea($footnotes['lists']['.def.']);
    }

    return $html;
}

/**
 *
 * Generate footnote area HTML
 *
 * @param $list array the array to turn into HTML
 *
 * @return string
 */

function genFootnoteArea($list)
{
    $smarty = TikiLib::lib('smarty');
    $smarty->assign('footnotes', $list['entry']);
    $smarty->assign('listType', $list['listType']);

    return $smarty->fetch('templates/wiki-plugins/wikiplugin_footnotearea.tpl');
}
