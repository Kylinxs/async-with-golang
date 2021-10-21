<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Test\Lib\Perspective;

use Tiki\Test\TestHelpers\TikiDbHelper;
use Tiki\Test\TestHelpers\TikiProfileHelper;
use Tiki\Test\TestHelpers\WebClientHelper;

/**
 * @group RequiresWebServer
 */
class AreasMultiDomainBasicTest extends PerspectivesNavigationBaseTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        TikiDbHelper::refreshDb();
        $folder = TikiProfileHelper::createTemporaryDomainFromTemplate(
            __DIR__ . '/fixtures',
            'testAreasMultiDomainBasic',
            [self::FIXTURE_HOST, self::FIXTURE_SITE],
            [getenv('TIKI_TEST_HOST'), getenv('TIKI_TEST_HOST_A')]
        );
        TikiProfileHelper::applyProfile($folder, 'testAreasMultiDomainBasic');
    }

    /**
     * @dataProvider navigations
     * @param $testName
     * @param $cleanCookies
     * @param $steps
     */
    public function testAreasMultiDomainBasic($testName, $cleanCookies, $steps)
    {
        $this->navigateSteps($steps, $cleanCookies);
    }

    public function navigations()
    {
        $host = getenv('TIKI_TEST_HOST');
        $site = getenv('TIKI_TEST_HOST_A');

        // $testName, $cleanCookies
        // $url, $httpCode, $location, $perspective
        return [
            ['01 load homepage without cookies', true, [
                ['http://' . $host . '/tiki-index.php', 200, null, '!perspective'],
            ]],
            ['02 load homepage with cookies', false, [
                ['http://' . $host . '/tiki-index.php', 200, null, '!perspective'],
            ]],
            ['03 load "test info 1" page opens in perspective info', true, [
                ['http://' . $host . '/tiki-index.php?page=test-info-1', 200, null , 'perspective_info'],
            ]],
            ['04 load "test site 1" page loads in other vhost and perspective site', true, [
                ['http://' . $host . '/tiki-index.php?page=test-site-1', 301, 'http://' . $site . '/tiki-index.php?page=test-site-1', null],
                ['follow-redirect', 200, null, 'perspective_site'],
                ['http://' . $site . '/tiki-index.php?page=test-site-1', 200, null, 'perspective_site'],
            ]],
            ['05 load "test site 1" page loads in other vhost and perspective site, with cookies', false, [
                ['http://' . $host . '/tiki-index.php?page=test-site-1', 301, 'http://' . $site . '/tiki-index.php?page=test-site-1', null],
                ['follow-redirect', 200, null, 'perspective_site'],
                ['http://' . $site . '/tiki-index.php?page=test-site-1', 200, null, 'perspective_site'],
            ]],
            ['06 load "test info 1" in the site vhost, with cookies', false, [
                ['http://' . $site . '/tiki-index.php?page=test-info-1', 301, 'http://' . $host . '/tiki-index.php?page=test-info-1', null],
                ['follow-redirect', 200, null, 'perspective_info'],
                ['http://' . $host . '/tiki-index.php?page=test-info-1', 200, null, 'perspective_info'],
            ]],
        ];
    }
}
