
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

class DeclFilterBaseTest extends TikiTestCase
{
    public function testSimple()
    {
        $data = [
            'numeric' => '123abc',
            'alpha' => 'alpha123',
        ];

        $filter = new DeclFilter();
        $filter->addStaticKeyFilters(
            [
                'numeric' => 'digits',
                'alpha' => 'alpha',
            ]
        );

        $data = $filter->filter($data);

        $this->assertEquals('123', $data['numeric']);
        $this->assertEquals('alpha', $data['alpha']);
    }

    public function testStructure()
    {
        $data = [
            'num_array' => [134, '456', 'abc', '123abc'],
        ];

        $filter = new DeclFilter();
        $filter->addStaticKeyFiltersForArrays(['num_array' => 'digits',]);

        $data = $filter->filter($data);

        $this->assertContains('134', $data['num_array']);
        $this->assertContains('456', $data['num_array']);
        $this->assertContains('123', $data['num_array']);

        $this->assertNotContains('abc', $data);
    }

    public function testDefault()
    {
        $filter = new DeclFilter();
        $filter->addStaticKeyFilters(['hello' => 'digits',]);
        $filter->addCatchAllFilter('alpha');

        $data = $filter->filter(
            [
                'hello' => '123abc',
                'world' => '123abc',
            ]
        );

        $this->assertEquals('abc', $data['world']);
        $this->assertEquals('123', $data['hello']);
    }

    public function testNoDefault()
    {
        $filter = new DeclFilter();
        $filter->addStaticKeyFilters(['hello' => 'digits',]);

        $data = $filter->filter(
            [
                'hello' => '123abc',
                'world' => '123abc',
            ]
        );

        $this->assertEquals('123abc', $data['world']);
        $this->assertEquals('123', $data['hello']);
    }
}