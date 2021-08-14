<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class TikiLib_MultiExplodeTest extends PHPUnit\Framework\TestCase
{
    private $saved;

    public function setUp(): void
    {
        global $prefs;
        $this->saved = $prefs['namespace_separator'];
    }

    public function tearDown(): void
    {
        global $prefs;
        $prefs['namespace_separator'] = $this->saved;
    }

    public function testSimple(): void
    {
        $lib = TikiLib::lib('tiki');
        $this->assertEquals(['A', 'B'], $lib->multi_explode(':', 'A:B'));
        $this->assertEquals(['A', '', 'B'], $lib->multi_explode(':', 'A::B'));
        $this->assertEquals(['A', '', '', 'B'], $lib->multi_explode(':', 'A:::B'));
    }

    public function testEmpty(): void
    {
        $lib = TikiLib::lib('tiki');
        $this->assertEquals([''], $lib->multi_explode(':', ''));
        $this->assertEquals(['', ''], $lib->multi_explode(':', ':'));
        $this->assertEquals(['', 'B'], $lib->multi_explode(':', ':B'));
        $this->assertEquals(['A', ''], $lib->multi_explode(':', 'A:'));
    }

    public function testIgnoreCharactersUsedInNamespace(): void
    {
        global $prefs;
        $lib = TikiLib::lib('tiki');

        $prefs['namespace_separator'] = ':+:';
        $this->assertEquals(['A:+:B:+:C', 'A:+:B'], $lib->multi_explode(':', 'A:+:B:+:C:A:+:B'));
        $this->assertEquals(['A', '-', 'B:+:C', 'A:+:B'], $lib->multi_explode(':', 'A:-:B:+:C:A:+:B'));

        $prefs['namespace_separator'] = ':-:';
        $this->assertEquals(['A', '+', 'B', '+', 'C', 'A', '+', 'B'], $lib->multi_explode(':', 'A:+:B:+:C:A:+:B'));
        $this->assertEquals(['A:-:B', '+', 'C', 'A', '+', 'B'], $lib->multi_explode(':', 'A:-:B:+:C:A:+:B'));
    }

    public function testSimpleImplode(): void
    {
        $lib = TikiLib::lib('tiki');
        $this->assertEquals('A:B', $lib->multi_implode(':', ['A', 'B']));
        $this->assertEquals('A+C:B+D', $lib->multi_implode([':', '+'], [['A', 'C'], ['B', 'D']]));
    }
}
