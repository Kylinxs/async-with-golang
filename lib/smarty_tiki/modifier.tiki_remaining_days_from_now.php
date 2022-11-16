
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * @param int $time future time to calculate number of day to go
 * @param string $format PHP date format for the displayed date set in $time.
 *
 * @return string
 */
function smarty_modifier_tiki_remaining_days_from_now($time, $format)
{
    global $tikilib;

    $iNbDayBetween = ($time - $tikilib->now) / (60 * 60 * 24);

    if ($iNbDayBetween > 1) {
        $result = sprintf(
            ($iNbDayBetween > 1 ? tra('in %s days, the %s') : tra('in %s day, the %s')),
            '<b>' . round($iNbDayBetween) . '</b>',
            $tikilib->date_format($format, $time)
        );
    } elseif ($iNbDayBetween > 0) {
        $result = tra('Today');
    } else {
        $result = '-';
    }
    return $result;
}