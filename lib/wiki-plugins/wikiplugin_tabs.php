
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function wikiplugin_tabs_info()
{
    return [
        'name' => tra('Tabs'),
        'documentation' => 'PluginTabs',
        'description' => tra('Arrange content in tabs'),
        'prefs' => [ 'wikiplugin_tabs' ],
        'body' => tra('Tabs content, separated by "/////"'),
        'iconname' => 'th-large',
        'introduced' => 4,
        'filter' => 'wikicontent',
        'tags' => [ 'basic' ],
        'params' => [
            'name' => [
                'required' => false,
                'name' => tra('Tabset Name'),
                'description' => tr('Unique tabset name (if you want the last state to be remembered). Example:')
                    . '<code>user_profile_tabs</code>',
                'since' => '4.0',
                'filter' => 'text',
                'default' => '',
            ],
            'tabs' => [
                'required' => true,
                'name' => tra('Tab Titles'),
                'description' => tra('Pipe-separated list of tab titles. Example:') . '<code>tab 1|tab 2|tab 3</code>',
                'since' => '4.0',
                'filter' => 'text',
                'default' => '',
            ],
            'toggle' => [
                'required' => false,
                'name' => tra('Toggle Tabs'),
                'description' => tra('Allow toggling between tabs and no-tabs view'),
                'since' => '8.0',
                'default' => 'y',
                'filter' => 'alpha',
                'options' => [
                    ['text' => '', 'value' => ''],
                    ['value' => 'y' , 'text' => tra('Yes')],
                    ['value' => 'n', 'text' => tra('No')],
                ],
            ],
            'inside_pretty' => [
                'required' => false,
                'name' => tra('Inside Pretty Tracker'),
                'description' => tra('Parse pretty tracker variables within tabs'),
                'since' => '8.0',
                'default' => 'n',
                'filter' => 'alpha',
                'options' => [
                    ['text' => '', 'value' => ''],
                    ['value' => 'n', 'text' => tra('No')],
                    ['value' => 'y' , 'text' => tra('Yes')],
                ],
            ],
            'direction' => [
                'required' => false,
                'name' => tra('Tabs direction'),
                'description' => tra('Change direction of tabs (horizontal by default).'),
                'since' => '25.0',
                'default' => 'horizontal',
                'filter' => 'word',
                'options' => [
                    ['text' => '', 'value' => ''],
                    ['text' => tra('Horizontal'), 'value' => 'horizontal'],
                    ['text' => tra('Vertical'), 'value' => 'vertical']
                ],
            ],
            'tabbgcolor' => [
                'required'    => false,
                'name'        => tra('Tabs background color'),
                'description' => tra(
                    'Enter a valid CSS color hex code, or an RGBA value if setting opacity is desired; for example: #000 or rgba(00, 00, 00, 0.5).'
                ),
                'filter'      => 'text',
                'default'     => '',
                'since'       => '25.0',
            ],
            'tabactivebgcolor' => [
                'required'    => false,
                'name'        => tra('Tab active background color'),
                'description' => tra(
                    'Enter a valid CSS color hex code, or an RGBA value if setting opacity is desired; for example: #000 or rgba(00, 00, 00, 0.5).'
                ),
                'filter'      => 'text',
                'default'     => '',
                'since'       => '25.0',
            ],
            'tabborderstyle' => [
                'required'    => false,
                'name'        => tra('Tabs border style'),
                'description' => tra(
                    'Determine the kind of border to apply to the tabs'
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
            'tabborderwidth' => [
                'required'    => false,
                'name'        => tra('Tabs border width'),
                'description' => tra(
                    'To change the width of tabs border in pixels (1 by default). For example: 3 for 3 pixels'
                ),
                'filter'      => 'text',
                'default'     => '1',
                'since'       => '25.0',
            ],
            'tabbordercolor' => [
                'required'    => false,
                'name'        => tra('Tabs border color'),
                'description' => tra(
                    'Enter a valid CSS color hex code, or an RGBA value if setting opacity is desired; for example: #000 or rgba(00, 00, 00, 0.5).'
                ),
                'filter'      => 'text',
                'default'     => '',
                'since'       => '25.0',
            ],
            'tabfontstyle' => [
                'required'    => false,
                'name'        => tra('Tabs font style'),
                'description' => tra(
                    'Use to specify the font style of the tabs text'
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
            'tabfontweight' => [
                'required'    => false,
                'name'        => tra('Tabs font weight'),
                'description' => tra(
                    'Use to define the thickness of the text characters to be displayed in the tabs'
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
                ],            ],
            'tabfontsize' => [
                'required'    => false,
                'name'        => tra('Tabs font size'),
                'description' => tra(
                    'To set the size of the tabs text in pixels. For example: 20 for 20 pixels'
                ),
                'filter'      => 'text',
                'default'     => '16',
                'since'       => '25.0',
            ],
            'tabtextcolor' => [
                'required'    => false,
                'name'        => tra('Tabs text color'),
                'description' => tra(
                    'Enter a valid CSS color hex code, or an RGBA value if setting opacity is desired; for example: #000 or rgba(00, 00, 00, 0.5).'
                ),
                'filter'      => 'text',
                'default'     => '',
                'since'       => '25.0',
            ],
            'tabactivetextcolor' => [
                'required'    => false,
                'name'        => tra('Tab active text color'),
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
            'paneltextcolor' => [
                'required'    => false,
                'name'        => tra('Panel text color'),
                'description' => tra(
                    'Enter a valid CSS color hex code, or an RGBA value if setting opacity is desired; for example: #000 or rgba(00, 00, 00, 0.5).'
                ),
                'filter'      => 'text',
                'default'     => '',
                'since'       => '25.0',
            ],
            'paneltextstyle' => [
                'required'    => false,
                'name'        => tra('Panel text style'),
                'description' => tra(
                    'Use to change the panels text style'
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
            'panelfontweight' => [
                'required'    => false,
                'name'        => tra('Panel font weight'),
                'description' => tra(
                    'Use to define the thickness of the text characters to be displayed in the panels'
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
            'panelfontsize' => [
                'required'    => false,
                'name'        => tra('Panel font size'),
                'description' => tra(
                    'To set the size of the text in pixels. For example: 20 for 20 pixels'
                ),
                'filter'      => 'text',
                'default'     => '16',
                'since'       => '25.0',
            ],
            'panelborderstyle' => [
                'required'    => false,
                'name'        => tra('Panel border style'),
                'description' => tra(
                    'Determine the kind of border to apply to the panels'
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
        ],
    ];
}

function wikiplugin_tabs($data, $params)
{
    $tikilib = TikiLib::lib('tiki');
    if (! empty($params['name'])) {
        $tabsetname = $params['name'];
    } else {
        $tabsetname = '';
    }

    if (! empty($params['toggle'])) {
        $toggle = $params['toggle'];
    } else {
        $toggle = 'y';
    }

    if (! empty($params['inside_pretty']) && $params['inside_pretty'] == 'y') {
        $inside_pretty = true;
    } else {
        $inside_pretty = false;
    }

    $tabs = [];
    if (! empty($params['tabs'])) {
        $tabs = explode('|', $params['tabs']);
    } else {
        return "''" . tra("No tab title specified. At least one must be specified in order for the tabs to appear.") . "''";
    }
    if (! empty($data)) {
        $data = TikiLib::lib('parser')->parse_data($data, ['suppress_icons' => true, 'inside_pretty' => $inside_pretty]);
        $tabData = explode('/////', $data);
    }
    $smarty = TikiLib::lib('smarty');
    $smarty->assign('tabsetname', $tabsetname);
    $smarty->assign_by_ref('tabs', $tabs);
    $smarty->assign('toggle', $toggle);
    $smarty->assign('params', $params);
    $smarty->assign_by_ref('tabcontent', $tabData);

    $content = $smarty->fetch('wiki-plugins/wikiplugin_tabs.tpl');

    return $content;
}