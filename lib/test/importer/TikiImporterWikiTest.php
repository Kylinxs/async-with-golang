
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

require_once(__DIR__ . '/tikiimporter_testcase.php');
require_once(__DIR__ . '/../../importer/tikiimporter_wiki.php');
require_once(__DIR__ . '/../../importer/tikiimporter_wiki_mediawiki.php');

/**
 * @group importer
 */
class TikiImporter_Wiki_Test extends TikiImporter_TestCase
{
    public function testImportShouldCallMethodsToStartImportProcess(): void
    {
        ob_start();
        $obj = $this->getMockBuilder('TikiImporter_Wiki')
           ->onlyMethods(['validateInput', 'parseData', 'insertData'])
           ->getMock();
        $obj->expects($this->once())->method('validateInput');
        $obj->expects($this->once())->method('parseData');
        $obj->expects($this->once())->method('insertData');

        $obj->import();

        $output = ob_get_clean();
        $this->assertEquals("\nImportation completed!\n\n<b><a href=\"tiki-importer.php\">Click here</a> to finish the import process</b>", $output);
    }

    public function testImportShouldSetInstanceProperties(): void
    {
        ob_start();

        $obj = $this->getMockBuilder('TikiImporter_Wiki')
           ->onlyMethods(['validateInput', 'parseData', 'insertData'])
           ->getMock();
        $_POST['alreadyExistentPageName'] = 'override';
        $_POST['wikiRevisions'] = 100;

        $obj->import();

        $this->assertEquals(100, $obj->revisionsNumber);
        $this->assertEquals('override', $obj->alreadyExistentPageName);

        unset($_POST['alreadyExistentPageName'], $_POST['wikiRevisions']);
        $obj->import();

        $this->assertEquals(0, $obj->revisionsNumber);
        $this->assertEquals('doNotImport', $obj->alreadyExistentPageName);

        ob_get_clean();
    }

    public function testImportShouldSetSessionVariables(): void
    {
        ob_start();

        $expectedImportFeedback = ['importedPages' => 10, 'totalPages' => '13'];
        $obj = $this->getMockBuilder('TikiImporter_Wiki')
           ->onlyMethods(['validateInput', 'parseData', 'insertData', 'saveAndDisplayLog'])
           ->getMock();
        $obj->expects($this->once())->method('validateInput');
        $obj->expects($this->once())->method('parseData');
        $obj->expects($this->once())->method('insertData')->willReturn($expectedImportFeedback);
        $obj->expects($this->once())->method('saveAndDisplayLog');

        $obj->log = 'some log string';
        $obj->import();

        $this->assertEquals($expectedImportFeedback, $_SESSION['tiki_importer_feedback']);
        $this->assertEquals('some log string', $_SESSION['tiki_importer_log']);

        ob_get_clean();
    }

    public function testInsertDataCallInsertPageFourTimes(): void
    {
        ob_start();

        $obj = $this->getMockBuilder('TikiImporter_Wiki')
           ->onlyMethods(['insertPage'])
           ->getMock();
        $obj->expects($this->exactly(4))->method('insertPage');
        $parsedData = [['name' => '1'],['name' => '2'],['name' => '3'],['name' => '4'],];
        $obj->insertData($parsedData);

        ob_get_clean();
    }

    public function testInsertDataCallInsertPageOnceWithProperParam(): void
    {
        ob_start();

        $obj = $this->getMockBuilder('TikiImporter_Wiki')
           ->onlyMethods(['insertPage'])
           ->getMock();
        $obj->expects($this->once())->method('insertPage')->with(['name' => '1']);
        $parsedData = [['name' => '1'],];
        $obj->insertData($parsedData);

        ob_get_clean();
    }

    public function testInsertDataShouldNotCallInsertPage(): void
    {
        ob_start();

        $obj = $this->getMockBuilder('TikiImporter_Wiki')
           ->onlyMethods(['insertPage'])
           ->getMock();
        $obj->expects($this->never())->method('insertPage');
        $parsedData = [];
        $obj->insertData($parsedData);

        ob_get_clean();
    }

    public function testInsertDataShouldReturnCountData(): void
    {
        ob_start();

        $obj = $this->getMockBuilder('TikiImporter_Wiki')
           ->onlyMethods(['insertPage'])
           ->getMock();
        $obj->expects($this->exactly(6))->method('insertPage')->willReturnOnConsecutiveCalls(true, true, false, true, false, true);

        $parsedData = [
            ['name' => 'Page1'],
            ['name' => 'Page2'],
            ['name' => 'Page3'],
            ['name' => 'Page4'],
            ['name' => 'Page5'],
            ['name' => 'Page6'],
        ];
        $countData = $obj->insertData($parsedData);
        $expectedResult = ['totalPages' => 6, 'importedPages' => 4];

        $this->assertEquals($expectedResult, $countData);

        ob_get_clean();
    }
}

class TikiImporter_Wiki_InsertPage_Test extends TikiImporter_TestCase
{
    protected function setUp(): void
    {
        require_once(__DIR__ . '/fixtures/mediawiki_page_as_array.php');
        global $tikilib;
        $tikilib = $this->getMockBuilder('TikiLib')
           ->onlyMethods(['create_page', 'update_page', 'page_exists', 'remove_all_versions'])
           ->getMock();
        $this->obj = new TikiImporter_Wiki_Mediawiki();
        $this->obj->revisionsNumber = 0;
    }

    public function testInsertPage(): void
    {
        global $tikilib, $page;

        $tikilib->expects($this->once())->method('page_exists')->with($page['name'])->willReturn(false);
        $tikilib->expects($this->once())->method('create_page')->with($page['name'], 0, $page['revisions'][0]['data'], $page['revisions'][0]['lastModif'], $page['revisions'][0]['comment'], $page['revisions'][0]['user'], $page['revisions'][0]['ip']);
       // TODO: how to test parameters for update_page for the 7 different calls
        $tikilib->expects($this->exactly(7))->method('update_page');

       // $page is set on mediawiki_page_as_array.php
        $this->assertEquals('Redes de ensino', $this->obj->insertPage($page));
    }

    public function testInsertPageAlreadyExistentPageNameOverride(): void
    {
        global $tikilib, $page;
        $tikilib->expects($this->once())->method('page_exists')->with($page['name'])->willReturn(true);
        $tikilib->expects($this->once())->method('remove_all_versions')->with($page['name']);
        $tikilib->expects($this->once())->method('create_page');
        $tikilib->expects($this->exactly(7))->method('update_page');

        $this->obj->alreadyExistentPageName = 'override';
        $this->assertEquals('Redes de ensino', $this->obj->insertPage($page));
    }

    public function testInsertPageAlreadyExistentPageNameAppendPrefix(): void
    {
        global $tikilib, $page;

        $newPageName = $this->obj->softwareName . '_' . $page['name'];

        $tikilib->expects($this->once())->method('page_exists')->with($page['name'])->willReturn(true);
        $tikilib->expects($this->once())->method('create_page')->with($newPageName);
        $tikilib->expects($this->exactly(7))->method('update_page')->with($newPageName);

        $this->obj->alreadyExistentPageName = 'appendPrefix';
        $this->assertEquals('Mediawiki_Redes de ensino', $this->obj->insertPage($page));
    }

    public function testInsertPageAlreadyExistentPageNameDoNotImport(): void
    {
        global $tikilib, $page;

        $tikilib->expects($this->once())->method('page_exists')->with($page['name'])->willReturn(true);
        $tikilib->expects($this->never())->method('create_page');
        $tikilib->expects($this->never())->method('update_page');

        $this->obj->alreadyExistentPageName = 'doNotImport';
        $this->assertFalse($this->obj->insertPage($page));
    }
}