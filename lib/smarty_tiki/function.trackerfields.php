
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/*
 * Render fields of a trackeritem when called from the tracker
 * @param array $params - params passed from tempate as key/value pairs
 * Added keys to set the view/edit item template in tiki14. Format as defined in the tracker, i.e. 'wiki:myPageName, 'tpl:myTpl.tpl'
 * 'viewItemPretty': define a template to view the item.
 * 'editItemPretty': define a template to edit the item.
 * These keys treat the template setting in the tracker as a default value and will therefore override if present.
 * They only apply if the default setting would apply - i.e sectionformat must be set to configured.
 */
function smarty_function_trackerfields($params, $smarty)
{
    if (! isset($params['fields']) || ! is_array($params['fields'])) {
        return tr('Invalid fields provided.');
    }

    if (! isset($params['trackerId']) || ! $definition = Tracker_Definition::get($params['trackerId'])) {
        return tr('Missing or invalid tracker reference.');
    }

    if (! isset($params['mode'])) {
        $params['mode'] = 'edit';
    }

    $smarty->loadPlugin('smarty_function_trackeroutput');
    $smarty->loadPlugin('smarty_function_trackerinput');

    $sectionFormat = $definition->getConfiguration('sectionFormat', 'flat');

    if (! empty($params['format'])) {
        $sectionFormat = $params['format'];
    }

    $editItemPretty = isset($params['editItemPretty']) ? $params['editItemPretty'] : '';
    $viewItemPretty = isset($params['viewItemPretty']) ? $params['viewItemPretty'] : '';

    $trklib = TikiLib::lib('trk');
    $trklib->registerSectionFormat('config', 'edit', $editItemPretty, tr('Configured'));
    $trklib->registerSectionFormat('config', 'view', $viewItemPretty, tr('Configured'));
    $template = $trklib->getSectionFormatTemplate($sectionFormat, $params['mode']);

    // smarty doesn't use tpl: as a resource prefix any more
    $template = stripos($template, 'tpl:') === 0 ? substr($template, 4) : $template;

    $trklib->unregisterSectionFormat('config');

    $prettyModifier = [];
    if (stripos($template, 'wiki:') === 0) {
        $trklib->get_pretty_fieldIds(substr($template, 5), 'wiki', $prettyModifier, $params['trackerId']);
    } else {
        $trklib->get_pretty_fieldIds($template, 'tpl', $prettyModifier, $params['trackerId']);
    }

    $trackerInfo = $definition->getInformation();
    $smarty->assign('tracker_info', $trackerInfo);
    $smarty->assign('status_types', TikiLib::lib('trk')->status_types());

    $title = tr('General');
    $sections = [];
    $auto = ['input' => [], 'output' => [], 'inline' => []];

    $datepicker = false;
    foreach ($params['fields'] as $field) {
        if ($field['type'] == 'h') {
            $title = tr($field['name']);
        } else {
            $sections[$title][] = $field;
        }
        $permName = $field['permName'];

        $itemId = isset($params['itemId']) ? $params['itemId'] : null;
        if ($itemId) {
            $item = ['itemId' => $itemId];
        } else {
            $item = [];
        }
        $smarty->assign('item', $item);

        $auto['input'][$permName] = new Tiki_Render_Lazy(function () use ($field, $smarty, $item) {
            return smarty_function_trackerinput([
                'field' => $field,
                'showlinks' => 'n',
                'list_mode' => 'n',
                'item' => $item,
            ], $smarty);
        });


        // the item-list field needs the itemId here - passed via the template - otherwise it does not show a value in the template
        $auto['output'][$permName] = new Tiki_Render_Lazy(function () use ($field, $smarty, $itemId) {
            return smarty_function_trackeroutput([
                'field' => $field,
                'showlinks' => 'n',
                'list_mode' => 'n',
                'itemId' => $itemId,
            ], $smarty);
        });


        // not sure wether we can always pass itemId bc i do not know wether the key or the value is checked
        if ($itemId) {
            $auto['inline'][$permName] = new Tiki_Render_Lazy(function () use ($field, $smarty, $itemId) {
                return smarty_function_trackeroutput([
                    'field' => $field,
                    'showlinks' => 'n',
                    'list_mode' => 'n',
                    'editable' => 'inline',
                    'itemId' => $itemId,
                ], $smarty);
            });
        }

        if ($field['type'] == 'j') {
            $datepicker = true;
        }
    }

    $out = [];
    foreach ($sections as $title => $fields) {
        $out[md5($title)] = [
            'heading' => $title,
            'fields' => $fields,
        ];
    }

    if ($params['mode'] == 'view') {
        $auto['default'] = $auto['output'];
    } else {
        $auto['default'] = $auto['input'];
    }

    // Compatibility attempt with the legacy $f_X format.
    // Note: Here we set the the closures for the field, NOT the final values!
    // The final values are set in trackerlib.php using field_render_value()
    // Using $params['fields'] as $fields is only the last "section" now
    foreach ($params['fields'] as $field) {
        $id = $field['fieldId'];
        $permName = $field['permName'];
        if (empty($prettyModifier[$id])) {
            $smarty->assign('f_' . $id, $auto['default'][$permName]);
            // https://doc.tiki.org/Pretty+Tracker states that next to {f_id} also {f_fieldname} can be used.
            // Somehow there is the support missing here - so add it
            $smarty->assign('f_' . $permName, $auto['default'][$permName]);
        } elseif ($prettyModifier[$id] == "output") {
            $smarty->assign('f_' . $id, $auto['output'][$permName]);
            $smarty->assign('f_' . $permName, $auto['output'][$permName]);
        } else {
            $smarty->assign("field_name", $field['name']);
            $smarty->assign("field_id", $id);
            $smarty->assign("permname", $permName);
            $smarty->assign("mandatory_sym", '');
            $smarty->assign("field_input", $auto['input'][$permName]);
            $smarty->assign("description", '');
            $smarty->assign("field_type", $field['type']);
            $prettyout = $smarty->fetch($prettyModifier[$id]); //fetch template identified in prettyModifier
            $smarty->assign('f_' . $id, $prettyout);
            $smarty->assign('f_' . $permName, $prettyout);
        }
    }

    $smarty->assign('sections', array_values($out));
    $smarty->assign('fields', $params['fields']);
    $smarty->assign('auto', $auto);

    try {
        $result = $smarty->fetch($template);
    } catch (Exception $e) {
        // catch any exception probably casued by a pretty tracker template issue
        Feedback::error(tr('Tracker rendering error (section="%0" mode="%1")', $sectionFormat, $params['mode']) .
            '<br><br>' . htmlentities($e->getMessage()));
        // try again with the default section format "flat"
        $template = $trklib->getSectionFormatTemplate('flat', $params['mode']);
        $result = $smarty->fetch($template);
    }

    if ($datepicker) {
        $smarty->loadPlugin('smarty_function_js_insert_icon');
        $result .= smarty_function_js_insert_icon(['type' => "jscalendar"], $smarty);
    }

    return $result;
}