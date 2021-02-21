
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Reports_Send_EmailBuilderTest extends TikiTestCase
{
    protected $obj;

    protected $tikilib;

    protected function setUp(): void
    {
        $this->tikilib = $this->getMockBuilder('TikiLib')->getMock();
        $this->factory = $this->createMock('Reports_Send_EmailBuilder_Factory');

        $this->obj = new Reports_Send_EmailBuilder($this->tikilib, new Reports_Send_EmailBuilder_Factory());

        $this->defaultReportPreferences = ['type' => 'plain', 'view' => 'detailed'];
    }

    public function testMakeEmailBodyShouldReturnStringIfNothingHappened()
    {
        $this->assertEquals('Nothing has happened.', $this->obj->makeEmailBody([], $this->defaultReportPreferences));
    }

    public function testMakeEmailBodyShouldReturnCalendarChangedReportInDetailedViewMode()
    {
        $this->tikilib->expects($this->exactly(2))->method('get_short_datetime')
            ->willReturn('2011-09-13 11:19');

        $calendarlib = $this->createMock(get_class(TikiLib::lib('calendar')));
        $calendarlib->expects($this->exactly(2))
            ->method('get_item')
            ->willReturn(['name' => 'Calendar item name']);

        $tikilib = new TestableTikiLib();
        $tikilib->overrideLibs(['calendar' => $calendarlib]);

        $this->defaultReportPreferences['view'] = 'detailed';

        $reportCache = [
            [
                'user' => 'admin',
                'event' => 'calendar_changed',
                'data' => ['event' => 'calendar_changed', 'calitemId' => '2', 'user' => 'admin', 'base_url' => 'http://example.com'],
                'time' => '2011-09-12 20:30:31',
            ],
            [
                'user' => 'admin',
                'event' => 'calendar_changed',
                'data' => ['event' => 'calendar_changed', 'calitemId' => '1', 'user' => 'admin', 'base_url' => 'http://example.com'],
                'time' => '2011-09-13 11:19:31',
            ],
        ];

        $output = $this->obj->makeEmailBody($reportCache, $this->defaultReportPreferences);

        $this->assertStringContainsString('2011-09-13 11:19: admin added or updated event Calendar item name', $output);
    }

    public function testMakeEmailBodyShouldReturnTrackerItemCommentReportInDetailedViewMode()
    {
        $this->tikilib->expects($this->once())->method('get_short_datetime')
            ->willReturn('2011-09-12 20:30');

        $trklib = $this->createMock(get_class(TikiLib::lib('trk')));
        $trklib->expects($this->once())
            ->method('get_tracker')
            ->willReturn(['id' => '2', 'name' => 'Test Tracker']);
        $trklib->expects($this->once())
            ->method('get_isMain_value')
            ->willReturn('Tracker item name');

        $tikilib = new TestableTikiLib();
        $tikilib->overrideLibs(['trk' => $trklib]);

        $this->defaultReportPreferences['view'] = 'detailed';

        $reportCache = [
            [
                'user' => 'admin',
                'event' => 'tracker_item_comment',
                'data' => ['event' => 'tracker_item_comment', 'trackerId' => '2', 'itemId' => '4', 'threadId' => '13', 'user' => 'admin', 'base_url' => 'http://example.com'],
                'time' => '2011-09-12 20:30:31',
            ],
        ];

        $output = $this->obj->makeEmailBody($reportCache, $this->defaultReportPreferences);

        $this->assertStringContainsString('2011-09-12 20:30: admin added a new comment to Tracker item name', $output);
    }

    public function testMakeEmailBodyShouldUseCategoryChangedObject()
    {
        $obj = new Reports_Send_EmailBuilder($this->tikilib, $this->factory);

        $reportCache = [
            [
                'user' => 'admin',
                'categoryId' => 1,
                'event' => 'category_changed',
                'data' => ['action' => 'object entered category', 'user' => 'admin', 'objectType' => '', 'objectUrl' => '', 'objectName' => '', 'categoryId' => '', 'categoryName' => ''],
                'time' => '2011-09-12 20:30:31',
            ],
        ];

        $categoryChanged = $this->createMock('Reports_Send_EmailBuilder_CategoryChanged');
        $categoryChanged->expects($this->once())->method('getTitle');
        $categoryChanged->expects($this->once())->method('getOutput');

        $this->factory->expects($this->once())->method('build')
            ->with('category_changed')->willReturn($categoryChanged);

        $obj->makeEmailBody($reportCache, $this->defaultReportPreferences);
    }
}