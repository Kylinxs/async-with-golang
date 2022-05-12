<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

require_once(__DIR__ . '/../../wiki-plugins/wikiplugin_translationof.php');
require_once(__DIR__ . '/../../test/TestHelpers.php');
$relationlib = TikiLib::lib('relation');

class WikiPlugin_TranslationOfTest extends TikiTestCase
{
    public $orig_user;

    private $page_containing_plugin = "PageToBeCreated";

    protected function setUp(): void
    {
        global $user, $prefs;
        $this->orig_user = $user;

        $prefs['site_language'] = 'en';


        /* Need to set those global vars to be able to create and delete pages */
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REQUEST_URI'] = 'phpunit';
        $user = "user_that_can_edit";

        /* Remove all translationof relations */
        //
    }

    protected function tearDown(): void
    {
        global $user, $testhelpers;

        $testhelpers->removeAllVersions($this->page_containing_plugin);

        unset($_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);
        $user = $this->orig_user;
    }

    /**
     * @dataProvider provider
     * @param $data
     * @param $expectedOutput
     * @param array $params
     * @param string $message
     */
    public function testWikiPluginTranslationOf($data, $expectedOutput, $params = [], $message = ""): void
    {
        $this->assertEquals($expectedOutput, wikiplugin_translationof($data, $params), $message);
    }

    public function provider(): array
    {
        return [
            ['', '<a href="tiki-index.php?page=SomePage"  data-bs-toggle="popover" data-bs-container="body" data-bs-trigger="click" data-bs-content="&lt;a&#x20;href&#x3D;&quot;tiki-edit_translation.php&#x3F;page&#x3D;SomePage&amp;target_lang&#x3D;fr&#x23;new_translation&quot;&gt;Translate&#x20;this&#x20;link&lt;&#x2F;a&gt;"  data-bs-delay=\'{"show":500,"hide":250}\'>SomePage</a>',
                  ['orig_page' => 'SomePage', 'translation_lang' => 'fr'],
                  "Happy Path Case"],
            ['', '<a href="tiki-index.php?page=SomePage"  data-bs-toggle="popover" data-bs-container="body" data-bs-trigger="click" data-bs-content="&lt;a&#x20;href&#x3D;&quot;tiki-edit_translation.php&#x3F;page&#x3D;SomePage&amp;target_lang&#x3D;fr&amp;translation_name&#x3D;UnePage&#x23;new_translation&quot;&gt;Translate&#x20;this&#x20;link&lt;&#x2F;a&gt;"  data-bs-delay=\'{"show":500,"hide":250}\'>UnePage</a>',
                  ['orig_page' => 'SomePage', 'translation_lang' => 'fr', 'translation_page' => 'UnePage'],
                  "Case with name of translated page provided"],
        ];
    }

    public function testCreatePageThatContainsAtranslationOfPluginGeneratesAnObjectRelation(): void
    {
        global $prefs;
        $tikilib = TikiLib::lib('tiki');
        $relationlib = TikiLib::lib('relation');

        // Make sure the page doesn't exist to start with.
        $tikilib->remove_all_versions($this->page_containing_plugin);

        $link_source_page = "SourcePage";
        $link_target_page = "TargetPage";

        $relation_id = $relationlib->get_relation_id('tiki.wiki.translationof', 'wiki page', $this->page_containing_plugin, 'wiki page', $link_target_page);
        $this->assertFalse(
            $relation_id,
            "Before creating a page that contains a TranslationOf plugin, there should NOT have been a 'translationof' relation from $this->page_containing_plugin to $link_target_page."
        );

        $page_containing_plugin_content = "{TranslationOf(orig_page=\"$link_source_page\" translation_page=\"$link_target_page\") /}";
        $prefs['wikiplugin_translationof'] = 'y';
        $prefs['feature_multilingual'] = 'y';

        $tikilib->create_page($this->page_containing_plugin, 0, $page_containing_plugin_content, time(), "");

        $relation_id = $relationlib->get_relation_id('tiki.wiki.translationof', 'wiki page', $this->page_containing_plugin, 'wiki page', $link_target_page);
        $this->assertNotNull(
            $relation_id,
            "After we created a page that contains a TranslationOf plugin, there SHOULD have been a 'translationof' relation from $this->page_containing_plugin to $link_target_page."
        );
    }
}
