
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Services_Tracker_Controller
{
    /**
     * @var Services_Tracker_Utilities
     */
    private $utilities;

    public function setUp()
    {
        global $prefs;
        $this->utilities = new Services_Tracker_Utilities();

        Services_Exception_Disabled::check('feature_trackers');
    }

    /**
     * Returns the section for use with certain features like banning
     * @return string
     */
    public function getSection()
    {
        return 'trackers';
    }

    public function action_view($input)
    {
        if ($input->id->int()) {
            $item = Tracker_Item::fromId($input->id->int());
        } elseif ($input->itemId->int()) {
            $item = Tracker_Item::fromId($input->itemId->int());
        } else {
            $item = null;
        }

        if (! $item) {
            throw new Services_Exception_NotFound(tr('Item not found'));
        }

        if (! $item->canView()) {
            throw new Services_Exception_Denied(tr('Permission denied'));
        }

        $definition = $item->getDefinition();

        $fields = $item->prepareOutput(new JitFilter([]));

        $info = TikiLib::lib('trk')->get_item_info($item->getId());

        return [
            'title' => TikiLib::lib('object')->get_title('trackeritem', $item->getId()),
            'format' => $input->format->word(),
            'itemId' => $item->getId(),
            'trackerId' => $definition->getConfiguration('trackerId'),
            'fields' => $fields,
            'canModify' => $item->canModify(),
            'item_info' => $info,
            'info' => $info,
        ];
    }

    public function action_add_field($input)
    {
        $modal = $input->modal->int();
        $trackerId = $input->trackerId->int();

        $perms = Perms::get('tracker', $trackerId);
        if (! $perms->admin_trackers) {
            throw new Services_Exception_Denied(tr('Reserved for tracker administrators'));
        }

        $trklib = TikiLib::lib('trk');
        $definition = Tracker_Definition::get($trackerId);

        if (! $definition) {
            throw new Services_Exception_NotFound();
        }

        $name = $input->name->text();

        $permName = $trklib::generatePermName($definition, $input->permName->word());

        $type = $input->type->text();
        $description = $input->description->text();
        $wikiparse = $input->description_parse->int();
        $adminOnly = $input->adminOnly->int();
        $fieldId = 0;

        $types = $this->utilities->getFieldTypes();

        if (empty($type)) {
            $type = 't';
        }

        if (! isset($types[$type])) {
            throw new Services_Exception(tr('Type does not exist'), 400);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $input->type->word()) {
            if (empty($name)) {
                throw new Services_Exception_MissingValue('name');
            }

            if ($definition->getFieldFromNameMaj($name)) {
                Feedback::error(tr('This field name %0 is already used in this tracker', $name));
                return Services_Utilities::closeModal();
            }

            if ($definition->getFieldFromPermName($permName)) {
                Feedback::error(tr('This permanent name %0 is already used', $permName));
                return Services_Utilities::closeModal();
            }

            $fieldId = $this->utilities->createField(
                [
                    'trackerId' => $trackerId,
                    'name' => $name,
                    'permName' => $permName,
                    'type' => $type,
                    'description' => $description,
                    'descriptionIsParsed' => $wikiparse,
                    'isHidden' => $adminOnly ? 'y' : 'n',
                ]
            );

            if ($input->submit_and_edit->none() || $input->next->word() === 'edit') {
                return [
                    'FORWARD' => [
                        'action' => 'edit_field',
                        'fieldId' => $fieldId,
                        'trackerId' => $trackerId,
                        'modal' => $modal,
                    ],
                ];
            }
        }

        return [
            'title' => tr('Add Field'),
            'trackerId' => $trackerId,
            'fieldId' => $fieldId,
            'name' => $name,
            'permName' => $permName,
            'type' => $type,
            'types' => $types,
            'description' => $description,
            'descriptionIsParsed' => $wikiparse,
            'modal' => $modal,
            'fieldPrefix' => $definition->getConfiguration('fieldPrefix'),
        ];
    }

    public function action_list_fields($input)
    {
        global $prefs;

        $trackerId = $input->trackerId->int();
        $perms = Perms::get('tracker', $trackerId);

        if (! $perms->view_trackers) {
            throw new Services_Exception_Denied(tr("You don't have permission to view the tracker"));
        }

        $definition = Tracker_Definition::get($trackerId);

        if (! $definition) {
            throw new Services_Exception_NotFound();
        }

        $fields = $definition->getFields();
        $types = $this->utilities->getFieldTypes();
        $typesDisabled = [];

        if ($perms->admin_trackers) {
            $typesDisabled = $this->utilities->getFieldTypesDisabled();
        }

        $missing = [];
        $duplicates = [];

        foreach ($fields as $field) {
            if (! array_key_exists($field['type'], $types) && ! in_array($field['type'], $missing)) {
                $missing[] = $field['type'];
            }
            if ($prefs['unified_engine'] === 'elastic') {
                $tracker_fields = TikiLib::lib('tiki')->table('tiki_tracker_fields');
                $dupeFields = $tracker_fields->fetchAll(
                    [
                        'fieldId',
                        'trackerId',
                        'name',
                        'permName',
                        'type',
                    ],
                    [
                        'fieldId'  => $tracker_fields->not($field['fieldId']),
                        'type'     => $tracker_fields->not($field['type']),
                        'permName' => $field['permName'],
                    ]
                );
                if ($dupeFields) {
                    TikiLib::lib('smarty')->loadPlugin('smarty_modifier_sefurl');
                    foreach ($dupeFields as & $df) {
                        $df['message'] = tr('Warning: There is a conflict in permanent names, which can cause indexing errors.') .
                            '<br><a href="' . smarty_modifier_sefurl($df['trackerId'], 'trackerfields') . '">' .
                            tr(
                                'Field #%0 "%1" of type "%2" also found in tracker #%3 with perm name %4',
                                $df['fieldId'],
                                $df['name'],
                                $types[$df['type']]['name'],
                                $df['trackerId'],
                                $df['permName']
                            ) .
                            '</a>';
                    }
                    $duplicates[$field['fieldId']] = $dupeFields;
                }
            }
            if ($field['type'] == 'i' && $prefs['tracker_legacy_insert'] !== 'y') {
                Feedback::error(tr('You are using the image field type, which is deprecated. It is recommended to activate \'Use legacy tracker insertion screen\' found on the <a href="%0">trackers admin configuration</a> screen.', 'tiki-admin.php?page=trackers'));
            }
        }
        if (! empty($missing)) {
            Feedback::error(tr('Warning: Required field types not enabled: %0', implode(', ', $missing)));
        }

        return [
            'fields' => $fields,
            'types' => $types,
            'typesDisabled' => $typesDisabled,
            'duplicates' => $duplicates,
        ];
    }

    public function action_save_fields($input)
    {
        $trackerId = $input->trackerId->int();

        $perms = Perms::get('tracker', $trackerId);
        if (! $perms->admin_trackers) {
            throw new Services_Exception_Denied(tr('Reserved for tracker administrators'));
        }

        $definition = Tracker_Definition::get($trackerId);

        if (! $definition) {
            throw new Services_Exception_NotFound();
        }

        $hasList = false;
        $hasLink = false;

        $tx = TikiDb::get()->begin();

        $fields = [];
        foreach ($input->field as $key => $value) {
            $fieldId = (int) $key;
            $isMain = $value->isMain->int();
            $isTblVisible = $value->isTblVisible->int();

            $fields[$fieldId] = [
                'position' => $value->position->int(),
                'isTblVisible' => $isTblVisible ? 'y' : 'n',
                'isMain' => $isMain ? 'y' : 'n',
                'isSearchable' => $value->isSearchable->int() ? 'y' : 'n',
                'isPublic' => $value->isPublic->int() ? 'y' : 'n',
                'isMandatory' => $value->isMandatory->int() ? 'y' : 'n',
            ];

            $this->utilities->updateField($trackerId, $fieldId, $fields[$fieldId]);

            $hasList = $hasList || $isTblVisible;
            $hasLink = $hasLink || $isMain;
        }

        if (! $hasList) {
            Feedback::error(tr('Tracker contains no listed field, no meaningful information will be provided in the default list.'), true);
        }

        if (! $hasLink) {
            Feedback::error(tr('The tracker contains no field in the title, so no link will be generated.'), true);
        }

        $tx->commit();

        return [
            'fields' => $fields,
        ];
    }

    /**
     * @param JitFilter $input
     * @return array
     * @throws Services_Exception
     * @throws Services_Exception_Denied
     * @throws Services_Exception_DuplicateValue
     * @throws Services_Exception_NotFound
     */
    public function action_edit_field($input)
    {
        global $prefs;

        $trackerId = $input->trackerId->int();

        $perms = Perms::get('tracker', $trackerId);
        if (! $perms->admin_trackers) {
            throw new Services_Exception_Denied(tr('Reserved for tracker administrators'));
        }

        $fieldId = $input->fieldId->int();
        $definition = Tracker_Definition::get($trackerId);

        if (! $definition) {
            throw new Services_Exception_NotFound();
        }

        $field = $definition->getField($fieldId);
        if (! $field) {
            throw new Services_Exception_NotFound();
        }

        $types = $this->utilities->getFieldTypes();
        $typeInfo = $types[$field['type']];
        if ($prefs['tracker_change_field_type'] !== 'y') {
            if (empty($typeInfo['supported_changes'])) {
                $types = [];
            } else {
                $types = $this->utilities->getFieldTypes($typeInfo['supported_changes']);
            }
        }

        $encryption_keys = TikiLib::lib('encryption')->get_keys();

        $permName = $input->permName->word();
        if ($field['permName'] != $permName) {
            if ($definition->getFieldFromPermName($permName)) {
                throw new Services_Exception_DuplicateValue('permName', tr('This permanent name %0 is already used', $permName));
            }
        }
        $name = $input->name->word();
        if ($field['name'] != $name) {
            if ($definition->getFieldFromNameMaj($name)) {
                throw new Services_Exception_DuplicateValue('name', tr('This field name %0 is already used in this tracker', $name));
            }
        }

        if (strlen($permName) > Tracker_Item::PERM_NAME_MAX_ALLOWED_SIZE) {
            throw new Services_Exception(tr('Tracker Field permanent name cannot contain more than %0 characters', Tracker_Item::PERM_NAME_MAX_ALLOWED_SIZE), 400);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $input->name->text()) {
            $input->replaceFilters(
                [
                    'visible_by' => 'groupname',
                    'editable_by' => 'groupname',
                ]
            );
            $visibleBy = $input->asArray('visible_by', false);
            $editableBy = $input->asArray('editable_by', false);

            $options = $this->utilities->buildOptions(new JitFilter($input->option), $typeInfo);

            $trklib = TikiLib::lib('trk');
            $handler = $trklib->get_field_handler($field);
            if (! $handler) {
                throw new Services_Exception(tr('Field handler not found'), 400);
            }
            if (method_exists($handler, 'validateFieldOptions')) {
                try {
                    $params = $this->utilities->parseOptions($options, $typeInfo);
                    $handler->validateFieldOptions($params);
                } catch (Exception $e) {
                    throw new Services_Exception($e->getMessage(), 400);
                }
            }

            if (! empty($types)) {
                $type = $input->type->text();
                if ($field['type'] !== $type) {
                    if (! isset($types[$type])) {
                        throw new Services_Exception(tr('Type does not exist'), 400);
                    }
                    $oldTypeInfo = $typeInfo;
                    $typeInfo = $types[$type];
                    if (! empty($oldTypeInfo['supported_changes']) && in_array($type, $oldTypeInfo['supported_changes'])) {
                        // changing supported types should not clear all options but only the ones that are not available in the new type
                        $options = Tracker_Options::fromInput(new JitFilter($input->option), $oldTypeInfo);
                        $params = $options->getAllParameters();
                        foreach (array_keys($params) as $param) {
                            if (empty($typeInfo['params'][$param])) {
                                unset($params[$param]);
                            }
                        }
                        // convert underneath data if field type supports it
                        if (method_exists($handler, 'convertFieldTo')) {
                            $convertedOptions = $handler->convertFieldTo($type);
                            $params = array_merge($params, $convertedOptions);
                        }
                        // prepare options
                        $options = json_encode($params);
                    } else {
                        // clear options for unsupported field type changes
                        $options = json_encode([]);
                    }
                } elseif (method_exists($handler, 'convertFieldOptions')) {
                    $params = $this->utilities->parseOptions($options, $typeInfo);
                    $handler->convertFieldOptions($params);
                }
            } else {
                $type = $field['type'];
            }

            $rules = '';
            if ($input->conditions->text()) {
                $actions = json_decode($input->actions->text());
                $else = json_decode($input->else->text());
                // filter out empty defaults - TODO work out how to remove rules in Vue
                if ($actions->predicates[0]->target_id !== 'NoTarget' || $else->predicates[0]->target_id !== 'NoTarget') {
                    $conditions = json_decode($input->conditions->text());
                    $rules = json_encode([
                        'conditions' => $conditions,
                        'actions'    => $actions,
                        'else'       => $else,
                    ]);
                }
            }

            $data = [
                'name' => $input->name->text(),
                'description' => $input->description->text(),
                'descriptionIsParsed' => $input->description_parse->int() ? 'y' : 'n',
                'options' => $options,
                'validation' => $input->validation_type->word(),
                'validationParam' => $input->validation_parameter->none(),
                'validationMessage' => $input->validation_message->text(),
                'isMultilingual' => $input->multilingual->int() ? 'y' : 'n',
                'visibleBy' => array_filter(array_map('trim', $visibleBy)),
                'editableBy' => array_filter(array_map('trim', $editableBy)),
                'isHidden' => $input->visibility->alpha(),
                'errorMsg' => $input->error_message->text(),
                'permName' => $permName,
                'type' => $type,
                'rules' => $rules,
                'encryptionKeyId' => $input->encryption_key_id->int(),
                'excludeFromNotification' => $input->exclude_from_notification->int() ? 'y' : 'n',
                'visibleInViewMode' => $input->visible_in_view_mode->int() ? 'y' : 'n',
                'visibleInEditMode' => $input->visible_in_edit_mode->int() ? 'y' : 'n',
                'visibleInHistoryMode' => $input->visible_in_history_mode->int() ? 'y' : 'n',
            ];

            $submitted_keys = $input->keys();
            if (in_array('position', $submitted_keys)) {
                $data['position'] = $input->position->int();
            }
            foreach (['isTblVisible', 'isMain', 'isSearchable', 'isPublic', 'isMandatory'] as $key) {
                if (in_array($key, $submitted_keys)) {
                    $data[$key] = $input->$key->int() ? 'y' : 'n';
                }
            }

            $this->utilities->updateField(
                $trackerId,
                $fieldId,
                $data
            );

            // run field specific post save function
            $handler = TikiLib::lib('trk')->get_field_handler($field);
            if ($handler && method_exists($handler, 'handleFieldSave')) {
                $handler->handleFieldSave($data);
            }
        }

        array_walk($typeInfo['params'], function (&$param) use ($fieldId, $field) {
            if (isset($param['profile_reference'])) {
                $lib = TikiLib::lib('object');
                $param['selector_type'] = $lib->getSelectorType($param['profile_reference']);
                if (isset($param['parent'])) {
                    if (! preg_match('/[\[\]#\.]/', $param['parent'])) {
                        $param['parent'] = "#option-{$param['parent']}";
                    }
                } else {
                    $param['parent'] = null;
                }
                $param['parentkey'] = isset($param['parentkey']) ? $param['parentkey'] : null;
                $param['sort_order'] = isset($param['sort_order']) ? $param['sort_order'] : null;
                $param['format'] = isset($param['format']) ? $param['format'] : null;
                if ($param['selector_type'] === 'trackerfield' && $field['options_map']['mirrorField']) {
                    $param['searchfilter'] = ['object_id' => 'NOT ' . $fieldId];
                }
            } else {
                $param['selector_type'] = null;
            }
        });

        $validation_types = [
            '' => tr('None'),
            'captcha' => tr('CAPTCHA'),
            'distinct' => tr('Distinct'),
            'pagename' => tr('Page Name'),
            'password' => tr('Password'),
            'regex' => tr('Regular Expression (Pattern)'),
            'username' => tr('Username'),
        ];
        if ($definition->getConfiguration('tabularSync', false)) {
            $validation_types['remotelock'] = tr('Remote Lock');
        }

        $userlib = TikiLib::lib('user');
        $groups = $userlib->list_all_groups();
        $field['all_groups'] = $groups;

        $fields = $definition->getFields();
        if ($definition->getConfiguration('showStatus') === 'y') {
            $fields[] = [
                'type' => 'status',
                'fieldId' => 'status',
                'name' => tr('Item Status'),
                'rules' => null,
            ];
        }

        return [
            'title' => tr('Edit') . " " . tr('%0', $field['name']),
            'field' => $field,
            'info' => $typeInfo,
            'options' => $this->utilities->parseOptions($field['options'], $typeInfo),
            'validation_types' => $validation_types,
            'types' => $types,
            'permNameMaxAllowedSize' => Tracker_Item::PERM_NAME_MAX_ALLOWED_SIZE,
            'fields' => $fields,
            'encryption_keys' => $encryption_keys,
        ];
    }

    public function action_remove_fields($input)
    {
        $trackerId = $input->trackerId->int();

        $perms = Perms::get('tracker', $trackerId);
        if (! $perms->admin_trackers) {
            throw new Services_Exception_Denied(tr('Reserved for tracker administrators'));
        }

        $fields = $input->fields->int();

        $definition = Tracker_Definition::get($trackerId);

        if (! $definition) {
            throw new Services_Exception_NotFound();
        }

        foreach ($fields as $fieldId) {
            if (! $definition->getField($fieldId)) {
                throw new Services_Exception_NotFound();
            }
        }

        if (($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'DELETE') && $input->confirm->int()) {
            $trklib = TikiLib::lib('trk');
            $tx = TikiDb::get()->begin();
            foreach ($fields as $fieldId) {
                $trklib->remove_tracker_field($fieldId, $trackerId);
            }
            $tx->commit();

            return [
                'status' => 'DONE',
                'trackerId' => $trackerId,
                'fields' => $fields,
            ];
        } else {
            return [
                'trackerId' => $trackerId,
                'fields' => $fields,
            ];
        }
    }

    public function action_export_fields($input)
    {
        $trackerId = $input->trackerId->int();

        $perms = Perms::get('tracker', $trackerId);
        if (! $perms->admin_trackers) {
            throw new Services_Exception_Denied(tr('Reserved for tracker administrators'));
        }

        $fields = $input->fields->int();

        $definition = Tracker_Definition::get($trackerId);

        if (! $definition) {
            throw new Services_Exception_NotFound();
        }

        if ($fields) {
            $fields = $this->utilities->getFieldsFromIds($definition, $fields);
        } else {
            $fields = $definition->getFields();
        }

        $data = "";
        foreach ($fields as $field) {
            $data .= $this->utilities->exportField($field);
        }

        return [
            'title' => tr('Export Fields'),
            'trackerId' => $trackerId,
            'fields' => $fields,
            'export' => $data,
        ];
    }

    public function action_import_fields($input)
    {
        if (! Perms::get()->admin_trackers) {
            throw new Services_Exception_Denied(tr('Reserved for tracker administrators'));
        }

        $trackerId = $input->trackerId->int();
        $definition = Tracker_Definition::get($trackerId);

        if (! $definition) {
            throw new Services_Exception_NotFound();
        }

        $raw = $input->raw->none();
        $preserve = $input->preserve_ids->int();
        $last_position = $input->last_position->int();

        $data = TikiLib::lib('tiki')->read_raw($raw, $preserve);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (! $data) {
                throw new Services_Exception(tr('Invalid data provided'), 400);
            }

            $trklib = TikiLib::lib('trk');

            foreach ($data as $info) {
                $info['permName'] = $trklib::generatePermName($definition, $info['permName']);

                $this->utilities->importField($trackerId, new JitFilter($info), $preserve, $last_position);
            }
        }

        return [
            'title' => tr('Import Tracker Fields'),
            'trackerId' => $trackerId,
        ];
    }

    public function action_list_trackers($input)
    {
        // Return the ones user is allowed to view
        $trklib = TikiLib::lib('trk');
        return $trklib->list_trackers();
    }

    public function action_list_items($input)
    {
        // TODO : Eventually, this method should filter according to the actual permissions, but because
        //        it is only to be used for tracker sync at this time, admin privileges are just fine.

        if (! Perms::get()->admin_trackers) {
            throw new Services_Exception_Denied(tr('Reserved for tracker administrators'));
        }

        $trackerId = $input->trackerId->int();
        $offset = $input->offset->int();
        $maxRecords = $input->maxRecords->int();
        $status = $input->status->word();
        $format = $input->format->word();
        $modifiedSince = $input->modifiedSince->int();

        $definition = Tracker_Definition::get($trackerId);

        if (! $definition) {
            throw new Services_Exception_NotFound();
        }

        $items = $this->utilities->getItems(
            [
                'trackerId' => $trackerId,
                'status' => $status,
                'modifiedSince' => $modifiedSince,
            ],
            $maxRecords,
            $offset
        );

        if ($format !== 'raw') {
            foreach ($items as & $item) {
                $item = $this->utilities->processValues($definition, $item);
            }
        }

        return [
            'trackerId' => $trackerId,
            'offset' => $offset,
            'maxRecords' => $maxRecords,
            'result' => $items,
        ];
    }

    /**
     * @param JitFilter $input
     * @return mixed
     * @throws Services_Exception_Denied
     * @throws Services_Exception_NotFound
     */
    public function action_get_item_inputs($input)
    {
        $trackerId = $input->trackerId->int();
        $trackerName = $input->trackerName->text();
        $itemId = $input->itemId->int();
        $byName = $input->byName->bool();
        $defaults = $input->asArray('defaults');

        $this->trackerNameAndId($trackerId, $trackerName);

        $definition = Tracker_Definition::get($trackerId);

        if (! $definition) {
            throw new Services_Exception_NotFound();
        }

        $itemObject = Tracker_Item::newItem($trackerId);

        if (! $itemObject->canModify()) {
            throw new Services_Exception_Denied();
        }

        $query = Tracker_Query::tracker($byName ? $trackerName : $trackerId)
            ->itemId($itemId);

        if ($input > 0) {
            $query->byName();
        }
        if (! empty($defaults)) {
            $query->inputDefaults($defaults);
        }

        $inputs = $query
            ->queryInput();

        return $inputs;
    }

    public function action_clone_item($input)
    {
        global $prefs;

        Services_Exception_Disabled::check('tracker_clone_item');

        $trackerId = $input->trackerId->int();
        $definition = Tracker_Definition::get($trackerId);

        if (! $definition) {
            throw new Services_Exception_NotFound();
        }

        $itemId = $input->itemId->int();
        if (! $itemId) {
            throw new Services_Exception_Denied(tr('No item to clone'));
        }

        $itemObject = Tracker_Item::fromId($itemId);

        if (! $itemObject->canView()) {
            throw new Services_Exception_Denied(tr("The item to clone isn't visible"));
        }

        $newItem = Tracker_Item::newItem($trackerId);

        if (! $newItem->canModify()) {
            throw new Services_Exception_Denied(tr("You don't have permission to create new items"));
        }

        global $prefs;
        if ($prefs['feature_jquery_validation'] === 'y') {
            $_REQUEST['itemId'] = 0;    // let the validation code know this will be a new item
            $validationjs = TikiLib::lib('validators')->generateTrackerValidateJS(
                $definition->getFields(),
                'ins_',
                '',
                '',
                // not custom submit handler that is only needed when called by this service
                'submitHandler: function(form, event){return process_submit(form, event);}'
            );
            TikiLib::lib('header')->add_jq_onready('$("#cloneItemForm' . $trackerId . '").validate({' . $validationjs . $this->get_validation_options());
        }

        $itemObject->asNew();
        $itemData = $itemObject->getData($input);
        $processedFields = [];

        $id = 0;
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $itemObject = $this->utilities->cloneItem($definition, $itemData, $itemId);
            $id = $itemObject->getId();
            if ($id === false) {
                throw new Services_Exception_Denied(tr("There were errors cloning the item, please check error messages"));
            }

            $processedItem = $this->utilities->processValues($definition, $itemData);
            $processedFields = $processedItem['fields'];
        }

        // sets all fields for the tracker item with their value
        $processedFields = $itemObject->prepareInput($input);
        // fields that we want to change in the form. If
        $editableFields = $input->editable->none();
        // fields where the value is forced.
        $forcedFields = $input->forced->none();

        // if forced fields are set, remove them from the processedFields since they will not show up visually
        // in the form; they will be set up separately and hidden.
        if (! empty($forcedFields)) {
            foreach ($processedFields as $k => $f) {
                $permName = $f['permName'];
                if (isset($forcedFields[$permName])) {
                    unset($processedFields[$k]);
                }
            }
        }

        if (empty($editableFields)) {
            //if editable fields, show all fields in the form (except the ones from forced which have been removed).
            $displayedFields = $processedFields;
        } else {
            // if editableFields is set, only add the field if found in the editableFields array
            $displayedFields = [];
            foreach ($processedFields as $k => $f) {
                $permName = $f['permName'];
                if (in_array($permName, $editableFields)) {
                    $displayedFields[] = $f;
                }
            }
        }

        return [
            'title' => tr('Duplicate Item'),
            'trackerId' => $trackerId,
            'itemId' => $itemId,
            'created' => $id,
            'data' => $itemData['fields'],
            'fields' => $displayedFields,
            'forced' => $forcedFields,
        ];
    }

    public function action_insert_item($input)
    {
        $processedFields = [];

        $trackerId = $input->trackerId->int();

        if (! $trackerId) {
            return [
                'FORWARD' => ['controller' => 'tracker', 'action' => 'select_tracker'],
            ];
        }

        $trackerName = $this->trackerName($trackerId);
        $definition = Tracker_Definition::get($trackerId);

        if (! $definition) {
            throw new Services_Exception_NotFound();
        }

        $itemObject = Tracker_Item::newItem($trackerId);

        if (! $itemObject->canModify()) {
            throw new Services_Exception_Denied();
        }

        $fields = $input->fields->none();
        $forced = $input->forced->none();
        $processedFields = $itemObject->prepareInput($input);
        $suppressFeedback = $input->suppressFeedback->bool();
        $toRemove = [];

        if (empty($fields)) {
            $fields = [];
            foreach ($processedFields as $k => $f) {
                $permName = $f['permName'];
                $fields[$permName] = $f['value'];

                if (isset($forced[$permName])) {
                    $toRemove[$permName] = $k;
                }
            }

            foreach ($toRemove as $permName => $key) {
                unset($fields[$permName]);
                unset($processedFields[$key]);
            }
        } else {
            $out = [];
            foreach ($fields as $key => $value) {
                if ($itemObject->canModifyField($key)) {
                    $out[$key] = $value;
                }
            }
            $fields = $out;

            // if fields are specified in the form creation url then use only those ones
            if (! empty($fields) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
                foreach ($processedFields as $k => $f) {
                    $permName = $f['permName'];

                    if (! isset($fields[$permName])) {
                        $toRemove[$permName] = $k;
                    }
                }

                foreach ($toRemove as $permName => $key) {
                    unset($processedFields[$key]);
                }
            }
        }

        global $prefs;
        if (! empty($fields)) {
            $noDefaultValueFields = []; // will content all mandatory fields with no value for the default language

            foreach ($processedFields as $key => $f) {
                if ($f["isMultilingual"] == "y"  && $f["isMandatory"] == "y") {
                    $field = $fields[$f["permName"]];
                    $isDefaultValueDefined = false;

                    if (is_array($field)) {
                        foreach ($field as $k => $v) {
                            if ($v != "" && $prefs["language"] == $k) {
                                $isDefaultValueDefined = true;
                            }
                        }
                        // the user fill the default language value of a mandatory field value
                        // the value will be used for all languages with no value.
                        if ($isDefaultValueDefined) {
                            foreach ($field as $k => $v) {
                                if ($k != $prefs["language"] && $v == "") {
                                    $fields[$f["permName"]][$k] = $field[$prefs["language"]];
                                }
                            }
                        } else {
                            $noDefaultValueFields[] = $f["name"];
                        }
                    }
                }
            }
            if (! empty($noDefaultValueFields)) {
                $feedback = "Please note that the mandatory field" . (count($noDefaultValueFields) > 1 ? "s " : " ") . "%0";
                $feedback .= (count($noDefaultValueFields) > 1 ? " don't have values" : " doesn't have a value") . " for the language selected by default";

                Feedback::warning(tr($feedback, implode(", ", $noDefaultValueFields)));
            }
        }

        if ($prefs['feature_jquery_validation'] === 'y') {
            $validationjs = TikiLib::lib('validators')->generateTrackerValidateJS(
                $definition->getFields(),
                'ins_',
                '',
                '',
                // not custom submit handler that is only needed when called by this service
                'submitHandler: function(form, event){return process_submit(form, event);}'
            );
            TikiLib::lib('header')->add_jq_onready('$("#insertItemForm' . $trackerId . '").validate({' . $validationjs . $this->get_validation_options('#insertItemForm' . $trackerId));
        }

        if ($prefs['tracker_field_rules'] === 'y') {
            $js = TikiLib::lib('vuejs')->generateTrackerRulesJS($definition->getFields());
            TikiLib::lib('header')->add_jq_onready($js);
        }

        $itemId = 0;
        $util = new Services_Utilities();
        if (! empty($fields) && $util->isActionPost()) {
            foreach ($forced as $key => $value) {
                if ($itemObject->canModifyField($key)) {
                    $fields[$key] = $value;
                }
            }

            // test if one item per user
            if ($definition->getConfiguration('oneUserItem', 'n') == 'y') {
                $perms = Perms::get('tracker', $trackerId);

                if ($perms->admin_trackers) {   // tracker admins can make items for other users
                    $field = $definition->getField($definition->getUserField());
                    $theUser = isset($fields[$field['permName']]) ? $fields[$field['permName']] : null; // setup error?
                } else {
                    $theUser = null;
                }

                $tmp = TikiLib::lib('trk')->get_user_item($trackerId, $definition->getInformation(), $theUser);
                if ($tmp > 0) {
                    throw new Services_Exception(tr('Item could not be created. Only one item per user is allowed.'), 400);
                }
            }

            $deletedFiles = $itemObject->deletedFiles($input);

            $itemId = $this->utilities->insertItem(
                $definition,
                [
                    'status' => $input->status->word(),
                    'fields' => $fields,
                    'deletedFiles' => $deletedFiles
                ]
            );

            if ($itemId) {
                TikiLib::lib('unifiedsearch')->processUpdateQueue();
                TikiLib::events()->trigger('tiki.process.redirect'); // wait for indexing to complete before loading of next request to ensure updated info shown

                if ($next = $input->next->url()) {
                    $access = TikiLib::lib('access');
                    $access->redirect($next, tr('Item created'));
                }

                $item = $this->utilities->getItem($trackerId, $itemId);
                $item['itemTitle'] = $this->utilities->getTitle($definition, $item);
                $processedItem = $this->utilities->processValues($definition, $item);
                $item['processedFields'] = $processedItem['fields'];

                if ($suppressFeedback !== true) {
                    if ($input->ajax->bool()) {
                        $trackerinfo = $definition->getInformation();
                        $trackername = tr($trackerinfo['name']);
                        $msg = tr('New "%0" item successfully created.', $trackername);
                        Feedback::success($msg);
                        Feedback::sendHeaders();
                    } else {
                        Feedback::success(tr('New tracker item %0 successfully created.', $itemId));
                    }
                }
                // send a new ticket back to allow subsequent new items
                $util->setTicket();
                $item['nextTicket'] = $util->getTicket();

                return $item;
            } else {
                throw new Services_Exception(tr('Tracker item could not be created.'), 400);
            }
        }

        $editableFields = $input->editable->none();
        if (empty($editableFields)) {
            //if editable fields, show all fields in the form (except the ones from forced which have been removed).
            $displayedFields = $processedFields;
        } else {
            // if editableFields is set, only add the field if found in the editableFields array
            $displayedFields = [];
            foreach ($processedFields as $k => $f) {
                $permName = $f['permName'];
                if (in_array($permName, $editableFields)) {
                    $displayedFields[] = $f;
                }
            }
        }
        $status = $input->status->word();
        if ($status === null) { // '=== null' means status was not set. if status is set to "", it skips the status and uses the default
            $status = $itemObject->getDisplayedStatus();
        } else {
            $status = $input->status->word();
        }

        $title = $input->title->none();
        if (empty($title)) { // '=== null' means status was not set. if status is set to "", it skips the status and uses the default
            $title = tr('Create Item');
        } else {
            $title = $title;
        }

        if ($input->format->word()) {
            $format = $input->format->word();
        } else {
            $format = $definition->getConfiguration('sectionFormat');
        }

        $editItemPretty = '';
        if ($format === 'config') {
            $editItemPretty = $definition->getConfiguration('editItemPretty');
        }

        return [
            'title' => $title,
            'trackerId' => $trackerId,
            'trackerName' => $trackerName,
            'itemId' => $itemId,
            'fields' => $displayedFields,
            'forced' => $forced,
            'trackerLogo' => $definition->getConfiguration('logo'),
            'modal' => $input->modal->int(),
            'status' => $status,
            'skip_preview' => $input->skip_preview->word(),
            'format' => $format,
            'editItemPretty' => $editItemPretty,
            'next' => $input->next->url(),
            'suppressFeedback' => $suppressFeedback,
        ];
    }

    /**
     * @param $input JitFilter
     * - "trackerId" required
     * - "itemId" required
     * - "editable" optional. array of field names. e.g. ['title', 'description', 'user']. If not set, all fields
     *    all fields will be editable
     * - "forced" optional. associative array of fields where the value is 'forced'. Commonly used with skip_form.
     *    e.g ['isArchived'=>'y']. For example, this can be used to create a button that allows you to set the
     *    trackeritem to "Closed", or to set a field to a pre-determined value.
     * - "skip_form" - Allows users to skip the input form. This must be used with "forced" or "status" otherwise nothing would change
     * - "status" - sets a status for the object to be set to. Often used with skip_form
     *
     * Formatting the edit screen
     * - "title" optional. Sets a title for the edit screen.
     * - "skip_form_message" optional. Used with skip_form. E.g. "Are you sure you want to set this item to 'Closed'".
     * - "button_label" optional. Used to override the label for the Update/Save button.
     * - "redirect" set a url to which a user should be redirected, if any.
     *
     * @return array
     * @throws Exception
     * @throws Services_Exception
     * @throws Services_Exception_Denied
     * @throws Services_Exception_MissingValue
     * @throws Services_Exception_NotFound
     * @throws Services_Exception_EditConflict
     *
     */
    public function action_update_item($input)
    {
        global $prefs;

        $trackerId = $input->trackerId->int();
        $definition = Tracker_Definition::get($trackerId);
        $suppressFeedback = $input->suppressFeedback->bool();

        if (! $definition) {
            throw new Services_Exception_NotFound();
        }

        if (! $itemId = $input->itemId->int()) {
            throw new Services_Exception_MissingValue('itemId');
        }

        $itemInfo = TikiLib::lib('trk')->get_tracker_item($itemId);
        if (! $itemInfo || $itemInfo['trackerId'] != $trackerId) {
            throw new Services_Exception_NotFound();
        }

        $itemObject = Tracker_Item::fromInfo($itemInfo);
        if (! $itemObject->canModify()) {
            throw new Services_Exception_Denied();
        }

        if ($prefs['feature_warn_on_edit'] == 'y' && $input->conflictoverride->int() !== 1) {
            try {
                Services_Exception_EditConflict::checkSemaphore($itemId, 'trackeritem');
            } catch (Services_Exception_EditConflict $e) {
                if ($input->modal->int() && TikiLib::lib('access')->is_xml_http_request()) {
                    $smarty = TikiLib::lib('smarty');
                    $smarty->loadPlugin('smarty_function_service');
                    $href = smarty_function_service([
                        'controller' => 'tracker',
                        'action' => 'update_item',
                        'trackerId' => $trackerId,
                        'itemId' => $itemId,
                        'redirect' => $input->redirect->url(),
                        'conflictoverride' => 1,
                        'modal' => 1,
                    ], $smarty);
                    TikiLib::lib('header')->add_jq_onready('
    var lock_link = $(\'<a href="' . $href . '">' . tra('Override lock and carry on with edit') . '</a>\');
    lock_link.on("click", function(e) {
        var $link = $(this);
        e.preventDefault();
        $.closeModal({
            done: function() {
                $.openModal({
                    size: "modal-lg",
                    remote: $link.attr("href"),
                });
            }
        });
        return false;
    })
    $(".modal.fade.show .modal-body").append(lock_link);
                    ');
                }
                throw($e);
            }
            TikiLib::lib('service')->internal('semaphore', 'set', ['object_id' => $itemId, 'object_type' => 'trackeritem']);
        }

        if ($prefs['feature_jquery_validation'] === 'y') {
            $validationjs = TikiLib::lib('validators')->generateTrackerValidateJS(
                $definition->getFields(),
                'ins_',
                '',
                '',
                // not custom submit handler that is only needed when called by this service
                'submitHandler: function(form, event){return process_submit(form, event);}'
            );
            TikiLib::lib('header')->add_jq_onready('$("#updateItemForm' . $trackerId . '").validate({' . $validationjs . $this->get_validation_options());
        }

        if ($prefs['tracker_field_rules'] === 'y') {
            $js = TikiLib::lib('vuejs')->generateTrackerRulesJS($definition->getFields());
            TikiLib::lib('header')->add_jq_onready($js);
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            TikiLib::lib('access')->preventRedirect(true);
            //fetch the processed fields and the changes made in the form. Put them in the 'fields' variable
            $processedFields = $itemObject->prepareInput($input);
            $fields = [];
            foreach ($processedFields as $k => $f) {
                $permName = $f['permName'];
                $fields[$permName] = isset($f['value']) ? $f['value'] : '';
            }
            // for each input from the form, ensure user has modify rights. If so, add to the fields var to be edited.
            $userInput = $input->fields->none();
            if (! empty($userInput)) {
                foreach ($userInput as $key => $value) {
                    if ($itemObject->canModifyField($key)) {
                        // process input using the field's getFieldData function
                        $field = $definition->getFieldFromPermName($key);
                        $field = $itemObject->prepareFieldInput($field, $input->none());
                        $fields[$key] = $field['value'];
                    }
                }
            }
            // for each input from the form, ensure user has modify rights. If so, add to the fields var to be edited.
            $forcedInput = $input->forced->none();
            if (! empty($forcedInput)) {
                foreach ($forcedInput as $key => $value) {
                    if ($itemObject->canModifyField($key)) {
                        $fields[$key] = $value;
                    }
                }
            }

            $deletedFiles = $itemObject->deletedFiles($input);

            $result = $this->utilities->updateItem(
                $definition,
                [
                    'itemId' => $itemId,
                    'status' => $input->status->word(),
                    'fields' => $fields,
                    'deletedFiles' => $deletedFiles
                ]
            );

            if ($prefs['feature_warn_on_edit'] == 'y') {
                TikiLib::lib('service')->internal('semaphore', 'unset', ['object_id' => $itemId, 'object_type' => 'trackeritem']);
            }

            TikiLib::lib('access')->preventRedirect(false);

            if ($result !== false) {
                TikiLib::lib('unifiedsearch')->processUpdateQueue();
                TikiLib::events()->trigger('tiki.process.redirect'); // wait for indexing to complete before loading of next request to ensure updated info shown
                //only need feedback if success - feedback already set if there was an update error
            }
            if (isset($input['edit']) && $input['edit'] === 'inline') {
                if ($result && $suppressFeedback !== true) {
                    Feedback::success(tr('Tracker item %0 has been updated', $itemId), true);
                } else {
                    Feedback::sendHeaders();
                }
            } else {
                $item = $this->utilities->getItem($trackerId, $itemId);
                if ($result && $suppressFeedback !== true) {
                    if ($input->ajax->bool()) {
                        $trackerinfo = $definition->getInformation();
                        $trackername = tr($trackerinfo['name']);
                        $itemtitle = $this->utilities->getTitle($definition, $item);
                        $msg = tr('%0: Updated "%1"', $trackername, $itemtitle) . " [" . TikiLib::lib('tiki')->get_long_time(TikiLib::lib('tiki')->now) . "]";
                        Feedback::success($msg);
                        Feedback::sendHeaders();
                    } else {
                        Feedback::success(tr('Tracker item %0 has been updated', $itemId));
                    }
                } else {
                    Feedback::sendHeaders();
                }
                $redirect = $input->redirect->url();

                if ($input->saveAndComment->int()) {
                    $version = TikiLib::lib('trk')->last_log_version($itemId);

                    return [
                        'FORWARD' => [
                            'controller' => 'comment',
                            'action' => 'post',
                            'type' => 'trackeritem',
                            'objectId' => $itemId,
                            'parentId' => 0,
                            'version' => $version,
                            'return_url' => $redirect,
                            'title' => tr('Comment for edit #%0', $version),
                        ],
                    ];
                }
                //return to page
                if (! $redirect) {
                    $referer = Services_Utilities::noJsPath();

                    // Return item data and refresh info
                    $return = Services_Utilities::refresh($referer);
                    $return = array_merge($return, $item);
                    // send a new ticket back to allow subsequent updates
                    $util = new Services_Utilities();
                    $util->setTicket();
                    $return['nextTicket'] = $util->getTicket();

                    return $return;
                } else {
                    return Services_Utilities::redirect($redirect);
                }
            }
        }

        // sets all fields for the tracker item with their value
        $processedFields = $itemObject->prepareInput($input);
        // fields that we want to change in the form. If
        $editableFields = $input->editable->none();
        // fields where the value is forced.
        $forcedFields = $input->forced->none();

        // if forced fields are set, remove them from the processedFields since they will not show up visually
        // in the form; they will be set up separately and hidden.
        if (! empty($forcedFields)) {
            foreach ($processedFields as $k => $f) {
                $permName = $f['permName'];
                if (isset($forcedFields[$permName])) {
                    unset($processedFields[$k]);
                }
            }
        }

        if (empty($editableFields)) {
            //if editable fields, show all fields in the form (except the ones from forced which have been removed).
            $displayedFields = $processedFields;
        } else {
            // if editableFields is set, only add the field if found in the editableFields array
            $displayedFields = [];
            foreach ($processedFields as $k => $f) {
                $permName = $f['permName'];
                if (in_array($permName, $editableFields)) {
                    $displayedFields[] = $f;
                }
            }
        }

        /* Allow overriding of default wording in the template */
        if (empty($input->title->text())) {
            $title = tr('Update Item');
        } else {
            $title = $input->title->text();
        }

        if ($input->format->word()) {
            $format = $input->format->word();
        } else {
            $format = $definition->getConfiguration('sectionFormat');
        }

        $editItemPretty = '';
        if ($format === 'config') {
            $editItemPretty = $definition->getConfiguration('editItemPretty');
        }

        //Used if skip form is set
        if (empty($input->skip_form_message->text())) {
            $skip_form_message = tr('Are you sure you would like to update this item?');
        } else {
            $skip_form_message = $input->skip_form_message->text();
        }

        if (empty($input->button_label->text())) {
            $button_label = tr('Save');
        } else {
            $button_label = $input->button_label->text();
        }

        if ($input->status->word() === null) {
            $status = $itemObject->getDisplayedStatus();
        } else {
            $status = $input->status->word();
        }

        $saveAndComment = $definition->getConfiguration('saveAndComment');
        if ($saveAndComment !== 'n') {
            if (! Tracker_Item::fromId($itemId)->canPostComments()) {
                $saveAndComment = 'n';
            }
        }

        return [
            'title' => $title,
            'trackerId' => $trackerId,
            'itemId' => $itemId,
            'fields' => $displayedFields,
            'forced' => $forcedFields,
            'status' => $status,
            'skip_preview' => $input->skip_preview->word(),
            'skip_form' => $input->skip_form->word(),
            'skip_form_message' => $skip_form_message,
            'format' => $format,
            'editItemPretty' => $editItemPretty,
            'button_label' => $button_label,
            'redirect' => $input->redirect->none(),
            'saveAndComment' => $saveAndComment,
            'suppressFeedback' => $suppressFeedback,
            'conflictoverride' => $input->conflictoverride->int(),
        ];
    }

    /**
     * Preview tracker items
     *
     * @param JitFilter $input
     * @return null
     */
    public function action_preview_item($input)
    {
        global $prefs;

        $input = $input->fields;
        $trackerId = $input->trackerId->int();
        $definition = Tracker_Definition::get($trackerId);

        if (! $definition) {
            throw new Services_Exception_NotFound();
        }

        $itemId = $input->itemId->int();

        if ($itemId) {
            $itemInfo = TikiLib::lib('trk')->get_tracker_item($itemId);
            if (! $itemInfo || $itemInfo['trackerId'] != $trackerId) {
                throw new Services_Exception_NotFound();
            }
        } else {
            $itemInfo = ['trackerId' => $trackerId];
        }

        $trklib = TikiLib::lib('trk');
        $smarty = TikiLib::lib('smarty');

        $itemObject = Tracker_Item::fromInfo($itemInfo);
        $processedFields = $itemObject->prepareInput($input);
        $fieldsProcessed = [];
        foreach ($processedFields as $k => $f) {
            $permName = $f['permName'];
            $fieldsProcessed[$permName] = ['value' => $f['value'] ?? ''];
            if (isset($f['pvalue'])) {
                $fieldsProcessed[$permName]['pvalue'] = $f['pvalue'];
            }
            if (isset($f['relations'])) {
                $fieldsProcessed[$permName] = ['relations' => $f['relations']];
            }
            if (isset($f['selected'])) {
                $fieldsProcessed[$permName] = ['selected' => $f['selected']];
            }
            if (isset($f['selected_categories'])) {
                $fieldsProcessed[$permName] = ['selected_categories' => $f['selected_categories']];
            }
            if (isset($f['files'])) {
                $fieldsProcessed[$permName] = ['files' => $f['files']];
            }
        }

        $fieldDefinitions = $definition->getFields();
        $smarty->assign('tracker_is_multilingual', $prefs['feature_multilingual'] == 'y' && $definition->getLanguageField());

        if ($prefs['feature_groupalert'] == 'y') {
            $groupalertlib = TikiLib::lib('groupalert');
            $groupforalert = $groupalertlib->GetGroup('tracker', $trackerId);
            if ($groupforalert != "") {
                $showeachuser = $groupalertlib->GetShowEachUser('tracker', $trackerId, $groupforalert);
                $userlib = TikiLib::lib('user');
                $listusertoalert = $userlib->get_users(0, -1, 'login_asc', '', '', false, $groupforalert, '');
                $smarty->assign_by_ref('listusertoalert', $listusertoalert['data']);
            }
            $smarty->assign_by_ref('groupforalert', $groupforalert);
            $smarty->assign_by_ref('showeachuser', $showeachuser);
        }

        $smarty->assign('itemId', $itemId);
        $smarty->assign_by_ref('item_info', $itemInfo);
        $smarty->assign('item', ['itemId' => $itemId, 'trackerId' => $trackerId]);

        $trackerInfo = $definition->getInformation();

        include_once('tiki-sefurl.php');

        $statusTypes = $trklib->status_types();
        $smarty->assign('status_types', $statusTypes);
        $fields = [];
        $ins_fields = [];
        $itemUsers = $trklib->get_item_creators($trackerId, $itemId);
        $smarty->assign_by_ref('itemUsers', $itemUsers);

        if (empty($trackerInfo)) {
            $itemInfo = [];
        }

        $fieldFactory = $definition->getFieldFactory();

        foreach ($fieldDefinitions as &$fieldDefinition) {
            $fid = $fieldDefinition["fieldId"];
            $fieldDefinition["ins_id"] = 'ins_' . $fid;
            $fieldDefinition["filter_id"] = 'filter_' . $fid;
        }
        unset($fieldDefinition);

        $itemObject = Tracker_Item::fromInfo($itemInfo);

        foreach ($fieldDefinitions as $i => $currentField) {
            $currentFieldIns = null;
            $fid = $currentField['fieldId'];

            $handler = $fieldFactory->getHandler($currentField, $itemInfo);

            $fieldIsVisible = $itemObject->canViewField($fid);
            $fieldIsEditable = $itemObject->canModifyField($fid);

            if ($fieldIsVisible || $fieldIsEditable) {
                $currentFieldIns = $currentField;

                if ($handler) {
                    $insertValues = $handler->getFieldData();

                    if ($insertValues) {
                        $currentFieldIns = array_merge($currentFieldIns, $insertValues);
                    }
                }
            }

            if (! empty($currentFieldIns)) {
                if ($fieldIsVisible) {
                    $fields['data'][$i] = $currentFieldIns;
                }
                if ($fieldIsEditable) {
                    $ins_fields['data'][$i] = $currentFieldIns;
                }
            }
        }

        if ($trackerInfo['doNotShowEmptyField'] == 'y') {
            $trackerlib = TikiLib::lib('trk');
            $fields['data'] = $trackerlib->mark_fields_as_empty($fields['data']);
        }

        foreach ($fields["data"] as &$field) {
            $permName = isset($field['permName']) ? $field['permName'] : null;
            if (isset($fieldsProcessed[$permName])) {
                $field['value'] = $fieldsProcessed[$permName]['value'];
                $field['pvalue'] = $fieldsProcessed[$permName]['pvalue'];
                if (isset($fieldsProcessed[$permName]['relations'])) {
                    $field['relations'] = $fieldsProcessed[$permName]['relations'];
                }
                if (isset($fieldsProcessed[$permName]['selected'])) {
                    $field['selected'] = $fieldsProcessed[$permName]['selected'];
                }
                if (isset($fieldsProcessed[$permName]['selected_categories'])) {
                    $field['selected_categories'] = $fieldsProcessed[$permName]['selected_categories'];
                }
                if (isset($field['freetags'])) {
                    $freetags = trim($fieldsProcessed[$permName]);
                    $freetags = explode(' ', $freetags);
                    $field['freetags'] = $freetags;
                }
                if (isset($fieldsProcessed[$permName]['files'])) {
                    $field['files'] = $fieldsProcessed[$permName]['files'];
                }
            }
        }

        $smarty->assign('trackerId', $trackerId);
        $smarty->assign('tracker_info', $trackerInfo);
        $smarty->assign_by_ref('info', $itemInfo);
        $smarty->assign_by_ref('fields', $fields["data"]);
        $smarty->assign_by_ref('ins_fields', $ins_fields["data"]);


        if ($trackerInfo['useComments'] == 'y') {
            $comCount = $trklib->get_item_nb_comments($itemId);
            $smarty->assign("comCount", $comCount);
            $smarty->assign("canViewCommentsAsItemOwner", $itemObject->canViewComments());
        }

        if ($trackerInfo["useAttachments"] == 'y') {
            if ($input->removeattach->int()) {
                $_REQUEST["show"] = "att";
            }
            if ($input->editattach->int()) {
                $att = $trklib->get_item_attachment($input->editattach->int());
                $smarty->assign("attach_comment", $att['comment']);
                $smarty->assign("attach_version", $att['version']);
                $smarty->assign("attach_longdesc", $att['longdesc']);
                $smarty->assign("attach_file", $att["filename"]);
                $smarty->assign("attId", $att["attId"]);
                $_REQUEST["show"] = "att";
            }
            // If anything below here is changed, please change lib/wiki-plugins/wikiplugin_attach.php as well.
            $attextra = 'n';
            if (strstr($trackerInfo["orderAttachments"], '|')) {
                $attextra = 'y';
            }
            $attfields = explode(',', strtok($trackerInfo["orderAttachments"], '|'));
            $atts = $trklib->list_item_attachments($itemId, 0, -1, 'comment_asc', '');
            $smarty->assign('atts', $atts["data"]);
            $smarty->assign('attCount', $atts["cant"]);
            $smarty->assign('attfields', $attfields);
            $smarty->assign('attextra', $attextra);
        }

        ask_ticket('view-trackers-items');

        $smarty->assign('canView', $itemObject->canView());

        // View
        $viewItemPretty = [
                'override' => false,
                'value' => $trackerInfo['viewItemPretty'],
                'type' => 'wiki'
        ];
        if (! empty($trackerInfo['viewItemPretty'])) {
            // Need to check wether this is a wiki: or tpl: template, bc the smarty template needs to take care of this
            if (strpos(strtolower($viewItemPretty['value']), 'wiki:') === false) {
                $viewItemPretty['type'] = 'tpl';
            }
        }
        $smarty->assign('viewItemPretty', $viewItemPretty);

        try {
            $smarty->assign('print_page', 'y');
            $smarty->display('templates/tracker/preview_item.tpl');
        } catch (SmartyException $e) {
            $message = tr('The requested element cannot be displayed. One of the view/edit templates is missing or has errors: %0', $e->getMessage());
            trigger_error($e->getMessage(), E_USER_ERROR);
            $smarty->loadPlugin('smarty_modifier_sefurl');
            $access = TikiLib::lib('access');
            $access->redirect(smarty_modifier_sefurl($trackerId, 'tracker'), $message, 302, 'error');
        }
    }

    /**
     * Links wildcard ItemLink entries to the base tracker by cloning wildcard items
     * and removes unselected ItemLink entries that were already linked before.
     * Used by ItemLink update table button to refresh list of associated entries.
     *
     * @param JitFilter $input
     * @return array|string
     * @throws Services_Exception_Denied
     * @throws Services_Exception_NotFound
     */
    public function action_link_items($input)
    {
        $trackerId = $input->trackerId->int();
        $definition = Tracker_Definition::get($trackerId);

        if (! $definition) {
            throw new Services_Exception_NotFound();
        }

        if (! $field = $definition->getField($input->linkField->int())) {
            throw new Services_Exception_NotFound();
        }

        $linkedItemIds = [];
        $linkValue = trim($input->linkValue->text());

        foreach ($input->items as $itemId) {
            $itemObject = Tracker_Item::fromId($itemId);

            if (! $itemObject) {
                throw new Services_Exception_NotFound();
            }

            if (! $itemObject->canView()) {
                throw new Services_Exception_Denied(tr("The item to clone isn't visible"));
            }

            $output = $itemObject->prepareFieldOutput($field);
            $currentValue = $output['value'];

            if ($currentValue === '*') {
                $itemData = $itemObject->getData();
                $itemData['fields'][$field['permName']] = $linkValue;
                $itemObject = $this->utilities->cloneItem($definition, $itemData);
                if ($itemObject === false) {
                    continue; // skip this item as there was an error, errors will show in Feedback as normal
                }
                $linkedItemIds[] = $itemObject->getId();
            } else {
                $this->utilities->updateItem(
                    $definition,
                    [
                        'itemId' => $itemId,
                        'fields' => [
                            $field['permName'] => $linkValue
                        ]
                    ]
                );
                $linkedItemIds[] = $itemId;
            }
        }

        $allItemIds = TikiLib::lib('trk')->get_items_list($trackerId, $field['fieldId'], $linkValue);
        $toDelete = array_diff($allItemIds, $linkedItemIds);
        foreach ($toDelete as $itemId) {
            $itemObject = Tracker_Item::fromId($itemId);

            if (! $itemObject) {
                throw new Services_Exception_NotFound();
            }

            if (! $itemObject->canRemove()) {
                throw new Services_Exception_Denied(tr("Cannot remove item %0 from this tracker", $itemId));
            }

            $uncascaded = TikiLib::lib('trk')->findUncascadedDeletes($itemId, $trackerId);
            $this->utilities->removeItemAndReferences($definition, $itemObject, $uncascaded, '');
        }

        if ($trackerlistParams = $input->asArray('trackerlistParams')) {
            include_once 'lib/smarty_tiki/block.wikiplugin.php';
            $trackerlistParams['_name'] = 'trackerlist';
            $trackerlistParams['checkbox'] = preg_replace('#/[\d,]*$#', '/' . implode(',', $linkedItemIds), $trackerlistParams['checkbox']);
            return smarty_block_wikiplugin($trackerlistParams, '', TikiLib::lib('smarty')) . TikiLib::lib('header')->output_js();
        } else {
            return [
                'status' => 'ok'
            ];
        }
    }

    public function action_fetch_item_field($input)
    {
        global $prefs;

        $trackerId = $input->trackerId->int();
        $mode = $input->mode->word();                       // output|input (default input)
        $listMode = $input->listMode->word();
        $definition = Tracker_Definition::get($trackerId);

        if (! $definition) {
            throw new Services_Exception_NotFound();
        }

        if (! $field = $definition->getField($input->fieldId->int())) {
            throw new Services_Exception_NotFound();
        }

        if ($itemId = $input->itemId->int()) {
            $itemInfo = TikiLib::lib('trk')->get_tracker_item($itemId);
            if (! $itemInfo || $itemInfo['trackerId'] != $trackerId) {
                throw new Services_Exception_NotFound();
            }

            $itemObject = Tracker_Item::fromInfo($itemInfo);
            if (! $processed = $itemObject->prepareFieldInput($field, $input->none())) {
                throw new Services_Exception_Denied();
            }
        } else {
            $itemObject = Tracker_Item::newItem($trackerId);
            $processed = $itemObject->prepareFieldInput($field, $input->none());
        }

        if ($itemId && $mode != 'output' && $prefs['feature_warn_on_edit'] == 'y') {
            Services_Exception_EditConflict::checkSemaphore($itemId, 'trackeritem');
            TikiLib::lib('service')->internal('semaphore', 'set', ['object_id' => $itemId, 'object_type' => 'trackeritem']);
        }

        return [
            'field' => $processed,
            'mode' => $mode,
            'listMode' => $listMode,
            'itemId' => $itemId
        ];
    }

    public function action_set_location($input)
    {
        $location = $input->location->text();

        if (! $itemId = $input->itemId->int()) {
            throw new Services_Exception_MissingValue('itemId');
        }

        $itemInfo = TikiLib::lib('trk')->get_tracker_item($itemId);
        if (! $itemInfo) {
            throw new Services_Exception_NotFound();
        }

        $trackerId = $itemInfo['trackerId'];
        $definition = Tracker_Definition::get($trackerId);
        if (! $definition) {
            throw new Services_Exception_NotFound();
        }

        $itemObject = Tracker_Item::fromInfo($itemInfo);
        if (! $itemObject->canModify()) {
            throw new Services_Exception_Denied();
        }

        $field = $definition->getGeolocationField();
        if (! $field) {
            throw new Services_Exception_NotFound();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $field = $definition->getField($field);

            $this->utilities->updateItem(
                $definition,
                [
                    'itemId' => $itemId,
                    'status' => $itemInfo['status'],
                    'fields' => [
                        $field['permName'] => $location,
                    ],
                ]
            );
            TikiLib::lib('unifiedsearch')->processUpdateQueue();
            TikiLib::events()->trigger('tiki.process.redirect'); // wait for indexing to complete before loading of next request to ensure updated info shown
        }

        return [
            'trackerId' => $trackerId,
            'itemId' => $itemId,
            'location' => $location,
        ];
    }

    public function action_remove_item($input)
    {
        $trackerId = $input->trackerId->int();
        $definition = Tracker_Definition::get($trackerId);

        if (! $definition) {
            throw new Services_Exception_NotFound();
        }

        if (! $itemId = $input->itemId->int()) {
            throw new Services_Exception_MissingValue('itemId');
        }

        $trklib = TikiLib::lib('trk');

        $itemInfo = $trklib->get_tracker_item($itemId);
        if (! $itemInfo || $itemInfo['trackerId'] != $trackerId) {
            throw new Services_Exception_NotFound();
        }

        $itemObject = Tracker_Item::fromInfo($itemInfo);
        if (! $itemObject->canRemove()) {
            throw new Services_Exception_Denied();
        }

        $uncascaded = $trklib->findUncascadedDeletes($itemId, $trackerId);

        if ($_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['REQUEST_METHOD'] == 'DELETE') {
            $this->utilities->removeItemAndReferences($definition, $itemObject, $uncascaded, $input->replacement->int() ?: '');

            Feedback::success(tr('Tracker item %0 has been successfully deleted.', $itemId));

            TikiLib::events()->trigger('tiki.process.redirect'); // wait for indexing to complete before loading of next request to ensure updated info shown
        }

        return [
            'title' => tr('Remove'),
            'trackerId' => $trackerId,
            'itemId' => $itemId,
            'affectedCount' => count($uncascaded['itemIds']),
        ];
    }

    public function action_remove($input)
    {
        $trackerId = $input->trackerId->int();
        $confirm = $input->confirm->int();

        $perms = Perms::get('tracker', $trackerId);
        if (! $perms->admin_trackers) {
            throw new Services_Exception_Denied(tr('Reserved for tracker administrators'));
        }

        $definition = Tracker_Definition::get($trackerId);

        if (! $definition) {
            throw new Services_Exception_NotFound();
        }

        if (($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'DELETE') && $confirm) {
            $this->utilities->removeTracker($trackerId);

            return [
                'trackerId' => 0,
            ];
        }

        return [
            'trackerId' => $trackerId,
            'name' => $definition->getConfiguration('name'),
            'info' => $definition->getInformation(),
        ];
    }

    //Function to just change the status of the tracker item
    public function action_update_item_status($input)
    {
        if ($input->status->word() == 'DONE') {
            return [
                'status' => 'DONE',
                'redirect' => $input->redirect->word(),
            ];
        }

        $trackerId = $input->trackerId->int();
        $definition = Tracker_Definition::get($trackerId);

        if (! $definition) {
            throw new Services_Exception_NotFound();
        }

        if (! $itemId = $input->itemId->int()) {
            throw new Services_Exception_MissingValue('itemId');
        }

        $itemInfo = TikiLib::lib('trk')->get_tracker_item($itemId);
        if (! $itemInfo || $itemInfo['trackerId'] != $trackerId) {
            throw new Services_Exception_NotFound();
        }

        if (empty($input->item_label->text())) {
            $item_label = "item";
        } else {
            $item_label = $input->item_label->text();
        }

        if (empty($input->title->text())) {
            $title = "Change item status";
        } else {
            $title = $input->title->text();
        }

        if (empty($input->button_label->text())) {
            $button_label = "Update " . $item_label;
        } else {
            $button_label = $input->button_label->text();
        }

        $itemObject = Tracker_Item::fromInfo($itemInfo);
        if (! $itemObject->canModify()) {
            throw new Services_Exception_Denied();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $input->confirm->int()) {
            $result = $this->utilities->updateItem(
                $definition,
                [
                    'itemId' => $itemId,
                    'trackerId' => $trackerId,
                    'status' => $input->status->text(),
                ]
            );

            return [
                'FORWARD' => [
                    'controller' => 'tracker',
                    'action' => 'update_item_status',
                    'status' => 'DONE',
                    'redirect' => $input->redirect->text(),
                ]
            ];
        } else {
            return [
                'trackerId' => $trackerId,
                'itemId' => $itemId,
                'item_label' => $item_label,
                'status' => $input->status->text(),
                'redirect' => $input->redirect->text(),
                'confirmation_message' => $input->confirmation_message->text(),
                'title' => $title,
                'button_label' => $button_label,
            ];
        }
        if (false === $result) {
            throw new Services_Exception(tr('Validation error'), 406);
        }
    }

    public function action_clear($input)
    {

        return TikiLib::lib('tiki')->allocate_extra(
            'tracker_clear_items',
            function () use ($input) {
                $trackerId = $input->trackerId->int();
                $perms = Perms::get('tracker', $trackerId);
                if (! $perms->admin_trackers) {
                    throw new Services_Exception_Denied(tr('Reserved for tracker administrators'));
                }

                $definition = Tracker_Definition::get($trackerId);

                if (! $definition) {
                    throw new Services_Exception_NotFound();
                }
                $util = new Services_Utilities();
                if ($util->isConfirmPost()) {
                    $result = $this->utilities->clearTracker($trackerId);
                    if (! empty($result)) {
                        if ($result === 1) {
                            Feedback::success(tr('One tracker item deleted'));
                        } else {
                            Feedback::success(tr('%0 tracker items deleted', $result));
                        }
                    } else {
                        Feedback::error(tr('No tracker items deleted'));
                    }

                    return [
                        'trackerId' => 0,
                    ];
                }

                return [
                    'trackerId' => $trackerId,
                    'name' => $definition->getConfiguration('name'),
                ];
            }
        );
    }

    public function action_replace($input)
    {
        $trackerId = $input->trackerId->int();
        $confirm = $input->confirm->int();

        $perms = Perms::get('tracker', $trackerId);
        if (! $perms->admin_trackers) {
            throw new Services_Exception_Denied(tr('Reserved for tracker administrators'));
        }

        if ($trackerId) {
            $definition = Tracker_Definition::get($trackerId);

            if (! $definition) {
                throw new Services_Exception_NotFound();
            }
        } else {
            $definition = Tracker_Definition::getDefault();
        }

        $cat_type = 'tracker';
        $cat_objid = $trackerId;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $confirm) {
            $name = $input->name->text();

            if (! $name) {
                throw new Services_Exception_MissingValue('name');
            }

            $data = [
                'name' => $name,
                'description' => $input->description->text(),
                'descriptionIsParsed' => $input->descriptionIsParsed->int() ? 'y' : 'n',
                'fieldPrefix' => $input->fieldPrefix->text(),
                'permName' => $input->permName->text(),
                'showStatus' => $input->showStatus->int() ? 'y' : 'n',
                'showStatusAdminOnly' => $input->showStatusAdminOnly->int() ? 'y' : 'n',
                'showCreated' => $input->showCreated->int() ? 'y' : 'n',
                'showCreatedView' => $input->showCreatedView->int() ? 'y' : 'n',
                'showCreatedBy' => $input->showCreatedBy->int() ? 'y' : 'n',
                'showCreatedFormat' => $input->showCreatedFormat->text(),
                'showLastModif' => $input->showLastModif->int() ? 'y' : 'n',
                'showLastModifView' => $input->showLastModifView->int() ? 'y' : 'n',
                'showLastModifBy' => $input->showLastModifBy->int() ? 'y' : 'n',
                'showLastModifFormat' => $input->showLastModifFormat->text(),
                'defaultOrderKey' => $input->defaultOrderKey->int(),
                'defaultOrderDir' => $input->defaultOrderDir->word(),
                'doNotShowEmptyField' => $input->doNotShowEmptyField->int() ? 'y' : 'n',
                'showPopup' => $input->showPopup->text(),
                'defaultStatus' => implode('', (array) $input->defaultStatus->word()),
                'newItemStatus' => $input->newItemStatus->word(),
                'modItemStatus' => $input->modItemStatus->word(),
                'outboundEmail' => $input->outboundEmail->email(),
                'simpleEmail' => $input->simpleEmail->int() ? 'y' : 'n',
                'userCanSeeOwn' => $input->userCanSeeOwn->int() ? 'y' : 'n',
                'groupCanSeeOwn' => $input->groupCanSeeOwn->int() ? 'y' : 'n',
                'writerCanModify' => $input->writerCanModify->int() ? 'y' : 'n',
                'writerCanRemove' => $input->writerCanRemove->int() ? 'y' : 'n',
                'userCanTakeOwnership' => $input->userCanTakeOwnership->int() ? 'y' : 'n',
                'oneUserItem' => $input->oneUserItem->int() ? 'y' : 'n',
                'writerGroupCanModify' => $input->writerGroupCanModify->int() ? 'y' : 'n',
                'writerGroupCanRemove' => $input->writerGroupCanRemove->int() ? 'y' : 'n',
                'useRatings' => $input->useRatings->int() ? 'y' : 'n',
                'showRatings' => $input->showRatings->int() ? 'y' : 'n',
                'ratingOptions' => $input->ratingOptions->text(),
                'useComments' => $input->useComments->int() ? 'y' : 'n',
                'showComments' => $input->showComments->int() ? 'y' : 'n',
                'showLastComment' => $input->showLastComment->int() ? 'y' : 'n',
                'saveAndComment' => $input->saveAndComment->int() ? 'y' : 'n',
                'useAttachments' => $input->useAttachments->int() ? 'y' : 'n',
                'showAttachments' => $input->showAttachments->int() ? 'y' : 'n',
                'orderAttachments' => implode(',', $input->orderAttachments->word()),
                'start' => $input->start->int() ? $this->readDate($input, 'start') : 0,
                'end' => $input->end->int() ? $this->readDate($input, 'end') : 0,
                'autoCreateGroup' => $input->autoCreateGroup->int() ? 'y' : 'n',
                'autoCreateGroupInc' => $input->autoCreateGroupInc->groupname(),
                'autoAssignCreatorGroup' => $input->autoAssignCreatorGroup->int() ? 'y' : 'n',
                'autoAssignCreatorGroupDefault' => $input->autoAssignCreatorGroupDefault->int() ? 'y' : 'n',
                'autoAssignGroupItem' => $input->autoAssignGroupItem->int() ? 'y' : 'n',
                'autoCopyGroup' => $input->autoCopyGroup->int() ? 'y' : 'n',
                'viewItemPretty' => $input->viewItemPretty->text(),
                'editItemPretty' => $input->editItemPretty->text(),
                'autoCreateCategories' => $input->autoCreateCategories->int() ? 'y' : 'n',
                'publishRSS' => $input->publishRSS->int() ? 'y' : 'n',
                'sectionFormat' => $input->sectionFormat->word(),
                'adminOnlyViewEditItem' => $input->adminOnlyViewEditItem->int() ? 'y' : 'n',
                'logo' => $input->logo->text(),
                'useFormClasses' => $input->useFormClasses->int() ? 'y' : 'n',
                'formClasses' => $input->formClasses->text(),
                'tabularSync' => $input->tabularSync->int(),
                'tabularSyncModifiedField' => $input->tabularSyncModifiedField->int(),
                'tabularSyncLastImport' => $input->tabularSyncLastImport->int(),
                'notifyOn' => $input->notifyOn->word(),
            ];

            $trackerId = $this->utilities->updateTracker($trackerId, $data);

            $cat_desc = $data['description'];
            $cat_name = $data['name'];
            $cat_href = "tiki-view_tracker.php?trackerId=" . $trackerId;
            $cat_objid = $trackerId;
            include "categorize.php";

            $groupforAlert = $input->groupforAlert->groupname();

            if ($groupforAlert) {
                $groupalertlib = TikiLib::lib('groupalert');
                $showeachuser = $input->showeachuser->int() ? 'y' : 'n';
                $groupalertlib->AddGroup('tracker', $trackerId, $groupforAlert, $showeachuser);
            }

            $definition = Tracker_Definition::get($trackerId);
        }

        include_once("categorize_list.php");
        $trklib = TikiLib::lib('trk');
        $groupalertlib = TikiLib::lib('groupalert');
        $groupforAlert = $groupalertlib->GetGroup('tracker', 'trackerId');
        return [
            'title' => $trackerId ? tr('Edit') . " " . tr('%0', $definition->getConfiguration('name')) : tr('Create Tracker'),
            'trackerId' => $trackerId,
            'info' => $definition->getInformation(),
            'statusTypes' => TikiLib::lib('trk')->status_types(),
            'statusList' => preg_split('//', $definition->getConfiguration('defaultStatus', 'o'), -1, PREG_SPLIT_NO_EMPTY),
            'sortFields' => $this->getSortFields($definition),
            'attachmentAttributes' => $this->getAttachmentAttributes($definition->getConfiguration('orderAttachments', 'created,filesize,hits')),
            'startDate' => $this->format($definition->getConfiguration('start'), '%Y-%m-%d'),
            'startTime' => $this->format($definition->getConfiguration('start'), '%H:%M'),
            'endDate' => $this->format($definition->getConfiguration('end'), '%Y-%m-%d'),
            'endTime' => $this->format($definition->getConfiguration('end'), '%H:%M'),
            'groupList' => $this->getGroupList(),
            'groupforAlert' => $groupforAlert,
            'showeachuser' => $groupalertlib->GetShowEachUser('tracker', 'trackerId', $groupforAlert),
            'sectionFormats' => $trklib->getGlobalSectionFormats(),
            'remoteTabulars' => TikiLib::lib('tabular')->getList(['odbc_config' => new TikiDb_Expr('$$ != ? AND $$ IS NOT NULL', ['[]'])]),
        ];
    }

    public function action_duplicate($input)
    {
        $confirm = $input->confirm->int();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $confirm) {
            $trackerId = $input->trackerId->int();
            $perms = Perms::get('tracker', $trackerId);
            if (! $perms->admin_trackers || ! Perms::get()->admin_trackers) {
                throw new Services_Exception_Denied(tr('Reserved for tracker administrators'));
            }
            $definition = Tracker_Definition::get($trackerId);
            if (! $definition) {
                throw new Services_Exception_NotFound();
            }
            $name = $input->name->text();
            if (! $name) {
                throw new Services_Exception_MissingValue('name');
            }
            $newId = $this->utilities->duplicateTracker($trackerId, $name, $input->dupCateg->int(), $input->dupPerms->int());
            return [
                'trackerId' => $newId,
                'name' => $name,
            ];
        } else {
            $trackers = $this->action_list_trackers($input);
            return [
                'title' => tr('Duplicate Tracker'),
                'trackers' => $trackers["data"],
            ];
        }
    }

    public function action_export($input)
    {
        $trackerId = $input->trackerId->int();
        $filterField = $input->filterfield->string();
        $filterValue = $input->filtervalue->string();

        $perms = Perms::get('tracker', $trackerId);
        if (! $perms->export_tracker) {
            throw new Services_Exception_Denied(tr('Reserved for tracker administrators'));
        }

        $definition = Tracker_Definition::get($trackerId);

        if (! $definition) {
            throw new Services_Exception_NotFound();
        }

        if ($perms->admin_trackers) {
            $info = $definition->getInformation();

            $out = "[TRACKER]\n";

            foreach ($info as $key => $value) {
                if ($key && $value) {
                    $out .= "$key = $value\n";
                }
            }
        } else {
            $out = null;
        }

        // Check if can view field otherwise exclude it
        $fields = $definition->getFields();
        $item = Tracker_Item::newItem($trackerId);
        foreach ($fields as $k => $field) {
            if (! $item->canViewField($field['fieldId'])) {
                unset($fields[$k]);
            }
        }

        return [
            'title' => tr('Export Items'),
            'trackerId' => $trackerId,
            'export' => $out,
            'fields' => $fields,
            'filterfield' => $filterField,
            'filtervalue' => $filterValue,
            'recordsMax' => $definition->getConfiguration('items'),
        ];
    }

    public function action_export_items($input)
    {
        @ini_set('max_execution_time', 0);
        TikiLib::lib('tiki')->allocate_extra(
            'tracker_export_items',
            function () use ($input) {
                $trackerId = $input->trackerId->int();

                $definition = Tracker_Definition::get($trackerId);

                if (! $definition) {
                    throw new Services_Exception_NotFound();
                }

                $perms = Perms::get('tracker', $trackerId);
                if (! $perms->export_tracker) {
                    throw new Services_Exception_Denied(tr("You don't have permission to export"));
                }

                $fields = [];
                foreach ((array) $input->listfields->int() as $fieldId) {
                    if ($f = $definition->getField($fieldId)) {
                        $fields[$fieldId] = $f;
                    }
                }

                if (0 === count($fields)) {
                    $fields = $definition->getFields();
                }

                $filterField = $input->filterfield->string();
                $filterValue = $input->filtervalue->string();

                $showItemId = $input->showItemId->int();
                $showStatus = $input->showStatus->int();
                $showCreated = $input->showCreated->int();
                $showLastModif = $input->showLastModif->int();
                $keepItemlinkId = $input->keepItemlinkId->int();
                $keepCountryId = $input->keepCountryId->int();
                $dateFormatUnixTimestamp = $input->dateFormatUnixTimestamp->int();

                $encoding = $input->encoding->text();
                if (! in_array($encoding, ['UTF-8', 'ISO-8859-1'])) {
                    $encoding = 'UTF-8';
                }
                $separator = $input->separator->none();
                $delimitorR = $input->delimitorR->none();
                $delimitorL = $input->delimitorL->none();

                $cr = $input->CR->none();

                $recordsMax = $input->recordsMax->int();
                $recordsOffset = $input->recordsOffset->int() - 1;

                $writeCsv = function ($fields) use ($separator, $delimitorL, $delimitorR, $encoding, $cr) {
                    $values = [];
                    foreach ($fields as $v) {
                        $values[] = "$delimitorL$v$delimitorR";
                    }

                    $line = implode($separator, $values);
                    $line = str_replace(["\r\n", "\n", "<br/>", "<br />"], $cr, $line);

                    if ($encoding === 'ISO-8859-1') {
                        echo utf8_decode($line) . "\n";
                    } else {
                        echo $line . "\n";
                    }
                };

                 session_write_close();

                $trklib = TikiLib::lib('trk');
                $trklib->write_export_header($encoding, $trackerId);

                $header = [];
                if ($showItemId) {
                    $header[] = 'itemId';
                }
                if ($showStatus) {
                    $header[] = 'status';
                }
                if ($showCreated) {
                    $header[] = 'created';
                }
                if ($showLastModif) {
                    $header[] = 'lastModif';
                }
                foreach ($fields as $f) {
                    $header[] = $f['name'] . ' -- ' . $f['fieldId'];
                }

                $writeCsv($header);

                /** @noinspection PhpParamsInspection */
                $items = $trklib->list_items($trackerId, $recordsOffset, $recordsMax, 'itemId_asc', $fields, $filterField, $filterValue);

                $smarty = TikiLib::lib('smarty');
                $smarty->loadPlugin('smarty_modifier_tiki_short_datetime');
                foreach ($items['data'] as $row) {
                    $toDisplay = [];
                    if ($showItemId) {
                        $toDisplay[] = $row['itemId'];
                    }
                    if ($showStatus) {
                        $toDisplay[] = $row['status'];
                    }
                    if ($showCreated) {
                        if ($dateFormatUnixTimestamp) {
                            $toDisplay[] = $row['created'];
                        } else {
                            $toDisplay[] = smarty_modifier_tiki_short_datetime($row['created'], '', 'n');
                        }
                    }
                    if ($showLastModif) {
                        if ($dateFormatUnixTimestamp) {
                            $toDisplay[] = $row['lastModif'];
                        } else {
                            $toDisplay[] = smarty_modifier_tiki_short_datetime($row['lastModif'], '', 'n');
                        }
                    }
                    foreach ($row['field_values'] as $val) {
                        if (($keepItemlinkId) && ($val['type'] == 'r')) {
                            $toDisplay[] = $val['value'];
                        } elseif (($keepCountryId) && ($val['type'] == 'y')) {
                            $toDisplay[] = $val['value'];
                        } elseif (($dateFormatUnixTimestamp) && ($val['type'] == 'f')) {
                            $toDisplay[] = $val['value'];
                        } elseif (($dateFormatUnixTimestamp) && ($val['type'] == 'j')) {
                            $toDisplay[] = $val['value'];
                        } else {
                            $toDisplay[] = $trklib->get_field_handler($val, $row)->renderOutput([
                                'list_mode' => 'csv',
                                'CR' => $cr,
                                'delimitorL' => $delimitorL,
                                'delimitorR' => $delimitorR,
                            ]);
                        }
                    }

                    $writeCsv($toDisplay);
                }
            }
        );

        exit;
    }

    public function action_dump_items($input)
    {
        $trackerId = $input->trackerId->int();

        $definition = Tracker_Definition::get($trackerId);

        if (! $definition) {
            throw new Services_Exception_NotFound();
        }

        $perms = Perms::get('tracker', $trackerId);
        if (! $perms->export_tracker) {
            throw new Services_Exception_Denied(tr("You don't have permission to export"));
        }

        $trklib = TikiLib::lib('trk');
        $trklib->dump_tracker_csv($trackerId);
        exit;
    }

    public function action_export_profile($input)
    {
        if (! Perms::get()->admin_trackers) {
            throw new Services_Exception_Denied(tr('Reserved for tracker administrators'));
        }

        $trackerId = $input->trackerId->int();

        $profile = Tiki_Profile::fromString('dummy', '');
        $data = [];
        $profileObject = new Tiki_Profile_Object($data, $profile);
        $profileTrackerInstallHandler = new Tiki_Profile_InstallHandler_Tracker($profileObject, []);

        $export_yaml = $profileTrackerInstallHandler->dumpExport($trackerId, $profileObject);

        include_once 'lib/wiki-plugins/wikiplugin_code.php';
        $export_yaml = wikiplugin_code($export_yaml, ['caption' => 'YAML', 'colors' => 'yaml']);
        $export_yaml = preg_replace('/~[\/]?np~/', '', $export_yaml);

        return [
            'trackerId' => $trackerId,
            'yaml' => $export_yaml,
        ];
    }

    private function trackerName($trackerId)
    {
        return TikiLib::lib('tiki')->table('tiki_trackers')->fetchOne('name', ['trackerId' => $trackerId]);
    }

    private function trackerId($trackerName)
    {
        return TikiLib::lib('tiki')->table('tiki_trackers')->fetchOne('trackerId', ['name' => $trackerName]);
    }

    private function trackerNameAndId(&$trackerId, &$trackerName)
    {
        if ($trackerId > 0 && empty($trackerName)) {
            $trackerName = $this->trackerName($trackerId);
        } elseif ($trackerId < 1 && ! empty($trackerName)) {
            $trackerId = $this->trackerId($trackerName);
        }
    }

    public function action_import($input)
    {
        if (! Perms::get()->admin_trackers) {
            throw new Services_Exception_Denied(tr('Reserved for tracker administrators'));
        }

        unset($success);
        $confirm = $input->confirm->int();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $confirm) {
            $raw = $input->raw->none();
            $preserve = $input->preserve->int();

            $data = TikiLib::lib('tiki')->read_raw($raw, $preserve);

            if (! $data || ! isset($data['tracker'])) {
                throw new Services_Exception(tr('Invalid data provided'), 400);
            }

            $data = $data['tracker'];

            $trackerId = 0;
            if ($preserve) {
                $trackerId = (int) $data['trackerId'];
            }

            unset($data['trackerId']);
            $trackerId = $this->utilities->updateTracker($trackerId, $data);
            $success = 1;

            return [
                'trackerId' => $trackerId,
                'name' => $data['name'],
                'success' => $success,
            ];
        }

        return [
            'title' => tr('Import Tracker Structure'),
            'modal' => $input->modal->int(),
        ];
    }

    public function action_import_items($input)
    {
        $trackerId = $input->trackerId->int();

        $perms = Perms::get('tracker', $trackerId);
        if (! $perms->admin_trackers) {
            throw new Services_Exception_Denied(tr('Reserved for tracker administrators'));
        }

        $definition = Tracker_Definition::get($trackerId);

        if (! $definition) {
            throw new Services_Exception_NotFound();
        }

        if (isset($_FILES['importfile'])) {
            if (! is_uploaded_file($_FILES['importfile']['tmp_name'])) {
                throw new Services_Exception(tr('File upload failed.'), 400);
            }

            if (! $fp = @ fopen($_FILES['importfile']['tmp_name'], "rb")) {
                throw new Services_Exception(tr('Uploaded file could not be read.'), 500);
            }

            $trklib = TikiLib::lib('trk');
            $count = $trklib->import_csv(
                $trackerId,
                $fp,
                ($input->add_items->int() !== 1), // checkbox is "Create as new items" - param is replace_rows
                $input->dateFormat->text(),
                $input->encoding->text(),
                $input->separator->text(),
                $input->updateLastModif->int(),
                $input->convertItemLinkValues->int()
            );

            fclose($fp);

            return [
                'trackerId' => $trackerId,
                'return' => $count,
                'importfile' => $_FILES['importfile']['name'],
            ];
        }

        return [
            'title' => tr('Import Items'),
            'trackerId' => $trackerId,
            'return' => '',
        ];
    }

    public function action_vote($input)
    {
        $requestData = [];
        $requestData['itemId'] = $input->i->int();
        $requestData['fieldId'] = $input->f->int();
        $requestData['vote'] = 'y';

        $v = $input->v->text();
        if ($v !== 'NULL') {
            $v = $input->v->int();
        }
        $requestData['ins_' . $requestData['fieldId']] = $v;

        $trklib = TikiLib::lib('trk');
        $field = $trklib->get_tracker_field($requestData['fieldId']);

        $handler = $trklib->get_field_handler($field);

        $result = $handler->getFieldData($requestData);

        return [$result];
    }

    public function action_import_profile($input)
    {
        $tikilib = TikiLib::lib('tiki');

        $perms = Perms::get();
        if (! $perms->admin) {
            throw new Services_Exception_Denied(tr('Reserved for administrators'));
        }

        unset($success);
        $confirm = $input->confirm->int();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $confirm) {
            $transaction = $tikilib->begin();
            $installer = new Tiki_Profile_Installer();

            $yaml = $input->yaml->text();
            $name = "tracker_import:" . md5($yaml);
            $profile = Tiki_Profile::fromString('{CODE(caption="yaml")}' . "\n" . $yaml . "\n" . '{CODE}', $name);

            if ($installer->isInstallable($profile) == true) {
                if ($installer->isInstalled($profile) == true) {
                    $installer->forget($profile);
                }

                $installer->install($profile);
                $feedback = $installer->getFeedback();
                $transaction->commit();
                return $feedback;
                $success = 1;
            } else {
                return false;
            }
        }
        return [
            'title' => tr('Import Tracker From Profile/YAML'),
            'modal' => $input->modal->int(),
        ];
    }

    private function getSortFields($definition)
    {
        $sorts = [];

        foreach ($definition->getFields() as $field) {
            $sorts[$field['fieldId']] = $field['name'];
        }

        $sorts[-1] = tr('Last Modification');
        $sorts[-2] = tr('Creation Date');
        $sorts[-3] = tr('Item ID');

        return $sorts;
    }

    private function getAttachmentAttributes($active)
    {
        $active = explode(',', $active);

        $available = [
            'filename' => tr('Filename'),
            'created' => tr('Creation date'),
            'hits' => tr('Views'),
            'comment' => tr('Comment'),
            'filesize' => tr('File size'),
            'version' => tr('Version'),
            'filetype' => tr('File type'),
            'longdesc' => tr('Long description'),
            'user' => tr('User'),
        ];

        $active = array_intersect(array_keys($available), $active);

        $attributes = array_fill_keys($active, null);
        foreach ($available as $key => $label) {
            $attributes[$key] = ['label' => $label, 'selected' => in_array($key, $active)];
        }

        return $attributes;
    }

    private function readDate($input, $prefix)
    {
        $date = $input->{$prefix . 'Date'}->text();
        $time = $input->{$prefix . 'Time'}->text();

        if (! $time) {
            $time = '00:00';
        }

        list($year, $month, $day) = explode('-', $date);
        list($hour, $minute) = explode(':', $time);
        $second = 0;

        $tikilib = TikiLib::lib('tiki');
        $tikidate = TikiLib::lib('tikidate');
        $display_tz = $tikilib->get_display_timezone();
        if ($display_tz == '') {
            $display_tz = 'UTC';
        }
        $tikidate->setTZbyID($display_tz);
        $tikidate->setLocalTime($day, $month, $year, $hour, $minute, $second, 0);
        return $tikidate->getTime();
    }

    private function format($date, $format)
    {
        if ($date) {
            return TikiLib::date_format($format, $date);
        }
    }

    private function getGroupList()
    {
        $userlib = TikiLib::lib('user');
        $groups = $userlib->list_all_groupIds();
        $out = [];

        foreach ($groups as $g) {
            $out[] = $g['groupName'];
        }

        return $out;
    }

    public function action_select_tracker($input)
    {
        $confirm = $input->confirm->int();

        if ($confirm) {
            $trackerId = $input->trackerId->int();
            return [
                'FORWARD' => [
                        'action' => 'insert_item',
                        'trackerId' => $trackerId,
                ],
            ];
        } else {
            $trklib = TikiLib::lib('trk');
            $trackers = $trklib->list_trackers();
            return [
                'title' => tr('Select Tracker'),
                'trackers' => $trackers["data"],
            ];
        }
    }

    public function action_search_help($input)
    {
        return [
            'title' => tr('Help'),
        ];
    }

    public function get_validation_options($formId = '')
    {
        $jsString = ',
        onkeyup: false,
        errorClass: "invalid-feedback",
        errorPlacement: function(error,element) {
            if ($(element).parents(".input-group").length > 0) {
                error.insertAfter($(element).parents(".input-group").first());
            } else {
                error.appendTo($(element).parents().first());
            }
        },
        highlight: function(element) {
            $(element).addClass("is-invalid");

            // Highlight chosen element if exists
            $("#" + element.getAttribute("id") + "_chosen").addClass("is-invalid");
        },
        unhighlight: function(element) {
            $(element).removeClass("is-invalid");

            // Unhighlight chosen element if exists
            $("#" + element.getAttribute("id") + "_chosen").removeClass("is-invalid");
        },
        ignore: ".ignore"
        });';

        if ($formId) {
            $jsString .= "\n" . '
                $("' . $formId . '").on("click.validate", ":submit", function(){$("' . $formId . '").find("[name^=other_ins_]").each(function(key, item){$(item).data("tiki_never_visited","")})});
            ';
        }

        return $jsString;
    }

    public function action_itemslist_output($input)
    {
        $trklib = TikiLib::lib('trk');
        $field = $trklib->get_tracker_field($input->field->int());
        if (! $field) {
            return '';
        }
        $fieldHandler = $trklib->get_field_handler($field, [
            $input->fieldIdHere->int() => $input->value->text()
        ]);
        if (! $fieldHandler) {
            return '';
        }
        return $fieldHandler->renderOutput();
    }

    public function actionFileTrackers($input)
    {
        $trk = TikiLib::lib('trk');

        $fields = $trk->get_fields_by_type('FG');
        $fields_data = [];
        foreach ($fields as $field) {
            $tracker = $trk->get_tracker($field['trackerId']);
            $fields_data[] = [
                'tracker_id' => $field['trackerId'],
                'field_id' => $field['fieldId'],
                'name' => $tracker['name'] . ' - ' . $field['name']
            ];
        }

        return [
            'title' => tr('Select Tracker'),
            'fields_data' => $fields_data
        ];
    }

    public function actionMoveItemFile($input)
    {
        $trklib = TikiLib::lib('trk');

        if (! $sourceItemId = $input->sourceItemId->int()) {
            throw new Services_Exception_MissingValue('sourceItemId');
        }

        if (! $targetItemId = $input->targetItemId->int()) {
            throw new Services_Exception_MissingValue('targetItemId');
        }

        if (! $sourceFieldId = $input->sourceFieldId->int()) {
            throw new Services_Exception_MissingValue('sourceFieldId');
        }

        if (! $targetFieldId = $input->targetFieldId->int()) {
            throw new Services_Exception_MissingValue('targetFieldId');
        }

        if (! $fileId = $input->fileId->int()) {
            throw new Services_Exception_MissingValue('fileId');
        }

        $sourceItemInfo = $trklib->get_tracker_item($sourceItemId);
        $targetItemInfo = $trklib->get_tracker_item($targetItemId);
        if (! $sourceItemInfo || ! $targetItemInfo) {
            throw new Services_Exception_NotFound();
        }

        $targetFieldInfo = $trklib->get_field_info($targetFieldId);
        if (! $targetFieldInfo) {
            throw new Services_Exception_NotFound();
        }

        $targetItemObject = Tracker_Item::fromInfo($targetItemInfo);
        if (! $targetItemObject->canModify()) {
            throw new Services_Exception_Denied();
        }

        $targetTrackerDefinition = Tracker_Definition::get($targetItemInfo['trackerId']);

        if (! $targetField = $targetTrackerDefinition->getField($targetFieldId)) {
            throw new Services_Exception_NotFound();
        }

        $sourceFieldInfo = $trklib->get_field_info($sourceFieldId);
        if (! $sourceFieldInfo) {
            throw new Services_Exception_NotFound();
        }

        $sourceTrackerDefinition = Tracker_Definition::get($sourceItemInfo['trackerId']);

        if (! $sourceField = $sourceTrackerDefinition->getField($sourceFieldId)) {
            throw new Services_Exception_NotFound();
        }

        $targetHandler = $trklib->get_field_handler($targetFieldInfo, $targetItemInfo);

        $this->utilities->updateItem(
            $targetTrackerDefinition,
            [
                'itemId' => $targetItemId,
                'fields' => [
                    $targetField['permName'] => $targetHandler->bindFiles($fileId)
                ]
            ]
        );

        if ($input->doAction->word() == 'move') {
            $sourceHandler = $trklib->get_field_handler($sourceFieldInfo, $sourceItemInfo);

            $this->utilities->updateItem(
                $sourceTrackerDefinition,
                [
                    'itemId' => $sourceItemId,
                    'fields' => [
                        $sourceField['permName'] => $sourceHandler->bindFiles($fileId, false)
                    ]
                ]
            );
        }

        return [
            'success' => true
        ];
    }
}