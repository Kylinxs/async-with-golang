
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

/**
 * Current syntax - filters and display formatting support
 * {KANBAN(boardTrackerId=9 title=taskTask description=taskDescription column=taskResolutionStatus order=taskPriority columnValues=",Unsorted cards,5" swimlane=taskJob swimlaneValues=",Unsorted cards" )}
 *   {filter field="tracker_field_taskPriority" editable="content"}
 *   {display name="tracker_field_taskTask" format="objectlink"}
 *   This will be ignored...
 * {KANBAN}
 */
function wikiplugin_kanban_info(): array
{
    return [
        'name' => tr('Kanban'),
        'documentation' => 'PluginKanban',
        'description' => tr(''),
        'prefs' => ['wikiplugin_kanban'],
        'format' => 'html',
        'iconname' => 'th',
        'introduced' => 25,
        'params' => [
            'boardTrackerId' => [
                'name' => tr('Tracker ID'),
                'description' => tr('Id of the tracker the board is a partial representation of'),
                'since' => '24.0',
                'required' => true,
                'filter' => 'int',
                'profile_reference' => 'tracker',
            ],
            'title' => [
                'name' => tr('Card title field'),
                'description' => tr('Tracker field containing the inline editable text shown on each card.'),
                'hint' => tr('e.g. "kanbanTitle"'),
                'since' => '25.0',
                'required' => true,
                'filter' => 'word',
            ],
            'description' => [
                'name' => tr('Card description field'),
                'description' => tr('Optional text shown below the title on each card.'),
                'hint' => tr('e.g. "kanbanDescription"'),
                'since' => '25.0',
                'required' => false,
                'filter' => 'word',
            ],
            'column' => [
                'name' => tr('Column (state) field'),
                'description' => tr('Tracker field representing the columns, usually a dropdown list with options such as "Wishes", "Work" and "Done".'),
                'hint' => tr('e.g. "kanbanColumns"'),
                'since' => '25.0',
                'required' => true,
                'filter' => 'word',
            ],
            'columnValues' => [
                'name' => tr('Column acceptable values and configuration'),
                'description' => tr('For the tracker field mapped in "column", defines for each column the value a tracker item must have for that field, as well as the label displayed as the column header and the WiP limit for that column. Implicitely defines the number of columns and in which order they are shown; You can skip values so they are not part of the board (and you typically do, if only to eventually archive done cards).

The parameter is and array of colon separated values, each containing a coma separated arguments configuring the column.

In order, the configuration represent the:

1) Mandatory. The value the mapped field must have in the tracker item for the card to be shown in the matching column.
2) Optional. If present and not "null", the text to be displayed as the column header instead of the normal tracker field label for the value above. (For example "Done" instead of "Closed")
3) Optional. If present and not "null", the WiP (Work in Progress) limit for the cards in the column. In "null", there is no limit for the number of cards in the column. Typically you will use null for the first and last column.

null or nothing between the comas means the parameter is not set. Necessary since the arguments are positional.

So for example:
someValue,someAlternateTextToDisplay,null:someOtherValue,,4

Means the board would have two colums, the first column would be titled "someAlternateTextToDisplay" containing cards with the value "someValue" for the mapped field and no limit to the number of cards. The second column would have cards with "someOtherValue" for the mapped field, with whatever the label is for that value in the field definition, and the column would be highlighted red if there is more than 4 cards. No card with any other value would be anywhere on the board.

To allow empty values, include a field with an empty value (ex: someValue:someOtherValue:,Unsorted cards)

If the whole parameter is absent (not recommended), all possible field values will be used to generate columns (except the empty value).

                '),
                'hint' => tr('e.g. "someValue,someAlternateTextToDisplay,null:someOtherValue,,4"'),
                'since' => '25.0',
                'required' => false,
                'filter' => 'text',
                'separator' => ':'
            ],
            'order' => [
                'name' => tr('Card relative order field'),
                'description' => tr('Sort order for cards within a cell.  Must be a numeric field.  You will have to create it if the board represents an existing tracker.  It is not meant to be displayed to the user, or represent something global like "priority" (that would make no sense on a partial representation).  It merely means that the card is displayed above any card wifh lower value, and below any card with a higher one if displayed in the same cell.  When a card is moved board will halve the value of the two surrounding cards to compute the new value.'),
                'hint' => tr('e.g. "kanbanOrder"'),
                'since' => '25.0',
                'required' => true,
                'filter' => 'word',
            ],
            'swimlane' => [
                'name' => tr('Swimlane (row) field'),
                'description' => tr('Tracker field representing the "rows" or "swimlanes" of the board. Can be any field with discrete values.  Usually represents a client, a project, or a team member.  
                
                By default, all tracker items with that field set to a valid values will be shown on the board. To allow empty values, see swimlaneValues.

                Note:  A kanban board can have multiple rows, but these rows aren\'t independent, they share the same possible States and Wip limits.  If what you want is completely independent "rows", create two boards on the same tracker, with different filters.'),
                'hint' => tr('e.g. "kanbanSwimlanes'),
                'since' => '25.0',
                'required' => false,
                'filter' => 'word',
            ],
            'swimlaneValues' => [
                'name' => tr('Swimlanes acceptable values and configuration'),
                'description' => tr('Similar to columnValues, except there is no WiP limit.

                To allow empty values, include a field with an empty value (ex: someValue:someOtherValue:,Unsorted cards).  An aditional swimlane will be included for empty values.

                If the parameter is present but only contains the empty value (ex: ,Unsorted cards), all possible field values will be used to generate swimlanes, and an aditional swimlane will be included for empty values.
                '),
                'hint' => tr('e.g. "someValue,someAlternateTextToDisplay:someOtherValue"'),
                'since' => '25.0',
                'required' => false,
                'filter' => 'text',
                'separator' => ':'
            ],
        ],
    ];
}

function _map_field($fieldHandler, string $fieldValuesParamName, $fieldValuesParam, string $fieldPermName, array $fieldDefaultConfig)
{
    //echo '<pre>Field';print_r($fieldHandler->getFieldDefinition());echo '</pre>';
    if (! $fieldHandler instanceof Tracker_Field_EnumerableInterface) {
        throw new TypeError(tra('The tracker field "%0" selected in parameter is of a type that is not enumerable (does not implement Tracker_Field_EnumerableInterface)', '', false, [
            $fieldPermName
        ]));
    }
    if ($fieldHandler->canHaveMultipleValues()) {
        throw new TypeError(tra('The tracker field "%0" selected in parameter is configured to allow multiple values.  This is not mappable in a kanban board', '', false, [
            $fieldPermName
        ]));
    }
    $fieldValuesMap = $fieldHandler->getPossibleItemValues();

    //echo '<pre>Possible item values';print_r($fieldValuesMap);echo '</pre>';
    $fieldInfo = [];

    $appendAllPossibleFieldValues = false;
    if (! $fieldValuesParam) {
        $appendAllPossibleFieldValues = true;
    } elseif (count($fieldValuesParam) === 1) {
        $fieldParamsArray = explode(',', $fieldValuesParam[0]);
        $fieldValue = trim($fieldParamsArray[0]);
        if ($fieldValue === '') {
            //print_r("We have a single field in the configuration, and it's the empty value");
            $appendAllPossibleFieldValues = true;
        }
    }

    if ($appendAllPossibleFieldValues) {
        //Get values from all the possible field values
        foreach ($fieldValuesMap as $value => $label) {
            $fieldInfo[$value] = array_merge($fieldDefaultConfig, ['title' => $label, 'value' => $value]);
        }
        //echo'<pre>';print_r($fieldInfo);echo '</pre>';
    }

    foreach ($fieldValuesParam as $key => $fieldParams) {
        $fieldParamsArray = explode(',', $fieldParams);

        $fieldValue = trim($fieldParamsArray[0]);
        if ($fieldValue !== '' && ! $fieldValuesMap[$fieldValue]) {
            throw new TypeError(tra('Column value "%0" specified in parameter "%1=%2" is not found in tracker field "%3".  Possible values are %4', '', false, [
                $fieldValue,
                $fieldValuesParamName,
                implode(':', $fieldValuesParam),
                $fieldPermName,
                implode(',', array_keys($fieldValuesMap))
            ]));
        }
        //echo '<pre>';print_r($fieldValue);echo '</pre>';
        if ($fieldValue !== '') {
            $fieldData = ['title' => $fieldValuesMap[$fieldValue], 'value' => $fieldValue];
        } else {
            $fieldData = ['title' => tra('Empty values'), 'value' => $fieldValue];
        }

        $fieldInfo[$fieldValue] = array_merge($fieldDefaultConfig, $fieldData);
        //Override column label
        if (isset($fieldParamsArray[1]) && $fieldParamsArray[1] !== 'null') {
            $fieldInfo[$fieldValue]['title'] = trim($fieldParamsArray[1]);
        }
        //wip limit
        if (isset($fieldParamsArray[2]) && $fieldParamsArray[2] !== 'null') {
            if (! is_numeric($fieldParamsArray[2])) {
                throw new TypeError(tra('Wip limit value "%0" specified in parameter "%1=%2" is not numeric', '', false, [
                    $fieldParamsArray[2],
                    $fieldValuesParamName,
                    implode(':', $fieldValuesParam)
                ]));
            }
            $wipValue = intval($fieldParamsArray[2]);
            $fieldInfo[$fieldValue]['wip'] = $wipValue;
        }
    }

    //echo '<pre>_map_field returning:';print_r($fieldInfo);echo '</pre>';
    return $fieldInfo;
}
function wikiplugin_kanban(string $data, array $params): WikiParser_PluginOutput
{
    global $user, $prefs;
    static $id = 0;

    if ($prefs['auth_api_tokens'] !== 'y') {
        return WikiParser_PluginOutput::userError(tr('Security -> API access is disabled but Kanban plugin needs it.'));
    }

    //set defaults
    $plugininfo = wikiplugin_kanban_info();
    $defaults = [];
    foreach ($plugininfo['params'] as $key => $param) {
        $defaults[$key] = $param['default'] ?? null;
    }
    $params = array_merge($defaults, $params);

    $jit = new JitFilter($params);

    // Begin mapping the fields
    $trackerId = $jit->boardTrackerId->int();
    if (! $trackerId) {
        return WikiParser_PluginOutput::userError(tr('Tracker not specified in param "boardTrackerId".'));
    }

    $mappedTrackerDefinition = Tracker_Definition::get($jit->boardTrackerId->int());
    if (! $mappedTrackerDefinition) {
        return WikiParser_PluginOutput::userError(tr('Tracker not found.'));
    }

    $boardFields = [
        'title' => $jit->title->word(),
        'description' => $jit->description->word(),
        'column' => $jit->column->word(),
        'order' => $jit->order->word(),
        'swimlane' => $jit->swimlane->word(),
    ];

    foreach ($boardFields as $key => $field) {
        if (! $field) {
            return WikiParser_PluginOutput::userError(tr('Param "%0" is missing', $key));
        }
        $fieldDef = $mappedTrackerDefinition->getFieldFromPermName($field);
        if (! $fieldDef) {
            return WikiParser_PluginOutput::userError(tra('Tracker field with permName "%0" not found for param "%1".  Possible fields are %2', '', false, [
                $field,
                $key,
                implode(',', array_column($mappedTrackerDefinition->getFields(), 'permName'))
            ]));
        }
        $boardFields[$key] = $fieldDef;
    }

    $fieldFactory = $mappedTrackerDefinition->getFieldFactory();
    try {
        $columnsHandler = $fieldFactory->getHandler($boardFields['column']);
        $columnFieldPermName = $boardFields['column']['permName'];
        $columnsInfo = _map_field(
            $columnsHandler,
            'columnValues',
            $jit->columnValues->text(),
            $columnFieldPermName,
            [
                'wip' => null
            ],
            true
        );


        $swimlanesHandler = $fieldFactory->getHandler($boardFields['swimlane']);
        $swimlaneFieldPermName = $boardFields['swimlane']['permName'];
        $swimlanesInfo = _map_field(
            $swimlanesHandler,
            'swimlaneValues',
            $jit->swimlaneValues->text(),
            $swimlaneFieldPermName,
            [
                'wip' => null
            ],
            true
        );
    } catch (TypeError $e) {
        return WikiParser_PluginOutput::userError($e);
    }


    //echo '<pre>';print_r($columnsInfo);echo '</pre>';
    //END mapping the fields

    //Begin mapping the cards
    $query = new Search_Query();
    $query->filterType('trackeritem');
    $query->filterContent((string)$jit->boardTrackerId->int(), 'tracker_id');
    //print_r(array_keys($swimlanesInfo));

    //Filter the cards
     //We only filter the swimlane or column field values if we don't allow empty values. Search_Query cannot include specific values plus the empty ones.
    if (
        $jit->columnValues->text()
        &&
        ! array_key_exists('', $columnsInfo)
    ) {
        foreach (array_keys($columnsInfo) as $index => $fieldValue) {
            $query->filterContent(implode(' OR ', array_keys($columnsInfo)), 'tracker_field_' . $columnFieldPermName);
        }
    }
    if (
        $jit->swimlaneValues->text() &&
        ! array_key_exists('', $swimlanesInfo)
    ) {
        foreach (array_keys($swimlanesInfo) as $index => $fieldValue) {
            $query->filterContent(implode(' OR ', array_keys($swimlanesInfo)), 'tracker_field_' . $swimlaneFieldPermName);
        }
    }

    $unifiedsearchlib = TikiLib::lib('unifiedsearch');
    $unifiedsearchlib->initQuery($query);

    $matches = WikiParser_PluginMatcher::match($data);

    $builder = new Search_Query_WikiBuilder($query);
    $builder->apply($matches);

    if (! $index = $unifiedsearchlib->getIndex()) {
        return WikiParser_PluginOutput::userError(tr('Unified search index not found.'));
    }

    $result = [];
    foreach ($query->scroll($index) as $row) {
        $result[] = $row;
    }

    $result = Search_ResultSet::create($result);
    $result->setId('wpkanban-' . $id);

    $resultBuilder = new Search_ResultSet_WikiBuilder($result);
    $resultBuilder->apply($matches);

    $data .= '{display name="object_id"}';
    $plugin = new Search_Formatter_Plugin_ArrayTemplate($data);
    $usedFields = array_keys($plugin->getFields());

    foreach ($boardFields as $key => $field) {
        if (! in_array('tracker_field_' . $field['permName'], $usedFields) && ! in_array($field['permName'], $usedFields)) {
            if ($field['type'] == 'e') {
                $data .= '{display name="tracker_field_' . $field['permName'] . '" format="categorylist" singleList="y" separator=" "}';
            } else {
                $data .= '{display name="tracker_field_' . $field['permName'] . '" default=" "}';
            }
        }
    }
    $objectIdField = ['object_id' => ['permName' => 'object_id']];

    $plugin = new Search_Formatter_Plugin_ArrayTemplate($data);
    $plugin->setFieldPermNames(array_merge($boardFields, $objectIdField));

    $builder = new Search_Formatter_Builder();
    $builder->setId('wpkanban-' . $id);
    $builder->setCount($result->count());
    $builder->apply($matches);
    $builder->setFormatterPlugin($plugin);

    $formatter = $builder->getFormatter();


    $entries = $formatter->getPopulatedList($result, false);
    $entries = $plugin->renderEntries($entries);

    //echo '<pre>TrackerQueryResults:\n';print_r($entries);echo '</pre>';
    $boardCards = [];


    $caslAbilities = []; //Spec at https://casl.js.org/
    $trackerPerms = Perms::get(['type' => 'tracker', 'object' => $trackerId]);

    if ($trackerPerms['tiki_p_create_tracker_items']) {
        //We need this to check field permissions.
        $trackerItem = Tracker_Item::newItem($trackerId);
        $updatableFields = [];
        foreach ($boardFields as $field) {
            if ($trackerItem->canModifyField($field['fieldId'])) {
                $updatableFields[] = $field['permName'];
            }
        }
        if (count($updatableFields) == 0) {
            $updatableFields = null;
        }
        $caslAbilities[] =
            [
                'action' => 'create',
                'subject' => 'Tracker_Item',
                'fields' => $updatableFields
            ];
    }
    foreach ($entries as $row) {
        //echo '<pre>ROW:';print_r($row);echo '</pre>';

        //$trackerItem = Tracker_Item::fromInfo($row);
        //The following will cause SQL query inside a loop, but the above just doesn't work right.   We really need a proper query engine...
        $trackerItem = Tracker_Item::fromId($row['object_id']);
        $updatableFields = [];
        foreach ($boardFields as $field) {
            if ($trackerItem->canModifyField($field['fieldId'])) {
                $updatableFields[] = $field['permName'];
            }
        }
        $trackerItemData = $trackerItem->getData();

        //echo '<pre>trackerItemData:';print_r($trackerItemData);echo '</pre>';

        //We don't use $row[$swimlaneFieldPermName], because it's the title, not the value
        $swimlaneValue = $trackerItemData['fields'][$swimlaneFieldPermName];

        //We don't use $row[$columnFieldPermName], because it's the title, not the value
        $columnValue = $trackerItemData['fields'][$columnFieldPermName];

        //Both Search_Query and Tracker_Query do not allow proper ANDing of OR groups, so we have to do some horrible filtering here in PHP.
        //Furthermore, we have to wait this late in the loop because Search_Query does not give us the proper field values

        //Filter the cards ,AGAIN!
        if ($jit->columnValues->text()) {
            if (! in_array($columnValue, array_keys($columnsInfo))) {
                /*print_r("SKIP card missing value in column map");
                print_r(array_keys($columnsInfo));*/
                continue;  //Skip tracker items that have fields with values not in the mapped enumerable fields
            }
        }
        if ($jit->swimlaneValues->text()) {
            if (! in_array($swimlaneValue, array_keys($swimlanesInfo))) {
                /*print_r("<pre>SKIP card missing value in swimlane map");
                print_r($swimlaneValue);
                print_r($swimlanesInfo);
                print_r("</pre>");*/
                continue;  //Skip tracker items that have fields with values not in the mapped enumerable fields
            }
        }

        $caslAbilities[] =
            [
                'action' => 'update',
                'subject' => 'Tracker_Item',
                'fields' => $updatableFields,
                //This explicit cast is required because getId aparently returns strings or int depending on php version or some other factor
                'conditions' => ['itemId' => (int)$trackerItem->getId()]
            ];

        $caslAbilities[] =
            [
                'action' => 'delete',
                'subject' => 'Tracker_Item',
                'conditions' => ['itemId' => (int)$trackerItem->getId()]
            ];

        //if ($perms['tiki_p_create_tracker_items'] == 'n' && empty($itemId)) {

        $boardCards[] = [
            'id' => (int)$row['object_id'],
            'title' => $row[$boardFields['title']['permName']],
            'description' => $row[$boardFields['description']['permName']],
            'row' => $swimlaneValue,
            'column' => $columnValue,
            'sortOrder' => $row[$boardFields['order']['permName']],
        ];
    }

    $token = TikiLib::lib('api_token')->createToken([
        'type' => 'kanban',
        'user' => $user,
        'expireAfter' => strtotime("+1 hour"),
    ]);

    $smarty = TikiLib::lib('smarty');
    $kanbanData =
        [
            'id' => 'kanban' . ++$id,
            'accessToken' => $token['token'],
            'trackerId' => $jit->boardTrackerId->int(),
            'xaxisField' => $jit->column->word(),
            'yaxisField' => $jit->order->word(),
            'swimlaneField' => $jit->swimlane->word(),
            'titleField' => $jit->title->word(),
            'descriptionField' => $jit->description->word(),
            'columns' => $columnsInfo,
            'rows' => $swimlanesInfo,
            'cards' => $boardCards,
            'user' => $user,
            'CASLAbilityRules' => $caslAbilities
        ];
    //echo ("<pre>");var_dump($kanbanData);echo ("</pre>");
    $smarty->assign(
        'kanbanData',
        $kanbanData
    );
    TikiLib::lib('header')
        ->add_js_module('
        import \'@vue-mf/root-config\';
        import \'@vue-mf/kanban\';
    ');
    $out = "";
    //$out = str_replace(['~np~', '~/np~'], '', $formatter->renderFilters());

    $out .= $smarty->fetch('wiki-plugins/wikiplugin_kanban.tpl');

    return WikiParser_PluginOutput::html($out);
}


function wikiplugin_kanban_format_list($handler)
{
    $fieldData = $handler->getFieldData();
    echo '<pre>';
    print_r($fieldData);
    echo '</pre>';
    $list = $formatted = [];
    if ($handler->getConfiguration('type') === 'd') {
        $list = $fieldData['possibilities'];
    } elseif ($handler->getConfiguration('type') === 'e') {
        foreach ($fieldData['list'] as $categ) {
            $list[$categ['categId']] = $categ['name'];
        }
    }
    $non_numeric_keys = array_filter(array_keys($list), function ($key) {
        return ! is_numeric($key);
    });
    $realKey = 1;
    foreach ($list as $key => $val) {
        if ($non_numeric_keys) {
            $id = $realKey++;
        } else {
            $id = $key;
        }
        $formatted[] = ['id' => $id, 'title' => $val, 'value' => $key];
    }
    return $formatted;
}