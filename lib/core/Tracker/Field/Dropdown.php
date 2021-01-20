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

        // Check if option is contains quotes, ex: "apple, banana, orange"
        if (preg_match('/^(").*\1$/', $value)) {
            $value = substr($value, 1, strlen($value) - 2);
        }

        return $value;
    }

    private function getLabelPortion($value)
    {
        if (false !== $pos = strpos($value, '=')) {
            $value = substr($value, $pos + 1);
        }

        if (preg_match('/^(").*\1$/', $value)) {
            $value = substr($value, 1, strlen($value) - 2);
        }

        return $value;
    }

    public function getDocumentPart(Search_Type_Factory_Interface $typeFactory)
    {
        $value = $this->getValue();
        $label = $this->getValueLabel($value);
        $baseKey = $this->getBaseKey();

        return [
            $baseKey => $typeFactory->identifier($value),
            "{$baseKey}_text" => $typeFactory->sortable($label),
        ];
    }

    public function getProvidedFields()
    {
        $baseKey = $this->getBaseKey();
        return [$baseKey, $baseKey . '_text'];
    }

    public function getProvidedFieldTypes()
    {
        $baseKey = $this->getBaseKey();
        return [
            $baseKey => 'identifier',
            $baseKey . '_text' => 'sortable'
        ];
    }

    public function getGlobalFields()
    {
        $baseKey = $this->getBaseKey();
        return ["{$baseKey}_text" => true];
    }

    public function getFacets()
    {
        $baseKey = $this->getBaseKey();
        return [
            Search_Query_Facet_Term::fromField($baseKey)
                ->setLabel($this->getConfiguration('name'))
                ->setRenderMap($this->getPossibleItemValues())
        ];
    }

    public function getTabularSchema()
    {
        $schema = new Tracker\Tabular\Schema($this->getTrackerDefinition());

        $permName = $this->getConfiguration('permName');
        $name = $this->getConfiguration('name');

        $possibilities = $this->getPossibleItemValues();
        $invert = array_flip($possibilities);
        $withOther = ($this->getConfiguration('type') === 'D');

        $schema->addNew($permName, 'code')
            ->setLabel($name)
            ->setRenderTransform(function ($value) {
                return $value;
            })
            ->setParseIntoTransform(function (&$info, $value) use ($permName) {
                $info['fields'][$permName] = $value;
            })
            ;

        $schema->addNew($permName, 'text')
            ->setLabel($name)
            ->addIncompatibility($permName, 'code')
            ->addQuerySource('text', "tracker_field_{$permName}_text")
            ->setRenderTransform(function ($value, $extra) use ($possibilities, $withOther) {
                if (isset($possibilities[$value])) {
                    return $possibilities[$value];
                } elseif ($withOther) {
                    return $value;
                } else {
                    return '';  // TODO something better?
                }
            })
            ->setParseIntoTransform(function (&$info, $value) use ($permName, $invert, $withOther) {
                if (isset($invert[$value])) {
                    $info['fields'][$permName] = $invert[$value];
                } elseif ($withOther) {
                    $info['fields'][$permName] = $value;
                }
            })
            ;

        return $schema;
    }

    public function getFilterCollection()
    {
        $filters = new Tracker\Filter\Collection($this->getTrackerDefinition());
        $permName = $this->getConfiguration('permName');
        $name = $this->getConfiguration('name');
        $baseKey = $this->getBaseKey();

        $possibilities = $this->getPossibleItemValues();
        if ($this->getConfiguration('type') == 'D') {
            // TODO: make these and the ones in wikiplugin_trackerFilter_get_filters actually return accessible items
            // i.e. if I am not able to see an item, I should not see its value in the filter as well (WYSIWYCA problem)
            $all = TikiLib::lib('trk')->list_tracker_field_values($this->getTrackerDefinition()->getConfiguration('trackerId'), $this->getFieldId());
            foreach ($all as $val) {
                if (! isset($possibilities[$val])) {
                    $possibilities[$val] = $val;
                }
            }
        }
        $possibilities['-Blank (no data)-'] = tr('-Blank (no data)-');

        $filters->addNew($permName, 'dropdown')
            ->setLabel($name)
            ->setControl(new Tracker\Filter\Control\DropDown("tf_{$permName}_dd", $possibilities))
            ->setApplyCondition(function ($control, Search_Query $query) use ($baseKey) {
                $value = $control->getValue();

                if ($value === '-Blank (no data)-') {
                    $query->filterIdentifier('', $baseKey . '_text');
                } elseif ($value) {
                    $query->filterIdentifier($value, $baseKey);
                }
            });

        $filters->addNew($permName, 'multiselect')
            ->setLabel($name)
            ->setControl(new Tracker\Filter\Control\MultiSelect("tf_{$permName}_ms", $possibilities))
            ->setApplyCondition(function ($control, Search_Query $query) use ($permName, $baseKey) {
                $values = $control->getValues();

                if (! empty($values)) {
                    $sub = $query->getSubQuery("ms_$permName");
                    foreach ($values as $v) {
                        if ($v === '-Blank (no data)-') {
                            $sub->filterIdentifier('', $baseKey . '_text');
                        } elseif ($v) {
                            $sub->filterIdentifier((string) $v, $baseKey);
                        }
                    }
                }
            });

        return $filters;
    }

    public function isValid()
    {
        if ($this->getConfiguration('type') !== 'D') {
            $value = $this->getValue($this->getDefaultValue());
            $allValues = $value === '' ? [] : explode(',', $value);

            if (! empty($allValues)) {
                foreach ($allValues as $val) {
                    if (! in_array($val, array_keys($this->getPossibleItemValues()))) {
                        return tr('Value not available in options');
                    }
                }
            }
        }

        return true;
    }
}
