<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

use Tiki\Installer\Installer;

/**
 * Tracker field date-only JsCalendar and DateTime fields store 12am GMT time of the dates as of Tiki 24.
 * Previous Tiki versions used the 12am date in user-configured timezone to store the date.
 * This conversion script ensures we migrate the currently stored timestamps to correct 12am GMT times.
 * Since we don't know what each user's timezone was at the time of timestamp storage, we use the closest
 * possible 12am GMT time. E.g. 12am GMT+8 needs substracting 8 hours while 12am GMT-5 needs adding 5 hours.
 * For the extremely rare GMT+13/14 timezone case, we will end up with a date shift but this seems unavoidable.
 *
 * @param Installer $installer
 *
 * @return bool
 */
function upgrade_20211126_timezone_date_update_tiki(Installer $installer): bool
{
    $date_fields = [];

    $result = $installer->query("SELECT fieldId, options FROM tiki_tracker_fields WHERE type in ('j', 'f')");
    while ($row = $result->fetchRow()) {
        $options = json_decode($row['options']);
        if ($options && isset($options->datetime) && $options->datetime == 'd') {
            $date_fields[] = $row['fieldId'];
        }
    }

    if (! $date_fields) {
        return true;
    }

    $old_tz = date_default_timezone_get();
    date_default_timezone_set('UTC');

    $result = $installer->query("SELECT * FROM tiki_tracker_item_fields WHERE fieldId IN (" . implode(',', $date_fields) . ")");
    while ($row = $result->fetchRow()) {
        if (empty($row['value'])) {
            continue;
        }
        $timestamp = \TikiDate::shiftToNearestGMT($row['value']);
        $installer->query("UPDATE tiki_tracker_item_fields SET value = ? WHERE itemId = ? and fieldId = ?", [$timestamp, $row['itemId'], $row['fieldId']]);
    }

    date_default_timezone_set($old_tz);

    return true;
}
