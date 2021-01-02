
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

function wikiplugin_tikimanagerclone_info()
{
    return [
        'name' => tra('TikiManagerClone'),
        'documentation' => 'PluginTikiManagerClone',
        'description' => tra('Make on demand clone of an instance to another'),
        'tags' => ['advanced'],
        'prefs' => ['feature_tiki_manager', 'wikiplugin_tikimanagerclone'],
        'body' => tra('Button text(The text that will be displayed on the clone instance button).'),
        'introduced' => 25,
        'params' => [
            'mode' => [
                'required' => false,
                'name' => tra('Mode'),
                'description' => tra('Clone mode(Clone or Upgrade). The Clone mode allows to make a clone only and the Upgrade mode, to make a clone with an extra upgrade operation.'),
                'since' => '25.0',
                'filter' => 'alpha',
                'options' => [
                    ['text' => tra('Clone'), 'value' => 'clone'],
                    ['text' => tra('Clone and Upgrade'), 'value' => 'upgrade'],
                ],
                'default' => 'clone',
                'advanced' => 'true'
            ],
            'source' => [
                'required' => true,
                'name' => tra('Source instance'),
                'description' => tra('Source instance(instance to clone) ID or name.'),
                'since' => '25.0',
                'filter' => 'string',
                'advanced' => false
            ],
            'target' => [
                'required' => true,
                'name' => tra('Target'),
                'description' => tra('Destination instance(s) ID or name, comma separated in case of multiple instances.'),
                'since' => '25.0',
                'filter' => 'string',
                'advanced' => false
            ],
            'branch' => [
                'required' => true,
                'name' => tra('Branch'),
                'description' => tra('Select branch.'),
                'since' => '25.0',
                'filter' => 'string',
                'advanced' => false,
            ],
            'skipReindex' => [
                'required' => false,
                'name' => tra('Skip reindex'),
                'description' => tra('Skip rebuilding index step. (Only in upgrade mode).'),
                'since' => '25.0',
                'filter' => 'int',
                'options' => [
                    ['text' => tra('No'), 'value' => '0'],
                    ['text' => tra('Yes'), 'value' => '1'],
                ],
                'default' => 0,
                'advanced' => true,
            ],
            'skipCacheWarmup' => [
                'required' => false,
                'name' => tra('Skip cache warmup'),
                'description' => tra('Skip generating cache step. (Only in upgrade mode).'),
                'since' => '25.0',
                'filter' => 'int',
                'options' => [
                    ['text' => tra('No'), 'value' => '0'],
                    ['text' => tra('Yes'), 'value' => '1'],
                ],
                'default' => 0,
                'advanced' => true,
            ],
            'unifiedIndexRebuild' => [
                'required' => false,
                'name' => tra('Unified index rebuild'),
                'description' => tra('Unified index rebuild, set instance maintenance off and after perform index rebuild. (Only in upgrade mode)'),
                'since' => '25.0',
                'filter' => 'int',
                'options' => [
                    ['text' => tra('Yes, and the site is open during index rebuild'), 'value' => '1'],
                    ['text' => tra('No, and the site is closed during index rebuild'), 'value' => '0'],
                ],
                'default' => 0,
                'advanced' => true,
            ],
            'direct' => [
                'required' => false,
                'name' => tra('Direct'),
                'description' => tra('Prevent using the backup step and rsync source to target.'),
                'since' => '25.0',
                'filter' => 'int',
                'options' => [
                    ['text' => tra('Yes'), 'value' => '1'],
                    ['text' => tra('No'), 'value' => '0'],
                ],
                'default' => 1,
                'advanced' => true,
            ],
            'stash' => [
                'required' => false,
                'name' => tra('Stash'),
                'description' => tra('Saves your local modifications, and try to apply after update/upgrade'),
                'since' => '25.0',
                'filter' => 'int',
                'options' => [
                    ['text' => tra('No'), 'value' => '0'],
                    ['text' => tra('Yes'), 'value' => '1'],
                ],
                'default' => 1,
                'advanced' => true,
            ],
            'timeout' => [
                'required' => false,
                'name' => tra('Timeout'),
                'description' => tra('Modify the default command execution timeout from 3600 seconds to a custom value'),
                'since' => '25.0',
                'filter' => 'int',
                'default' => 3600,
                'advanced' => false
            ],
            'ignoreRequirements' => [
                'required' => false,
                'name' => tra('Ignore requirements'),
                'description' => tra('Ignore version requirements. Allows to select non-supported branches, useful for testing.'),
                'since' => '25.0',
                'filter' => 'int',
                'options' => [
                    ['text' => tra('No'), 'value' => '0'],
                    ['text' => tra('Yes'), 'value' => '1'],
                ],
                'default' => 0,
                'advanced' => true,
            ],
            'toClone' => [
                'required' => false,
                'name' => tra('What to clone'),
                'description' => tra('What to clone.'),
                'since' => '25.0',
                'filter' => 'string',
                'options' => [
                    ['text' => tra('Clone data and code'), 'value' => 'data and code'],
                    ['text' => tra('Clone only data'), 'value' => 'data'],
                    ['text' => tra('Clone only code'), 'value' => 'code'],
                ],
                'default' => 0,
                'advanced' => true,
            ],
        ]
    ];
}

function wikiplugin_tikimanagerclone($data, $params)
{
    global $user;
    global $prefs;

    //Prevent the plugin to be executed by anonymous users
    if (! $user or $user === 'anonymous') {
        return;
    }

    try {
        $utilities = new Services_Manager_Utilities();
        $utilities->tikiManagerCheck();
        $utilities->loadEnv();
    } catch (Exception $e) {
        return WikiParser_PluginOutput::error(tra('Error'), $e->getMessage());
    }

    $params['source'] = trim($params['source']);
    $params['target'] = array_map(function ($i) {
        return trim($i);
    }, explode(',', $params['target']));

    extract($params, EXTR_SKIP);

    $instances = TikiManager\Application\Instance::getInstances();
    $source = array_map(function ($i) {
        return $i->id;
    }, array_filter($instances, function ($i) use ($source) {
        return $i->id == $source || $i->name == $source;
    }));
    $source = implode(',', $source);

    if (empty($source)) {
        return WikiParser_PluginOutput::error(tr('Error'), tra('Uknown source instance ' . $params['source']));
    }

    $target = array_map(function ($i) {
        return $i->id;
    }, array_filter($instances, function ($i) use ($target) {
        return in_array($i->id, $target) || in_array($i->name, $target);
    }));

    if (empty($target)) {
        return WikiParser_PluginOutput::error(tr('Error'), tra('Unknown target instance(s) ' . implode(',', $params['target'])));
    }

    if (in_array($source, $target)) {
        return WikiParser_PluginOutput::error(tr('Error'), tra('Source instance can not be the same as target instance or be part of target instances.'));
    }

    $target = implode(',', $target);
    $branches = $utilities::getAvailableTikiVersions();

    if (! in_array($branch, $branches)) {
        return WikiParser_PluginOutput::error(tr('Error'), tra('Branch not found !'));
    }

    if (empty($timeout)) {
        $timeout = 3600;
    }

    $featureRealtime = $prefs['feature_realtime'];
    $consoleCommand = 'manager:instance:clone ' . $mode . ' --source=' . $source . ' --target=' . $target . ' --branch=' . $branch . (($skipReindex) ? ' --skip-reindex' : '') . (($skipCacheWarmup) ? ' --skip-cache-warmup' : '') . (($unifiedIndexRebuild) ? ' --live-reindex' : '') . (($direct) ? ' --direct' : '') . (($stash) ? ' --stash' : '') . ' --timeout=' . $timeout . (($ignoreRequirements) ? ' --ignore-requirements' : '');

    if ($mode == 'clone') {
        $onlyData = ($toClone == 'data') ? true : false;
        $onlyCode = ($toClone == 'code') ? true : false;
        $consoleCommand .= (($onlyData) ? ' --only-data' : '') . (($onlyCode) ? ' --only-code' : '');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($featureRealtime === 'y') {
            $utilities->addInteractiveJS($consoleCommand);
        } else {
            Scheduler_Manager::queueJob('Clone instance ' . $source . ' to instance(s) ' . $target, 'ConsoleCommandTask', ['console_command' => $consoleCommand]);
            Feedback::success(tr("Instance %0 scheduled to clone to instance(s) %1 in the background. You can check command output via <a href='tiki-admin_schedulers.php#contenttabs_admin_schedulers-3'>Scheduler logs</a>.", $source, $target));
        }
    }

    $cloneInstanceButtonValue = ! empty($data) ? $data : 'Clone ' . (($mode == 'upgrade') ? 'and upgrade ' : '') . 'instance ' . $source . ' to instance(s) ' . $target;
    $wikiLib = TikiLib::lib('wiki');
    $url = $wikiLib->sefurl($_GET['page']);

    $smarty = TikiLib::lib('smarty');
    $smarty->assign('url', $url);
    $smarty->assign('cloneInstanceButtonValue', $cloneInstanceButtonValue);

    return $smarty->fetch('wiki-plugins/wikiplugin_tikimanagerclone.tpl');
}