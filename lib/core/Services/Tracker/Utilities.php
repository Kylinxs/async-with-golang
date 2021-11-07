
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Services_Tracker_Utilities
{
    public function insertItem($definition, $item)
    {
        $newItem = $this->replaceItem($definition, 0, $item['status'], $item['fields'], [
            'validate' => isset($item['validate']) ? $item['validate'] : true,
            'skip_categories' => false,
            'bulk_import' => isset($item['bulk_import']) ? $item['bulk_import'] : false,
            'skip_sync' => $item['skip_sync'] ?? false,
            'deleted_files' => isset($item['deletedFiles']) ? $item['deletedFiles'] : []
        ]);

        return $newItem;
    }

    public function updateItem($definition, $item)
    {
        return $this->replaceItem($definition, $item['itemId'], $item['status'], $item['fields'], [
            'validate' => isset($item['validate']) ? $item['validate'] : true,
            'skip_categories' => false,
            'bulk_import' => isset($item['bulk_import']) ? $item['bulk_import'] : false,
            'skip_sync' => $item['skip_sync'] ?? false,
            'deleted_files' => isset($item['deletedFiles']) ? $item['deletedFiles'] : []
        ]);
    }

    public function resaveItem($itemId)
    {
        $tracker = TikiLib::lib('trk')->get_item_info($itemId);
        if (! $tracker) {
            return;
        }
        $definition = Tracker_Definition::get($tracker['trackerId']);
        if (! $definition) {
            return;
        }
        $this->replaceItem($definition, $itemId, null, [], [
            'validate' => false,
            'skip_categories' => true,
            'bulk_import' => true,
            'skip_sync' => true,
        ]);
    }

    public function validateItem($definition, $item, $fields = [])
    {
        $trackerId = $definition->getConfiguration('trackerId');
        if (! $fields) {
            $fields = $this->initializeItemFields($definition, $item['itemId'], $item['fields']);
        }

        $trklib = TikiLib::lib('trk');
        $categorizedFields = $definition->getCategorizedFields();
        $itemErrors = $trklib->check_field_values(['data' => $fields], $categorizedFields, $trackerId, $item['itemId'] ? $item['itemId'] : '');

        $errors = [];

        if (count($itemErrors['err_mandatory']) > 0) {
            $names = [];
            foreach ($itemErrors['err_mandatory'] as $f) {
                $names[] = $f['name'];
            }
            $errors[] = tr('The following mandatory fields are missing: %0', implode(', ', $names));
        }

        foreach ($itemErrors['err_value'] as $f) {
            if (! empty($f['errorMsg'])) {
                $errors[] = tr('Invalid value in %0: %1', $f['name'], $f['errorMsg']);
            } else {
                $errors[] = tr('Invalid value in %0', $f['name']);
            }
        }

        return $errors;
    }

    private function replaceItem($definition, $itemId, $status, $fieldMap, array $options)
    {
        $trackerId = $definition->getConfiguration('trackerId');
        $fields = $this->initializeItemFields($definition, $itemId, $fieldMap);

        $trklib = TikiLib::lib('trk');

        if ($options['validate']) {
            $errors = $this->validateItem($definition, ['itemId' => $itemId, 'fields' => $fieldMap], $fields);
        }

        if ($options['skip_categories']) {
            $categorizedFields = $definition->getCategorizedFields();
            foreach ($categorizedFields as $fieldId) {
                unset($fields[$fieldId]);
            }
        }

        if (! $options['validate'] || count($errors) == 0) {
            $newItem = $trklib->replace_item($trackerId, $itemId, ['data' => $fields], $status, 0, $options['bulk_import'], $options['skip_sync'], $options['deleted_files'] ?? []);
            return $newItem;
        }

        foreach ($errors as $err) {
            Feedback::error($err);
        }

        return false;
    }

    private function initializeItemFields($definition, $itemId, $fieldMap)
    {
        $fields = [];
        foreach ($fieldMap as $key => $value) {
            if (preg_match('/ins_/', $key)) { //make compatible with the 'ins_' keys
                $id = (int)str_replace('ins_', '', $key);
                if ($field = $definition->getField($id)) {
                    $field['value'] = $value;
                    $fields[$field['fieldId']] = $field;
                }
            } elseif ($field = $definition->getField($key)) {
                $field['value'] = $value;
                $fields[$field['fieldId']] = $field;
            } elseif ($field = $definition->getFieldFromPermName($key)) {
                $field['value'] = $value;
                $fields[$field['fieldId']] = $field;
            }
        }

        if ($itemId) {
            $item = $this->getItem($definition->getConfiguration('trackerId'), $itemId);
            $initialData = new JitFilter($item['fields']);
        } else {
            $initialData = new JitFilter([]);
        }

        // Add unspecified fields for the validation to work correctly
        foreach ($definition->getFields() as $field) {
            $fieldId = $field['fieldId'];
            if (! isset($fields[$fieldId])) {
                $permName = $field['permName'];
                $field['value'] = $initialData->$permName->none();
                $fields[$fieldId] = $field;
            }
        }

        return $fields;
    }

    public function createField(array $data)
    {
        $definition = Tracker_Definition::get($data['trackerId']);

        $isFirst = 0 === count($definition->getFields());

        $trklib = TikiLib::lib('trk');
        return $trklib->replace_tracker_field(
            $data['trackerId'],
            0,
            $data['name'],
            $data['type'],
            ($isFirst ? 'y' : 'n'),
            'n',
            ($isFirst ? 'y' : 'n'),
            'y',
            isset($data['isHidden']) ? $data['isHidden'] : 'n',
            isset($data['isMandatory']) ? ($data['isMandatory'] ? 'y' : 'n') : ($isFirst ? 'y' : 'n'),
            $trklib->get_last_position($data['trackerId']) + 10,
            isset($data['options']) ? $data['options'] : '',
            $data['description'],
            '',
            null,
            '',
            null,
            null,
            $data['descriptionIsParsed'] ? 'y' : 'n',
            '',
            '',
            '',
            $data['permName']
        );
    }

    public function updateField($trackerId, $fieldId, array $properties)
    {
        $definition = Tracker_Definition::get($trackerId);

        $field = $definition->getField($fieldId);
        $trklib = TikiLib::lib('trk');
        $trklib->replace_tracker_field(
            $trackerId,
            $fieldId,
            isset($properties['name']) ? $properties['name'] : $field['name'],
            isset($properties['type']) ? $properties['type'] : $field['type'],
            isset($properties['isMain']) ? $properties['isMain'] : $field['isMain'],
            isset($properties['isSearchable']) ? $properties['isSearchable'] : $field['isSearchable'],
            isset($properties['isTblVisible']) ? $properties['isTblVisible'] : $field['isTblVisible'],
            isset($properties['isPublic']) ? $properties['isPublic'] : $field['isPublic'],
            isset($properties['isHidden']) ? $properties['isHidden'] : $field['isHidden'],
            isset($properties['isMandatory']) ? $properties['isMandatory'] : $field['isMandatory'],
            isset($properties['position']) ? $properties['position'] : $field['position'],
            isset($properties['options']) ? $properties['options'] : $field['options'],
            isset($properties['description']) ? $properties['description'] : $field['description'],
            isset($properties['isMultilingual']) ? $properties['isMultilingual'] : $field['isMultilingual'],
            '', // itemChoices
            isset($properties['errorMsg']) ? $properties['errorMsg'] : $field['errorMsg'],
            isset($properties['visibleBy']) ? $properties['visibleBy'] : $field['visibleBy'],
            isset($properties['editableBy']) ? $properties['editableBy'] : $field['editableBy'],
            isset($properties['descriptionIsParsed']) ? $properties['descriptionIsParsed'] : $field['descriptionIsParsed'],
            isset($properties['validation']) ? $properties['validation'] : $field['validation'],
            isset($properties['validationParam']) ? $properties['validationParam'] : $field['validationParam'],
            isset($properties['validationMessage']) ? $properties['validationMessage'] : $field['validationMessage'],
            isset($properties['permName']) ? $properties['permName'] : $field['permName'],
            isset($properties['rules']) ? $properties['rules'] : $field['rules'],
            isset($properties['encryptionKeyId']) ? $properties['encryptionKeyId'] : $field['encryptionKeyId'],
            isset($properties['excludeFromNotification']) ? $properties['excludeFromNotification'] : $field['excludeFromNotification'],
            isset($properties['visibleInViewMode']) ? $properties['visibleInViewMode'] : $field['visibleInViewMode'],
            isset($properties['visibleInEditMode']) ? $properties['visibleInEditMode'] : $field['visibleInEditMode'],
            isset($properties['visibleInHistoryMode']) ? $properties['visibleInHistoryMode'] : $field['visibleInHistoryMode']
        );
    }

    /**
     * @param array $conditions     e.g. array('trackerId' => 42)
     * @param int $maxRecords       default -1 (all)
     * @param int $offset           default -1
     * @param array $fields         array of fields to fetch (by permNames)
     *
     * @return mixed
     */
    public function getItems(array $conditions, $maxRecords = -1, $offset = -1, $fields = [])
    {
        $keyMap = [];
        $definition = Tracker_Definition::get($conditions['trackerId']);
        foreach ($definition->getFields() as $field) {
            if (! empty($field['permName']) && (empty($fields) || in_array($field['permName'], $fields))) {
                $keyMap[$field['fieldId']] = $field['permName'];
            }
        }

        $table = TikiDb::get()->table('tiki_tracker_items');

        if (! empty($conditions['status'])) {
            $conditions['status'] = $table->in(str_split($conditions['status'], 1));
        } else {
            unset($conditions['status']);
        }

        if (! empty($conditions['modifiedSince'])) {
            $conditions['lastModif'] = $table->greaterThan($conditions['modifiedSince']);
        }

        if (! empty($conditions['itemId'])) {
            $conditions['itemId'] = $table->in((array) $conditions['itemId']);
        }

        unset($conditions['modifiedSince']);

        $items = $table->fetchAll(['itemId', 'status'], $conditions, $maxRecords, $offset);

        foreach ($items as & $item) {
            $item['fields'] = $this->getItemFields($item['itemId'], $keyMap);
        }

        return $items;
    }

    public function getItem($trackerId, $itemId)
    {
        $items = $this->getItems(
            [
                'trackerId' => $trackerId,
                'itemId' => $itemId,
            ],
            1,
            0
        );
        $item = reset($items);

        return $item;
    }

    public function getTitle($definition, $item)
    {
        $parts = [];

        foreach ($definition->getFields() as $field) {
            if ($field['isMain'] == 'y') {
                $permName = $field['permName'];
                $parts[] = $item['fields'][$permName];
            }
        }

        return implode(' ', $parts);
    }

    public function processValues($definition, $item)
    {
        $trklib = TikiLib::lib('trk');

        foreach ($item['fields'] as $permName => $rawValue) {
            $field = $definition->getFieldFromPermName($permName);
            $field['value'] = $rawValue;
            $item['fields'][$permName] = $trklib->field_render_value(
                [
                    'field' => $field,
                    'process' => 'y',
                ]
            );
        }

        return $item;
    }

    private function getItemFields($itemId, $keyMap)
    {
        $trklib = TikiLib::lib('trk');
        $item = $trklib->get_tracker_item($itemId);

        $out = [];
        foreach ($keyMap as $fieldId => $name) {
            $info = $trklib->get_field_info($fieldId);
            $handler = $trklib->get_field_handler($info, $item);
            $data = $handler->getFieldData();
            $out[$name] = $data['value'] ?? '';
        }

        return $out;
    }

    public function createTracker($data)
    {
        $trklib = TikiLib::lib('trk');
        return $trklib->replace_tracker(
            0,
            $data['name'],
            $data['description'],
            [],
            $data['descriptionIsParsed']
        );
    }

    public function updateTracker($trackerId, $data)
    {
        $trklib = TikiLib::lib('trk');
        $name = $data['name'];
        $description = $data['description'];
        $descriptionIsParsed = $data['descriptionIsParsed'];

        unset($data['name']);
        unset($data['description']);
        unset($data['descriptionIsParsed']);

        return $trklib->replace_tracker($trackerId, $name, $description, $data, $descriptionIsParsed);
    }

    public function clearTracker($trackerId)
    {
        $table = TikiDb::get()->table('tiki_tracker_items');

        $items = $table->fetchColumn(
            'itemId',
            ['trackerId' => $trackerId,]
        );
        $success = 0;
        foreach ($items as $itemId) {
            $result = $this->removeItem($itemId);
            if ($result && $result->numRows()) {
                $success++;
            }
        }

        $trklib = TikiLib::lib('trk');
        $options = $trklib->get_tracker_options($trackerId);
        if (! empty($options['tabularSyncLastImport'])) {
            $trklib->replace_tracker_option($trackerId, 'tabularSyncLastImport', null);
        }

        return $success;
    }

    public function importField($trackerId, $field, $preserve, $lastposition = 0)
    {
        if ($lastposition == 1 || ! $field->position->int()) {
            // No position parameter was provided or user requested that new fields are added to the bottom
            $trklib = TikiLib::lib('trk');
            $position = $trklib->get_last_position($trackerId) + 10;
        } else {
            $position = $field->position->int();
        }

        if (! $preserve) {
            $fieldId = 0;
        } else {
            $fieldId = $field->fieldId->int();
        }

        $description = $field->descriptionStaticText->text();
        if (! $description) {
            $description = $field->description->text();
        }

        $data = [
                'name' => $field->name->text(),
                'permName' => $field->permName->word(),
                'type' => $field->type->word(),
                'position' => $position,
                'options' => $field->options->none(),

                'isMain' => $field->isMain->alpha(),
                'isSearchable' => $field->isSearchable->alpha(),
                'isTblVisible' => $field->isTblVisible->alpha(),
                'isPublic' => $field->isPublic->alpha(),
                'isHidden' => $field->isHidden->alpha(),
                'isMandatory' => $field->isMandatory->alpha(),
                'isMultilingual' => $field->isMultilingual->alpha(),

                'description' => $description,
                'descriptionIsParsed' => $field->descriptionIsParsed->alpha(),

                'validation' => $field->validation->word(),
                'validationParam' => $field->validationParam->none(),
                'validationMessage' => $field->validationMessage->text(),

                'itemChoices' => '',

                'editableBy' => $field->editableBy->groupname(),
                'visibleBy' => $field->visibleBy->groupname(),
                'errorMsg' => $field->errorMsg->text(),

                'rules' => $field->rules->text(),
                ];

        // enable prefs for imported fields if required
        $factory = new Tracker_Field_Factory(false);
        $completeList = $factory->getFieldTypes();

        if (! $this->isEnabled($completeList[$data['type']])) {
            foreach ($completeList[$data['type']]['prefs'] as $pref) {
                TikiLib::lib('tiki')->set_preference($pref, 'y');
            }
        }

        $this->updateField($trackerId, $fieldId, $data);
    }

    public function exportField($field)
    {
        return <<<EXPORT
[FIELD{$field['fieldId']}]
fieldId = {$field['fieldId']}
name = {$field['name']}
permName = {$field['permName']}
position = {$field['position']}
type = {$field['type']}
options = {$field['options']}
isMain = {$field['isMain']}
isTblVisible = {$field['isTblVisible']}
isSearchable = {$field['isSearchable']}
isPublic = {$field['isPublic']}
isHidden = {$field['isHidden']}
isMandatory = {$field['isMandatory']}
description = {$field['description']}
descriptionIsParsed = {$field['descriptionIsParsed']}
rules = {$field['rules']}

EXPORT;
    }

    public function buildOptions($input, $typeInfo)
    {
        if (is_string($typeInfo)) {
            $types = $this->getFieldTypes();
            $typeInfo = $types[$typeInfo];
        }

        if (is_array($input)) {
            $input = new JitFilter($input);
        }

        $options = Tracker_Options::fromInput($input, $typeInfo);
        return $options->serialize();
    }

    public function parseOptions($raw, $typeInfo)
    {
        $options = Tracker_Options::fromSerialized($raw, $typeInfo);

        return $options->getAllParameters();
    }

    public function getFieldTypesDisabled()
    {
        $factory = new Tracker_Field_Factory(false);
        $completeList = $factory->getFieldTypes();

        $list = [];

        foreach ($completeList as $code => $info) {
            if ($this->isEnabled($info) == false) {
                $list[$code] = $info;
            }
        }

        return $list;
    }

    public function getFieldTypes($filter = [])
    {
        $factory = new Tracker_Field_Factory(false);
        $completeList = $factory->getFieldTypes();

        if (! empty($filter)) {
            $completeList = array_intersect_key($completeList, array_flip($filter));
        }

        $list = [];

        foreach ($completeList as $code => $info) {
            if ($this->isEnabled($info)) {
                $list[$code] = $info;
            }
        }

        return $list;
    }

    private function isEnabled($info)
    {
        global $prefs;

        foreach ($info['prefs'] as $p) {
            if ($prefs[$p] != 'y') {
                return false;
            }
        }

        return true;
    }

    public function getFieldsFromIds($definition, $fieldIds)
    {
        $fields = [];
        foreach ($fieldIds as $fieldId) {
            $field = $field = $definition->getField($fieldId);

            if (! $field) {
                throw new Services_Exception(tr('Field %0 does not exist in tracker', $fieldId), 404);
            }

            $fields[] = $field;
        }

        return $fields;
    }

    public function removeItem($itemId)
    {
        $trklib = TikiLib::lib('trk');
        return $trklib->remove_tracker_item($itemId, true);
    }

    public function removeItemAndReferences($definition, $itemObject, $uncascaded, $replacement)
    {
        $tx = TikiDb::get()->begin();

        $itemData = $itemObject->getData();
        foreach ($definition->getFields() as $field) {
            $handler = $definition->getFieldFactory()->getHandler($field, $itemData);
            if (method_exists($handler, 'handleDelete')) {
                $handler->handleDelete();
            }
        }

        TikiLib::lib('trk')->replaceItemReferences($replacement, $uncascaded['itemIds'], $uncascaded['fieldIds']);

        $this->removeItem($itemObject->getId());

        $tx->commit();
    }

    public function removeTracker($trackerId)
    {
        $trklib = TikiLib::lib('trk');
        $trklib->remove_tracker($trackerId);
    }

    public function duplicateTracker($trackerId, $name, $duplicateCategories, $duplicatePermissions)
    {
        $trklib = TikiLib::lib('trk');
        $newTrackerId = $trklib->duplicate_tracker($trackerId, $name, '', 'n');

        if ($duplicateCategories) {
            $categlib = TikiLib::lib('categ');
            $cats = $categlib->get_object_categories('tracker', $trackerId);
            $catObjectId = $categlib->add_categorized_object('tracker', $newTrackerId, '', $name, "tiki-view_tracker.php?trackerId=$newTrackerId");
            foreach ($cats as $cat) {
                $categlib->categorize($catObjectId, $cat);
            }
        }

        if ($duplicatePermissions) {
            $userlib = TikiLib::lib('user');
            $userlib->copy_object_permissions($trackerId, $newTrackerId, 'tracker');
        }

        return $newTrackerId;
    }

    /**
     * @param Tracker_Definition $definition
     * @param array $itemData
     * @param int $itemId
     * @param boolean $strict
     *
     * @return Tracker_Item|bool Return the new tracker item of false in case of failure
     * @throws Exception
     */
    public function cloneItem($definition, $itemData, $itemId, $strict = false)
    {
        $transaction = TikiLib::lib('tiki')->begin();

        foreach ($definition->getFields() as $field) {
            $handler = $definition->getFieldFactory()->getHandler($field, $itemData);
            if (method_exists($handler, 'handleClone')) {
                $newData = $handler->handleClone($strict);
                $itemData['fields'][$field['permName']] = $newData['value'];
            }
        }

        $id = $this->insertItem($definition, $itemData);
        if ($id === false) {
            $transaction->commit(); // there is no rollback
            return false;
        }
        $insertIds = [$id];

        $itemObject = Tracker_Item::fromId($id);

        foreach (TikiLib::lib('trk')->get_child_items($itemId) as $info) {
            $field = TikiLib::lib('trk')->get_tracker_field($info['field']);
            $factory = new Tracker_Field_Factory();
            $options = Tracker_Options::fromSerialized($field['options'], $factory->getFieldInfo($field['type']));
            if (! $options->getParam('duplicateCascade')) {
                continue;
            }

            $childItem = Tracker_Item::fromId($info['itemId']);

            if ($childItem->canView()) {
                $childItem->asNew();
                $data = $childItem->getData();
                $data['fields'][$info['field']] = $id;

                $childDefinition = $childItem->getDefinition();

                // handle specific cloning actions

                foreach ($childDefinition->getFields() as $field) {
                    $handler = $childDefinition->getFieldFactory()->getHandler($field, $data);
                    if (method_exists($handler, 'handleClone')) {
                        $newData = $handler->handleClone($strict);
                        $data['fields'][$field['permName']] = $newData['value'];
                    }
                }

                $new = $this->insertItem($childDefinition, $data);
                if ($new === false) {
                    foreach ($insertIds as $id) { // undo items already created
                        $this->removeItem($id);
                    }
                    $transaction->commit(); // there is no rollback
                    return false;
                }
                $insertIds[] = $new;
            }
        }

        $transaction->commit();

        return $itemObject;
    }

    public static function convertToDefaultCurrency($data)
    {
        $trk = TikiLib::lib('trk');
        $rates = $trk->exchange_rates($data['date']);

        $defaultCurrency = array_search(1, $rates);
        if (empty($defaultCurrency)) {
            $defaultCurrency = 'USD';
        }

        $currency = new Math_Formula_Currency($data['amount'], $data['currency'], $rates);
        return $currency->convertTo($defaultCurrency)->getAmount();
    }
}