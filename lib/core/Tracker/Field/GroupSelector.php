
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * Handler class for GroupSelector
 *
 * Letter key: ~g~
 *
 */
class Tracker_Field_GroupSelector extends Tracker_Field_Abstract implements Tracker_Field_Exportable, Tracker_Field_Filterable
{
    public static function getTypes()
    {
        return [
            'g' => [
                'name' => tr('Group Selector'),
                'description' => tr('Allow a selection from a specified list of user groups.'),
                'help' => 'Group-selector',
                'prefs' => ['trackerfield_groupselector'],
                'tags' => ['advanced'],
                'default' => 'n',
                'params' => [
                    'autoassign' => [
                        'name' => tr('Auto-Assign'),
                        'description' => tr('Determines if any group should be automatically assigned to the field.'),
                        'filter' => 'int',
                        'options' => [
                            0 => tr('None'),
                            1 => tr('Creator'),
                            2 => tr('Modifier'),
                        ],
                        'legacy_index' => 0,
                    ],
                    'owner' => [
                        'name' => tr('Item Owner'),
                        'description' => tr('Field that determines permissions of the item when "Group can see their own items" is enabled for the tracker'),
                        'filter' => 'int',
                        'options' => [
                            0 => tr('No'),
                            1 => tr('Yes'),
                        ],
                        'default' => 0,
                    ],
                    'groupId' => [
                        'name' => tr('Group Filter'),
                        'description' => tr('Limit listed groups to those including the specified group.'),
                        'filter' => 'text',
                        'legacy_index' => 1,
                    ],
                    'userGroups' => [
                        'name' => tr('User Groups'),
                        'description' => tr('Show groups user belongs to instead of the ones user has permission to see.'),
                        'filter' => 'int',
                        'options' => [
                            0 => tr('No'),
                            1 => tr('Yes'),
                        ],
                        'default' => 0,
                        'legacy_index' => 4,
                    ],
                    'assign' => [
                        'name' => tr('Assign to the group'),
                        'description' => tr('For no auto-assigned field, the user (user selector if it exists, or user) will be assigned to the group and it will be his or her default group. The group must have the user choice setting activated.'),
                        'filter' => 'int',
                        'options' => [
                            0 => tr('None'),
                            1 => tr('Assign'),
                        ],
                        'default' => 0,
                        'legacy_index' => 2,
                    ],
                    'unassign' => [
                        'name' => tr('Unassign from previous selection'),
                        'description' => tr('When assign to the group is set, the user (user selector if it exists, or user) will be unassigned from the previously selected group (if any). That group must have the user choice setting activated.'),
                        'filter' => 'int',
                        'options' => [
                            0 => tr('None'),
                            1 => tr('Unassign'),
                        ],
                        'default' => 0,
                    ],
                    'notify' => [
                        'name' => tr('Email Notification'),
                        'description' => tr('Add selected group to group monitor the item. Group watches feature must be enabled.'),
                        'filter' => 'int',
                        'options' => [
                            0 => tr('No'),
                            1 => tr('Yes'),
                        ],
                        'legacy_index' => 3,
                    ]
                ],
            ],
        ];
    }

    public function getFieldData(array $requestData = [])
    {
        // $group is set to the default group in lib/setup/user_prefs.php
        global $group, $user;
        $usersLib = TikiLib::lib('user');

        $ins_id = $this->getInsertId();

        $data = [];
        $defGroup = $group;
        $userGroups = $usersLib->get_user_groups_inclusion($user);
        $perms = Perms::get('tracker', $this->getConfiguration('trackerId'));

        $groupId = $this->getOption('groupId');
        if (empty($groupId)) {
            if ($this->getOption('userGroups')) {
                $data['list'] = array_keys($userGroups);
                sort($data['list']);
            } else {
                $data['list'] = $usersLib->list_all_groups_with_permission();
            }
        } else {
            if (ctype_digit($groupId)) {
                $group_info = $usersLib->get_groupId_info($groupId);
                $data['list'] = $usersLib->get_including_groups($group_info['groupName']);
            } elseif ($usersLib->group_exists($groupId)) {
                $data['list'] = $usersLib->get_including_groups($groupId);
            }
        }

        // check the default group is one of the groups we are looking for
        if (! in_array($defGroup, $data['list'])) {
            // find the one in the list this user is in
            $includedGroups = array_intersect(array_keys($userGroups), $data['list']);
            if (empty($includedGroups) && ! $perms->admin_trackers) {
                // user not in any of the required groups, use the global default $group and warn
                $defGroup = $group;
                Feedback::warning(tr('User not in any of the required groups for GroupSelector field'));
            } elseif (count($includedGroups) === 1) {
                // just the one, easy
                $defGroup = array_shift($includedGroups);
            } else {
                // more than one?
                if (in_array($group, $includedGroups)) {
                    // use the user's default group if there
                    $defGroup = $group;
                } else {
                    $found = false;
                    foreach ($userGroups as $userGroup => $membership) {
                        if (in_array($userGroup, $includedGroups) && $membership === 'real') {
                            // use the first group this user is a real member of, not just included
                            $defGroup = $userGroup;
                            $found = true;
                            break;
                        }
                    }
                    if (! $found) {
                        // use the first one as a fall back
                        $defGroup = array_shift($includedGroups);
                    }
                }
            }
        }

        if (isset($requestData[$ins_id])) {
            if ($this->getOption('autoassign') < 1 || $perms->admin_trackers) {
                $data['value'] = in_array($requestData[$ins_id], $data['list']) ? $requestData[$ins_id] : '';
            } else {
                if ($this->getOption('autoassign') == 2) {
                    $data['defvalue'] = $defGroup;
                    $data['value'] = $defGroup;
                } elseif ($this->getOption('autoassign') == 1) {
                    $data['value'] = $defGroup;
                } else {
                    $data['value'] = '';
                }
            }
        } else {
            $data['defvalue'] = $defGroup;
            $data['value'] = $this->getValue();
        }

        return $data;
    }

    public function renderInput($context = [])
    {
        return $this->renderTemplate('trackerinput/groupselector.tpl', $context);
    }

    public function handleSave($value, $oldValue)
    {
        global $prefs, $user;

        if ($this->getOption('autoassign') && is_null($oldValue)) {
            $definition = $this->getTrackerDefinition();
            if ($prefs['groupTracker'] == 'y' && $definition->isEnabled('autoCreateGroup')) {
                $value = TikiLib::lib('trk')->groupName($definition->getInformation(), $this->getItemId());
            }
        }
        if ($this->getOption('assign')) {
            $creators = TikiLib::lib('trk')->get_item_creators($this->getConfiguration('trackerId'), $this->getItemId());
            if (empty($creators)) {
                $creators = [$user];
            }
            $ginfo = TikiLib::lib('user')->get_group_info($value);
            if ($this->getOption('unassign') && $oldValue) {
                $oldginfo = TikiLib::lib('user')->get_group_info($oldValue);
            }
            foreach ($creators as $creator) {
                if ($ginfo['userChoice'] == 'y') {
                    TikiLib::lib('user')->assign_user_to_group($creator, $value);
                    TikiLib::lib('user')->set_default_group($creator, $value);
                }
                if ($this->getOption('unassign') && $oldValue && $oldginfo['userChoice'] == 'y') {
                    TikiLib::lib('user')->remove_user_from_group($creator, $oldValue);
                }
            }
        }

        if ($this->getOption('notify') && $prefs['feature_group_watches'] == 'y') {
            $objectId = $this->getItemId();
            $watchEvent = 'tracker_item_modified';
            $objectType = 'tracker ' . $this->getConfiguration('trackerId');

            $tikilib = TikiLib::lib('tiki');
            $old_watches = $tikilib->get_groups_watching($objectId, $watchEvent, $objectType);

            foreach ($old_watches as $key => $group) {
                if ($group != $value) {
                    $tikilib->remove_group_watch($group, $watchEvent, $objectId, $objectType);
                }
            }

            if (! empty($value) && ! in_array($value, $old_watches)) {
                $trackerInfo = $this->getTrackerDefinition()->getInformation();
                $objectName = $trackerInfo['name'];
                $objectHref = 'tiki-view_tracker_item.php?trackerId=' . $this->getConfiguration('trackerId') . '&itemId=' . $this->getItemId();
                $tikilib->add_group_watch($value, $watchEvent, $objectId, $objectType, $objectName, $objectHref);
            }
        }

        return [
            'value' => $value,
        ];
    }

    public function getDocumentPart(Search_Type_Factory_Interface $typeFactory)
    {
        $baseKey = $this->getBaseKey();

        $value = $this->getValue();

        return [
            $baseKey => $typeFactory->identifier($value),
            "{$baseKey}_text" => $typeFactory->sortable($value),
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
            $baseKey => 'identifier',
            "{$baseKey}_text" => 'sortable'
        ];
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

    /**
     * Get user groups
     *
     * @return array
     */
    private function getApplicableUserGroups()
    {
        static $cache = [];
        $fieldId = $this->getConfiguration('fieldId');

        if (! isset($cache[$fieldId])) {
            $userGroups = TikiLib::lib('user')->get_groups();
        }
        $cache[$fieldId] = $userGroups['cant'] > 0 ? $userGroups['data'] : [];

        return $cache[$fieldId];
    }

    /**
     * Get tabular schema for user groups
     *
     * @return \Tracker\Tabular\Schema
     */
    public function getTabularSchema()
    {
        $schema = new Tracker\Tabular\Schema($this->getTrackerDefinition());
        $permName = $this->getConfiguration('permName');
        $name = $this->getConfiguration('name');

        $sourceUserGroups = $this->getApplicableUserGroups();

        $invertGroupName = array_flip(array_map(function ($item) {
            return $item['groupName'];
        }, $sourceUserGroups));

        $invertGroupId = array_flip(array_map(function ($item) {
            return $item['id'];
        }, $sourceUserGroups));

        $schema->addNew($permName, 'id')
            ->setLabel($name)
            ->addQuerySource('itemId', 'object_id')
            ->addQuerySource('usergroup_name', 'title')
            ->setRenderTransform(function ($value, $extra) use ($sourceUserGroups, $invertGroupName, $invertGroupId) {
                $extraUsergroupName = isset($extra['usergroup_name']) ? $extra['usergroup_name'] : '';
                if (
                    ! empty($value)
                    &&  isset($invertGroupName[$value])
                    && is_numeric($extraUsergroupName)
                ) {
                    return $value;
                }

                if (! empty($value) &&  isset($invertGroupId[$value])) {
                    return $value;
                }

                $itemId = isset($extra['itemId']) ? $extra['itemId'] : null;
                $invertKey = isset($invertGroupName[$extraUsergroupName]) ? $invertGroupName[$extraUsergroupName] : null;
                if (! isset($invertKey) && ! isset($itemId)) {
                    return '#invalid';
                }

                if (! isset($invertKey)) {
                    $trackerItem = TikiLib::lib('trk')->get_tracker_item($itemId);
                    $trackerItemInvert = array_flip(array_map(function ($item) use ($value) {
                        if ($item === $value) {
                            return $item;
                        }
                    }, $trackerItem));

                    if (isset($trackerItemInvert[$value]) && is_numeric($trackerItemInvert[$value])) {
                        $invertKey = isset($invertGroupName[$value]) ? $invertGroupName[$value] : null;
                    }
                }

                $userGroupId = ! empty($sourceUserGroups[$invertKey]['id']) ? $sourceUserGroups[$invertKey]['id'] : 0;
                if ($userGroupId > 0) {
                    return $userGroupId;
                }

                return '#invalid';
            })
            ->setParseIntoTransform(function (&$info, $value) use ($permName) {
                if ($value != '#invalid') {
                    $info['fields'][$permName] = $value;
                }
            })
        ;

        $schema->addNew($permName, 'name')
            ->setLabel($name)
            ->setRenderTransform(function ($value) {
                return $value;
            })
            ->setParseIntoTransform(function (&$info, $value) use ($permName) {
                if ($value != '#invalid') {
                    $info['fields'][$permName] = $value;
                }
            })
        ;

        return $schema;
    }
}