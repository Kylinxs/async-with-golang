
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function wikiplugin_accordion_info()
{
    return [
        'name' => tra('Accordion'),
        'documentation' => 'PluginAccordion',
        'description' => tra('Create content within collapsable items'),
        'prefs' => ['wikiplugin_accordion'],
        'body' => tra('Content of the collapsible zones, separated by "/////"'),
        'iconname' => 'wizard',
        'filter' => 'wikicontent',
        'format' => 'html',
        'introduced' => 25,
        'tags' => [ 'basic' ],
        'params' => [
            'headers' => [
                'required' => true,
                'name' => tra('Header Titles'),
                'description' => tra('Pipe-separated list of header titles (with or without icon syntax). Example:') . '<code>{i name=\'users\'} Item 1|Item 2|Item 3</code>',
                'since' => '25',
                'filter' => 'text',
                'default' => '',
            ],
            'headerbgcolor' => [
                'required'    => false,
                'name'        => tra('Header background color'),
                'description' => tra(
                    'Enter a valid CSS color hex code, or an RGBA value if setting opacity is desired; for example: #000 or rgba(00, 00, 00, 0.5).'
                ),
                'filter'      => 'text',
                'default'     => '',
                'since'       => '25.0',
            ],
            'headeractivebgcolor' => [
                'required'    => false,
                'name'        => tra('Header active background color'),
                'description' => tra(
                    'Enter a valid CSS color hex code, or an RGBA value if setting opacity is desired; for example: #000 or rgba(00, 00, 00, 0.5).'
                ),
                'filter'      => 'text',
                'default'     => '',
                'since'       => '25.0',
            ],
            'headercontentcolor' => [
                'required'    => false,
                'name'        => tra('Header text color'),
                'description' => tra(
                    'Enter a valid CSS color hex code, or an RGBA value if setting opacity is desired; for example: #000 or rgba(00, 00, 00, 0.5).'
                ),
                'filter'      => 'text',
                'default'     => '',
                'since'       => '25.0',
            ],
            'headerfontstyle' => [
                'required'    => false,
                'name'        => tra('Header text style'),
                'description' => tra(
                    'Use to change the headers text style.'
                ),
                'filter'      => 'text',
                'default'     => 'normal',
                'since'       => '25.0',
                'options' => [
                    ['text' => '', 'value' => ''],
                    ['value' => 'normal', 'text' => tra('Normal')],
                    ['value' => 'italic' , 'text' => tra('Italic')],
                    ['value' => 'oblique' , 'text' => tra('Oblique')],
                ],
            ],
            'headerfontsize' => [
                'required'    => false,
                'name'        => tra('Header text size'),
                'description' => tra(
                    'To set the size of the headers text in pixels. For example: 20 for 20 pixels.'
                ),
                'filter'      => 'text',
                'default'     => '16',
                'since'       => '25.0',
            ],
            'headerfontweight' => [
                'required'    => false,
                'name'        => tra('Header text weight'),
                'description' => tra(
                    'Use to change the headers text weight.'
                ),
                'filter'      => 'text',
                'default'     => 'normal',
                'since'       => '25.0',
                'options' => [
                    ['text' => '', 'value' => ''],
                    ['value' => 'normal', 'text' => tra('Normal')],
                    ['value' => 'bold' , 'text' => tra('Bold')],
                    ['value' => 'bolder' , 'text' => tra('Bolder')],
                    ['value' => 'lighter' , 'text' => tra('Lighter')],
                    ['value' => '900' , 'text' => tra('Boldest')],
                ],
            ],
            'headerborderstyle' => [
                'required'    => false,
                'name'        => tra('Header border style'),
                'description' => tra(
                    'Determine the kind of border to apply to the headers.'
                ),
                'default'     => 'none',
                'filter'      => 'text',
                'since'       => '25.0',
                'options' => [
                    ['text' => '', 'value' => ''],
                    ['value' => 'none', 'text' => tra('None')],
                    ['value' => 'hidden' , 'text' => tra('Hidden')],
                    ['value' => 'dotted' , 'text' => tra('Dotted')],
                    ['value' => 'dashed' , 'text' => tra('Dashed')],
                    ['value' => 'solid' , 'text' => tra('Solid')],
                    ['value' => 'double' , 'text' => tra('Double')],
                    ['value' => 'groove' , 'text' => tra('Groove')],
                    ['value' => 'ridge' , 'text' => tra('Ridge')],
                    ['value' => 'inset' , 'text' => tra('Inset')],
                    ['value' => 'outset' , 'text' => tra('Outset')],
                ],
            ],
            'headerborderwidth' => [
                'required'    => false,
                'name'        => tra('Header border width'),
                'description' => tra(
                    'To change the width of header border in pixels (1 by default). For example: 3 for 3 pixels.'
                ),
                'filter'      => 'text',
                'default'     => '1',
                'since'       => '25.0',
            ],
            'headerbordercolor' => [
                'required'    => false,
                'name'        => tra('Header border color'),
                'description' => tra(
                    'Enter a valid CSS color hex code, or an RGBA value if setting opacity is desired; for example: #000 or rgba(00, 00, 00, 0.5).'
                ),
                'filter'      => 'text',
                'default'     => '',
                'since'       => '25.0',
            ],
            'panelbgcolor' => [
                'required'    => false,
                'name'        => tra('Panel background color'),
                'description' => tra(
                    'Enter a valid CSS color hex code, or an RGBA value if setting opacity is desired; for example: #000 or rgba(00, 00, 00, 0.5).'
                ),
                'filter'      => 'text',
                'default'     => '',
                'since'       => '25.0',
            ],
            'panelcontentcolor' => [
                'required'    => false,
                'name'        => tra('Panel text color'),
                'description' => tra(
                    'Enter a valid CSS color hex code, or an RGBA value if setting opacity is desired; for example: #000 or rgba(00, 00, 00, 0.5).'
                ),
                'filter'      => 'text',
                'default'     => '',
                'since'       => '25.0',
            ],
            'panelfontstyle' => [
                'required'    => false,
                'name'        => tra('Panel text style'),
                'description' => tra(
                    'Use to change the panels text style.'
                ),
                'filter'      => 'text',
                'default'     => 'normal',
                'since'       => '25.0',
                'options' => [
                    ['text' => '', 'value' => ''],
                    ['value' => 'normal', 'text' => tra('Normal')],
                    ['value' => 'italic' , 'text' => tra('Italic')],
                    ['value' => 'oblique' , 'text' => tra('Oblique')],
                ],
            ],
            'panelfontsize' => [
                'required'    => false,
                'name'        => tra('Panel text size'),
                'description' => tra(
                    'To set the size of the panels text in pixels. For example: 20 for 20 pixels.'
                ),
                'filter'      => 'text',
                'default'     => '16',
                'since'       => '25.0',
            ],
            'panelfontweight' => [
                'required'    => false,
                'name'        => tra('Panel text weight'),
                'description' => tra(
                    'Use to change the panels text weight.'
                ),
                'filter'      => 'text',
                'default'     => 'normal',
                'since'       => '25.0',
                'options' => [
                    ['text' => '', 'value' => ''],
                    ['value' => 'normal', 'text' => tra('Normal')],
                    ['value' => 'bold' , 'text' => tra('Bold')],
                    ['value' => 'bolder' , 'text' => tra('Bolder')],
                    ['value' => 'lighter' , 'text' => tra('Lighter')],
                    ['value' => '900' , 'text' => tra('Boldest')],
                ],
            ],
            'paneltextalignment' => [
                'required'    => false,
                'name'        => tra('Panel text alignment'),
                'description' => tra(
                    'To set the horizontal alignment of the panels text.'
                ),
                'filter'      => 'text',
                'default'     => 'left',
                'since'       => '25.0',
                'options' => [
                    ['text' => '', 'value' => ''],
                    ['value' => 'left', 'text' => tra('Left')],
                    ['value' => 'right' , 'text' => tra('Right')],
                    ['value' => 'center' , 'text' => tra('Center')],
                    ['value' => 'justify' , 'text' => tra('Justify')],
                ],
            ],
            'panelborderstyle' => [
                'required'    => false,
                'name'        => tra('Panel border style'),
                'description' => tra(
                    'Determine the kind of border to apply to the panels.'
                ),
                'default'     => 'none',
                'filter'      => 'text',
                'since'       => '25.0',
                'options' => [
                    ['text' => '', 'value' => ''],
                    ['value' => 'none', 'text' => tra('None')],
                    ['value' => 'hidden' , 'text' => tra('Hidden')],
                    ['value' => 'dotted' , 'text' => tra('Dotted')],
                    ['value' => 'dashed' , 'text' => tra('Dashed')],
                    ['value' => 'solid' , 'text' => tra('Solid')],
                    ['value' => 'double' , 'text' => tra('Double')],
                    ['value' => 'groove' , 'text' => tra('Groove')],
                    ['value' => 'ridge' , 'text' => tra('Ridge')],
                    ['value' => 'inset' , 'text' => tra('Inset')],
                    ['value' => 'outset' , 'text' => tra('Outset')],
                ],
            ],
            'panelborderwidth' => [
                'required'    => false,
                'name'        => tra('Panel border width'),
                'description' => tra(
                    'To change the width of panel border in pixels (1 by default). For example: 3 for 3 pixels'
                ),
                'filter'      => 'text',
                'default'     => '1',
                'since'       => '25.0',
            ],
            'panelbordercolor' => [
                'required'    => false,
                'name'        => tra('Panel border color'),
                'description' => tra(
                    'Enter a valid CSS color hex code, or an RGBA value if setting opacity is desired; for example: #000 or rgba(00, 00, 00, 0.5).'
                ),
                'filter'      => 'text',
                'default'     => '',
                'since'       => '25.0',
            ],
        ]
    ];
}

function wikiplugin_accordion($body, $params)
{
    static $id = 0;
    $unique = 'accordion-' . ++$id;

    $headers = [];
    $icons = [];
    if (! empty($params['headers'])) {
        $headers = explode('|', $params['headers']);
        foreach ($headers as $key => $header) {
            $pattern = "/^{i name='+[a-z\-]+'}/";
            if (preg_match($pattern, $header, $matches)) {
                //extract icon name
                if (preg_match("/'([^']+)'/", $matches[0], $m)) {
                    $icons[$key] = $m[1];
                }
                $header = preg_split($pattern, $header, -1, PREG_SPLIT_NO_EMPTY);
                $headers[$key] = trim($header[0]);
            } else {
                $headers[$key] = $header;
            }
        }
    } else {
        return "''" . tra("No header title specified. At least one must be specified in order for the accordion to appear.") . "''";
    }

    if (! empty($body)) {
        $body = TikiLib::lib('parser')->parse_data($body);
        $accordionBody = explode('/////', $body);
    }

    addStyles($unique, $params);

    $smarty = TikiLib::lib('smarty');
    $smarty->assign('unique', $unique);
    $smarty->assign_by_ref('headers', $headers);
    $smarty->assign_by_ref('icons', $icons);
    $smarty->assign_by_ref('accordioncontent', $accordionBody);

    return $smarty->fetch('wiki-plugins/wikiplugin_accordion.tpl');
}

function addStyles($unique, $params)
{
    $headerlib = TikiLib::lib('header');
    $css = '';
    $accordionBody = '#' . $unique . ' .accordion-body';
    $accordionButton = '#' . $unique . ' .accordion-button';
    $css .= '#' . $unique . ' .accordion-icon {margin-right: 8px;} ';
    $css .= ! empty($params['panelbgcolor']) ? $accordionBody . '{background:' . $params['panelbgcolor'] . ';} ' : '';
    $css .= ! empty($params['panelcontentcolor']) ? $accordionBody . '{color:' . $params['panelcontentcolor'] . ';} ' : '';
    $css .= ! empty($params['panelfontstyle']) ? $accordionBody . '{font-style:' . $params['panelfontstyle'] . ';} ' : '';
    $css .= ! empty($params['panelfontsize']) ? $accordionBody . '{font-size:' . $params['panelfontsize'] . 'px;} ' : '';
    $css .= ! empty($params['panelfontweight']) ? $accordionBody . '{font-weight:' . $params['panelfontweight'] . ';} ' : '';
    $css .= ! empty($params['paneltextalignment']) ? $accordionBody . '{text-align:' . $params['paneltextalignment'] . ';} ' : '';
    $css .= ! empty($params['panelborderstyle']) ? $accordionBody . '{border-style:' . $params['panelborderstyle'] . ';} ' : '';
    $css .= ! empty($params['panelborderwidth']) ? $accordionBody . '{border-width:' . $params['panelborderwidth'] . 'px;} ' : '';
    $css .= ! empty($params['panelbordercolor']) ? $accordionBody . '{border-color:' . $params['panelbordercolor'] . ';} ' : '';
    $css .= ! empty($params['headerbgcolor']) ? $accordionButton . '{background:' . $params['headerbgcolor'] . ';} ' . $accordionButton . ':not(.collapsed) {background:#e7f1ff;} ' : '';
    $css .= ! empty($params['headeractivebgcolor']) ? $accordionButton . ':not(.collapsed) {background:' . $params['headeractivebgcolor'] . ';} ' : '';
    $css .= ! empty($params['headercontentcolor']) ? $accordionButton . '{color:' . $params['headercontentcolor'] . ';} ' . $accordionButton . ':not(.collapsed) {color:#0c63df;} ' : '';
    $css .= ! empty($params['headerfontstyle']) ? $accordionButton . '{font-style:' . $params['headerfontstyle'] . ';} ' : '';
    $css .= ! empty($params['headerfontsize']) ? $accordionButton . '{font-size:' . $params['headerfontsize'] . 'px;} ' : '';
    $css .= ! empty($params['headerfontweight']) ? $accordionButton . '{font-weight:' . $params['headerfontweight'] . ';} ' : '';
    $css .= ! empty($params['headerborderstyle']) ? $accordionButton . '{border-style:' . $params['headerborderstyle'] . ';} ' : '';
    $css .= ! empty($params['headerborderwidth']) ? $accordionButton . '{border-width:' . $params['headerborderwidth'] . 'px;} ' : '';
    $css .= ! empty($params['headerbordercolor']) ? $accordionButton . '{border-color:' . $params['headerbordercolor'] . ';} ' : '';

    $headerlib->add_css($css);
}