
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$
/**
 * @group integration
 */
class TrackerItemPermissionTest extends TikiTestCase
{
    protected static $trklib;
    protected static $unifiedlib;
    protected static $trackerId;
    protected static $old_pref;
    protected static $old_user;

    public static function setUpBeforeClass(): void
    {
        global $prefs;
        self::$old_pref = $prefs['feature_trackers'];
        $prefs['feature_trackers'] = 'y';

        parent::setUpBeforeClass();
        self::$trklib = TikiLib::lib('trk');

        // create tracker and couple of fields
        self::$trackerId = self::$trklib->replace_tracker(null, 'Test Tracker', '', [], 'n');

        $fields = [[
            'name' => 'Name',
            'type' => 't',
            'isHidden' => 'n',
            'isMandatory' => 'y',
            'visibleBy' => null,
            'permName' => 'test_name',
        ], [
            'name' => 'Admin info',
            'type' => 't',
            'isHidden' => 'y',
            'isMandatory' => 'n',
            'visibleBy' => null,
            'permName' => 'test_admin',
        ], [
            'name' => 'Registered info',
            'type' => 't',
            'isHidden' => 'n',
            'isMandatory' => 'n',
            'visibleBy' => ['Registered'],
            'permName' => 'test_registered',
        ], [
            'name' => 'Special info',
            'type' => 't',
            'isHidden' => 'n',
            'isMandatory' => 'n',
            'visibleBy' => ['SpecialGroup'],
            'permName' => 'test_special',
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
                '',
                '',
                '',
                null,
                '',
                $field['visibleBy'],
                null,
                'n',
                '',
                '',
                '',
                $field['permName']
            );
        }

        $definition = Tracker_Definition::get(self::$trackerId);
        $fields = $definition->getFields();
        $fields[0]['value'] = 'Test item';
        $fields[1]['value'] = 'Secret value';
        $fields[2]['value'] = 'Secret value';
        $fields[3]['value'] = 'Secret value';

        self::$trklib->replace_item(self::$trackerId, 0, ['data' => $fields], 'o');

        TikiDb::get()->query("INSERT INTO `users_grouppermissions` VALUES('Registered', 'tiki_p_view_trackers', '')");
        $builder = new Perms_Builder();
        Perms::set($builder->build());

        self::$unifiedlib = TikiLib::lib('unifiedsearch');
        self::$unifiedlib->rebuild();
    }

    public static function tearDownAfterClass(): void
    {
        global $prefs, $tikilib;
        $prefs['feature_trackers'] = self::$old_pref;

        parent::tearDownAfterClass();
        self::$trklib->remove_tracker(self::$trackerId);

        if (! empty($prefs['unified_mysql_index_current'])) {
            TikiDb::get()->query("DROP TABLE `{$prefs['unified_mysql_index_current']}`");
            $tikilib->delete_preference('unified_mysql_index_current');
        }

        self::$unifiedlib->invalidateIndicesCache();

        TikiDb::get()->query("DELETE FROM `users_grouppermissions` WHERE `groupName` = 'Registered' AND `permName` = 'tiki_p_view_trackers'");
        $builder = new Perms_Builder();
        Perms::set($builder->build());
    }

    public function testAdminCanSeeAllFields(): void
    {
        // impersonate admin
        new Perms_Context('admin');
        $perms = Perms::getInstance();
        $perms->setGroups(['Admins']);

        $query = self::$unifiedlib->buildQuery([
            'type' => 'trackeritem',
            'tracker_id' => self::$trackerId,
        ]);
        $result = $query->search(self::$unifiedlib->getIndex());
        $resultArray = $result->getArrayCopy();

        $this->assertEquals('Test item', $resultArray[0]['tracker_field_test_name']);
        $this->assertEquals('Secret value', $resultArray[0]['tracker_field_test_admin']);
        $this->assertEquals('Secret value', $resultArray[0]['tracker_field_test_registered']);
        $this->assertEquals('Secret value', $resultArray[0]['tracker_field_test_special']);
    }

    public function testRegisteredCanSeeSpecificFields(): void
    {
        // impersonate a regitered user
        new Perms_Context('someone');
        $perms = Perms::getInstance();
        $perms->setGroups(['Registered']);

        $query = self::$unifiedlib->buildQuery([
            'type' => 'trackeritem',
            'tracker_id' => self::$trackerId,
        ]);
        $result = $query->search(self::$unifiedlib->getIndex());
        $resultArray = $result->getArrayCopy();

        $this->assertEquals('Test item', $resultArray[0]['tracker_field_test_name']);
        $this->assertEquals('', $resultArray[0]['tracker_field_test_admin']);
        $this->assertEquals('Secret value', $resultArray[0]['tracker_field_test_registered']);
        $this->assertEquals('', $resultArray[0]['tracker_field_test_special']);
    }
}