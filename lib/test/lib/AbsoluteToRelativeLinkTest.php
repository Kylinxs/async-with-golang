
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use TikiLib;

class AbsoluteToRelativeLinkTest extends TestCase
{
    public const BASE_URL = 'https://tiki.org/';
    public const BASE_URL_HTTP = 'http://tiki.org/';

    public const DEMO_TEXT = 'Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh,' .
    ' ut fermentum massa justo sit amet risus ##### Fermentum Fringilla Dapibus.';

    protected function setUp(): void
    {
        global $base_url, $base_url_http, $base_url_https, $prefs, $page_regex;

        $base_url = self::BASE_URL;
        $base_url_http = self::BASE_URL_HTTP;
        $base_url_https = self::BASE_URL;
        $prefs['feature_absolute_to_relative_links'] = 'y';
        $page_regex = '([^\n|\(\)])((?!(\)\)|\||\n)).)*?';
    }

    /**
     * @dataProvider urlBases
     * @param $baseUrl
     * @throws Exception
     */
    public function testPluginHtml($baseUrl): void
    {
        $tikilib = TikiLib::lib('tiki');
        $data = '{HTML()}<span class="button"><a href="' . $baseUrl . 'tiki-index.php">Save changes</a></span> with my custom buttom to the page PluginHTML';
        $data .= '<a href="' . $baseUrl . 'tiki-index.php">' . $baseUrl . 'tiki-index.php</a>';
        $data .= $baseUrl . 'tiki-index.php';
        $data .= '{HTML}';

        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);
        $this->assertEquals($data, $dataConverted);
    }

    /**
     * @dataProvider urlBases
     * @param $baseUrl
     * @throws Exception
     */
    public function testTextLink($baseUrl): void
    {
        $tikilib = TikiLib::lib('tiki');
        $link = $baseUrl . 'tiki-index.php';
        $data = str_replace('#####', $link, self::DEMO_TEXT);
        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);
        $expectedLink = '[tiki-index.php|tiki-index.php]';
        $dataResult = str_replace('#####', $expectedLink, self::DEMO_TEXT);
        $this->assertEquals($dataResult, $dataConverted);
    }

    /**
     * @dataProvider urlBases
     * @param $baseUrl
     * @throws Exception
     */
    public function testTextLinkSubFolder($baseUrl): void
    {
        global $base_url, $base_url_http, $base_url_https;
        $base_url .= "xxxx/";
        $base_url_http .= "xxxx/";
        $base_url_https .= "xxxx/";
        $baseUrl .= "xxxx/";

        $this->testPluginHtml($baseUrl);
        $this->testTextLink($baseUrl);
        $this->testWikiLink($baseUrl);
        $this->testExternalLink($baseUrl);
        $this->testReplaceInsidePlugins($baseUrl);
        $this->testOtherMarkups($baseUrl);
        $this->testMixMultipleLinks($baseUrl);
        $this->testUnescapedLinks($baseUrl);
        $this->testUnescapedLinksReplacement($baseUrl);
    }

    /**
     * @dataProvider urlBases
     * @param $baseUrl
     * @throws Exception
     */
    public function testWikiLink($baseUrl): void
    {
        $tikilib = TikiLib::lib('tiki');
        $link = '((' . $baseUrl . 'tiki-index.php|Homepage))';
        $data = str_replace('#####', $link, self::DEMO_TEXT);
        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);
        $expectedLink = '((tiki-index.php|Homepage))';
        $dataResult = str_replace('#####', $expectedLink, self::DEMO_TEXT);
        $this->assertEquals($dataResult, $dataConverted);

        $link = '((' . $baseUrl . '|Homepage))';
        $data = str_replace('#####', $link, self::DEMO_TEXT);
        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);
        $expectedLink = '((Homepage|Homepage))';
        $dataResult = str_replace('#####', $expectedLink, self::DEMO_TEXT);
        $this->assertEquals($dataResult, $dataConverted);

        $link = '((' . rtrim($baseUrl, '/') . '|Homepage))';
        $data = str_replace('#####', $link, self::DEMO_TEXT);
        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);
        $expectedLink = '((Homepage|Homepage))';
        $dataResult = str_replace('#####', $expectedLink, self::DEMO_TEXT);
        $this->assertEquals($dataResult, $dataConverted);

        $link = '((' . $baseUrl . 'tiki-index.php))';
        $data = str_replace('#####', $link, self::DEMO_TEXT);
        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);
        $expectedLink = '((tiki-index.php))';
        $dataResult = str_replace('#####', $expectedLink, self::DEMO_TEXT);
        $this->assertEquals($dataResult, $dataConverted);

        $link = '((' . $baseUrl . '))';
        $data = str_replace('#####', $link, self::DEMO_TEXT);
        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);
        $expectedLink = '((Homepage))';
        $dataResult = str_replace('#####', $expectedLink, self::DEMO_TEXT);
        $this->assertEquals($dataResult, $dataConverted);

        $link = '((' . rtrim($baseUrl, '/') . '))';
        $data = str_replace('#####', $link, self::DEMO_TEXT);
        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);
        $expectedLink = '((Homepage))';
        $dataResult = str_replace('#####', $expectedLink, self::DEMO_TEXT);
        $this->assertEquals($dataResult, $dataConverted);

        $link = '((' . rtrim($baseUrl, '/') . '|Homepage))';
        $data = str_replace('#####', $link, self::DEMO_TEXT);
        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);
        $expectedLink = '((Homepage|Homepage))';
        $dataResult = str_replace('#####', $expectedLink, self::DEMO_TEXT);
        $this->assertEquals($dataResult, $dataConverted);

        $link = '((' . $baseUrl . 'tiki-index.php?page=Dummy|Dummy))';
        $data = str_replace('#####', $link, self::DEMO_TEXT);
        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);
        $expectedLink = '((tiki-index.php?page=Dummy|Dummy))';
        $dataResult = str_replace('#####', $expectedLink, self::DEMO_TEXT);
        $this->assertEquals($dataResult, $dataConverted);

        $link = '((' . $baseUrl . 'tiki-index.php?page=Dummy))';
        $data = str_replace('#####', $link, self::DEMO_TEXT);
        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);
        $expectedLink = '((tiki-index.php?page=Dummy))';
        $dataResult = str_replace('#####', $expectedLink, self::DEMO_TEXT);
        $this->assertEquals($dataResult, $dataConverted);
    }

    /**
     * @dataProvider urlBases
     * @param $baseUrl
     * @throws Exception
     */
    public function testExternalLink($baseUrl): void
    {
        $tikilib = TikiLib::lib('tiki');
        $link = '[https://doc.tiki.org/Documentation|Tiki Documentation]';
        $data = str_replace('#####', $link, self::DEMO_TEXT);
        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);
        $this->assertEquals($data, $dataConverted);

        $link = '[' . $baseUrl . 'Documentation|Tiki Documentation]';
        $data = str_replace('#####', $link, self::DEMO_TEXT);
        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);
        $expectedLink = '[Documentation|Tiki Documentation]';
        $dataResult = str_replace('#####', $expectedLink, self::DEMO_TEXT);
        $this->assertEquals($dataResult, $dataConverted);
    }

    /**
     * @dataProvider urlBases
     * @param $baseUrl
     * @throws Exception
     */
    public function testUnescapedLinks($baseUrl): void
    {
        $tikilib = TikiLib::lib('tiki');
        $link = "==$baseUrl/HomePage==";
        $data = str_replace('#####', $link, self::DEMO_TEXT);
        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);
        $this->assertEquals($data, $dataConverted);

        $link = "==$baseUrl/index.php?page=Homepage==";
        $data = str_replace('#####', $link, self::DEMO_TEXT);
        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);
        $this->assertEquals($data, $dataConverted);
    }

    /**
     * @dataProvider urlBases
     * @param $baseUrl
     * @throws Exception
     */
    public function testUnescapedLinksReplacement($baseUrl): void
    {
        $parserlib = TikiLib::lib('parser');
        $link = "$baseUrl/HomePage";
        $data = str_replace('#####', "==$link==", self::DEMO_TEXT);
        $parsedData = $parserlib->parse_data_simple($data);
        $expectedData = $parserlib->autolinks($link);
        $data = str_replace('#####', $expectedData, self::DEMO_TEXT);
        $this->assertEquals($data, $parsedData);

        $link = "$baseUrl/index.php?page=Homepage";
        $data = str_replace('#####', "==$link==", self::DEMO_TEXT);
        $parsedData = $parserlib->parse_data_simple($data);
        $expectedData = $parserlib->autolinks($link);
        $data = str_replace('#####', $expectedData, self::DEMO_TEXT);
        $this->assertEquals($data, $parsedData);
    }

    /**
     * @dataProvider urlBases
     * @param $baseUrl
     * @throws Exception
     */
    public function testPreferenceDisabled($baseUrl): void
    {
        global $prefs;
        $tikilib = TikiLib::lib('tiki');
        $prefs['feature_absolute_to_relative_links'] = 'n';
        $text = 'Nullam quis risus eget urna mollis ornare vel eu leo. #1' .
            ' Praesent commodo cursus magna, vel scelerisque nisl consectetur et. #2' .
            ' Aenean lacinia bibendum nulla sed consectetur. #3' .
            ' Dapibus Tellus Nibh Tortor Porta #4';

        $link1 = '[' . $baseUrl . 'tiki-index.php|' . $baseUrl . 'tiki-index.php]';
        $link2 = '[' . $baseUrl . 'tiki-pagehistory.php?page=sametitleandlink|' . $baseUrl . 'tiki-pagehistory.php?page=sametitleandlink]';
        $link3 = '((Homepage|Homepage))';
        $link4 = '[tiki-pagehistory.php?page=193&newver=12&oldver=11|tiki-pagehistory.php?page=193&newver=12&oldver=11]';

        $data = str_replace(
            ['#1', '#2', '#3', '#4'],
            [$link1, $link2, $link3, $link4],
            $text
        );

        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);

        $expectedLink1 = '[' . $baseUrl . 'tiki-index.php]';
        $expectedLink2 = '[' . $baseUrl . 'tiki-pagehistory.php?page=sametitleandlink]';
        $expectedLink3 = '((Homepage))';
        $expectedLink4 = '[tiki-pagehistory.php?page=193&newver=12&oldver=11]';
        $dataResult = str_replace(
            ['#1', '#2', '#3', '#4'],
            [$expectedLink1, $expectedLink2, $expectedLink3,
                  $expectedLink4],
            $text
        );

        $this->assertEquals($dataResult, $dataConverted);
    }

    /**
     * @dataProvider urlBases
     * @param $baseUrl
     * @throws Exception
     */
    public function testReplaceInsidePlugins($baseUrl): void
    {
        $tikilib = TikiLib::lib('tiki');
        $link = '{CODE()}' . $baseUrl . 'HomePage{CODE}';
        $data = str_replace('#####', $link, self::DEMO_TEXT);
        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);

        // Should not replace
        $this->assertEquals($data, $dataConverted);
    }

    /**
     * @dataProvider urlBases
     * @param $baseUrl
     * @throws Exception
     */
    public function testOtherMarkups($baseUrl): void
    {

        $tikilib = TikiLib::lib('tiki');
        $link = '-+' . $baseUrl . 'HomePage+-';
        $data = str_replace('#####', $link, self::DEMO_TEXT);
        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);

        // Should not replace
        $this->assertEquals($data, $dataConverted);

        $tikilib = TikiLib::lib('tiki');
        $link = '[' . $baseUrl . 'HomePage|Home Page]';
        $data = str_replace('#####', $link, self::DEMO_TEXT);
        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);

        $expectedLink = '[HomePage|Home Page]';
        $dataResult = str_replace('#####', $expectedLink, self::DEMO_TEXT);
        $this->assertEquals($dataResult, $dataConverted);

        $tikilib = TikiLib::lib('tiki');
        $link = '~np~' . $baseUrl . 'HomePage~/np~';
        $data = str_replace('#####', $link, self::DEMO_TEXT);
        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);

        // Should not replace
        $this->assertEquals($data, $dataConverted);

        $tikilib = TikiLib::lib('tiki');
        $link = '-+' . $baseUrl . 'HomePage+-';
        $data = str_replace('#####', $link, self::DEMO_TEXT);
        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);

        // Should not replace
        $this->assertEquals($data, $dataConverted);
        $tikilib = TikiLib::lib('tiki');
        $link = '~pp~' . $baseUrl . 'HomePage~/pp~';
        $data = str_replace('#####', $link, self::DEMO_TEXT);
        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);

        // Should not replace
        $this->assertEquals($data, $dataConverted);

        $tikilib = TikiLib::lib('tiki');
        $link = '~pre~' . $baseUrl . 'HomePage~/pre~';
        $data = str_replace('#####', $link, self::DEMO_TEXT);
        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);

        // Should not replace
        $this->assertEquals($data, $dataConverted);

        $tikilib = TikiLib::lib('tiki');
        $link = '-=' . $baseUrl . 'HomePage=-';
        $data = str_replace('#####', $link, self::DEMO_TEXT);
        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);

        // Should not replace
        $this->assertEquals($data, $dataConverted);

        $tikilib = TikiLib::lib('tiki');
        $link = '{webdocviewer url="' . $baseUrl . 'dl178" width="750" height="780"}';
        $data = str_replace('#####', $link, self::DEMO_TEXT);
        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);

        // Should not replace
        $this->assertEquals($data, $dataConverted);
    }

    /**
     * @dataProvider urlBases
     * @param $baseUrl
     * @throws Exception
     */
    public function testMixMultipleLinks($baseUrl): void
    {
        $tikilib = TikiLib::lib('tiki');
        $text = 'Nullam quis risus eget urna mollis ornare vel eu leo. #1' .
            ' Praesent commodo cursus magna, vel scelerisque nisl consectetur et. #2' .
            ' Aenean lacinia bibendum nulla sed consectetur. #3';

        $link1 = '((' . $baseUrl . 'tiki-index.php|Homepage))';
        $link2 = '[' . $baseUrl . 'tiki-index.php|Homepage]';
        $link3 = '[https://doc.tiki.org/Documentation]';
        $data = str_replace(
            ['#1', '#2', '#3'],
            [$link1, $link2, $link3],
            $text
        );
        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);

        $expectedLink1 = '((tiki-index.php|Homepage))';
        $expectedLink2 = '[tiki-index.php|Homepage]';
        $expectedLink3 = '[https://doc.tiki.org/Documentation]';
        $dataResult = str_replace(
            ['#1', '#2', '#3'],
            [$expectedLink1, $expectedLink2, $expectedLink3],
            $text
        );

        $this->assertEquals($dataResult, $dataConverted);
    }

    /**
     * @dataProvider urlBases
     * @param $baseUrl
     * @throws Exception
     */
    public function testLinkInsidePlugin($baseUrl): void
    {
        $tikilib = TikiLib::lib('tiki');
        $text = "{CODE()}\n* #1\n* #2\n* #3\n{CODE}";

        $link1 = '((' . $baseUrl . 'tiki-index.php|Homepage))';
        $link2 = '[' . $baseUrl . 'tiki-index.php|Homepage]';
        $link3 = '[https://doc.tiki.org/Documentation]';
        $data = str_replace(
            ['#1', '#2', '#3'],
            [$link1, $link2, $link3],
            $text
        );
        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);

        $this->assertEquals($data, $dataConverted);
    }

    /**
     * @dataProvider urlBases
     * @param $baseUrl
     * @throws Exception
     */
    public function testLinkOutsidePlugin($baseUrl): void
    {
        $tikilib = TikiLib::lib('tiki');
        $text = 'Nullam quis risus eget urna mollis ornare vel eu leo. #1' .
            ' Praesent commodo cursus magna, vel scelerisque nisl consectetur et.' .
            '~np~' . $baseUrl . 'example~/np~';

        $link1 = $baseUrl . 'tiki-index.php';

        $data = str_replace('#1', $link1, $text);

        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);

        $expectedLink1 = '[tiki-index.php|tiki-index.php]';

        $dataResult = str_replace('#1', $expectedLink1, $text);

        $this->assertEquals($dataResult, $dataConverted);
    }

    public function testInternalSameTitleAndLink(): void
    {
        $tikilib = TikiLib::lib('tiki');
        $text = 'Nullam quis risus eget urna mollis ornare vel eu leo. #1';

        $link1 = '((HomePage|HomePage))';
        $data = str_replace('#1', $link1, $text);

        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);

        $expectedLink1 = '((HomePage))';
        $dataResult = str_replace('#1', $expectedLink1, $text);

        $this->assertEquals($dataResult, $dataConverted);
    }

    /**
     * @dataProvider urlBases
     * @param $baseUrl
     * @throws Exception
     */
    public function testExternalSameTitleAndLink($baseUrl): void
    {
        $tikilib = TikiLib::lib('tiki');
        $text = 'Nullam quis risus eget urna mollis ornare vel eu leo. #1';

        $link1 = '[' . $baseUrl . 'tiki-pagehistory.php?page=sametitleandlink|' . $baseUrl . 'tiki-pagehistory.php?page=sametitleandlink]';
        $data = str_replace('#1', $link1, $text);

        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);

        $expectedLink1 = '[tiki-pagehistory.php?page=sametitleandlink]';
        $dataResult = str_replace('#1', $expectedLink1, $text);

        $this->assertEquals($dataResult, $dataConverted);
    }

    /**
     * @dataProvider urlBases
     * @param $baseUrl
     * @throws Exception
     */
    public function testMultipleSameTitleAndLinks($baseUrl): void
    {
        $tikilib = TikiLib::lib('tiki');
        $text = 'Nullam quis risus eget urna mollis ornare vel eu leo. #1' .
            ' Praesent commodo cursus magna, vel scelerisque #2 nisl consectetur et.';

        $link1 = '[' . $baseUrl . 'tiki-pagehistory.php?page=sametitleandlink|' . $baseUrl . 'tiki-pagehistory.php?page=sametitleandlink]';
        $link2 = '((HomePage|HomePage))';
        $data1 = str_replace('#1', $link1, $text);
        $data2 = str_replace('#2', $link2, $text);

        $dataConverted1 = $tikilib->convertAbsoluteLinksToRelative($data1);
        $dataConverted2 = $tikilib->convertAbsoluteLinksToRelative($data2);

        $expectedLink1 = '[tiki-pagehistory.php?page=sametitleandlink]';
        $expectedLink2 = '((HomePage))';
        $dataResult1 = str_replace('#1', $expectedLink1, $text);
        $dataResult2 = str_replace('#2', $expectedLink2, $text);

        $this->assertEquals($dataResult1, $dataConverted1);
        $this->assertEquals($dataResult2, $dataConverted2);
    }

    /**
     * @dataProvider urlBases
     * @param $baseUrl
     * @throws Exception
     */
    public function testWikiMarkerInUrlShouldBeIgnored($baseUrl): void
    {
        $tikilib = TikiLib::lib('tiki');
        $text = 'https://dev.tiki.org/Online+Publishing+House+-+Output+formats ' . PHP_EOL .
                '[#1]' . PHP_EOL .
                '-+#2+-';

        $link1 = '[' . $baseUrl . 'HomePage]';
        $data = str_replace('#1', $link1, $text);
        $data = str_replace('#2', $link1, $text);

        $dataConverted = $tikilib->convertAbsoluteLinksToRelative($data);

        $expectedLink1 = '[Homepage]';
        $dataResult = str_replace('#1', $expectedLink1, $text);
        $dataResult = str_replace('#2', $link1, $text);

        $this->assertEquals($dataResult, $dataConverted);
    }

    public function urlBases(): array
    {
        return [
            [self::BASE_URL],
            [self::BASE_URL_HTTP], // This tests tiki internal links with different schema protocol
        ];
    }
}