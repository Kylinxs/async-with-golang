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

    public function testEmpty():