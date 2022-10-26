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

class DeclFilterCatchAllUnsetTest extends TikiTestCase
{
    public function testMatch()
    {
        $rule = new DeclFilter_CatchAllUnsetRule();

        $this->assertTrue($rule->match('hello'));
    }

    public function testApply()
    {
        $rule = new DeclFilter_CatchAllUnsetRule();

        $data = [
            'hello' => '123abc',
        ];

        $rule->apply($data, 'hello');

        $this->assertFalse(isset($data['hello']));
    }
}
