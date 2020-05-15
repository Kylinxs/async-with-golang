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

class DeclFilterKeyPatternFilterTest extends TikiTestCase
{
    public function testMatch()
    {
        $rule = new DeclFilter_KeyPatternFilterRule(
            [
                '/^foo_\d+$/' => 'digits',
                '/^bar_[a-z]+$/' => 'digits',
            ]
        );

        $this->assertTrue($rule->match('foo_123'));
        $this->assertTrue($rule->match('bar_abc'));
        $this->assertFalse($rule->match('foo_abc'));
        $this->assertFalse($rule->match('baz'));
    }

    public function testApply()
    {
        $rule = new DeclFilter_KeyPatternFilterRule(
            [
                '/^foo_\d+$/' => 'digits',
                '/^bar_[a-z]+$/' => 'alpha',
            ]
        );

        $data = [
            'foo_123' => '123abc',
            'bar_abc' => '123abc',
            'foo' => '123abc',
        ];

        $rule->apply($data, 'foo_123');
        $rule->apply($data, 'bar_abc');

        $this->assertEquals('123', $data['foo_123']);
        $this->assertEquals('abc', $data['bar_abc']);
        $this->assertEquals('123abc', $data['foo']);
    }

    public function testApplyOnElements()
    {
        $rule = new DeclFilter_KeyPatternFilterRule(
            [
                '/^foo_\d+$/' => 'digits',
            ]
        );
        $rule->applyOnElements();

        $data = [
            'foo_123' => ['123abc', '456def'],
        ];

        $rule->apply($data, 'foo_123');

        $this->assertEquals(['123', '456'], $data['foo_123']);
    }
}
