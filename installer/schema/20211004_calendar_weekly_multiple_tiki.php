
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

use Tiki\Installer\Installer;

/**
 * Convert `weekday` recurrences field to `weekdays`
 *
 * @param Installer $installer
 *
 * @return bool
 */
function upgrade_20211004_calendar_weekly_multiple_tiki(Installer $installer): bool
{
    $daysnames_abr = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'];
    $result = $installer->query('ALTER TABLE `tiki_calendar_recurrence` CHANGE `weekday` `weekdays` VARCHAR(20)  NULL  DEFAULT NULL;');

    if ($result) {
        $result = $installer->query("SELECT * FROM `tiki_calendar_recurrence` WHERE `weekly` = '1';");
        while ($row = $result->fetchRow()) {
            $installer->query(
                "update `tiki_calendar_recurrence` set `weekdays`= '{$daysnames_abr[$row['weekdays']]}' where `recurrenceId`=" . $row['recurrenceId'] . "; "
            );
        }
    }

    return true;
}