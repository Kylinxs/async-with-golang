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

class DeclFilterConfigureTest extends TikiTestCase
{
    public function testSimple()
    {
        $configuration = [
            ['staticKeyFilters' => [
                'hello' => 'digits',
                'world' => 'alpha',
            ]],
            ['staticKeyFiltersForArrays' => [
                'foo' => 'digits',
            ]],
            ['catchAllFilter' => new Laminas\Filter\StringToUpper()],
        ];

        $filter = DeclFilter::fromConfiguration($configuration);

        $data = $filter->filter(
            [
                'hello' => '123abc',
                'world' => '123abc',
                'foo' => [
                    'abc123',
                    'def456',
                ],
                'bar' => 'undeclared',
            ]
        );

        $this->assertEquals('123', $data['hello']);
        $this->assertEquals('abc', $data['world']);
        $this->assertContains('123', $data['foo']);
        $this->assertContains('456', $data['foo']);
        $this->assertEquals('UNDECLARED', $data['bar']);
    }

    /**
     * Triggered errors become exceptions...
     */
    public function testDisallowed()
    {
        $this->expectError();
        $configuration = [
            ['catchAllFilter' => new Laminas\Filter\StringToUpper()],
        ];

        $filter = DeclFilter::fromConfiguration($configuration, ['catchAllFilter']);
    }

    public function testMissingLevel()
    {
        $this->expectError();
        $configuration = [
            'catchAllUnset' => null,
        ];

        $filter = DeclFilter::fromConfiguration($configuration);
    }

    public function testUnsetSome()
    {
        $configuration = [
            ['staticKeyUnset' => ['hello', 'world']],
            ['catchAllFilter' => new Laminas\Filter\StringToUpper()],
        ];

        $filter = DeclFilter::fromConfiguration($configuration);

        $data = $filter->filter(
            [
                'hello' => '123abc',
                'world' => '123abc',
                'bar' => 'undeclared',
            ]
        );

        $this->assertFalse(isset($data['hello']));
        $this->assertFalse(isset($data['world']));
        $this->assertEquals('UNDECLARED', $data['bar']);
    }

    public function testUnsetOthers()
    {
        $configuration = [
            ['staticKeyFilters' => [
                'hello' => 'digits',
                'world' => 'alpha',
            ]],
            ['catchAllUnset' => null],
        ];

        $filter = DeclFilter::fromConfiguration($configuration);

        $data = $filter->filter(
            [
                'hello' => '123abc',
                'world' => '123abc',
                'bar' => 'undeclared',
            ]
        );

        $this->assertEquals('123', $data['hello']);
        $this->assertEquals('abc', $data['world']);
        $this->assertFalse(isset($data['bar']));
    }

    public function testFilterPattern()
    {
        $configuration = [
            ['keyPatternFilters' => [
                '/^hello/' => 'digits',
            ]],
            ['keyPatternFiltersForArrays' => [
                '/^fo+$/' => 'alpha',
            ]],
        ];

        $filter = DeclFilter::fromConfiguration($configuration);

        $data = $filter->filter(
            [
                'hello123' => '123abc',
                'hello456' => '123abc',
                'world' => '123abc',
                'foo' => [
                    'abc123',
                    'def456',
                ],
            ]
        );

        $this->assertEquals('123', $data['hello123']);
        $this->assertEquals('123', $data['hello456']);
        $this->assertEquals('123abc', $data['world']);
        $this->assertContains('abc', $data['foo']);
        $this->assertContains('def', $data['foo']);
    }

    public function testUnsetPattern()
    {
        $configuration = [
            ['keyPatternUnset' => [
                '/^hello/',
            ]],
        ];

        $filter = DeclFilter::fromConfiguration($configuration);

        $data = $filter->filter(
            [
                'hello123' => '123abc',
                'hello456' => '123abc',
                'world' => '123abc',
            ]
        );

        $this->assertFalse(isset($data['hello123']));
        $this->assertFalse(isset($data['hello456']));
        $this->assertEquals('123abc', $data['world']);
    }
}
