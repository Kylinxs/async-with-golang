
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Search_Expr_ParserTest extends PHPUnit\Framework\TestCase
{
    private $parser;

    protected function setUp(): void
    {
        $this->parser = new Search_Expr_Parser();
    }

    public function testSimpleWord()
    {
        $result = $this->parser->parse('hello');

        $this->assertEquals($result, new Search_Expr_Token('hello'));
    }

    public function testMultipleWords()
    {
        $result = $this->parser->parse('"hello world" test again');
        $this->assertEquals(
            new Search_Expr_ImplicitPhrase([
                new Search_Expr_ExplicitPhrase('hello world'),
                new Search_Expr_ImplicitPhrase([
                    new Search_Expr_Token('test'),
                    new Search_Expr_Token('again'),
                ]),
            ]),
            $result
        );
    }

    public function testMultipleSimpleWords()
    {
        $result = $this->parser->parse('hello world test again');
        $this->assertEquals(
            new Search_Expr_ImplicitPhrase([
                new Search_Expr_Token('hello'),
                new Search_Expr_Token('world'),
                new Search_Expr_Token('test'),
                new Search_Expr_Token('again'),
            ]),
            $result
        );
    }

    public function testSimpleParenthesis()
    {
        $result = $this->parser->parse('(test again)');
        $this->assertEquals(
            new Search_Expr_ImplicitPhrase([
                new Search_Expr_Token('test'),
                new Search_Expr_Token('again'),
            ]),
            $result
        );
    }

    public function testMatchParenthesis()
    {
        $result = $this->parser->parse('(hello (bob roger)) (test again)');
        $this->assertEquals(
            new Search_Expr_ImplicitPhrase([
                new Search_Expr_ImplicitPhrase([
                    new Search_Expr_Token('hello'),
                    new Search_Expr_ImplicitPhrase([
                        new Search_Expr_Token('bob'),
                        new Search_Expr_Token('roger'),
                    ]),
                ]),
                new Search_Expr_ImplicitPhrase([
                    new Search_Expr_Token('test'),
                    new Search_Expr_Token('again'),
                ]),
            ]),
            $result
        );
    }

    public function testStripOr()
    {
        $result = $this->parser->parse('(bob roger) or (test again)');

        $this->assertEquals(
            new Search_Expr_Or(
                [
                    $this->parser->parse('bob roger'),
                    $this->parser->parse('test again'),
                ]
            ),
            $result
        );
    }

    public function testRecongnizeAnd()
    {
        $result = $this->parser->parse('(bob roger) and (test again)');

        $this->assertEquals(
            new Search_Expr_And(
                [
                    $this->parser->parse('bob roger'),
                    $this->parser->parse('test again'),
                ]
            ),
            $result
        );
    }

    public function testSequence()
    {
        $result = $this->parser->parse('1 and 2 and 3');

        $this->assertEquals(
            new Search_Expr_And(
                [
                    new Search_Expr_And(
                        [
                            $this->parser->parse('1'),
                            $this->parser->parse('2'),
                        ]
                    ),
                    $this->parser->parse('3'),
                ]
            ),
            $result
        );
    }

    public function testEquivalenceBetweenPlusAndAnd()
    {
        $result = $this->parser->parse('a php + framework');
        $expect = $this->parser->parse('a php and framework');

        $this->assertEquals($expect, $result);
    }

    public function testSequenceWithOr()
    {
        $result = $this->parser->parse('1 or 2 or 3');

        $this->assertEquals(
            new Search_Expr_Or(
                [
                    new Search_Expr_Or(
                        [
                            $this->parser->parse('1'),
                            $this->parser->parse('2'),
                        ]
                    ),
                    $this->parser->parse('3'),
                ]
            ),
            $result
        );
    }

    public function testRecongnizePlus()
    {
        $result = $this->parser->parse('(bob roger) + (test again)');

        $this->assertEquals(
            new Search_Expr_And(
                [
                    $this->parser->parse('bob roger'),
                    $this->parser->parse('test again'),
                ]
            ),
            $result
        );
    }

    public function testCheckPriority()
    {
        $result = $this->parser->parse('bob AND test OR again');

        $this->assertEquals(
            new Search_Expr_And(
                [
                    $this->parser->parse('bob'),
                    $this->parser->parse('test OR again'),
                ]
            ),
            $result
        );
    }

    public function testCheckLowerSpacePriority()
    {
        $result = $this->parser->parse('bob AND test again');

        $this->assertEquals(
            new Search_Expr_ImplicitPhrase([
                $this->parser->parse('bob AND test'),
                $this->parser->parse('again'),
            ]),
            $result
        );
    }

    public function testNotOperator()
    {
        $result = $this->parser->parse('bob AND NOT (roger alphonse)');

        $this->assertEquals(
            new Search_Expr_And(
                [
                    $this->parser->parse('bob'),
                    new Search_Expr_Not($this->parser->parse('roger alphonse')),
                ]
            ),
            $result
        );
    }

    public function testDoubleParenthesisClose()
    {
        $result = $this->parser->parse('hello (test) foo) bar');

        $this->assertEquals($this->parser->parse('hello (test) foo bar'), $result);
    }

    public function testMissingClose()
    {
        $result = $this->parser->parse('hello (test foo bar');

        $this->assertEquals($this->parser->parse('hello (test foo bar)'), $result);
    }

    public function testConsecutiveKeywords()
    {
        $result = $this->parser->parse('hello and and or + or world');

        $this->assertEquals($this->parser->parse('hello and world'), $result);
    }

    public function testNotWithNoValue()
    {
        $result = $this->parser->parse('hello and (not )');

        $this->assertEquals(
            new Search_Expr_And([
                new Search_Expr_Token('hello'),
                new Search_Expr_Not(
                    new Search_Expr_Token('')
                ),
            ]),
            $result
        );
    }
}