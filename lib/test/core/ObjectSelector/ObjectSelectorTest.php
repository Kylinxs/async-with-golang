
<?php

namespace Test\ObjectSelector;

use PHPUnit\Framework\TestCase;
use Tiki\ObjectSelector\Selector;
use Tiki\ObjectSelector\SelectorItem;

class ObjectSelectorTest extends TestCase
{
    private $selector;
    private $mock;

    private $calls = [];

    protected function setUp(): void
    {
        $this->selector = new Selector($this);
    }

    public function testReadEmpty()
    {
        $this->assertEquals(null, $this->selector->read(''));
    }

    public function testReadObjectFromString()
    {
        $expect = new SelectorItem($this->selector, 'wiki page', 'HomePage');
        $this->assertEquals($expect, $this->selector->read('wiki page:HomePage'));
    }

    public function testReadMultiple()
    {
        $this->assertEquals([], $this->selector->readMultiple(''));
    }

    public function testReadMultipleFromString()
    {
        $this->assertEquals([
            $this->selector->read('wiki page:HomePage'),
            $this->selector->read('trackeritem:12'),
        ], $this->selector->readMultiple("wiki page:HomePage\r\ntrackeritem:12\r\n"));
    }

    public function testReadMultipleFromArray()
    {
        $this->assertEquals([
            $this->selector->read('wiki page:HomePage'),
            $this->selector->read('trackeritem:12'),
        ], $this->selector->readMultiple([
            'wiki page:HomePage',
            'trackeritem:12',
        ]));
    }

    public function testExcludeDuplicates()
    {
        $this->assertEquals([
            $this->selector->read('trackeritem:12'),
        ], $this->selector->readMultiple([
            'trackeritem:12',
            'trackeritem:12',
        ]));
    }

    public function testObtainTitle()
    {
        $object = $this->selector->read('trackeritem:12');

        $this->assertEquals('Foobar', $object->getTitle());
    }

    public function testArrayAccess()
    {
        $object = $this->selector->read('trackeritem:12');

        $this->assertEquals('trackeritem', $object['type']);
        $this->assertEquals('12', $object['id']);
        $this->assertEquals('Foobar', $object['title']);
        $this->assertEquals('trackeritem:12', (string) $object);
    }

    public function testReadMultipleSimpleOnEmpty()
    {
        $this->assertEquals([], $this->selector->readMultipleSimple('trackeritem', '', ','));
    }

    public function testReadMultipleSimpleFromString()
    {
        $this->assertEquals([
            $this->selector->read('trackeritem:14'),
            $this->selector->read('trackeritem:12'),
        ], $this->selector->readMultipleSimple('trackeritem', '14:12', ':'));
    }

    public function testReadMultipleSimpleEliminatesDuplicates()
    {
        $this->assertEquals([
            $this->selector->read('trackeritem:14'),
        ], $this->selector->readMultipleSimple('trackeritem', '14,14', ','));
    }

    public function testReadMultipleSimpleHandlesArrays()
    {
        $this->assertEquals([
            $this->selector->read('trackeritem:14'),
            $this->selector->read('trackeritem:12'),
        ], $this->selector->readMultipleSimple('trackeritem', ['14', '12'], ':'));
    }

    public function get_title($type, $id)
    {
        return 'Foobar';
    }
}