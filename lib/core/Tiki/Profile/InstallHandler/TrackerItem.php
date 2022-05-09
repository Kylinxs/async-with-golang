
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Tiki_Profile_InstallHandler_TrackerItem extends Tiki_Profile_InstallHandler
{
    private $mode = 'create';

    private function getData()
    {
        if ($this->data) {
            return $this->data;
        }

        $data = $this->obj->getData();

        return $this->data = $data;
    }

    public function getDefaultValues()
    {
        return [
            'tracker' => 0,
            'status' => '',
            'values' => [],
        ];
    }

    public function getConverters()
    {
        return [
            'status' => new Tiki_Profile_ValueMapConverter([ 'open' => 'o', 'pending' => 'p', 'closed' => 'c' ]),
        ];
    }

    public function canInstall()
    {
        $data = $this->getData();

        if (! isset($data['tracker'])) {
            return false;
        }

        if ($this->convertMode($data)) {
            if ($this->mode == 'create' && ! is_array($data['values'])) {
                return false;
            }
            if (is_array($data['values'])) {
                foreach ($data['values'] as $row) {
                    if (! is_array($row) || count($row) != 2) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    public function convertMode($data)
    {
        if (isset($data['mode']) && $data['mode'] == 'update') {
            if (empty($data['itemId'])) {
                throw new Exception("itemId is mandatory to update tracker");
            } else {
                $this->mode = 'update';
            }
        }
        return true;
    }

    public function doInstall()
    {
        $data = $this->getData();
        $converters = $this->getConverters();
        $this->replaceReferences($data);
        $this->convertMode($data);

        foreach ($data as $key => &$value) {
            if (isset($converters[$key])) {
                $value = $converters[$key]->convert($value);
            }
        }

        $data = array_merge($this->getDefaultValues(), $data);

        $trklib = TikiLib::lib('trk');

        $fields = $trklib->list_tracker_fields($data['tracker']);
        $providedfields = [];
        foreach ($data['values'] as $row) {
            list( $f, $v) = $row;

            unset($fieldId);

            foreach ($fields['data'] as $key => $entry) {
                if ($entry['fieldId'] == $f || $entry['permName'] == $f) {
                    $fields['data'][$key]['value'] = $v;
                    $fieldId = $entry['fieldId'];
                    break;
                }
            }

            if ($fieldId) {
                $providedfields[] = $fieldId;
            }
        }

        if ($this->mode == 'update') {
            foreach ($fields['data'] as $key => $entry) {
                if (! in_array($entry['fieldId'], $providedfields)) {
                    unset($fields['data'][$key]);
                }
            }
            return $trklib->replace_item($data['tracker'], $data['itemId'], $fields, $data['status']);
        } else {
            return $trklib->replace_item($data['tracker'], 0, $fields, $data['status']);
        }
    }

    /**
     * Function to export one tracker item
     *
     * @param Tiki_Profile_Writer $writer
     * @param array $item
     *
     * @return bool
     */
    public static function export(Tiki_Profile_Writer $writer, $item)
    {
        if (empty($item) || ! is_array($item)) {
            return false;
        }

        if (! isset($item['field_values'])) {
            $item['field_values'] = [];
        }

        $statusMap = new Tiki_Profile_ValueMapConverter(['open' => 'o', 'pending' => 'p', 'closed' => 'c']);

        $data = [
            'tracker' => $writer->getReference('tracker', $item['trackerId']),
            'status' => $statusMap->reverse($item['status']),
            'values' => [],
        ];

        foreach ($item['field_values'] as $valueItems) {
            $fieldReference = $writer->getReference('tracker_field', $valueItems['fieldId']);
            if (isset($valueItems['value'])) {
                $data['values'][] = [$fieldReference, $valueItems['value']];
            } else {
                $data['values'][] = null;

                // just a note (--v) for header and prefs field types, but add a warning for others
                if (in_array($valueItems['type'], ['h', 'p'])) {
                    $feedbackFn = 'note';
                } else {
                    $feedbackFn = 'warning';
                }

                call_user_func(
                    ['Feedback', $feedbackFn],
                    tr(
                        'Field "%0" in Tracker %1 has no value for itemId %2',
                        $valueItems['permName'],
                        $valueItems['trackerId'],
                        $item['itemId']
                    )
                );
            }
        }

        $writer->addObject('tracker_item', $item['itemId'], $data);

        return true;
    }

    /**
     * Remove tracker item
     *
     * @param string $trackerItem
     * @return bool
     */
    public function remove($trackerItem)
    {
        if (! empty($trackerItem)) {
            $trklib = TikiLib::lib('trk');
            $trackerItems = $trklib
                ->table('tiki_tracker_item_fields')
                ->fetchAll(['itemId'], ['value' => $trackerItem]);
            if (count($trackerItems) == 1) {
                $trackerItemId = ! empty($trackerField[0]['itemId']) ? $trackerField[0]['itemId'] : 0;
                $result = $trklib->remove_tracker_item($trackerItemId);
                if ($result && $result->numRows()) {
                    return true;
                }
            }
        }

        return false;
    }
}