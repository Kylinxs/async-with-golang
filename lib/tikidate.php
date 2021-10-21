
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER['SCRIPT_NAME'], basename(__FILE__)) !== false) {
    header('location: index.php');
    exit;
}

/**
 * class: TikiDate
 *
 * This class takes care of all time/date conversions for
 * storing dates in the DB and displaying dates to the user.
 *
 * Dates are always stored in UTC in the database
 *
 * Created by: Jeremy Jongsma (jjongsma@tickchat.com)
 * Created on: Sat Jul 26 11:51:31 CDT 2003
 */
class TikiDate
{
    public $trad = [
                    'January','February','March','April','May','June','July','August','September','October','November','December',
                    'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec',
                    'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday',
                    'Mon','Tue','Wed','Thu','Fri','Sat','Sun','of'
    ];

    public $translated_trad = [];
    public $date;
    public $translation_array = [
                '%a' => 'D',
                '%A' => 'l',
                '%b' => 'M',
                '%B' => 'F',
                '%c' => 'r',    // not quite the same as locale but RFC 2822
                '%d' => 'd',
                '%D' => 'm/d/y',
                '%e' => 'j',
                '%F' => 'c',
                '%g' => 'y',
                '%G' => 'Y',
                '%h' => 'M',
                '%H' => 'H',
                '%i' => 'h',
                '%I' => 'h',
                '%j' => 'z',
                '%k' => 'G',
                '%l' => 'g',
                '%m' => 'm',
                '%M' => 'i',
                '%n' => "\n",
                '%p' => 'A',
                '%P' => 'a',
                '%r' => 'h:i:s A',
                '%R' => 'h:i',
                '%s' => 's',
                '%S' => 's',
                '%t' => "\t",
                '%T' => 'h:i:s',
                '%u' => 'N',
                '%U' => 'W',
                '%V' => 'W',
                '%w' => 'w',
                '%W' => 'W',
                '%y' => 'y',
                '%Y' => 'Y',
                '%z' => 'O',
                '%Z' => 'T',
    ];

    public static $deprecated_tz = [
        'CST6CDT',
        'Cuba',
        'Egypt',
        'Eire',
        'EST5EDT',
        'Factory',
        'GB-Eire',
        'GMT0',
        'Greenwich',
        'Hongkong',
        'Iceland',
        'Iran',
        'Israel',
        'Jamaica',
        'Japan',
        'Kwajalein',
        'Libya',
        'localtime',    // because PHP Fatal error was observed in Apache2 logfile
                // not mentioned here: https://bugs.php.net/bug.php?id=66985
        'leap-seconds.list', // same here
        'MST7MDT',
        'Navajo',
        'NZ-CHAT',
        'Poland',
        'Portugal',
        'PST8PDT',
        'Singapore',
        'Turkey',
        'Universal',
        'W-SU',
        'Zulu',
        'tzdata.zi',
        'leapseconds'
    ];

    /**
     * Default constructor
     */
    public function __construct()
    {

        if (isset($_SERVER['TZ']) && ! empty($_SERVER['TZ'])) { // apache - can be set in .htaccess
            $tz = $_SERVER['TZ'];
        } elseif (ini_get('date.timezone')) {                   // set in php.ini
            $tz = ini_get('date.timezone');
        } elseif (getenv('TZ')) {                               // system env setting
            $tz = getenv('TZ');
        } else {
            $tz = 'UTC';
        }
        date_default_timezone_set($tz);

        $this->date = new DateTime();   // was: DateTime(date("Y-m-d H:i:s Z"))
                                        // the Z (timezone) param was causing an error
                                        // DateTime constructor defaults to "now" anyway so unnecessary?
        $this->search = array_keys($this->translation_array);
        $this->replace = array_values($this->translation_array);
    }

    /**
     * @return array
     */
    public static function getTimeZoneList()
    {
        $tz = [];
        $now = new DateTime('now', new DateTimeZone('UTC'));
        $tz_list = DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC);
        ksort($tz_list);

        foreach ($tz_list as $tz_id) {
            if (self::isKnownTimezoneID($tz_id)) {
                $tmp_now = new DateTime('now', new DateTimeZone($tz_id));
                $tmp = $tmp_now->getOffset() - 3600 * $tmp_now->format('I');
                $tz[$tz_id]['offset'] = $tmp * 1000;
            }
        }
        return $tz;
    }

    public static function tzServerOffset($display_tz = null, $reference_point = 'now')
    {
        if (! $display_tz) {
            $display_tz = 'UTC';
        }
        $tz = new DateTimeZone($display_tz);
        if (is_numeric($reference_point)) {
            $reference_point = '@' . (string)$reference_point;
        }
        try {
            $d = new DateTime($reference_point, $tz);
            return $tz->getOffset($d);
        } catch (Exception $e) {
            // date parsing issues result in unknown server offset
            return 0;
        }
    }

    public static function getStartDay($timestamp, $tz)
    {
        $dt = DateTime::createFromFormat('U', $timestamp);
        $tz = new DateTimeZone($tz);
        $dt->setTimezone($tz);
        $dt->setTime(0, 0, 0);
        return $dt->getTimestamp();
    }

    /**
     * @param $format
     * @param bool $is_strftime_format
     * @return string
     */
    public function format($format, $is_strftime_format = true)
    {
        global $prefs;

        // Format the date
        if ($is_strftime_format) {
            $format = preg_replace('/(?<!%)([a-zA-Z])/', '\\\$1', $format);
            $return = $this->date->format(str_replace($this->search, $this->replace, $format));
        } else {
            $return = $this->date->format($format);
        }

        // Translate the date if we are not already in english

        // Divide the date into an array of strings by looking for dates elements
        // (specified in $this->trad)
        $words = preg_split('/(' . implode('|', $this->trad) . ')/', $return, -1, PREG_SPLIT_DELIM_CAPTURE);

        // For each strings in $words array...
        $return = '';
        foreach ($words as $w) {
            if (array_key_exists($w, $this->translated_trad)) {
                // ... we've loaded this previously
                $return .= $this->translated_trad["$w"];
            } elseif (in_array($w, $this->trad)) {
                // ... or we have a date element that needs a translation
                $t = tra($w, '', true);
                $this->translated_trad["$w"] = $t;
                $return .= $t;
            } else {
                // ... or we have a string that should not be translated
                $return .= $w;
            }
        }

        // replace POSIX GMT relative tz with ISO signs
        if (strpos($return, 'GMT+') !== false) {
            $return = str_replace('GMT+', 'GMT-', $return);
        } else {
            $return = str_replace('GMT-', 'GMT+', $return);
        }

        return $return;
    }

    /**
     * @param $days
     */
    public function addDays($days)
    {
        if ($days >= 0) {
            $this->date->modify("+$days day");
        } else {
            $this->date->modify("$days day");
        }
    }

    /**
     * @param $months
     */
    public function addMonths($months)
    {
        if ($months >= 0) {
            $this->date->modify("+$months months");
        } else {
            $this->date->modify("$months months");
        }
    }

    /**
     * @return int
     */
    public function getTime()
    {
        return (int)$this->date->format('U');
    }

    /**
     * @return int
     */
    public function getWeekOfYear()
    {
        return (int)$this->date->format('W');
    }

    /**
     * @param $date
     */
    public function setDate($date, $tz_id = null)
    {
        if (is_numeric($date)) {
            $this->date = new DateTime('@' . $date);
        } else {
            $this->date = new DateTime($date, $tz_id ? $this->getTZByID($tz_id) : null);
        }
    }

    /**
     * @param $day
     * @param $month
     * @param $year
     * @param $hour
     * @param $minute
     * @param $second
     * @param $partsecond
     */
    public function setLocalTime($day, $month, $year, $hour, $minute, $second, $partsecond)
    {
        $this->date->setDate($year, $month, $day);
        $this->date->setTime($hour, $minute, $second);
    }

    public function getTZByID($tz_id)
    {
        global $prefs;
        if (! self::TimezoneIsValidId($tz_id) && (! empty($prefs['timezone_offset']) || $prefs['timezone_offset'] == 0)) {  // timezone_offset in seconds
            $tz_id = timezone_name_from_abbr($tz_id, $prefs['timezone_offset']);
        }
        $dtz = null;
        while (! $dtz) {
            try {
                $dtz = new DateTimeZone($tz_id);
            } catch (Exception $e) {
                $tz_id = $this->convertMissingTimezone($tz_id);
            }
        }
        return $dtz;
    }

    /**
     * @param $tz_id
     */
    public function setTZbyID($tz_id)
    {
        $this->date->setTimezone($this->getTZByID($tz_id));
    }

    /**
     * @param $tz_id
     * @return string
     */
    public function convertMissingTimezone($tz_id)
    {
        switch ($tz_id) {       // Convert timezones not in PHP 5
            case 'A':
                $tz_id = 'Etc/GMT+1';       // military A to Z
                break;
            case 'B':
                $tz_id = 'Etc/GMT+2';
                break;
            case 'C':
                $tz_id = 'Etc/GMT+3';
                break;
            case 'D':
                $tz_id = 'Etc/GMT+4';
                break;
            case 'E':
                $tz_id = 'Etc/GMT+5';
                break;
            case 'F':
                $tz_id = 'Etc/GMT+6';
                break;
            case 'G':
                $tz_id = 'Etc/GMT+7';
                break;
            case 'H':
                $tz_id = 'Etc/GMT+8';
                break;
            case 'I':
                $tz_id = 'Etc/GMT+9';
                break;
            case 'K':
                $tz_id = 'Etc/GMT+10';
                break;
            case 'L':
                $tz_id = 'Etc/GMT+11';
                break;
            case 'M':
                $tz_id = 'Etc/GMT+12';
                break;
            case 'N':
                $tz_id = 'Etc/GMT-1';
                break;
            case 'O':
                $tz_id = 'Etc/GMT-2';
                break;
            case 'P':
                $tz_id = 'Etc/GMT-3';
                break;
            case 'Q':
                $tz_id = 'Etc/GMT-4';
                break;
            case 'R':
                $tz_id = 'Etc/GMT-5';
                break;
            case 'S':
                $tz_id = 'Etc/GMT-6';
                break;
            case 'T':
                $tz_id = 'Etc/GMT-7';
                break;
            case 'U':
                $tz_id = 'Etc/GMT-8';
                break;
            case 'V':
                $tz_id = 'Etc/GMT-9';
                break;
            case 'W':
                $tz_id = 'Etc/GMT-10';
                break;
            case 'X':
                $tz_id = 'Etc/GMT-11';
                break;
            case 'Y':
                $tz_id = 'Etc/GMT-12';
                break;
            case 'Z':
                $tz_id = 'Etc/GMT';
                break;
            default:
                $tz_id = 'UTC';
                break;
        }
        return $tz_id;
    }

    /**
     * @return string
     */
    public function getTimezoneId()
    {
        $tz = $this->date->format('e');
        if ($tz === 'GMT') {
            $tz = 'UTC';    // timezone list from DateTimeZone::listIdentifiers() only has UTC, no GMT any more
        }
        return $tz;
    }

    /**
     * Checks that the string is a timezone identifier (Note: timezone abbreviations
     * are not always valid timezones and don't handle daylight saving correctly).
     * display_timezone can be manually set to an identifier in preferences but
     * will be an [uppercase] abbreviation if auto-detected by JavaScript.
     */
    public static function TimezoneIsValidId($id)
    {
        return in_array($id, self::getTimezoneIdentifiers());
    }

    /**
     * Checks that the string timezone identifier is recognized as a timezone.
     * This does not rely on an ever-expanding blacklist TikiDate::$deprecated_tz
     * Therefore it should not break every time the OS updates the TZ list
     */
    public static function isKnownTimezoneID($tzid)
    {
        if (empty($tzid)) {
            return false;
        }
        foreach (timezone_abbreviations_list() as $zone) {
            foreach ($zone as $item) {
                if ($item["timezone_id"] == $tzid) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function getTimezoneAbbreviations()
    {
        static $abbrevs = null;

        if (! $abbrevs) {
            $abbrevs = array_keys(DateTimeZone::listAbbreviations());
        }

        return $abbrevs;
    }

    public static function getTimezoneIdentifiers()
    {
        static $ids = null;

        if (! $ids) {
            $t_ids = DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC);
            foreach ($t_ids as $id) {
                if (in_array($id, TikiDate::$deprecated_tz)) {
                    continue; // Workaround PHP5.5 no more this timezone https://bugs.php.net/bug.php?id=66985
                }
                $ids[] = $id;
            }
        }

        return $ids;
    }

    public static function shiftToNearestGMT($timestamp)
    {
        $hour = date('G', $timestamp);
        $minute = date('i', $timestamp);

        $hours = intval($hour) + $minute / 60;
        if ($hours > 12) {
            $hours = 24 - $hours;
            $timestamp += $hours * 3600;
        } else {
            $timestamp -= $hours * 3600;
        }

        return $timestamp;
    }

    /**
     * Uses a timezone identifier (if present) or timezone offset
     * coming from the browser to modify the timestamp to correct UTC
     * @param array $opts - input data
     * @param int $timestamp - the timestamp coming from jscalendar smarty function
     * @return int modified timestamp
     */
    public static function convertWithTimezone($opts, $timestamp)
    {
        if (isset($opts['tzname'])) {
            try {
                $dtz = new DateTimeZone($opts['tzname']);
                $dt = new DateTime('@' . $timestamp);
                $dt->setTimeZone($dtz);
                $timestamp += $dt->getOffset();
            } catch (Exception $e) {
                Feedback::error(tr('Error using local timezone %0: %1', $opts['tzname'], $e->getMessage()));
            }
        } elseif (isset($opts['tzoffset'])) {
            $browser_offset = (int)$opts['tzoffset'] * 60;
            $timestamp -= $browser_offset;
        }
        return $timestamp;
    }
}

/**
 *
 */
class Date_Calc
{
    /**
     * @param $month
     * @param $year
     * @return int
     */
    public static function daysInMonth($month, $year)
    {
        return cal_days_in_month(CAL_GREGORIAN, $month, $year);
    }
}