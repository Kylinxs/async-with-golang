<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function wikiplugin_pivottable_info()
{
    return [
        'name' => tr('Pivot table'),
        'description' => tr('Create and display data in pivot table for reporting'),
        'prefs' => ['wikiplugin_pivottable'],
        'body' => tra('Leave one space in the box below to allow easier editing of current values with the plugin popup helper later on'),
        'validate' => 'all',
        'format' => 'html',
        'iconname' => 'table',
        'introduced' => '16.1',
        'params' => [
            'data' => [
                'name' => tr('Data source'),
                'description' => tr("For example 'tracker:1' or 'activitystream'"),
                'required' => true,
                'default' => 0,
                'filter' => 'text',
                'profile_reference' => 'tracker',
                'separator' => ':',
                'profile_reference_extra_values' => ['activitystream' => 'Activity Stream'],
            ],
            'overridePermissions' => [
                'name' => tra('Override item permissions'),
                'description' => tra('Return all tracker items ignoring permissions to view the corresponding items.'),
                'since' => '18.1',
                'required' => false,
                'filter' => 'alpha',
                'default' => 'n',
                'options' => [
                    ['text' => '', 'value' => ''],
                    ['text' => tra('Yes'), 'value' => 'y'],
                    ['text' => tra('No'), 'value' => 'n']
                ]
            ],
            'width' => [
                'required' => false,
                'name' => tra('Width'),
                'description' => tr('Width of charts. You have to only put the value (Unit: px). For instance, use <code>500</code> for 500 pixels.'),
                'since' => '',
                'filter' => 'word',
                'default' => '100%',
            ],
            'height' => [
                'required' => false,
                'name' => tra('Height'),
                'description' => tr('Height of charts. You have to only put the value (Unit: px). For instance, use <code>500</code> for 500 pixels.'),
                'since' => '',
                'filter' => 'word',
                'default' => '400px',
            ],
            'rows' => [
                'required' => false,
                'name' => tra('Pivot table Rows'),
                'description' => tr('Which field or fields to use as table rows. Leaving blank will remove grouping by table rows. ') . ' ' . tr('Use permanentNames in case of tracker fields.') . ' ' . tr('Separated by colon (:) if more than one.'),
                'since' => '',
                'filter' => 'text',
                'default' => '',
                'profile_reference' => 'tracker_field',
                'separator' => ':',
            ],
            'cols' => [
                'required' => false,
                'name' => tra('Pivot table Columns'),
                'description' => tr('Which field or fields to use as table columns. Leaving blank will use the first available field.') . ' ' . tr('Use permanentNames in case of tracker fields.') . ' ' . tr('Separated by colon (:) if more than one.'),
                'since' => '',
                'filter' => 'text',
                'default' => '',
                'profile_reference' => 'tracker_field',
                'separator' => ':',
            ],
            'colOrder' => [
                'required' => false,
                'name' => 'Column sort order',
                'description' => tr('The order in which column data is provided to the renderer, must be one of "key_a_to_z", "value_a_to_z", "value_z_to_a", ordering by value orders by column total.'),
                'since' => '',
                'filter' => 'text',
                'default' => '',
            ],
            'rowOrder' => [
                'required' => false,
                'name' => 'Row sort order',
                'description' => tr('The order in which row data is provided to the renderer, must be one of "key_a_to_z", "value_a_to_z", "value_z_to_a", ordering by value orders by row total.'),
                'since' => '',
                'filter' => 'text',
                'default' => '',
            ],
            'heatmapDomain' => [
                'required' => false,
                'name' => tra('Values used to decide what heatmapColors to use.'),
                'description' => tr(''),
                'since' => '17',
                'filter' => 'text',
                'default' => '',
                'separator' => ':',
            ],
            'heatmapColors' => [
                'required' => false,
                'name' => tra('Color for each heatmapDomain value.'),
                'description' => tr(''),
                'since' => '17',
                'filter' => 'text',
                'default' => '',
                'separator' => ':',
            ],
            'rendererName' => [
                'name' => tr('Renderer Name'),
                'description' => tr('Display format of data'),
                'since' => '',
                'required' => false,
                'filter' => 'text',
                'default' => 'Table',
                'options' => [
                    ['text' => 'Table', 'value' => 'Table'],
                    ['text' => tra('Table Barchart'), 'value' => 'Table Barchart'],
                    ['text' => tra('Heatmap'), 'value' => 'Heatmap'],
                    ['text' => tra('Row Heatmap'), 'value' => 'Row Heatmap'],
                    ['text' => tra('Col Heatmap'), 'value' => 'Col Heatmap'],
                    ['text' => tra('Line Chart'), 'value' => 'Line Chart'],
                    ['text' => tra('Bar Chart'), 'value' => 'Bar Chart'],
                    ['text' => tra('Overlay Bar Chart'), 'value' => 'Overlay Bar Chart'],
                    ['text' => tra('Stacked Bar Chart'), 'value' => 'Stacked Bar Chart'],
                    ['text' => tra('Relative Bar Chart'), 'value' => 'Relative Bar Chart'],
                    ['text' => tra('Boxplot Chart'), 'value' => 'Boxplot Chart'],
                    ['text' => tra('Horizontal Boxplot Chart'), 'value' => 'Horizontal Boxplot Chart'],
                    ['text' => tra('Area Chart'), 'value' => 'Area Chart'],
                    ['text' => tra('Histogram'), 'value' => 'Histogram'],
                    ['text' => tra('Density Histogram'), 'value' => 'Density Histogram'],
                    ['text' => tra('Percent Histogram'), 'value' => 'Percent Histogram'],
                    ['text' => tra('Probability Histogram'), 'value' => 'Probability Histogram'],
                    ['text' => tra('Density Histogram Horizontal'), 'value' => 'Density Histogram Horizontal'],
                    ['text' => tra('Percent Histogram Horizontal'), 'value' => 'Percent Histogram Horizontal'],
                    ['text' => tra('Probability Histogram Horizontal'), 'value' => 'Probability Histogram Horizontal'],
                    ['text' => tra('Horizontal Histogram'), 'value' => 'Horizontal Histogram'],
                    ['text' => tra('Histogram2D'), 'value' => 'Histogram2D'],
                    ['text' => tra('Density Histogram2D'), 'value' => 'Density Histogram2D'],
                    ['text' => tra('Percent Histogram2D'), 'value' => 'Percent Histogram2D'],
                    ['text' => tra('Probability Histogram2D'), 'value' => 'Probability Histogram2D'],
                    ['text' => tra('Density Histogram2D Horizontal'), 'value' => 'Density Histogram2D Horizontal'],
                    ['text' => tra('Percent Histogram2D Horizontal'), 'value' => 'Percent Histogram2D Horizontal'],
                    ['text' => tra('Probability Histogram2D Horizontal'), 'value' => 'Probability Histogram2D Horizontal'],
                    ['text' => tra('Horizontal Histogram2D'), 'value' => 'Horizontal Histogram2D'],
                    ['text' => tra('Scatter Chart'), 'value' => 'Scatter Chart'],
                    ['text' => tra('Treemap'), 'value' => 'Treemap']
                ]
            ],
            'aggregatorName' => [
                'name' => tr('Aggregator Name'),
                'description' => tr('Function to apply on the numeric values from the variables selected.'),
                'since' => '',
                'required' => false,
                'filter' => 'text',
                'default' => 'Count',
                'options' => [
                    ['text' => 'Count', 'value' => 'Count'],
                    ['text' => tra('Count Unique Values'), 'value' => 'Count Unique Values'],
                    ['text' => tra('List Unique Values'), 'value' => 'List Unique Values'],
                    ['text' => tra('Sum'), 'value' => 'Sum'],
                    ['text' => tra('Integer Sum'), 'value' => 'Integer Sum'],
                    ['text' => tra('Average'), 'value' => 'Average'],
                    ['text' => tra('Minimum'), 'value' => 'Minimum'],
                    ['text' => tra('Maximum'), 'value' => 'Maximum'],
                    ['text' => tra('Sum over Sum'), 'value' => 'Sum over Sum'],
                    ['text' => tra('80% Upper Bound'), 'value' => '80% Upper Bound'],
                    ['text' => tra('80% Lower Bound'), 'value' => '80% Lower Bound'],
                    ['text' => tra('Sum as Fraction of Total'), 'value' => 'Sum as Fraction of Total'],
                    ['text' => tra('Sum as Fraction of Rows'), 'value' => 'Sum as Fraction of Rows'],
                    ['text' => tra('Sum as Fraction of Columns'), 'value' => 'Sum as Fraction of Columns'],
                    ['text' => tra('Count as Fraction of Total'), 'value' => 'Count as Fraction of Total'],
                    ['text' => tra('Count as Fraction of Rows'), 'value' => 'Count as Fraction of Rows'],
                    ['text' => tra('Count as Fraction of Columns'), 'value' => 'Count as Fraction of Columns']
                ]
            ],
            'vals' => [
                'name' => tr('Values'),
                'description' => tr('Variable with numeric values or tracker field permNames, on which the formula from the aggregator is applied. It can be left empty if aggregator is related to Counts.') . ' ' . tr('Use permanentNames in case of tracker fields, separated by : in case of multiple fields function.'),
                'since' => '',
                'required' => false,
                'filter' => 'text',
                'profile_reference' => 'tracker_field',
                'separator' => ':',
            ],
            'inclusions' => [
                'name' => tr('Inclusions'),
                'description' => tr('Filter values for fields in rows or columns. Contains JSON encoded object of arrays of strings.'),
                'since' => '',
                'required' => false,
                'filter' => 'text',
            ],
            'menuLimit' => [
                'name' => tr('Filter list limit'),
                'description' => tr('Pivottable menuLimit option override - number of entries to consider the menu list too big when filtering on a particular column or row.'),
                'since' => '16.2',
                'required' => false,
                'filter' => 'digits',
            ],
            'aggregateDetails' => [
                'name' => tr('Aggregate details'),
                'description' => tr('When enabled, clicking a table cell will popup all items that were aggregated into that cell. Specify the name of the field or fields to use to display the details separated by colon. Enabled by default. To disable, set contents to an empty string.'),
                'since' => '16.2',
                'required' => false,
                'filter' => 'text',
                'profile_reference' => 'tracker_field',
                'separator' => ':',
            ],
            'aggregateDetailsFormat' => [
                'name' => tr('Aggregate details format'),
                'description' => tr('Uses the translate function to replace %0 etc with the aggregate field values. E.g. "%0 any text %1"'),
                'since' => '22.1',
                'required' => false,
                'filter' => 'text',
                'depends' => [
                    'field' => 'aggregateDetails'
                ],
            ],
            'aggregateDetailsCallback' => [
                'name' => tr('Aggregate details popup building function callback'),
                'description' => tr('Use custom javascript function to build the aggregate details popup window.'),
                'since' => '24.1',
                'required' => false,
                'filter' => 'text',
                'depends' => [
                    'field' => 'aggregateDetails'
                ],
            ],
            'highlightMine' => [
                'name' => tra('Highlight my items'),
                'description' => tra('Highlight owned items\' values in Charts.'),
                'since' => '16.3',
                'required' => false,
                'filter' => 'alpha',
                'default' => 'n',
                'options' => [
                    ['text' => '', 'value' => ''],
                    ['text' => tra('Yes'), 'value' => 'y'],
                    ['text' => tra('No'), 'value' => 'n']
                ]
            ],
            'highlightGroup' => [
                'name' => tra('Highlight my group items'),
                'description' => tra('Highlight items\' values belonging to one of my groups in Charts.'),
                'since' => '16.3',
                'required' => false,
                'filter' => 'alpha',
                'default' => 'n',
                'options' => [
                    ['text' => '', 'value' => ''],
                    ['text' => tra('Yes'), 'value' => 'y'],
                    ['text' => tra('No'), 'value' => 'n']
                ]
            ],
            'highlightGroupColors' => [
                'required' => false,
                'name' => tra('Color for each highlighted group items.'),
                'description' => tr(''),
                'since' => '18.1',
                'filter' => 'text',
                'default' => '',
                'separator' => ':',
            ],
            'xAxisLabel' => [
                'name' => tr('xAxis label'),
                'description' => tr('Override label of horizontal axis when using Chart renderers.'),
                'since' => '16.3',
                'required' => false,
                'filter' => 'text',
            ],
            'yAxisLabel' => [
                'name' => tr('yAxis label'),
                'description' => tr('Override label of vertical axis when using Chart renderers.'),
                'since' => '16.3',
                'required' => false,
                'filter' => 'text',
            ],
            'chartTitle' => [
                'name' => tr('Chart title'),
                'description' => tr('Override title when using Chart renderers.'),
                'since' => '16.3',
                'required' => false,
                'filter' => 'text',
            ],
            'chartHoverBar' => [
                'name' => tr('Chart hover bar'),
                'description' => tr('Display the Chart hover bar or not.'),
                'since' => '16.3',
                'required' => false,
                'filter' => 'alpha',
                'default' => 'y',
                'options' => [
                    ['text' => tra('Yes'), 'value' => 'y'],
                    ['text' => tra('No'), 'value' => 'n']
                ]
            ],
            'translate' => [
                'name' => tr('Translate displayed data'),
                'description' => tr('Use translated data values for calculations and display.') . ' ' . tr('Default value: No'),
                'since' => '18.3',
                'required' => false,
                'filter' => 'alpha',
                'default' => 'n',
                'options' => [
                    ['text' => '', 'value' => ''],
                    ['text' => tra('No'), 'value' => 'n'],
                    ['text' => tra('Yes'), 'value' => 'y']
                ]
            ],
            'displayBeforeFilter' => [
                'name' => tr('Load data before filters are applied'),
                'description' => tr('Load PivotTable results on initial page load even before applying "editable" filters. Turn this off if you have a large data set and plan to use "editable" filters to dynamically filter it.') . ' ' . tr('Default value: Yes'),
                'since' => '21.1',
                'required' => false,
                'filter' => 'alpha',
                'default' => 'y',
                'options' => [
                    ['text' => '', 'value' => ''],
                    ['text' => tra('No'), 'value' => 'n'],
                    ['text' => tra('Yes'), 'value' => 'y']
                ]
            ]
        ],
    ];
}

function wikiplugin_pivottable($data, $params)
{

    //included globals for permission check
    global $prefs, $page, $wikiplugin_included_page, $user;

    //checking if vendor files are present
    if (! file_exists('vendor_bundled/vendor/nicolaskruchten/pivottable/')) {
        return WikiParser_PluginOutput::internalError(tr('Missing required files, please make sure plugin files are installed at vendor_bundled/vendor/nicolaskruchten/pivottable. <br/><br /> To install, please run composer or download from following url:<a href="https://github.com/nicolaskruchten/pivottable/archive/master.zip" target="_blank">https://github.com/nicolaskruchten/pivottable/archive/master.zip</a>'));
    }

    static $id = 0;
    $id++;

    $headerlib = TikiLib::lib('header');
    $headerlib->add_cssfile('vendor_bundled/vendor/nicolaskruchten/pivottable/dist/pivot.css');
    $headerlib->add_jsfile('vendor_bundled/vendor/nicolaskruchten/pivottable/dist/pivot.js', true);
    $headerlib->add_jsfile('vendor_bundled/vendor/plotly/plotly.js/dist/plotly-cartesian.min.js', true);
    $headerlib->add_jsfile('vendor_bundled/vendor/nagarajanchinnasamy/subtotal/dist/subtotal.min.js', true);
    $headerlib->add_jsfile('lib/jquery_tiki/wikiplugin-pivottable.js', true);

    $lang = substr($prefs['site_language'], 0, 2);
    if (file_exists('vendor_bundled/vendor/nicolaskruchten/pivottable/dist/pivot.' . $lang . '.js')) {
        $headerlib->add_jsfile('vendor_bundled/vendor/nicolaskruchten/pivottable/dist/pivot.' . $lang . '.js', true);
    }

    $translate = (! empty($params['translate']) && $params['translate'] == 'y') ? true : false;

    $smarty = TikiLib::lib('smarty');
    $smarty->assign('lang', $lang);


    //checking data type
    if (empty($params['data']) || ! is_array($params['data'])) {
        return WikiParser_PluginOutput::internalError(tr('Missing data parameter with format: source:ID, e.g. tracker:1'));
    }
    $dataType = $params['data'][0];
    if ($dataType !== 'activitystream' && $dataType !== 'tracker') {
        return WikiParser_PluginOutput::internalError(tr('Error data parameter'));
    }

    if (! empty($params['rendererName'])) {
        $rendererName = $params['rendererName'];
    } else {
        $rendererName = "Table";
    }

    if (! empty($params['aggregatorName'])) {
        $aggregatorName = $params['aggregatorName'];
    } else {
        $aggregatorName = "Count";
    }

    if (! empty($params['width'])) {
        $width = $params['width'];
    } else {
        $width = "100%";
    }

    if (! empty($params['height'])) {
        $height = $params['height'];
    } else {
        $height = "1000px";
    }

    if ($dataType === "tracker") {
        $trackerIds = preg_split('/\s*,\s*/', $params['data'][1]);
        $definitions = [];
        $fields = [];

        foreach ($trackerIds as $trackerId) {
            $definition = Tracker_Definition::get($trackerId);
            if (! $definition) {
                return WikiParser_PluginOutput::userError(tr('Tracker data source not found.'));
            }

            $definitions[] = $definition;

            $perms = Perms::get(['type' => 'tracker', 'object' => $trackerId]);

            $tracker_fields = $definition->getFields();

            if (! $perms->admin_trackers && $params['overridePermissions'] !== 'y') {
                $hasFieldPermissions = false;
                foreach ($tracker_fields as $key => $field) {
                    $isHidden = $field['isHidden'];
                    $visibleBy = $field['visibleBy'];

                    if ($isHidden != 'n' || ! empty($visibleBy)) {
                        $hasFieldPermissions = true;
                    }

                    if ($isHidden == 'c') {
                        // creators can see their own items coming from the search index
                    } elseif ($isHidden == 'y') {
                        // Visible by administrator only
                        unset($tracker_fields[$key]);
                    } elseif (! empty($visibleBy)) {
                        // Permission based on visibleBy apply
                        $commonGroups = array_intersect($visibleBy, $perms->getGroups());
                        if (count($commonGroups) == 0) {
                            unset($tracker_fields[$key]);
                        }
                    }
                }
                if (! $hasFieldPermissions && ! $perms->view_trackers && ! $definition->isEnabled('userCanSeeOwn') && ! $definition->isEnabled('groupCanSeeOwn') && ! $definition->isEnabled('writerCanModify')) {
                    return WikiParser_PluginOutput::userError(tr('You do not have rights to view tracker data.'));
                }
            }

            $fields = array_merge($fields, $tracker_fields);
        }

        $fields[] = [
            'name' => 'object_id',
            'permName' => 'object_id',
            'type' => 't'
        ];

        $fields[] = [
            'name' => 'object_type',
            'permName' => 'object_type',
            'type' => 't'
        ];

        $fields[] = [
            'name' => 'creation_date',
            'permName' => 'creation_date',
            'type' => 'f'
        ];
        $fields[] = [
            'name' => 'modification_date',
            'permName' => 'modification_date',
            'type' => 'f'
        ];
        $fields[] = [
            'name' => 'tracker_status',
            'permName' => 'tracker_status',
            'type' => 't'
        ];

        $heatmapParams = [];
        if ($rendererName === 'Heatmap') {
            $validConfig = ! (empty($params['heatmapDomain']) && empty($params['heatmapColors']))
                && is_array($params['heatmapDomain'])
                && is_array($params['heatmapColors'])
                && count($params['heatmapDomain']) === count($params['heatmapColors']);

            if ($validConfig) {
                $heatmapParams = [
                    'domain' => array_map(floatval, $params['heatmapDomain']),
                    'colors' => $params['heatmapColors']
                ];
            }

            unset($validConfig);
        }

        $query = new Search_Query();
        $query->filterType('trackeritem');
        $query->filterContent(implode(' OR ', $trackerIds), 'tracker_id');

        $unifiedsearchlib = TikiLib::lib('unifiedsearch');
        if (! empty($params['overridePermissions']) && $params['overridePermissions'] === 'y') {
            $unifiedsearchlib->initQueryBase($query);
            $unifiedsearchlib->initQueryPresentation($query);
        } else {
            $unifiedsearchlib->initQuery($query);
        }

        $matches = WikiParser_PluginMatcher::match($data);

        $builder = new Search_Query_WikiBuilder($query);
        $builder->apply($matches);

        if (! $index = $unifiedsearchlib->getIndex()) {
            return WikiParser_PluginOutput::userError(tr('Unified search index not found.'));
        }

        $result = [];
        if (empty($params['displayBeforeFilter']) || $params['displayBeforeFilter'] !== 'n' || (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST')) {
            foreach ($query->scroll($index) as $row) {
                $result[] = $row;
            }
        }
        $result = Search_ResultSet::create($result);
        $result->setId('wppivottable-' . $id);

        $resultBuilder = new Search_ResultSet_WikiBuilder($result);
        $resultBuilder->apply($matches);

        $columnsListed = false;
        $derivedAttributes = [];
        $splittedAttributes = [];
        $attributesOrder = [];

        foreach ($matches as $match) {
            if ($match->getName() == 'display' || $match->getName() == 'column') {
                $columnsListed = true;
            } elseif ($match->getName() == 'derivedattribute') {
                if (
                    preg_match('/name="([^"]+)"/', $match->getArguments(), $match_name)
                    && preg_match('/function="([^"]+)"/', $match->getArguments(), $match_function)
                    && preg_match('/parameters="([^"]*)"/', $match->getArguments(), $match_parameters)
                ) {
                    $derivedattr_name = $match_name[1];
                    $function_name = $match_function[1];
                    $function_params = explode(':', $match_parameters[1]);

                    if (empty($function_params)) {
                        $function_params = '';
                    } else {
                        $function_params = '"' . implode('","', $function_params) . '"';
                    }

                    $derivedAttributes[] = sprintf('"%s": %s(%s)', $derivedattr_name, $function_name, $function_params);
                }
            } elseif ($match->getName() == 'split') {
                $parser = new WikiParser_PluginArgumentParser();
                $arguments = $parser->parse($match->getArguments());
                if (! isset($arguments['field'])) {
                    return WikiParser_PluginOutput::userError(tr('Split wiki modifier should specify a field.'));
                }
                if (! isset($arguments['separator'])) {
                    $arguments['separator'] = ',';
                }
                $splittedAttributes[] = $arguments;
            } elseif ($match->getName() == 'attributesort') {
                $parser = new WikiParser_PluginArgumentParser();
                $arguments = $parser->parse($match->getArguments());
                if (! isset($arguments['field'])) {
                    return WikiParser_PluginOutput::userError(tr('Attributesort wiki modifier should specify a field.'));
                }
                if (! isset($arguments['order'])) {
                    return WikiParser_PluginOutput::userError(tr('Attributesort wiki modifier should specify an order.'));
                }
                $field = wikiplugin_pivottable_field_from_definitions($arguments['field'], $definitions);
                if (empty($field)) {
                    $field = $arguments['field'];
                } else {
                    $field = $field['name'];
                }
                $attributesOrder[$field] = str_getcsv($arguments['order']);
            }
        }

        if ($columnsListed) {
            $data .= '{display name="object_id"}{display name="object_type"}';
            $plugin = new Search_Formatter_Plugin_ArrayTemplate($data);
            $usedFields = array_keys($plugin->getFields());
            foreach ($fields as $key => $field) {
                if (
                    ! in_array('tracker_field_' . $field['permName'], $usedFields)
                    && ! in_array($field['permName'], $usedFields)
                ) {
                    unset($fields[$key]);
                }
            }
            $fields = array_values($fields);
            $plugin->setFieldPermNames($fields);
        } else {
            $plugin = new Search_Formatter_Plugin_ArrayTemplate(implode("", array_map(
                function ($f) {
                    if (in_array($f['permName'], ['object_id', 'object_type', 'creation_date', 'modification_date', 'tracker_status'])) {
                        return '{display name="' . $f['permName'] . '" default=" "}';
                    } elseif ($f['type'] == 'e') {
                        return '{display name="tracker_field_' . $f['permName'] . '" format="categorylist" singleList="y" separator=" "}';
                    } else {
                        return '{display name="tracker_field_' . $f['permName'] . '" default=" "}';
                    }
                },
                $fields
            )));
            $plugin->setFieldPermNames($fields);
        }
    }

    if ($dataType === "activitystream") {
        $unifiedsearchlib = TikiLib::lib('unifiedsearch');
        $query = new Search_Query();
        $unifiedsearchlib->initQuery($query);
        $query->filterType('activity');

        if ($params['overridePermissions'] === 'y') {
            $unifiedsearchlib->initQueryBase($query);
            $unifiedsearchlib->initQueryPresentation($query);
        } else {
            $unifiedsearchlib->initQuery($query);
        }

        $matches = WikiParser_PluginMatcher::match($data);

        $builder = new Search_Query_WikiBuilder($query);
        $builder->enableAggregate();
        $builder->apply($matches);

        $query->setOrder('modification_date_desc');

        if (! $index = $unifiedsearchlib->getIndex()) {
            throw new Services_Exception_NotAvailable(tr('Activity stream currently unavailable.'));
        }

        $result = [];
        foreach ($query->scroll($index) as $row) {
            $result[] = $row;
        }
        $result = Search_ResultSet::create($result);
        $result->setId('wppivottable-' . $id);

        $paginationArguments = $builder->getPaginationArguments();

        $resultBuilder = new Search_ResultSet_WikiBuilder($result);
        $resultBuilder->setPaginationArguments($paginationArguments);
        $resultBuilder->apply($matches);

        $fields = [];
        $fields[] = [
            'name' => 'object',
            'permName' => 'object',
            'type' => 't'
        ];

        $fields[] = [
            'name' => 'object_id',
            'permName' => 'object_id',
            'type' => 't'
        ];

        $fields[] = [
            'name' => 'object_type',
            'permName' => 'object_type',
            'type' => 't'
        ];

        $fields[] = [
            'name' => 'modification_date',
            'permName' => 'modification_date',
            'type' => 'f'
        ];

        $fields[] = [
            'name' => 'user',
            'permName' => 'user',
            'type' => 't'
        ];

        $fields[] = [
            'name' => 'event_type',
            'permName' => 'event_type',
            'type' => 't'
        ];

        $fields[] = [
            'name' => 'type',
            'permName' => 'type',
            'type' => 't'
        ];

        try {
            $plugin = new Search_Formatter_Plugin_ArrayTemplate(implode("", array_map(
                function ($f) {
                    $activityArray = [
                        'object',
                        'object_id',
                        'object_type',
                        'modification_date',
                        'user',
                        'event_type',
                        'type'
                    ];
                    if (in_array($f['permName'], $activityArray)) {
                        return '{display name="' . $f['permName'] . '" default=" "}';
                    }
                },
                $fields
            )));
            $plugin->setFieldPermNames($fields);
        } catch (SmartyException $e) {
            throw new Services_Exception_NotAvailable($e->getMessage());
        }
    }

    $builder = new Search_Formatter_Builder();
    $builder->setId('wppivottable-' . $id);
    $builder->setCount($result->count());
    $builder->apply($matches);
    $builder->setFormatterPlugin($plugin);

    $formatter = $builder->getFormatter();
    $entries = $formatter->getPopulatedList($result, false);
    $entries = $plugin->renderEntries($entries);

    $pivotData = [];
    foreach ($entries as $entry) {
        $row = [];
        foreach ($entry as $fieldName => $value) {
            if ($entry['object_type'] != 'activity' && $field = wikiplugin_pivottable_field_from_definitions($fieldName, $definitions)) {
                // Actual data values
                if ($translate) {
                    $row[$field['name']] = tra($value);
                } else {
                    $row[$field['name']] = $value;
                }
            } else {
                // predefined fields (created date, lastmod, etc.)
                $row[$fieldName] = $value;
            }
        }
        $pivotData[] = $row;
    }

    foreach ($splittedAttributes as $arguments) {
        $field = wikiplugin_pivottable_field_from_definitions($arguments['field'], $definitions);
        if (empty($field)) {
            continue;
        } else {
            $field = $field['name'];
        }
        $separator = str_replace("~nl~", "\n", $arguments['separator']);
        $key = 0;
        while ($key < count($pivotData)) {
            $row = $pivotData[$key];
            if (! isset($row[$field])) {
                $key++;
                continue;
            }
            $splitted = explode($separator, $row[$field]);
            if (isset($arguments['replace_fields'], $arguments['value_separator'])) {
                if (count($splitted) == 1 && empty($splitted[0])) {
                    $key++;
                    continue;
                }
                $replacement = [];
                foreach ($splitted as $split_value) {
                    $replaced_row = $row;
                    $values = explode($arguments['value_separator'], $split_value);
                    foreach (preg_split('/\s*,\s*/', $arguments['replace_fields']) as $replacement_key => $replacement_field) {
                        $real_field = wikiplugin_pivottable_field_from_definitions($replacement_field, $definitions);
                        if (! empty($real_field)) {
                            $replacement_field = $real_field['name'];
                        }
                        if (isset($replaced_row[$replacement_field])) {
                            $replaced_row[$replacement_field] = $values[$replacement_key];
                        }
                    }
                    $replacement[] = $replaced_row;
                }
            } else {
                if (count($splitted) == 1) {
                    $key++;
                    continue;
                }
                $replacement = array_map(function ($value) use ($row, $field) {
                    return array_merge($row, [$field => ltrim($value)]);
                }, $splitted);
            }
            array_splice($pivotData, $key, 1, $replacement);
            $key += count($replacement);
        }
    }

    //translating permName to field name for columns and rows
    $cols = [];
    if (! empty($params['cols'])) {
        foreach ($params['cols'] as $colName) {
            if ($params['data'][0] !== 'activitystream' && $field = wikiplugin_pivottable_field_from_definitions(trim($colName), $definitions)) {
                $cols[] = $field['name'];
            } else {
                $cols[] = $colName;
            }
        }
    } elseif (! empty($fields) && empty($params['rows'])) {
        $cols[] = $fields[0]['name'];
    }

    $rows = [];
    if (! empty($params['rows'])) {
        foreach ($params['rows'] as $rowName) {
            if ($params['data'][0] !== 'activitystream' && $field = wikiplugin_pivottable_field_from_definitions(trim($rowName), $definitions)) {
                $rows[] = $field['name'];
            } else {
                $rows[] = $rowName;
            }
        }
    }

    $vals = [];
    if (! empty($params['vals'])) {
        foreach ($params['vals'] as $valName) {
            if ($params['data'][0] !== 'activitystream' && $field = wikiplugin_pivottable_field_from_definitions(trim($valName), $definitions)) {
                $vals[] = $field['name'];
            } else {
                $vals[] = $valName;
            }
        }
    }

    $inclusions = ! empty($params['inclusions']) ? $params['inclusions'] : '{}';

    // parsing array to hold permNames mapped with field names for save button
    // and list of date fields for custom sorting
    $fieldsArr = [];
    $dateFields = [];
    foreach ($fields as $field) {
        $field = wikiplugin_pivottable_field_from_definitions($field['permName'], $definitions, $field);
        $fieldsArr[$field['name']] = $field['permName'];
        if ($field['type'] == 'f') {
            $dateFields[] = $field['name'];
        }
    }

    $smarty->loadPlugin('smarty_function_object_link');

    if (! isset($params['aggregateDetails'])) {
        if (isset($fields[2])) {
            $params['aggregateDetails'][] = $fields[2]['permName'];
        } elseif (isset($fields[0])) {
            $params['aggregateDetails'][] = $fields[0]['permName'];
        }
    }

    if (! empty($params['aggregateDetails']) && ! empty($params['aggregateDetails'][0])) {
        $aggregateDetails = [];
        foreach ($params['aggregateDetails'] as $fieldName) {
            if ($params['data'][0] != 'activitystream' && $field = wikiplugin_pivottable_field_from_definitions(trim($fieldName), $definitions)) {
                $aggregateDetails[] = $field['name'];
            } else {
                $aggregateDetails[] = trim($fieldName);
            }
        }
        foreach ($pivotData as &$row) {
            $arr = array_map(function ($field) use ($row) {
                return $row[$field] ?? '';
            }, $aggregateDetails);
            if (! empty($params['aggregateDetailsFormat'])) {
                $title = tra($params['aggregateDetailsFormat'], '', false, $arr);
            } else {
                $title = implode(' ', $arr);
            }
            $pivotLinkParams = [
                'type' => $row['object_type'],
                'id' => $row['object_id'],
                'title' => $title,
            ];
            if ($row['object_type'] === 'activity') {
                $pivotLinkParams = [
                    'type' => $row['type'],
                    'id' => $row['object'],
                    'title' => $row['type'],
                ];
            }
            $row['pivotLink'] = smarty_function_object_link($pivotLinkParams, $smarty->getEmptyInternalTemplate());
        }
    } else {
        $params['aggregateDetails'] = [];
    }

    $highlight = [];
    if (! empty($params['highlightMine']) && $params['highlightMine'] === 'y' && $params['data'][0] !== 'activitystream') {
        $ownerFields = [];
        foreach ($definitions as $definition) {
            foreach ($definition->getItemOwnerFields() as $fieldId) {
                $ownerFields[] = $definition->getField($fieldId);
            }
        }
        foreach ($pivotData as $item) {
            foreach ($ownerFields as $ownerField) {
                $itemUsers = TikiLib::lib('trk')->parse_user_field(@$item[$ownerField['name']]);
                if (in_array($user, $itemUsers)) {
                    $highlight[] = ['item' => $item];
                    break;
                }
            }
        }
    }
    if (! empty($params['highlightGroup']) && $params['highlightGroup'] === 'y') {
        $groupField = null;
        foreach ($fields as $field) {
            if ($field['type'] == 'g') {
                $groupField = $field;
                break;
            }
        }
        if ($groupField) {
            $myGroups = TikiLib::lib('tiki')->get_user_groups($user);
            foreach ($pivotData as $item) {
                $group = @$item[$groupField['name']];
                if (in_array($group, $myGroups)) {
                    $highlight[] = ['item' => $item, 'group' => $group];
                }
            }
            $groupColors = [];
            if ($prefs['feature_conditional_formatting'] === 'y') {
                $groupsInfo = TikiLib::lib('user')->get_group_info($myGroups);
                foreach ($groupsInfo as $groupInfo) {
                    $groupColors[$groupInfo['groupName']] = $groupInfo['groupColor'];
                }
            }
            if (! empty($params['highlightGroupColors'])) {
                $index = 0;
                foreach ($myGroups as $group) {
                    if (empty($params['highlightGroupColors'][$index])) {
                        break;
                    }
                    $groupColors[$group] = $params['highlightGroupColors'][$index];
                    $index++;
                }
            }
            if ($groupColors) {
                foreach ($highlight as &$row) {
                    if (! empty($row['group'])) {
                        $group = $row['group'];
                        if ($group && ! empty($groupColors[$group])) {
                            $row['color'] = $groupColors[$group];
                        }
                    }
                }
            }
        }
    }

    //checking if user can see edit button
    if (! empty($wikiplugin_included_page)) {
        $sourcepage = $wikiplugin_included_page;
    } else {
        $sourcepage = $page;
    }
    //checking if user has edit permissions on the wiki page using the current permission library to obey global/categ/object perms
    $objectperms = Perms::get([ 'type' => 'wiki page', 'object' => $sourcepage ]);
    if ($objectperms->edit) {
        $showControls = true;
    } else {
        $showControls = false;
    }

    $out = str_replace(['~np~', '~/np~'], '', $formatter->renderFilters());

    $smarty->assign('pivottable', [
        'id' => 'pivottable' . $id,
        'trows' => $rows,
        'tcolumns' => $cols,
        'dataSource' => $dataType == 'activitystream' ? $dataType : $dataType . ':' . implode(',', $trackerIds),
        'data' => $pivotData,
        'derivedAttributes' => $derivedAttributes,
        'rendererName' => $rendererName,
        'aggregatorName' => $aggregatorName,
        'vals' => $vals,
        'width' => $width,
        'height' => $height,
        'heatmapParams' => $heatmapParams,
        'showControls' => $showControls,
        'page' => $sourcepage,
        'fieldsArr' => $fieldsArr,
        'dateFields' => $dateFields,
        'inclusions' => $inclusions,
        'colOrder' => $params['colOrder'] ?? 'key_a_to_z',
        'rowOrder' => $params['rowOrder'] ?? 'key_a_to_z',
        'menuLimit' => empty($params['menuLimit']) ? null : $params['menuLimit'],
        'aggregateDetails' => implode(':', $params['aggregateDetails']),
        'aggregateDetailsFormat' => $params['aggregateDetailsFormat'] ?? null,
        'aggregateDetailsCallback' => $params['aggregateDetailsCallback'] ?? null,
        'attributesOrder' => $attributesOrder,
        'highlight' => $highlight,
        'highlightMine' => empty($params['highlightMine']) ? null : $params['highlightMine'],
        'highlightGroup' => empty($params['highlightGroup']) ? null : $params['highlightGroup'],
        'xAxisLabel' => empty($params['xAxisLabel']) ? null : $params['xAxisLabel'],
        'yAxisLabel' => empty($params['yAxisLabel']) ? null : $params['yAxisLabel'],
        'chartTitle' => empty($params['chartTitle']) ? null : $params['chartTitle'],
        'chartHoverBar' => empty($params['chartHoverBar']) ? null : $params['chartHoverBar'],
        'translate' => empty($params['translate']) ? null : $params['translate'],
        'index' => $id
    ]);

    $out .= $smarty->fetch('wiki-plugins/wikiplugin_pivottable.tpl');

    return $out;
}

function wikiplugin_pivottable_field_from_definitions($permName, $definitions, $default = null)
{
    foreach ($definitions as $definition) {
        if ($field = $definition->getFieldFromPermName(str_replace('tracker_field_', '', $permName))) {
            if (count($definitions) > 1) {
                $field['name'] = $definition->getConfiguration('name') . ' - ' . $field['name'];
            }
            return $field;
        }
    }
    return $default;
}
