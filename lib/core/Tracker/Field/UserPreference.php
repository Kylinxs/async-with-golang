<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * Handler class for User preference
 *
 * Letter key: ~p~
 *
 */
class Tracker_Field_UserPreference extends Tracker_Field_Abstract
{
    public static function getTypes()
    {
        global $prefs;

        $options = [
            'email' => 'email',
            'password' => 'password',
            'location' => 'location',
            'avatar' => 'avatar',
        ];

        foreach (array_keys($prefs) as $prefName) {
            $options[$prefName] = $prefName;
        }

        return [
            'p' => [
                'name' => tr('User Preference'),
                'description' => tr('Allow user preference changes from a tracker.'),
                'help' => 'User-Preference-Field',
                'prefs' => ['trackerfield_userpreference'],
                'tags' => ['advanced'],
                'default' => 'n',
                'params' => [
                    'type' => [
                        'name' => tr('Preference Name'),
                        'description' => tr('Name of the preference to manipulate. avatar, location, avatar, location, password and email are not preferences, but are also valid values that will modify the user\'s profile.'),
                        'filter' => 'word',
                        'options' => $options,
                        'legacy_index' => 0,
                    ],
                ],
            ],
        ];
    }

    public function getFieldData(array $requestData = [])
    {
        global $tikilib;

        $ins_id = $this->getInsertId();

        if (isset($requestData[$ins_id])) {
            $value = $requestData[$ins_id];
        } else {
            $userlib = TikiLib::lib('user');
            $trklib = TikiLib::lib('trk');

            $value = '';
            $itemId = $this->getItemId();

            if ($itemId) {
                $itemUsers = $this->getTrackerDefinition()->getItemUsers($itemId);

                if (! empty($itemUsers)) {
                    if ($this->getOption('type') == 'email') {
                        $value = $userlib->get_user_email($itemUsers[0]);
                    } elseif ($this->getOption('type') == 'location') {
                        $location = [
                            'lat' => (float) $tikilib->get_user_preference($itemUsers[0], 'lat', ''),
                            'lon' => (float) $tikilib->get_user_preference($itemUsers[0], 'lon', ''),
                            'zoom' => (int) $tikilib->get_user_preference($itemUsers[0], 'zoom', ''),
                        ];

                        $value = TikiLib::lib('geo')->build_location_string($location);
                    } elseif ($this->getOption('type') == 'avatar') {
                        $value = $tikilib->get_user_avatar($itemUsers[0]);
                    } else {
                        $value = $userlib->get_user_preference($itemUsers[0], $this->getOption('type'));
                    }
                }
            }
        }

        return ['value' => $value];
    }

    public function renderInnerOutput($context = [])
    {
        $fieldData = $this->getFieldData();
        $value = $fieldData['value'];
        if ($this->getOption('type') === 'country') {
            $value = str_replace('_', ' ', $value);
        } elseif ($this->getOption('type') === 'display_timezone' && empty($value)) {
            $value = tr('Detect user time zone if browser allows, otherwise site default');
        }
        return $value;
    }

    public function renderInput($context = [])
    {
        if ($this->getOption('type') === 'country') {
            $context['flags'] = TikiLib::lib('tiki')->get_flags('', '', '', true);
        } elseif ($this->getOption('type') === 'display_timezone') {
            $context['timezones'] = TikiDate::getTimeZoneList();
        } elseif (in_array($this->getOption('type'), ['location'])) {
            TikiLib::lib('header')->add_map();
        }
        return $this->renderTemplate('trackerinput/userpreference.tpl', $context);
    }

    public function getDocumentPart(Search_Type_Factory_Interface $typeFactory)
    {
        $baseKey = $this->getBaseKey();
        return [
            $baseKey => $typeFactory->plaintext($this->renderInnerOutput()),
        ];
    }
}
