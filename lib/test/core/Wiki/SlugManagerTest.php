
<?php

namespace Test\Wiki;

use PHPUnit\Framework\TestCase;
use Tiki\Wiki\SlugManager;
use Tiki\Wiki\SlugManager\UrlencodeGenerator;
use Tiki\Wiki\SlugManager\UnderscoreGenerator;

class SlugManagerTest extends TestCase
{
    private $manager;

    protected function setUp(): void
    {
        $this->manager = new SlugManager();
        $this->manager->setValidationCallback(function () {
            return false;
        });
        $this->manager->addGenerator(new UrlencodeGenerator());
        $this->manager->addGenerator(new UnderscoreGenerator());
    }

    public function testGenerateSimple()
    {
        $slug = $this->manager->generate('urlencode', 'Hello World');

        $this->assertEquals('Hello+World', $slug);
    }

    public function testGenerateUnderscore()
    {
        $slug = $this->manager->generate('underscore', 'Hello World');

        $this->assertEquals('Hello_World', $slug);
    }

    public function testDuplicateAddsSuffix()
    {
        $tracker = new SlugManager\InMemoryTracker();
        $tracker->add('Hello_World');
        $this->manager->setValidationCallback($tracker);

        $slug = $this->manager->generate('underscore', 'Hello World');

        $this->assertEquals('Hello_World_2', $slug);
    }

    /**
     * @dataProvider generatorCases
     *
     * @param $gen
     * @param $slug
     * @param $page
     * @param $suffix
     */
    public function testGeneratorCases($gen, $slug, $page, $suffix)
    {
        $this->assertEquals($slug, $gen->generate($page, $suffix));
    }

    public function testManagerIsClonable()
    {
        $manager = clone $this->manager;

        $tracker = new SlugManager\InMemoryTracker();
        $tracker->add('Hello_World');
        $manager->setValidationCallback($tracker);

        $slug = $this->manager->generate('underscore', 'Hello World');
        $slug2 = $manager->generate('underscore', 'Hello World');

        $this->assertEquals('Hello_World', $slug);
        $this->assertEquals('Hello_World_2', $slug2);
    }

    public function generatorCases()
    {
        return [
            [new UrlencodeGenerator(), 'Hello', 'Hello', null],
            [new UrlencodeGenerator(), 'Hello+World', 'Hello World', null],
            [new UrlencodeGenerator(), 'Hello+World2', 'Hello World', 2],
            [new UrlencodeGenerator(), 'Hello+World3', 'Hello World', 3],
            [new UnderscoreGenerator(), 'Hello', 'Hello', null],
            [new UnderscoreGenerator(), 'Hello_World', 'Hello World', null],
            [new UnderscoreGenerator(), 'Hello_World_2', 'Hello World', 2],
            [new UnderscoreGenerator(), 'Hello_World_3', 'Hello World', 3],
            [new UnderscoreGenerator(), 'Hello_World', 'Hello   World', null],
            [new UnderscoreGenerator(), 'Hello_World', '  Hello   World  ', null],
        ];
    }
}