<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * Handler class for dropdown
 *
 * Letter key: ~d~ ~D~ ~R~ ~M~
 *
 */
class Tracker_Field_Dropdown extends Tracker_Field_Abstract implements Tracker_Field_Synchronizable, Search_FacetProvider_Interface, Tracker_Field_Exportable, Tracker_Field_Filterable, Tracker_Field_EnumerableInterface
{
    public static function getTypes()
    {
        return [
            'd' => [
                'name' => tr('Dropdown'),
                'description' => tr('Allow users to select only from a specified set of options'),
                'help' => 'Drop-Down---Radio-Tracker-Field',
                'prefs' => ['trackerfield_dropdown'],
                'tags' => ['basic'],
                'default' => 'y',
                'supported_changes' => ['d', 'D', 'R', 'M', 'm', 't', 'a', 'L'],
                'params' => [
                    'options' => [
                        'name' => tr('Option'),
                        'description' => tr('If an option contains an equal sign, the part before the equal sign will be used as the value, and the second part as the label'),
                        'filter' => 'text',
                        'count' => '*',
                        'legacy_index' => 0,
                    ],
                ],
            ],
            'D' => [
                'name' => tr('Dropdown selector with "Other" field'),
                'description' => tr('Allow users to select from a specified set of options or to enter an alternate option'),
                'help' => 'Drop-Down---Radio-Tracker-Field',
                'prefs' => ['trackerfield_dropdownother'],
                'tags' => ['basic'],
                'default' => 'n',
                'supported_changes' => ['d', 'D', 'R', 'M', 'm', 't', 'a', 'L'],
                'params' => [
                    'options' => [
                        'name' => tr('Option'),
                        'description' => tr('If an option contains an equal sign, the part before the equal sign will be used as the value, and the second part as the label.') . ' ' . tr('To change the label of the "Other" option, use "other=Label".'),
                        'filter' => 'text',
                        'count' => '*',
                        'legacy_index' => 0,
                    ],
                ],
            ],
            'R' => [
                'name' => tr('Radio Buttons'),
                'description' => tr('Allow users to select only from a specified set of options'),
                'help' => 'Drop-Down---Radio-Tracker-Field',
                'prefs' => ['trackerfield_radio'],
                'tags' => ['basic'],
                'default' => 'y',
                'supported_changes' => ['d', 'D', 'R', 'M', 'm', 't', 'a', 'L'],
                'params' => [
                    'options' => [
                        'name' => tr('Option'),
                        'description' => tr('If an option contains an equal sign, the part before the equal sign will be used as the value, and the second part as the label'),
                        'filter' => 'text',
                        'count' => '*',
                        'legacy_index' => 0,
                    ],
                ],
            ],
            'M' => [
                'name' => tr('Multiselect'),
                'description' => tr('Allow a user to select multiple values from a specified set of options'),
                'help' => 'Multiselect-Tracker-Field',
                'prefs' => ['trackerfield_multiselect'],
                'tags' => ['basic'],
                'default' => 'y',
                'supported_changes' => ['M', 'm', 't', 'a', 'L'],
                'params' => [
                    'options' => [
                        'name' => tr('Option'),
                        'description' => tr('If an option contains an equal sign, the part before the equal sign will be used as the value, and the second part as the label'),
                        'filter' => 'text',
                        'count' => '*',
                        'legacy_index' => 0,
                    ],
                    'inputtype' => [
                        'name' => tr('Input Type'),
                        'description' => tr('User interface control to be used.'),
                        'default' => '',
                        'filter' => 'alpha',
                        'options' => [
                            '' => tr('Multiple-selection checkboxes'),
                            'm' => tr('List box'),
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function build($type, $trackerDefinition, $fieldInfo, $itemData)
    {
        return new Tracker_Field_Dropdown($fieldInfo, $itemData, $trackerDefinition);
    }

    public function getFieldData(array $requestData = [])
    {

        $ins_id = $this->getInsertId();

        if (! empty($requestData['other_' . $ins_id])) {
            $value = $requestData['other_' . $ins_id];
        } elseif (isset($requestData[$ins_id])) {
            $value = implode(',', (array) $requestData[$ins_id]);
        } elseif (isset($requestData[$ins_id . '_old'])) {
            $value = '';
        } else {
            $value = $this->getValue($this->getDefaultValue());
        }

        return [
            'value' => $value,
            'selected' => $value === '' ? [] : explode(',', $value),
            'possibilities' => $this->getPossibleItemValues(),
        ];
    }

    public function addValue($value)
    {
        $existing = explode(',', $this->getValue());
        if (! in_array($value, $existing)) {
            $existing[] = $value;
        }
        return implode(',', $existing);
    }

    public function removeValue($value)
    {
        $existing = explode(',', $this->getValue());
        $existing = array_filter($existing, function ($v) use ($value) {
            return $v != $value;
        });
        return implode(',', $existing);
    }

    public function renderInput($context = [])
    {
        return $this->renderTemplate('trackerinput/dropdown.tpl', $context);
    }

    public function renderInnerOutput($context = [])
    {
        if (! empty($context['list_mode']) && $context['list_mode'] === 'csv') {
            return implode(', ', $this->getConfiguration('selected', []));
        } else {
            $labels = array_map([$this, 'getValueLabel'], $this->getConfiguration('selected', []));
            return implode(', ', $labels);
        }
    }

    private function getValueLabel($value)
    {
        $possibilities = $this->getPossibleItemValues();
        if (isset($possibilities[$value])) {
            return $possibilities[$value];
        } else {
            return $value;
        }
    }

    public function importRemote($value)
    {
        return $value;
    }

    public function exportRemote($value)
    {
        return $value;
    }

    public function importRemoteField(array $info, array $syncInfo)
    {
        return $info;
    }

    public function canHaveMultipleValues()
    {
        return false;
        $withOther = $this->getConfiguration('type') !== 'M';
    }
    public function getPossibleItemValues()
    {
        static $localCache = [];

        $string = $this->getConfiguration('options');
        if (! isset($localCache[$string])) {
            $options = $this->getOption('options');

            if (empty($options)) {
                return [];
            }

            $out = [];
            foreach ($options as $value) {
                $out[$this->getValuePortion($value)] = $this->getLabelPortion($value);
            }

            $localCache[$string] = $out;
        }
        return $localCache[$string];
    }

    private function getDefaultValue()
    {
        $options = $this->getOption('options');
        if (empty($options)) {
            $options = [];
        }
        $parts = [];
        $last = false;
        foreach ($options as $opt) {
            if ($last === $opt) {
                $parts[] = $this->getValuePortion($opt);
            }

            $last = $opt;
        }

        return implode(',', $parts);
    }

    private function getValuePortion($value)
    {
        if (false !== $pos = strpos($value, '=')) {
            $value = substr($value, 0, $pos);
        }

        // Check if option is