<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * Handler class for UserGroups
 *
 * Letter key: ~usergroups~
 *
 */
class Tracker_Field_UserGroups extends Tracker_Field_Abstract implements Tracker_Field_Filterable
{
    public static function getTypes()
    {
        return [
            'usergroups' => [
                'name' => tr('User Groups'),
                'description' => tr('Display the list of groups for the user associated with the tracker items.'),
                'help' => 'User-Groups',
                'prefs' => ['trackerfield_usergroups'],
                'tags' => ['advanced'],
                'default' => 'n',
                'params' => [
                    'directOnly' => [
                        'name' => tr('Show direct groups memberships only'),
                        'description' => tr('Do not show inherited/included groups'),
                        'filter' => 'int',
                        'options' => [
                            0 => tr('No'),
                            1 => tr('Yes'),
                        ],
                        'default' => 0,
                    ],
                    'specifyFields' => [
                        'name' => tr('Specify Fields'),
                        'description' => tr('Get the groups for users from these fields, not the "owner" user fields.'),
                        'separator' => '|',
                        'filter' => 'int',
                        'profile_reference' => 'tracker_field',
                        'parent' => 'input[name=trackerId]',
                        'parentkey' => 'tracker_id',
                        'sort_order' => 'position_nasc',
                    ],
                ],
            ],
        ];
    }

    public function getFieldData(array $requestData = [])
    {
        $itemId = $this->getItemId();

        $value = [];

        if ($itemId) {
            $fields = $this->getOption('specifyFields');

            if (empty($fields) or empty(array_filter($fields))) {
                $itemUsers = $this->getTrackerDefinition()->getItemUsers($itemId);
            } else {
                $trackerId = $this->getConfiguration('trackerId');
                $trackerlib = TikiLib::lib('trk');
                $itemUsers = array_map(
                    function ($fieldId) use ($trackerId, $itemId, $trackerlib) {
                        $owners = $trackerlib->get_item_value($trackerId, $itemId, $fieldId);
                        return $trackerlib->parse_user_field($owners);
                    },
                    $fields
                );

                $itemUsers = call_user_func_array('array_merge', $itemUsers);
            }

            if (! empty($itemUsers)) {
                $tikilib = TikiLib::lib('tiki');
                foreach ($itemUsers as $itemUser) {
                    $value = array_merge($value, array_diff(
                        $tikilib->get_user_groups($itemUser, ! $this->getOption('directOnly')),
                        ['Registered', 'Anonymous']
                    ));
                }
            }
            $value = array_unique(array_filter($value));

            // add Registered back in if the user exists and is in no other groups
            if (empty($value) && ! empty($itemUsers)) {
                $value = ['Registered'];
            }
            natcasesort($value);
        }

        return [
            'value' => implode(',', $value),
            'groups' => $value
        ];
    }

    public function renderInput($context = [])
    {
        return $this->renderOutput($context);
    }

    public function renderOutput($context = [])
    {
        return $this->renderTemplate('trackeroutput/usergroups.tpl', $context);
    }

    public function renderDiff($context = [])
    {
        if (isset($context['oldValue'])) {
            $context['renderedOldValue'] = $context['oldValue'];
        }
        return parent::renderDiff($context);
    }

    public function watchCompare($old, $new)
    {
        // TODO properly
        return '';
    }

    public function getDocumentPart(Search_Type_Factory_Interface $typeFactory)
    {
        $baseKey = $this->getBaseKey();
        $data = $this->getFieldData();
        $listtext = implode(' ', $data['groups']);

        return [
            $baseKey => $typeFactory->multivalue($data['groups']),
            "{$baseKey}_text" => $typeFactory->plaintext($listtext),
        ];
    }

    public function getProvidedFields()
    {
        $baseKey = $this->getBaseKey();
        return [$baseKey, "{$baseKey}_text"];
    }

    public function getProvidedFieldTypes()
    {
        $baseKey = $this->getBaseKey();
        return [
            $baseKey => 'multivalue',
            "{$baseKey}_text" => 'plaintext'
        ];
    }

    public function getGlobalFields()
    {
        $baseKey = $this->getBaseKey();
        return [$baseKey => true];
    }

    public function getFilterCollection()
    {
        $userlib = TikiLib::lib('user');
        $groups = $userlib->list_all_groups_with_permission();
        $groups = $userlib->get_group_info($groups);

        $possibilities = [];
        foreach ($groups as $group) {
            $possibilities[$group['groupName']] = $group['groupName'];
        }
        $possibilities['-Blank (no data)-'] = tr('-Blank (no data)-');

        $filters = new Tracker\Filter\Collection($this->getTrackerDefinition());
        $permName = $this->getConfiguration('permName');
        $name = $this->getConfiguration('name');
        $baseKey = $this->getBaseKey();

        $filters->addNew($permName, 'dropdown')
            ->setLabel($name)
            ->setControl(new Tracker\Filter\Control\DropDown("tf_{$permName}_dd", $possibilities))
            ->setApplyCondition(function ($control, Search_Query $query) use ($baseKey) {
                $value = $control->getValue();

                if ($value === '-Blank (no data)-') {
                    $query->filterIdentifier('', $baseKey . '_text');
                } elseif ($value) {
                    $query->filterMultivalue('"' . ((string) $value) . '"', $baseKey);
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
                            $sub->filterMultivalue('"' . ((string) $v) . '"', $baseKey);
                        }
                    }
                }
            });

        return $filters;
    }
}
