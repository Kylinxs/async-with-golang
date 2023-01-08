<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER['SCRIPT_NAME'], basename(__FILE__)) !== false) {
    header('location: index.php');
    exit;
}

$opcode_stats = $adminlib->getOpcodeCacheStatus();
$stat_flag = $opcode_stats['stat_flag'];
if ($stat_flag) {
    $smarty->assign('stat_flag', $stat_flag);
}

$opcode_cache = $opcode_stats['opcode_cache'];
$smarty->assign('opcode_cache', $opcode_cache);
$smarty->assign('opcode_stats', $opcode_stats);
$smarty->assign('opcode_compatible', $adminlib->checkOPCacheCompatibility());

$txtUsed = tr('Used');
$txtAvailable = tr('Available');
$smarty->assign(
    'memory_graph',
    ([
            'data' => $opcode_stats['memory_used'] . ':' . $opcode_stats['memory_avail'],
            'data_labels' => $txtUsed . '|' . $txtAvailable,
       ])
);

$txtHit = tr('Hit');
$txtMiss = tr('Miss');
$smarty->assign(
    'hits_graph',
    ([
            'data' => $opcode_stats['hit_hit'] . ':' . $opcode_stats['hit_miss'],
            'data_labels' => $txtHit . ':' . $txtMiss,
            ])
);

// realpath_cache_size can make considerable difference on php performance apparently
if (function_exists('realpath_cache_size')) {
    $rpcs_current = realpath_cache_size();
    $rpcs_ini = ini_get('realpath_cache_size');
    $rpc_ttl = ini_get('realpath_cache_ttl');
    $smarty->assign('realpath_cache_size_current', $rpcs_current);
    $smarty->assign('realpath_cache_size_ini', $rpcs_ini);
    $smarty->assign('realpath_cache_ttl', $rpc_ttl);
    $smarty->assign('realpath_cache_size_percent', round($rpcs_current / TikiLib::lib('tiki')->return_bytes($rpcs_ini) * 100, 2));
}
