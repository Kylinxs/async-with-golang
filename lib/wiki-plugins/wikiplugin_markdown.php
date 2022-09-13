
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function wikiplugin_markdown_info()
{
    return [
        'name' => tra('Markdown'),
        'documentation' => 'PluginMarkdown',
        'description' => tra('Parse the body of the plugin using a Markdown parser.'),
        'prefs' => ['wikiplugin_markdown'],
        'body' => tra('Markdown syntax to be parsed'),
        'iconname' => 'code',
        'introduced' => 20,
        'filter' => 'rawhtml_unsafe',
        'format' => 'html',
        'tags' => [ 'advanced' ],
        'params' => [
            // TODO: add some useful params here
        ],
    ];
}

// common requirement for extension packages
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\Table\TableRenderer;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Block\Element\FencedCode;
use League\CommonMark\Block\Renderer\FencedCodeRenderer;

function wikiplugin_markdown($data, $params)
{

    global $prefs;
    extract($params, EXTR_SKIP);

    $md = trim($data);
    $md = str_replace('&lt;x&gt;', '', $md);
    $md = str_replace('<x>', '', $md);

    $md = (new WikiParser_ParsableMarkdown(''))->wikiParse($md);

    # TODO: "if param wiki then" $md = TikiLib::lib('parser')->parse_data($md, ['is_html' => true, 'parse_wiki' => true]);
    return $md;
}