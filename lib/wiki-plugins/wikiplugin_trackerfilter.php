<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function wikiplugin_trackerfilter_info()
{
    require_once 'lib/wiki-plugins/wikiplugin_trackerlist.php';
    $list = wikiplugin_trackerlist_info();
    $params = array_merge(
        [
            'filters' => [
                'required' => true,
                'name' => tra('Filters'),
                'description' => tr(
                    'The list of fields that can be used as filters along with their formats.
                    The field number and format are separated by a %0/%1 and multiple fields are separated by %0:%1.',
                    '<code>',
                    '</code>'
                )
                    . tr('Format choices are:') . '<br /><code>d</code> - ' . tr('dropdown')
                    . '<br /><code>r</code> - ' . tr('radio buttons')
                    . '<br /><code>m</code> - ' . tr('multiple choice dropdown')
                    . '<br /><code>c</code> - ' . tr('checkbox')
                    . '<br /><code>t</code> - ' . tr('text with wild characters')
                    . '<br /><code>T</code> - ' . tr('exact text match')
                    . '<br /><code>i</code> - ' . tr('initials')
                    . '<br /><code>sqlsearch</code> - ' . tr('advanced search')
                    . '<br /><code>range</code> - ' . tr('range search (from/to)')
                    . '<br /><code>></code>, <code>><</code>, <code>>>=</code>, <code>><=</code> - ' . tr('greater
                        than, less than, greater than or equal, less than or equal.') . '<br />'
                    . tr('Example:') . ' <code>2/d:4/r:5:(6:7)/sqlsearch</code>',
                'since' => '1',
                'doctype' => 'filter',
                'default' => '',
                'profile_reference' => 'tracker_field_string',
            ],
            'action' => [
                'required' => false,
                'name' => tra('Action'),
                'description' => tr('Label on the submit button. Default: %0Filter%1. Use a space character to omit the
                    button (for use in datachannels etc)', '<code>', '</code>'),
                'since' => '2.0',
                'doctype' => 'show',
                'default' => 'Filter'
            ],
            'displayList' => [
                'required' => false,
                'name' => tra('Display List'),
                'description' => tra('Show the full list (before filtering) initially (filtered list shown by default)'),
                'since' => '2.0',
                'doctype' => 'show',
                'filter' => 'alpha',
                'default' => 'n',
                'options' => [
                    ['text' => '', 'value' => ''],
                    ['text' => tra('Yes'), 'value' => 'y'],
                    ['text' => tra('No'), 'value' => 'n']
                ]
            ],
            'line' => [
                'required' => false,
                'name' => tra('Line'),
                'description' => tra('Displays all the filters on the same line (not shown on same line by default)'),
                'since' => '2.0',
                'doctype' => 'show',
                'filter' => 'alpha',
                'default' => 'n',
                'options' => [
                    ['text' => '', 'value' => ''],
                    ['text' => tra('Yes'), 'value' => 'y'],
                    ['text' => tra('Yes with field label in dropdown'), 'value' => 'in'],
                    ['text' => tra('No'), 'value' => 'n']
                ]
            ],
            'noflipflop' => [
                'required' => false,
                'name' => tra('No Toggle'),
                'description' => tr('The toggle button to show/hide filters will not be shown if set to Yes (%0y%1).
                    Default is not to show the toggle (default changed from "n" to "y" in Tiki 20.0).', '<code>', '</code>'),
                'since' => '6.0',
                'doctype' => 'show',
                'filter' => 'alpha',
                'default' => 'y',
                'options' => [
                    ['text' => '', 'value' => ''],
                    ['text' => tra('Yes'), 'value' => 'y'],
                    ['text' => tra('No'), 'value' => 'n']
                ]
            ],
            'export_action' => [
                'required' => false,
                'name' => tra('Export CSV.'),
                'description' => tra('Label for an export button. Leave blank to show the usual "Filter" button instead.'),
                'since' => '6.0',
                'doctype' => 'export',
                'default' => '',
                'advanced' => true,
            ],
            'export_status' => [
                'required' => false,
                'name' => tra('Export Status Field'),
                'description' => tra('Export the status field if the Export CSV option is used'),
                'since' => '11.1',
                'advanced' => true,
                'filter' => 'alpha',
                'doctype' => 'export',
                'default' => 'n',
                'options' => [
                    ['text' => '', 'value' => ''],
                    ['text' => tra('Yes'), 'value' => 'y'],
                    ['text' => tra('No'), 'value' => 'n']
                ]
            ],
            'export_created' => [
                'required' => false,
                'name' => tra('Export Created Date Field'),
                'description' => tra('Export the created date field if the Export CSV option is used'),
                'since' => '11.1',
                'advanced' => true,
                'filter' => 'alpha',
                'doctype' => 'export',
                'default' => 'n',
                'options' => [
                    ['text' => '', 'value' => ''],
                    ['text' => tra('Yes'), 'value' => 'y'],
                    ['text' => tra('No'), 'value' => 'n']
                ]
            ],
            'export_modif' => [
                'required' => false,
                'name' => tra('Export Modified Date Field'),
                'description' => tra('Export the modified date field if the Export CSV option is used'),
                'since' => '11.1',
                'advanced' => true,
                'filter' => 'alpha',
                'doctype' => 'export',
                'default' => 'n',
                'options' => [
                    ['text' => '', 'value' => ''],
                    ['text' => tra('Yes'), 'value' => 'y'],
                    ['text' => tra('No'), 'value' => 'n']
                ]
            ],
            'export_charset' => [
                'required' => false,
                'name' => tra('Export Character Set'),
                'description' => tra('Character set to be used if the Export CSV option is used'),
                'since' => '11.1',
                'doctype' => 'export',
                'default' => 'UTF-8',
                'advanced' => true,
            ],
            'mapButtons' => [
                'required' => false,
                'name' => tra('Map View Buttons'),
                'description' => tra('Display Mapview and Listview buttons'),
                'since' => '6.0' . tr(' - was %0 until 12.0', '<code>googlemapButtons</code>'),
                'filter' => 'alpha',
                'doctype' => 'show',
                'default' => '',
                'options' => [
                    ['text' => '', 'value' => ''],
                    ['text' => tra('Yes'), 'value' => 'y'],
                    ['text' => tra('No'), 'value' => 'n']
                ]
            ],
        ],
        $list['params']
    );

    return [
        'name' => tra('Tracker Filter'),
        'documentation' => 'PluginTrackerFilter',
        'description' => tra('Create a form to filter tracker fields'),
        'prefs' => [ 'feature_trackers', 'wikiplugin_trackerfilter' ],
        'body' => tra('notice'),
        'iconname' => 'filter',
        'introduced' => 1,
        'params' => $params,
        'format' => 'html',
        'extraparams' => true,
    ];
}

function wikiplugin_trackerfilter($data, $