
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class WikiParser_PluginOutputTest extends PHPUnit\Framework\TestCase
{
    public function testWikiToWikiOutput()
    {
        $output = WikiParser_PluginOutput::wiki('^Hello world!^');

        $this->assertEquals('^Hello world!^', $output->toWiki());
    }

    public function testWikiToHtmlOutput()
    {
        $output = WikiParser_PluginOutput::wiki('^Hello world!^');

        $this->assertStringContainsString('<div class="card bg-light"><div class="card-body">Hello world!</div></div>', $output->toHtml());
    }

    public function testHtmlToWikiOutput()
    {
        $output = WikiParser_PluginOutput::html('<div>Hello</div>');

        $this->assertEquals('~np~<div>Hello</div>~/np~', $output->toWiki());
    }

    public function testHtmlToHtmlOutput()
    {
        $output = WikiParser_PluginOutput::html('<div>Hello</div>');

        $this->assertEquals('<div>Hello</div>', $output->toHtml());
    }

    public function testInternalError()
    {
        $output = WikiParser_PluginOutput::internalError(tra('Unknown conversion'));

        $this->assertStringContainsString('Unknown conversion', $output->toHtml());
    }

    public function testMissingArguments()
    {
        $output = WikiParser_PluginOutput::argumentError(['id', 'test']);

        $this->assertStringContainsString('<li>id</li>', $output->toHtml());
    }
}