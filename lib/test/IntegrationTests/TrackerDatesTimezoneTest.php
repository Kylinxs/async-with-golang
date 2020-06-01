<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$
/**
 * @group integration
 * The purpose of these tests is to ensure consistency between storage, display
 * and filtering of tracker date/time fields with different timezone settings.
 */
class TrackerDatesTimezoneTest extends TikiTestCase
{
    protected static $trklib;
    protected static $unifiedlib;
    protected static $trackerId;
    protected static $old_prefs;
    protected static $old_tz;
    protected static $ist = 'Asia/Kolkata'; // GMT+5:30
    protected static $est = 'America/New_York'; // GMT-5:00/GMT-4:00

    public static function setUpBeforeClass(): void
    {
        global $prefs;
        self::$old_prefs = $prefs;
        self::$old_tz = date_default_timezone_get();
        $prefs['feature_trackers'] = 'y';
        $prefs['short_date_format'] = '%Y-%m-%d';
        $prefs['short_time_format'] = '%H:%M';

        parent::setUpBeforeClass();
        self::$trklib = TikiLib::lib('trk');

        // create tracker and couple of fields
        self::$trackerId = self::$trklib->replace_tracker(null, 'Test Tracker', '', [], 'n');

        $fields = [[
            'name' => 'Date (legacy)',
            'type' => 'f',
            'isHidden' => 'n',
            'isMandatory' => 'n',
            'permName' => 'test_date_legacy',
            'options' => json_encode(['datetime' => 'd']),
        ], [
            'name' => 'DateTime (legacy)',
            'type' => 'f',
            'isHidden' => 'n',
            'isMandatory' => 'n',
            'permName' => 'test_datetime_legacy',
            'options' => json_encode(['datetime' => 'dt']),
        ], [
            'name' => 'Date',
            'type' => 'j',
            'isHidden' => 'n',
            'isMandatory' => 'n',
            'permName' => 'test_date',
            'options' => json_encode(['datetime' => 'd']),
        ], [
            'name' => 'DateTime',
            'type' => 'j',
            'isHidden' => 'n',
            'isMandatory' => 'n',
            'permName' => 'test_datetime',
            'options' => json_encode(['datetime' => 'dt']),
        ]];
        foreach ($fields as $i => $field) {
            self::$trklib->replace_tracker_field(
                self::$trackerId,
                0,
                $field['name'],
                $field['type'],
                'y',
                'y',
                'y',
                'y',
                $field['isHidden'],
                $field['isMandatory'],
                ($i + 1) * 10,
                $field['options'] ?? '',
                '',
                '',
                null,
                '',
                null,
                null,
                'n',
                '',
                '',
                '',
                $field['permName']
            );
        }

        TikiDb::get()->query("REPLACE INTO `users_grouppermissions` VALUES('Registered', 'tiki_p_admin_trackers', '')");
        TikiDb::get()->query("REPLACE INTO `users_grouppermissions` VALUES('Registered', 'tiki_p_view_trackers', '')");
        $builder = new Perms_Builder();
        Perms::set($builder->build());

        // impersonate a regitered user
        new Perms_Context('someone');
        $perms = Perms::getInstance();
        $perms->setGroups(['Registered']);

        self::$unifiedlib = TikiLib::lib('unifiedsearch');
        self::$unifiedlib->rebuild();
    }

    public static function tearDownAfterClass(): void
    {
        global $prefs, $tikilib;

        if (! empty($prefs['unified_mysql_index_current'])) {
            TikiDb::get()->query("DROP TABLE `{$prefs['unified_mysql_index_current']}`");
            $tikilib->delete_preference('unified_mysql_index_current');
        }

        self::$unifiedlib->invalidateIndicesCache();

        $prefs = self::$old_prefs;
        date_default_timezone_set(self::$old_tz);

        parent::tearDownAfterClass();
        self::$trklib->remove_tracker(self::$trackerId);

        TikiDb::get()->query("DELETE FROM `users_grouppermissions` WHERE `groupName` = 'Registered' AND `permName` = 'tiki_p_admin_trackers'");
        TikiDb::get()->query("DELETE FROM `users_grouppermissions` WHERE `groupName` = 'Registered' AND `permName` = 'tiki_p_view_trackers'");
        $builder = new Perms_Builder();
        Perms::set($builder->build());
    }

    public function testTimeStorageInUTC(): void
    {
        global $prefs;

        date_default_timezone_set('UTC');
        $prefs['display_timezone'] = 'UTC';

        $this->executeTest([
            'test_date_legacy' => '2021-06-01',
            'test_datetime_legacy' => '2021-06-01 10:00:00',
            'test_date' => strtotime('2021-06-01'),
            'test_datetime' => strtotime('2021-06-01 10:00:00'),
        ], [
            'test_date_legacy' => '2021-06-01',
            'test_datetime_legacy' => '2021-06-01 10:00',
            'test_date' => '2021-06-01',
            'test_datetime' => '2021-06-01 10:00',
        ], [
            'test_date_legacy' => '2021-06-01',
            'test_datetime_legacy' => '2021-06-01 10:00:00',
            'test_date' => '2021-06-01',
            'test_datetime' => '2021-06-01 10:00:00',
        ]);
    }

    public function testTimeStorageServerNonUTC(): void
    {
        global $prefs;

        date_default_timezone_set('UTC');
        $d = strtotime('2021-06-01');
        $dt = strtotime('2021-06-01 10:00:00');

        date_default_timezone_set(self::$ist);
        $prefs['display_timezone'] = 'UTC';

        $this->executeTest([
            'test_date_legacy' => '2021-06-01',
            'test_datetime_legacy' => '2021-06-01 10:00:00',
            'test_date' => $d,
            'test_datetime' => $dt,
        ], [
            'test_date_legacy' => '2021-06-01',
            'test_datetime_legacy' => '2021-06-01 10:00',
            'test_date' => '2021-06-01',
            'test_datetime' => '2021-06-01 10:00',
        ], [
            'test_date_legacy' => '2021-06-01',
            'test_datetime_legacy' => '2021-06-01 10:00:00',
            'test_date' => '2021-06-01',
            'test_datetime' => '2021-06-01 10:00:00',
        ]);
    }

    public function testTimeStorageServerTikiSameNonUTC(): void
    {
        global $prefs;

        date_default_timezone_set('UTC');
        $d = strtotime('2021-06-01');
        $dt = strtotime('2021-06-01 10:00:00');

        date_default_timezone_set(self::$ist);
        $prefs['display_timezone'] = self::$ist;

        $this->executeTest([
            'test_date_legacy' => '2021-06-01',
            'test_datetime_legacy' => '2021-06-01 10:00:00',
            'test_date' => $d,
            'test_datetime' => $dt,
        ], [
            'test_date_legacy' => '2021-06-01',
            'test_datetime_legacy' => '2021-06-01 10:00',
            'test_date' => '2021-06-01',
            'test_datetime' => '2021-06-01 10:00',
        ], [
            'test_date_legacy' => '2021-06-01',
            'test_datetime_legacy' => '2021-06-01 04:30:00', // index stores in GMT
            'test_date' => '2021-06-01',
            'test_datetime' => '2021-06-01 04:30:00', // index stores in GMT
        ]);
    }

    public function testTimeStorageServerTikiDiffNonUTC(): void
    {
        global $prefs;

        date_default_timezone_set('UTC');
        $d = strtotime('2021-06-01');
        $dt = strtotime('2021-06-01 10:00:00');

        date_default_timezone_set(self::$ist);
        $prefs['display_timezone'] = self::$est;

        $this->executeTest([
            'test_date_legacy' => '2021-06-01',
            'test_datetime_legacy' => '2021-06-01 10:00:00',
            'test_date' => $d,
            'test_datetime' => $dt,
        ], [
            'test_date_legacy' => '2021-06-01',
            'test_datetime_legacy' => '2021-06-01 10:00',
            'test_date' => '2021-06-01',
            'test_datetime' => '2021-06-01 10:00',
        ], [
            'test_date_legacy' => '2021-06-01',
            'test_datetime_legacy' => '2021-06-01 14:00:00', // index stores in GMT
            'test_date' => '2021-06-01',
            'test_datetime' => '2021-06-01 14:00:00', // index stores in GMT
        ]);
    }

    public function testTimeStorageInUTCWithBrowserOffset(): void
    {
        global $prefs;

        date_default_timezone_set('UTC');
        $prefs['display_timezone'] = 'UTC';

        $this->executeTest(
            [
                'test_date_legacy' => '2021-06-01',
                'test_datetime_legacy' => '2021-06-01 10:00:00',
                'test_date' => strtotime('2021-06-01') - 180 * 60,
                'test_datetime' => strtotime('2021-06-01 10:00:00') - 180 * 60,
            ],
            [
                'test_date_legacy' => '2021-06-01',
                'test_datetime_legacy' => '2021-06-01 10:00',
                'test_date' => '2021-06-01',
                'test_datetime' => '2021-06-01 10:00',
            ],
            [
                'test_date_legacy' => '2021-06-01',
                'test_datetime_legacy' => '2021-06-01 10:00:00',
                'test_date' => '2021-06-01',
                'test_datetime' => '2021-06-01 10:00:00',
            ],
            -180
        );
    }

    public function testTimeStorageServerTikiDiffNonUTCWithBrowserOffset(): void
    {
        global $prefs;

        date_default_timezone_set('UTC');
        $d = strtotime('2021-06-01');
        $dt = strtotime('2021-06-01 10:00:00');

        date_default_timezone_set(self::$ist);
        $prefs['display_timezone'] = self::$est;

        $this->executeTest([
            'test_date_legacy' => '2021-06-01',
            'test_datetime_legacy' => '2021-06-01 10:00:00',
            'test_date' => $d - 180 * 60,
            'test_datetime' => $dt - 180 * 60,
        ], [
            'test_date_legacy' => '2021-06-01',
            'test_datetime_legacy' => '2021-06-01 10:00',
            'test_date' => '2021-06-01',
            'test_datetime' => '2021-06-01 10:00',
        ], [
            'test_date_legacy' => '2021-06-01',
            'test_datetime_legacy' => '2021-06-01 14:00:00', // index stores in GMT
            'test_date' => '2021-06-01',
            'test_datetime' => '2021-06-01 14:00:00', // index stores in GMT
        ], -180);
    }

    public function testStoreRetrieveDiffTimezonesDB(): void
    {
        global $prefs;

        date_default_timezone_set('UTC');
        $d = strtotime('2021-06-01');
        $dt = strtotime('2021-06-01 10:00:00');

        date_default_timezone_set(self::$est);
        $prefs['display_timezone'] = self::$ist;

        $itemId = $this->createItem([
            'test_date_legacy' => '2021-06-01',
            'test_datetime_legacy' => '2021-06-01 10:00:00',
            'test_date' => $d - 180 * 60,
            'test_datetime' => $dt - 180 * 60,
        ], -180);

        date_default_timezone_set('UTC');
        $prefs['display_timezone'] = self::$est;

        // store in IST, display in EST -> time should shift by 9:30 but date should stay the same
        $values = $this->getItemValues($itemId);
        $this->assertEquals('2021-06-01', $values['test_date_legacy']);
        $this->assertEquals('2021-06-01 00:30', $values['test_datetime_legacy']);
        $this->assertEquals('2021-06-01', $values['test_date']);
        $this->assertEquals('2021-06-01 00:30', $values['test_datetime']);
    }

    public function testStoreRetrieveDiffTimezonesIndex(): void
    {
        global $prefs;

        date_default_timezone_set('UTC');
        $d = strtotime('2021-06-01');
        $dt = strtotime('2021-06-01 10:00:00');

        date_default_timezone_set(self::$ist);
        $prefs['display_timezone'] = self::$ist;

        $itemId = $this->createItem([
            'test_date_legacy' => '2021-06-01',
            'test_datetime_legacy' => '2021-06-01 10:00:00',
            'test_date' => $d - 180 * 60,
            'test_datetime' => $dt - 180 * 60,
        ], -180);

        require_once('lib/search/refresh-functions.php');
        refresh_index('trackeritem', $itemId);

        date_default_timezone_set('UTC');
        $prefs['display_timezone'] = self::$est;

        $query = self::$unifiedlib->buildQuery([
            'type' => 'trackeritem',
            'object_id' => $itemId,
        ]);
        $result = $query->search(self::$unifiedlib->getIndex());
        $resultArray = $result->getArrayCopy();

        $this->assertEquals('2021-06-01', $resultArray[0]['tracker_field_test_date_legacy']);
        $this->assertEquals('2021-06-01 04:30:00', $resultArray[0]['tracker_field_test_datetime_legacy']); // index stores in GMT
        $this->assertEquals('2021-06-01', $resultArray[0]['tracker_field_test_date']);
        $this->assertEquals('2021-06-01 04:30:00', $resultArray[0]['tracker_field_test_datetime']); // index stores in GMT
    }

    public function testExistingDataInDiffTimezone(): void
    {
        global $prefs;

        date_default_timezone_set(self::$ist);
        $d = strtotime('2021-06-01');
        $dt = strtotime('2021-06-01 10:00:00');

        date_default_timezone_set('UTC');
        $prefs['display_timezone'] = self::$est;

        $itemId = $this->createItem([
            'test_date_legacy' => '2021-06-01',
            'test_datetime_legacy' => '2021-06-01 10:00:00',
            'test_date' => $d - 180 * 60,
            'test_datetime' => $dt - 180 * 60,
        ], -180);

        $definition = Tracker_Definition::get(self::$trackerId);
        $fields = $definition->getFields();
        self::$trklib->modify_field($itemId, $fields[0]['fieldId'], $d);
        self::$trklib->modify_field($itemId, $fields[1]['fieldId'], $dt);
        self::$trklib->modify_field($itemI