<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * @group unit
 *
 */

class WikiParser_PluginArgumentParserTest extends TikiTestCase
{
    public function testSingleSimpleArgument()
    {
        $out = ['foo' => 'bar'];
        $parser = new WikiParser_PluginArgumentParser();
        $this->assertEquals($parser->parse('foo=bar'), $out);
        $this->assertEquals($parser->parse('foo=>bar'), $out);
        $this->assertEquals($parser->parse('foo => bar'), $out);
    }

    public function testSingleArgumentWithQuotes()
    {
        $out = ['foo' => 'bar'];
        $parser = new WikiParser_PluginArgumentParser();
        $this->assertEquals($parser->parse('foo="bar"'), $out);
        $this->assertEquals($parser->parse('foo=>"bar"'), $out);
        $this->assertEquals($parser->parse('foo => "bar"'), $out);
    }

    public function testEqualsWithinQuotes()
    {
        $out = ['foo' => 'bar=baz'];
        $parser = new WikiParser_PluginArgumentParser();
        $this->assertEquals($parser->parse('foo="bar=baz"'), $out);
    }

    public function testArgumentChaining()
    {
        $out = [
            'foo' => 'bar',
            'hello' => 'world',
            'bar' => 'baz',
        ];

        $parser = new WikiParser_PluginArgumentParser();
        $this->assertEquals($parser->parse('foo=bar hello=world bar=baz'), $out);
        $this->assertEquals($parser->parse('foo=bar,hello=world,bar=baz'), $out);
        $this->assertEquals($parser->parse('foo=bar,hello=world bar=baz'), $out);
        $this->assertEquals($parser->parse('foo=bar,hello=>world bar=baz'), $out);
    }

    public function testQuoteEscape()
    {
        $out = ['foo' => 'bar " test'];
        $parser = new WikiParser_PluginArgumentParser();
        $this->assertEquals($parser->parse('foo=>"bar \" test"'), $out);
    }

    public function testUnclosedQuote()
    {
        $out = ['foo' => '" bar'];
        $parser = new WikiParser_PluginArgumentParser();
        $this->assertEquals($parser->parse('foo=>" bar'), $out);
    }

    public function testNoArgument()
    {
        $parser = new WikiParser_PluginArgumentParser();
        $this->assertEquals([], $parser->parse(''));
        $this->assertEquals([], $parser->parse('foo'));
    }

    public function testInvalidEnd()
    {
        $out = ['a' => 'b'];
        $parser = new WikiParser_PluginArgumentParser();
        $this->assertEquals($parser->parse('a=b foo='), $out);
    }
}
