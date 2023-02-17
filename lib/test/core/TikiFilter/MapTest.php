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

class TikiFilter_MapTest extends TikiTestCase
{
    /** @var string Test string to use which has a variety of input types */
    private $standardString = '!“#π$ΣeÑ‘()¿*+,½-./«»2÷≈y:·;{<|=}>~?@ü©    :/g.,:|4h&#Δδ_🍘コン onclick<script>alert(1)</script><b>δ</b><x>';

    public function testTikiFiltersExpectedOutput()
    {
        $standardStringOutputs = [
            'int' => 0,
            'bool' => true,
            'isodate' => null,
            'isodatetime' => null,
            'iso8601' => null,
            'attribute_type' => 'e-.2yg.4h_onclickscriptalert1scriptbbx',
            'lang' => '',
            'relativeurl' => '%21%E2%80%9C#%CF%80$%CE%A3e%C3%91%E2%80%98()%C2%BF*+,%C2%BD-./%C2%AB%C2%BB2%C3%B7%E2%89%88y:%C2%B7;%7B~?@%C3%BC%C2%A9%20%20%20%20:/g.,:%7C4h&%23%CE%94%CE%B4_%F0%9F%8D%98%E3%82%B3%E3%83%B3%20onclickalert(1)%CE%B4',
            'digits' => '241',
            'intscolons' => '2:::41',
            'intscommas' => ',2,41',
            'intspipes' => '2||41',
            'alpha' => 'πΣeÑyüghΔδコンonclickscriptalertscriptbδbx',
            'alphaspace' => 'πΣeÑyü    ghΔδコン onclickscriptalertscriptbδbx',
            'word' => 'e2yg4h_onclickscriptalert1scriptbbx',
            'wordspace' => 'e2y    g4h_ onclickscriptalert1scriptbbx',
            'alnum' => 'πΣeÑ½2yüg4hΔδコンonclickscriptalert1scriptbδbx',
            'alnumdash' => 'πΣeÑ½-2yüg4hΔδ_コンonclickscriptalert1scriptbδbx',
            'alnumspace' => 'πΣeÑ½2yü    g4hΔδコン onclickscriptalert1scriptbδbx',
            'username' => '!“#π$ΣeÑ‘()¿*+,½-./«»2÷≈y:·;{~?@ü©    :/g.,:|4h&#Δδ_🍘コン onclickalert(1)δ',
            'groupname' => '!“#π$ΣeÑ‘()¿*+,½-./«»2÷≈y:·;{~?@ü©    :/g.,:|4h&#Δδ_🍘コン onclickalert(1)δ',
            'pagename' => '!“#π$ΣeÑ‘()¿*+,½-./«»2÷≈y:·;{~?@ü©    :/g.,:|4h&#Δδ_🍘コン onclickalert(1)δ',
            'topicname' => '!“#π$ΣeÑ‘()¿*+,½-./«»2÷≈y:·;{~?@ü©    :/g.,:|4h&#Δδ_🍘コン onclickalert(1)δ',
            'themename' => '!“#π$ΣeÑ‘()¿*+,½-./«»2÷≈y:·;{~?@ü©    :/g.,:|4h&#Δδ_🍘コン onclickalert(1)δ',
            'email' => '!“#π$ΣeÑ‘()¿*+,½-./«»2÷≈y:·;{~?@ü©    :/g.,:|4h&#Δδ_🍘コン onclickalert(1)δ',
            'url' => '!“#π$ΣeÑ‘()¿*+,½-./«»2÷≈y:·;{~?@ü©    :/g.,:|4h&#Δδ_🍘コン onclickalert(1)δ',
            'text' => '!“#π$ΣeÑ‘()¿*+,½-./«»2÷≈y:·;{~?@ü©    :/g.,:|4h&#Δδ_🍘コン onclickalert(1)δ',
            'date' => '!“#π$ΣeÑ‘()¿*+,½-./«»2÷≈y:·;{~?@ü©    :/g.,:|4h&#Δδ_🍘コン onclickalert(1)δ',
            'time' => '!“#π$ΣeÑ‘()¿*+,½-./«»2÷≈y:·;{~?@ü©    :/g.,:|4h&#Δδ_🍘コン onclickalert(1)δ',
            'datetime' => '!“#π$ΣeÑ‘()¿*+,½-./«»2÷≈y:·;{~?@ü©    :/g.,:|4h&#Δδ_🍘コン onclickalert(1)δ',
            'striptags' => '!“#π$ΣeÑ‘()¿*+,½-./«»2÷≈y:·;{~?@ü©    :/g.,:|4h&#Δδ_🍘コン onclickalert(1)δ',
            'purifier' => '!“#π$ΣeÑ‘()¿*+,½-./«»2÷≈y:·;{&lt;|=}&gt;~?@ü©    :/g.,:|4hΔδ_🍘コン onclick<b>δ</b>',
            'html' => '!“#π$ΣeÑ‘()¿*+,½-./«»2÷≈y:·;{&lt;|=}&gt;~?@ü©    :/g.,:|4hΔδ_🍘コン onclick<b>δ</b>',
            'xss' => '!“#π$ΣeÑ‘()¿*+,½-./«»2÷≈y:·;{<|=}>~?@ü©    :/g.,:|4h&#Δδ_🍘コン on<x>click<sc<x>ript>alert(1)</script><b>δ</b><x>',
            'wikicontent' => '!“#π$ΣeÑ‘()¿*+,½-./«»2÷≈y:·;{<|=}>~?@ü©    :/g.,:|4h&#Δδ_🍘コン on<x>click<sc<x>ript>alert(1)</script><b>δ</b><x>',
            'rawhtml_unsafe' => '!“#π$ΣeÑ‘()¿*+,½-./«»2÷≈y:·;{<|=}>~?@ü©    :/g.,:|4h&#Δδ_🍘コン onclick<script>alert(1)</script><b>δ</b>',
            'none' => '!“#π$ΣeÑ‘()¿*+,½-./«»2÷≈y:·;{<|=}>~?@ü©    :/g.,:|4h&#Δδ_🍘コン onclick<script>alert(1)</script><b>δ</b><x>'
            ];

        foreach ($standardStringOutputs as $filter => $expected) {
            $this->assertSame($expected, TikiFilter::get($filter)->filter($this->standardString), "The TikiFilter '$filter' failed to match expected output.");
        }
    }

    /**
     * Triggered errors become exceptions...
     *
     */
    public function testTikiFiltersInstanceOfUnknown(): void
    {
        $this->expectError();
        $this->assertInstanceOf(TikiFilter_PreventXss::class, TikiFilter::get('does_not_exist'));
    }

    public function testComposed(): void
    {
        $filter = new JitFilter(['foo' => 'test123']);
        $filter->replaceFilter('foo', 'digits');

        $this->assertEquals('123', $filter['foo']);
    }

    public function testTikifilterSetDefaultFilter(): void
    {
        $filter = new JitFilter(['foo' => 'test123']);
        $filter->setDefaultFilter('digits');

        $this->assertEquals('123', $filter['foo']);
    }
}
