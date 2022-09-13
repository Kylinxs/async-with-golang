
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 *  Test wiki page rendering options
 */

namespace test\Tiki\Lib\wiki;

use Exception;
use PHPUnit\Framework\TestCase;
use TikiLib;

class WikiLibTest extends TestCase
{
    private $pageName = 'WikiLib Test Page';

    protected function setUp(): void
    {
        global $testhelpers;

        require_once(__DIR__ . '/../TestHelpers.php');
        $testhelpers->simulateTikiScriptContext();

        require_once(__DIR__ . '/../../../lib/wiki/renderlib.php');
    }

    protected function tearDown(): void
    {
        global $testhelpers;

        $testhelpers->removeAllVersions($this->pageName);

        $testhelpers->stopSimulatingTikiScriptContext();
    }

    /**
     * Test per wiki page autotoc settings
     *
     * @throws Exception
     */
    public function testProcessPageDisplayOptions(): void
    {
        global $prefs, $testhelpers, $headerlib;
        $wikilib = TikiLib::lib('wiki');

        // testing autotoc per page settings
        $prefs['wiki_auto_toc'] = 'y';
        $prefs['feature_page_title'] = 'n';
        $prefs['javascript_enabled'] = 'y';

        $pageContent = '! Heading H1
!! Heading H2
Some text
!!! Heading H3
Some text
!! Second Heading H2
Some more text
';

        $testhelpers->createPage($this->pageName, 0, $pageContent);

        // processPageDisplayOptions needs this
        $_REQUEST['page'] = $this->pageName;

        $prefs['wiki_toc_default'] = 'on';
        $wikilib->set_page_auto_toc($this->pageName, 0);

        $wikilib->processPageDisplayOptions();
        $tags = $headerlib->output_js_files();
        $expected = 'lib/jquery_tiki/autoToc.js';
        $this->assertStringNotContainsString($expected, $tags, 'Autotoc on, page set to default');

        $headerlib->clear_js(true);
        $wikilib->set_page_auto_toc($this->pageName, -1);
        $wikilib->processPageDisplayOptions();
        $tags = $headerlib->output_js_files();
        $this->assertStringNotContainsString($expected, $tags, 'Autotoc on, page set to off');

        $headerlib->clear_js(true);
        $wikilib->set_page_auto_toc($this->pageName, 1);
        $wikilib->processPageDisplayOptions();
        $tags = $headerlib->output_js_files();
        $this->assertStringContainsString($expected, $tags, 'Autotoc on, page set to on');

        $prefs['wiki_toc_default'] = 'off';
        $headerlib->clear_js(true);
        $wikilib->set_page_auto_toc($this->pageName, 0);
        $wikilib->processPageDisplayOptions();
        $tags = $headerlib->output_js_files();
        $this->assertStringNotContainsString($expected, $tags, 'Autotoc off, page set to default');

        $tags = $headerlib->output_js_files();
        $wikilib->set_page_auto_toc($this->pageName, -1);
        $wikilib->processPageDisplayOptions();
        $headerlib->clear_js(true);
        $this->assertStringNotContainsString($expected, $tags, 'Autotoc off, page set to off');

        $headerlib->clear_js(true);
        $wikilib->set_page_auto_toc($this->pageName, 1);
        $wikilib->processPageDisplayOptions();
        $tags = $headerlib->output_js_files();
        $this->assertStringContainsString($expected, $tags, 'Autotoc off, page set to on');
    }
}