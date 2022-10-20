<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function prefs_allocate_list()
{
    $formatMemoryLimits = function ($value) {
        $ret = $value;
        if (strlen($value) > 0) {
            $units = 'BKMGTP';
            $last_char = substr($value, -1);
            if (strpos($units, $last_char) === false && $value > 0) {
                $index = 0;
                $prefix = $value;
                while ($prefix > 1023) {
                    $prefix = floor($prefix / 1024);
                    $index++;
                }
                $ret = $prefix . $units[$index];
            }
        }
        return $ret;
    };

    $prefs = [
        'unified_rebuild' => ['label' => tr('Search index rebuild'), 'memory' => true, 'time' => true],
        'tracker_export_items' => ['label' => tr('Tracker item export'), 'memory' => true, 'time' => true],
        'tracker_import_items' => ['label' => tr('Tracker item import'), 'memory' => true, 'time' => true],
        'tracker_clear_items' => ['label' => tr('Tracker clear'), 'memory' => false, 'time' => true],
        'print_pdf' => ['label' => tr('Printing to PDF'), 'memory' => true, 'time' => true],
        'secdb_check' => [
            'label' => tr('Security check'),
            'memory' => false,
            'time' => true,
            'extras_time' => [
                'default' => 60,
                'shorthint' => tr('time allocated to check all tiki files on tiki-admin_security.php'),
            ],
        ],
        'php_execution' => [
            'label' => tr('PHP execution'),
            'memory' => true,
            'time' => true,
            'extras_memory' => [
                'shorthint' => tr('for example 256M, currently is %0', $formatMemoryLimits(ini_get('memory_limit'))),
            ],
            'extras_time' => [
                'shorthint' => tr('for example 30 seconds, currently is %0 seconds', ini_get('max_execution_time')),
            ],
        ],
    ];

    $out = [];
    foreach ($prefs as $name => $info) {
        if ($info['memory']) {
            $out['allocate_memory_' . $name] = [
                'name' => tr('%0 memory limit', $info['label']),
                'description' => tr('Temporarily adjust the memory limit to use during %0. Depending on the volume of data, some large operations require more memory. Increasing it locally, per operation, allows to keep a lower memory limit globally. Keep in mind that memory usage is still limited to what is available on the server.', $info['label']),
                'help' => 'Memory-Limit',
                'type' => 'text',
                'default' => '',
                'shorthint' => tr('for example: 256M'),
                'size' => 8,
            ];

            if (isset($info['extras_memory'])) {
                foreach ($info['extras_memory'] as $key => $value) {
                    $out['allocate_memory_' . $name][$key] = $value;
                }
            }
        }

        if ($info['time']) {
            $out['allocate_time_' . $name] = [
                'name' => tr('%0 time limit', $info['label']),
                'description' => tr('Temporarily adjust the time limit to use during %0. Depending on the volume of data, some requests may take longer. Increase the time limit locally to resolve the issue. Use reasonable values.', $info['label']),
                'help' => 'Time-Limit',
                'type' => 'text',
                'default' => '',
                'shorthint' => tr('for example: 30'),
                'units' => tr('seconds'),
                'size' => 8,
            ];

            if (isset($info['extras_time'])) {
                foreach ($info['extras_time'] as $key => $value) {
                    $out['allocate_time_' . $name][$key] = $value;
                }
            }
        }
    }

    return $out;
}
