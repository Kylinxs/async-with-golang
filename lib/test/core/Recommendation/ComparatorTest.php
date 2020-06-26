
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Recommendation;

use PHPUnit\Framework\TestCase;

class ComparatorTest extends TestCase
{
    public function testNoEngines()
    {
        $engineSet = new EngineSet();
        $comparator = new Comparator($engineSet);

        $input = new Input\UserInput('bob');

        $this->assertEmpty($comparator->generate($input));
    }

    public function testSingleEngine()
    {
        $engineSet = new EngineSet();
        $engineSet->register('test-a', new Engine\FakeEngine([
            ['type' => 'wiki page', 'object' => 'Content A'],
            ['type' => 'wiki page', 'object' => 'Content B'],
        ]));
        $comparator = new Comparator($engineSet);

        $input = new Input\UserInput('bob');

        $expect = new RecommendationSet('test-a');
        $expect->add(new Recommendation('wiki page', 'Content A'));
        $expect->add(new Recommendation('wiki page', 'Content B'));

        $this->assertEquals([$expect], $comparator->generate($input));
    }

    public function testMultipleEngine()
    {
        $engineSet = new EngineSet();
        $engineSet->register('test-a', new Engine\FakeEngine([
            ['type' => 'wiki page', 'object' => 'Content A'],
        ]));
        $engineSet->register('test-b', new Engine\FakeEngine([
            ['type' => 'wiki page', 'object' => 'Content B'],
        ]));
        $comparator = new Comparator($engineSet);

        $input = new Input\UserInput('bob');

        $expectA = new RecommendationSet('test-a');
        $expectA->add(new Recommendation('wiki page', 'Content A'));
        $expectB = new RecommendationSet('test-b');
        $expectB->add(new Recommendation('wiki page', 'Content B'));

        $this->assertEquals([$expectA, $expectB], $comparator->generate($input));
    }

    public function testEngineProvidesDebugInformation()
    {
        $engineSet = new EngineSet();
        $engineSet->register('test-a', new Engine\FakeEngine([
            ['type' => 'wiki page', 'object' => 'Content A'],
            new Debug\SourceDocument('wiki page', 'Content Z'),
        ]));

        $comparator = new Comparator($engineSet);
        $input = new Input\UserInput('bob');

        $expect = new RecommendationSet('test-a');
        $expect->add(new Recommendation('wiki page', 'Content A'));
        $expect->addDebug(new Debug\SourceDocument('wiki page', 'Content Z'));

        $this->assertEquals([$expect], $comparator->generate($input));
    }
}