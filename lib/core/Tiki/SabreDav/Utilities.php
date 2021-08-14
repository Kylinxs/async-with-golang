
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\SabreDav;

use Sabre\DAV;
use Sabre\VObject;
use TikiLib;
use TikiMail;

class Utilities
{
    public static function checkUploadPermission($galleryDefinition)
    {
        $canUpload = TikiLib::lib('filegal')->can_upload_to($galleryDefinition->getInfo());
        if (! $canUpload) {
            throw new DAV\Exception\Forbidden('Permission denied.');
        }
    }

    public static function checkCreatePermission($galleryDefinition)
    {
        $perms = TikiLib::lib('tiki')->get_perm_object('', 'file gallery', $galleryDefinition->getInfo());
        if ($perms['tiki_p_create_file_galleries'] != 'y') {
            throw new DAV\Exception\Forbidden('Permission denied.');
        }
    }

    public static function checkDeleteGalleryPermission($galleryDefinition)
    {
        global $user, $prefs;

        $info = $galleryDefinition->getInfo();
        $perms = TikiLib::lib('tiki')->get_perm_object('', 'file gallery', $info);

        $mygal_to_delete = ! empty($user) && $info['type'] === 'user' && $info['user'] !== $user && $perms['tiki_p_userfiles'] === 'y' && $info['parentId'] !== $prefs['fgal_root_user_id'];

        if ($perms['tiki_p_admin_file_galleries'] != 'y' && ! $mygal_to_delete) {
            throw new DAV\Exception\Forbidden('Permission denied.');
        }
    }

    public static function checkDeleteFilePermission($galleryDefinition)
    {
        $perms = TikiLib::lib('tiki')->get_perm_object('', 'file gallery', $galleryDefinition->getInfo());
        if ($perms['tiki_p_remove_files'] != 'y' && $perms['tiki_p_admin_file_galleries'] != 'y') {
            throw new DAV\Exception\Forbidden('Permission denied.');
        }
    }

    public static function parseContents($name, $data)
    {
        if (is_resource($data)) {
            $content = stream_get_contents($data);
        } else {
            $content = (string)$data;
        }

        $filesize = strlen($content);
        $mime = TikiLib::lib('mime')->from_content($name, $content);

        return compact('content', 'filesize', 'mime');
    }

  /**
   * Parses some information from calendar objects, used for optimized
   * calendar-queries and field mapping to Tiki. RRULE parsing partially
   * supports RFC 5545 as Tiki does not handle all of the specification.
   *
   * @param string $calendarData
   * @return array
   */
    public static function getDenormalizedData($calendarData, $timezone)
    {
        $vObject = VObject\Reader::read($calendarData);
        $componentType = null;
        $component = null;
        $uid = null;
        foreach ($vObject->getComponents() as $component) {
            if ($component->name !== 'VTIMEZONE') {
                $componentType = $component->name;
                $uid = (string)$component->UID;
                break;
            }
        }
        if (! $componentType) {
            throw new \Sabre\DAV\Exception\BadRequest('Calendar objects must have a VJOURNAL, VEVENT or VTODO component');
        }

        $result = [
        'etag'           => md5($calendarData),
        'size'           => strlen($calendarData),
        'componenttype'  => $componentType,
        'uid'            => $uid,
        ];
        $result = array_merge(
            $result,
            self::getDenormalizedDataFromComponent($component, $timezone)
        );

      // check for individual instances changed in recurring events
        $result['overrides'] = [];
        foreach ($vObject->getComponents() as $component) {
            if ($component->name !== 'VEVENT') {
                continue;
            }
            if ($component->{'RECURRENCE-ID'}) {
                $result['overrides'][] = self::getDenormalizedDataFromComponent($component, $timezone);
            }
        }

      // Destroy circular references to PHP will GC the object.
        $vObject->destroy();

        return $result;
    }

    public static function getDenormalizedDataFromComponent($component, $timezone)
    {
        $firstOccurence = null;
        $lastOccurence = null;
        $rec = null;

        if ($component && $component->name == 'VEVENT') {
            $firstOccurence = $component->DTSTART->getDateTime()->getTimeStamp();
            if (isset($component->DTEND)) {
                $lastOccurence = $component->DTEND->getDateTime()->getTimeStamp();
            } elseif (isset($component->DURATION)) {
                $endDate = clone $component->DTSTART->getDateTime();
                $endDate = $endDate->add(VObject\DateTimeParser::parse($component->DURATION->getValue()));
                $lastOccurence = $endDate->getTimeStamp();
            } elseif (! $component->DTSTART->hasTime()) {
                $endDate = clone $component->DTSTART->getDateTime();
                $endDate = $endDate->modify('+1 day');
                $lastOccurence = $endDate->getTimeStamp();
            } else {
                $lastOccurence = $firstOccurence;
            }
            if (isset($component->RRULE)) {
                $rec = new \CalRecurrence();
                $parts = $component->RRULE->getParts();
                switch ($parts['FREQ']) {
                    case "WEEKLY":
                        if (isset($parts['BYDAY'])) {
                            $weekdays = $parts['BYDAY'];
                        } else {
                            $weekdays = substr($component->DTSTART->getDateTime()->format('D'), 0, 2);
                        }
                        $rec->setWeekly(true);
                        $rec->setWeekdays(strtoupper($weekdays));
                        $rec->setMonthly(false);
                        $rec->setYearly(false);
                        break;
                    case "MONTHLY":
                        if (isset($parts['BYMONTHDAY'])) {
                            $monthday = $parts['BYMONTHDAY'];
                        } else {
                            $monthday = $component->DTSTART->getDateTime()->format('j');
                        }
                        $rec->setWeekly(false);
                        $rec->setMonthly(true);
                        $rec->setDayOfMonth($parts['BYMONTHDAY']);
                        $rec->setYearly(false);
                        break;
                    case "YEARLY":
                        if (isset($parts['BYMONTH'])) {
                            $month = $parts['BYMONTH'];
                        } else {
                            $month = $component->DTSTART->getDateTime()->format('n');
                        }
                        if (isset($parts['BYMONTH'])) {
                            $monthday = $parts['BYMONTHDAY'];
                        } else {
                            $monthday = $component->DTSTART->getDateTime()->format('j');
                        }
                        $rec->setWeekly(false);
                        $rec->setMonthly(false);
                        $rec->setYearly(true);
                        $rec->setDateOfYear(str_pad($month, 2, '0', STR_PAD_LEFT) . str_pad($monthday, 2, '0', STR_PAD_LEFT));
                        break;
                }
                $rec->setStartPeriod(\TikiDate::getStartDay($firstOccurence, $timezone));
                if (isset($parts['COUNT'])) {
                    $rec->setNbRecurrences($parts['COUNT']);
                } elseif (isset($parts['UNTIL'])) {
                    $rec->setEndPeriod(\TikiDate::getStartDay(strtotime($parts['UNTIL']), $timezone));
                } else {
                    $rec->setEndPeriod(\TikiDate::getStartDay(strtotime(CalDAVBackend::MAX_DATE), $timezone));
                }
                $rec->setLang('en');
                $rec->setNlId(0);
                $rec->setAllday(0);
            }

          // Ensure Occurence values are positive
            if ($firstOccurence < 0) {
                $firstOccurence = 0;
            }
            if ($lastOccurence < 0) {
                $lastOccurence = 0;
            }
        }

        $result = [
        'start'          => $firstOccurence,
        'end'            => $lastOccurence,
        'rec'            => $rec,
        ];

        if (isset($component->{'RECURRENCE-ID'})) {
            $result['recurrenceStart'] = $component->{'RECURRENCE-ID'}->getDateTime()->getTimeStamp();
        }

        if (isset($component->CREATED)) {
            $result['created'] = $component->CREATED->getDateTime()->getTimeStamp();
        }
        if (isset($component->DTSTAMP)) {
            $result['lastmodif'] = $component->DTSTAMP->getDateTime()->getTimeStamp();
        }
        if (isset($component->{'LAST-MODIFIED'})) {
            $result['lastmodif'] = $component->{'LAST-MODIFIED'}->getDateTime()->getTimeStamp();
        }
        if (isset($component->SUMMARY)) {
            $result['name'] = $component->SUMMARY;
        }
        if (isset($component->DESCRIPTION)) {
            $result['description'] = $component->DESCRIPTION;
        }
        if (isset($component->LOCATION)) {
            $result['newloc'] = $component->LOCATION;
        }
        if (isset($component->CATEGORIES)) {
            $cats = explode(',', $component->CATEGORIES);
            $result['newcat'] = $cats[0];
        }
        if (isset($component->PRIORITY)) {
            $result['priority'] = $component->PRIORITY;
        }
        if (isset($component->STATUS)) {
            $result['status'] = self::reverseMapEventStatus($component->STATUS);
        }
        if (isset($component->URL)) {
            $result['url'] = $component->URL;
        }
        if (isset($component->ORGANIZER)) {
            $result['organizers'] = [];
            $result['real_organizers'] = [];
            foreach ($component->ORGANIZER as $organizer) {
                $email = preg_replace("/MAILTO:\s*/i", "", (string)$organizer);
                $user = TikiLib::lib('user')->get_user_by_email($email);
                if ($user) {
                    $result['organizers'][] = $user;
                }
                $cn = (string)$organizer->CN;
                if (empty($cn)) {
                    $result['real_organizers'][] = $email;
                } else {
                    $result['real_organizers'][] = "$cn <$email>";
                }
            }
        }
        if (isset($component->ATTENDEE)) {
          // participants is used by calendarlib to store in Tiki - these are mapped attendees to Tiki users or plain email addresses
            $result['participants'] = [];
            foreach ($component->ATTENDEE as $attendee) {
                $email = preg_replace("/MAILTO:\s*/i", "", (string)$attendee);
                $user = TikiLib::lib('user')->get_user_by_email($email);
                $participant = [
                'username' => ! empty($user) ? $user : $email,
                'email' => $email,
                ];
                $role = self::reverseMapAttendeeRole((string)$attendee['ROLE']);
                if ($role) {
                    $participant['role'] = $role;
                }
                if (isset($attendee['PARTSTAT'])) {
                    $participant['partstat'] = (string)$attendee['PARTSTAT'];
                }
                $result['participants'][] = $participant;
            }
          // fetch attendees as they are for later reference like RSVP actions via Cypht
            $result['attendees'] = [];
            foreach ($component->ATTENDEE as $attendee) {
                $email = preg_replace("/MAILTO:\s*/i", "", (string)$attendee);
                $cn = (string)$attendee->CN;
                if (empty($cn)) {
                    $cn = $email;
                }
                $result['attendees'][] = "$cn <$email>";
            }
        }

        return $result;
    }

    public static function mapEventStatus($event_status)
    {
        switch ($event_status) {
            case '0':
                return 'TENTATIVE';
            case '1':
                return 'CONFIRMED';
            case '2':
                return 'CANCELLED';
        }
        return '';
    }

    public static function reverseMapEventStatus($event_status)
    {
        switch ($event_status) {
            case 'TENTATIVE':
                return '0';
            case 'CONFIRMED':
                return '1';
            case 'CANCELLED':
                return '2';
        }
        return '';
    }

    public static function mapAttendeeRole($role)
    {
        switch ($role) {
            case '0':
                return 'CHAIR';
            case '1':
                return 'REQ-PARTICIPANT';
            case '2':
                return 'OPT-PARTICIPANT';
            case '3':
                return 'NON-PARTICIPANT';
        }
        return '';
    }

    public static function reverseMapAttendeeRole($role)
    {
        switch ($role) {
            case 'CHAIR':
                return '0';
            case 'REQ-PARTICIPANT':
                return '1';
            case 'OPT-PARTICIPANT':
                return '2';
            case 'NON-PARTICIPANT':
                return '3';
        }
        return '';
    }

  /**
   * Notes on ics format fields. See https://tools.ietf.org/html/rfc5545 for more information.
   * CREATED, DTSTAMP, LAST-MODIFIED - must be UTC. They are stored as UTC in Tiki database. No timezone conversion happens.
   * DTSTART, DTEND - must be in calendar timezone which currently is defined as the timezone of the Tiki user owning the calendar.
   * VTIMEZONE - we use TZID properties on start and end dates as specified in the RFC. It requires us to use relevant VTIMEZONE
   * descriptors as well. However, PHP does not have enough information to generate proper rules for DST changes
   * (see https://github.com/sabre-io/vobject/issues/248 for more information why). We can possibly use DateTimeZone::getTransitions
   * but we should do this for the whole time-span of the calendar events which could be many years and also recurring events in the
   * future may recur indefinitely long. Thus, current implementation leaves parsing the timezone identifier to the clients as TZID
   * is all we really have in Tiki - the timezone name user is acting in.
   */
    public static function constructCalendarData($row)
    {
        static $calendar_timezones = [];
        if (isset($calendar_timezones[$row['calendarId']])) {
            $timezone = $calendar_timezones[$row['calendarId']];
        } else {
            $calendar = TikiLib::lib('calendar')->get_calendar($row['calendarId']);
            $timezone = TikiLib::lib('tiki')->get_display_timezone($calendar['user']);
            $calendar_timezones[$row['calendarId']] = $timezone;
        }
        $dtzone = new \DateTimeZone($timezone);
        $dtstart = \DateTime::createFromFormat('U', $row['start']);
        $dtstart->setTimezone($dtzone);
        $dtend = \DateTime::createFromFormat('U', $row['end']);
        $dtend->setTimezone($dtzone);
        $data = [
        'CREATED' => \DateTime::createFromFormat('U', $row['created'])->format('Ymd\THis\Z'),
        'DTSTAMP' => \DateTime::createFromFormat('U', $row['lastModif'])->format('Ymd\THis\Z'),
        'LAST-MODIFIED' => \DateTime::createFromFormat('U', $row['lastModif'])->format('Ymd\THis\Z'),
        'SUMMARY' => $row['name'],
        'PRIORITY' => $row['priority'],
        'STATUS' => self::mapEventStatus($row['status']),
        'TRANSP' => 'OPAQUE',
        'DTSTART' => $dtstart,
        'DTEND'   => $dtend,
        ];
        if (! empty($row['recurrenceUid'])) {
            $data['UID'] = $row['recurrenceUid'];
        } elseif (! empty($row['uid'])) {
            $data['UID'] = $row['uid'];
        }
        if (! empty($row['description'])) {
            $data['DESCRIPTION'] = $row['description'];
        }
        if (! empty($row['location'])) {
            $data['LOCATION'] = $row['location'];
        }
        if (! empty($row['locationName'])) {
            $data['LOCATION'] = $row['locationName'];
        }
        if (! empty($row['category'])) {
            $data['CATEGORIES'] = $row['category'];
        }
        if (! empty($row['categoryName'])) {
            $data['CATEGORIES'] = $row['categoryName'];
        }
        if (! empty($row['url'])) {
            $data['URL'] = $row['url'];
        }
        if (! empty($row['recurrenceStart'])) {
            $data['RECURRENCE-ID'] = \DateTime::createFromFormat('U', $row['recurrenceStart'])->setTimezone($dtzone);
        }

        $vcalendar = new VObject\Component\VCalendar();
        $vevent = $vcalendar->add('VEVENT', $data);

      // TODO: optimize this for N+1 query problem
        if (! isset($row['organizers'], $row['participants'])) {
            $item = TikiLib::lib('calendar')->get_item($row['calitemId']);
            $organizers = $item['organizers'];
            $participants = $item['participants'];
        } else {
            $organizers = $row['organizers'];
            $participants = $row['participants'];
        }
        foreach ($organizers as $user) {
            $vevent->add(
                'ORGANIZER',
                TikiLib::lib('user')->get_user_email($user),
                [
                'CN' => TikiLib::lib('tiki')->get_user_preference($user, 'realName'),
                ]
            );
        }
        foreach ($participants as $par) {
            $vevent->add(
                'ATTENDEE',
                $par['email'],
                [
                'CN' => TikiLib::lib('tiki')->get_user_preference($par['username'], 'realName'),
                'ROLE' => Utilities::mapAttendeeRole($par['role']),
                'PARTSTAT' => $par['partstat'],
                ]
            );
        }

        if ((string)$vevent->UID != @$row['uid']) {
          // save UID for Tiki-generated calendar events as this must not change in the future
          // SabreDav automatically generates UID value if none is present
            TikiLib::lib('calendar')->fill_uid($row['calitemId'], (string)$vevent->UID);
        }

        return $vcalendar;
    }

    public static function handleITip($args)
    {
        if (empty($args['process_itip'])) {
            return;
        }
        if (! empty($args['old_data'])) {
          // update or delete operation
            $old_vcalendar = self::constructCalendarData($args['old_data']);
        } else {
          // create operation
            $old_vcalendar = null;
        }
        $calitem = TikiLib::lib('calendar')->get_item($args['object']);
        if ($calitem) {
          // create or update operation
            $vcalendar = self::constructCalendarData($calitem);
        } else {
          // delete operation
            $vcalendar = null;
        }
        $broker = new VObject\ITip\Broker();
        $messages = $broker->parseEvent(
            $vcalendar,
            TikiLib::lib('user')->get_user_email($args['user']),
            $old_vcalendar
        );
        foreach ($messages as $message) {
            if (! $message->significantChange) {
                continue;
            }
            $sender_email = (string)$message->sender;
            $sender_name = (string)$message->senderName;
            $sender = $sender_name ? "$sender_name <$sender_email>" : $sender_email;
            $recipient_email = (string)$message->recipient;
            $recipient_name = (string)$message->recipientName;
            $recipient = $recipient_name ? "$recipient_name <$recipient_email>" : $recipient_email;
            switch ($message->method) {
                case 'REQUEST':
                    $subject = "Event Invitation: " . $message->message->VEVENT->SUMMARY->getValue();
                    $body = "You have been invited to the following event:";
                    break;
                case 'CANCEL':
                    $subject = "Event Canceled: " . $message->message->VEVENT->SUMMARY->getValue();
                    $body = "The following event has been canceled:";
                    break;
                case 'REPLY':
                    $subject = "Re: invitation to " . $message->message->VEVENT->SUMMARY->getValue();
                    $body = "$sender has updated their participation status in the following event:";
                    break;
                default:
                    throw new Exception("Unsupported ITip method: " . $message->method);
            }
            $attendees = [];
            foreach ($message->message->VEVENT->ATTENDEE as $attendee) {
                $email = preg_replace("/MAILTO:\s*/i", "", (string)$attendee);
                $cn = (string)$attendee->CN;
                if (empty($cn)) {
                    $cn = $email;
                }
                $attendees[] = "$cn <$email>";
            }
            $body .= "

*{$message->message->VEVENT->SUMMARY->getValue()}*

When: " . TikiLib::lib('tiki')->get_long_datetime($message->message->VEVENT->DTSTART->getDateTime()->getTimeStamp()) . " - " . TikiLib::lib('tiki')->get_long_datetime($message->message->VEVENT->DTEND->getDateTime()->getTimeStamp()) . "

Invitees: " . implode(",\n", $attendees);
          // TODO: IMip messages are using configured Tiki SMTP server for now, but we might want to use cypht SMTP server for the sender user in order to get the replies back in cypht and be able to update participant statuses.
          // The other way would be via Mail-in to calendars and a reply-to address configured as a mail-in source.
            $mail = new TikiMail($args['user'], $sender_email, $sender_name);
            $mail->setSubject($subject);
            $mail->setText($body);
            $mail->addPart($message->message->serialize(), 'text/calendar; method=' . $message->method . '; name=event.ics');
            $mail->send([$recipient]);
        }
    }
}