
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

//THIS HANDLER STILL DON'T WORK PROPERLY. USE WITH CAUTION.
class Tiki_Profile_InstallHandler_Calendar extends Tiki_Profile_InstallHandler
{
    public function getData()
    {
        if ($this->data) {
            return $this->data;
        }

        $data = $this->obj->getData();
        $this->replaceReferences($data);

        if (! empty($data['name'])) {
            $calendarlib = TikiLib::lib('calendar');
            $data['calendarId'] = $calendarlib->get_calendarId_from_name($data['name']);
        }

        return $this->data = $data;
    }

    public function canInstall()
    {
        $data = $this->getData();

        if (! isset($data['name'])) {
            return false;
        }
        return $this->convertMode($data);
    }
    private function convertMode($data)
    {
        if (! isset($data['mode'])) {
            return true; // will duplicate if already exists
        }
        switch ($data['mode']) {
            case 'update':
                if (empty($data['calendarId'])) {
                    throw new Exception(tra('Calendar does not exist') . ' ' . $data['name']);
                }
                break;
            case 'create':
                if (! empty($data['calendarId'])) {
                    throw new Exception(tra('Calendar already exists') . ' ' . $data['name']);
                }
                break;
        }
        return true;
    }

    public function doInstall()
    {
        if ($this->canInstall()) {
            $calendarlib = TikiLib::lib('calendar');

            $calendar = $this->getData();

            global $user;
            $customflags = isset($calendar['customflags']) ? $calendar['customflags'] : [];
            $options = isset($calendar['options']) ? $calendar['options'] : [];
            if (! isset($calendar['options']) && ! isset($calendar['customflags']) && ! empty($calendar['calendarId'])) {
                return $calendar['calendarId']; //only pick up the id
            }
            $id = $calendarlib->set_calendar($calendar['calendarId'], $user, $calendar['name'], $calendar['description'], $customflags, $options);
            return $id;
        }
    }

    /**
     * Export calendars
     *
     * @param Tiki_Profile_Writer $writer
     * @param int $calendarId
     * @param bool $all
     * @return bool
     */
    public static function export(Tiki_Profile_Writer $writer, $calendarId, $all = false)
    {
        $calendarlib = TikiLib::lib('calendar');

        if (isset($calendarId) && ! $all) {
            $listCalendar = [];
            $listCalendar[] = ['calendarId' => $calendarId];
        } else {
            $listCalendar = $calendarlib->list_calendars();
            $listCalendar = $listCalendar['data'];
        }

        foreach ($listCalendar as $calendar) {
            $calendarId = $calendar['calendarId'];
            $cal = $calendarlib->get_calendar($calendarId);

            if (! $cal || empty($cal['calendarId'])) {
                return false;
            }

            $customflags = array_intersect_key($cal, array_flip(self::getCustomFlags()));
            $options = array_diff_key($cal, array_flip(array_merge(
                [
                    'calendarId',
                    'name',
                    'description',
                    'user',
                    'created',
                    'lastmodif',
                    'personal'
                ],
                self::getCustomFlags()
            )));

            $writer->addObject(
                'calendar',
                $calendarId,
                [
                    'name' => $cal['name'],
                    'description' => $cal['description'],
                    'customflags' => $customflags,
                    'options' => $options
                ]
            );
        }
        return true;
    }

    private static function getCustomFlags()
    {
        return [
            'customlocations',
            'customcategories',
            'customlanguages',
            'custompriorities',
            'customparticipants',
            'customsubscription',
            'customstatus'
        ];
    }

    /**
     * Remove calendar
     *
     * @param string $calendar
     * @return bool
     */
    public function remove($calendar)
    {
        if (! empty($calendar)) {
            $calendarlib = TikiLib::lib('calendar');
            $calendarId = $calendarlib->get_calendarId_from_name($calendar);
            if (! empty($calendarId) && $calendarlib->drop_calendar($calendarId)) {
                return true;
            }
        }
        return false;
    }
}