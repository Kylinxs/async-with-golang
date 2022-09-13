
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER['SCRIPT_NAME'], basename(__FILE__)) !== false) {
    header('location: index.php');
    exit;
}

use Symfony\Component\Console\Input\ArrayInput;
use TikiManager\Application\Instance;
use TikiManager\Application\Tiki\Versions\Fetcher\YamlFetcher;
use TikiManager\Application\Tiki\Versions\TikiRequirementsHelper;

/**
 * Class Services_Manager_Controller
 */
class Services_Manager_Controller
{
    use Services_Manager_Trait;

    public function action_index()
    {
        return [
            'title' => tr('Tiki Manager'),
            'instances' => TikiManager\Application\Instance::getInstances(false),
        ];
    }

    public function action_info()
    {
        global $prefs;
        if ($prefs['feature_realtime'] === 'y') {
            $command = 'manager:manager:info --ansi';
            $this->addInteractiveJS($command);
            return [
                'override_action' => 'interactive',
                'title' => tr('Tiki Manager Info')
            ];
        } else {
            $this->runCommand(new TikiManager\Command\ManagerInfoCommand());
            return [
                'title' => tr('Tiki Manager Info'),
                'info' => $this->manager_output->fetch(),
            ];
        }
    }

    public function action_update($input)
    {
        global $prefs;
        $instanceId = $input->instanceId->int();
        if ($instance = TikiManager\Application\Instance::getInstance($instanceId)) {
            if ($prefs['feature_realtime'] === 'y') {
                $command = 'manager:instance:update -i ' . $instanceId . ' --ansi';
                $this->addInteractiveJS($command);
                return [
                    'override_action' => 'interactive',
                    'title' => tr('Tiki Manager Update')
                ];
            } else {
                Scheduler_Manager::queueJob('Update instance ' . $instanceId, 'ConsoleCommandTask', ['console_command' => 'manager:instance:update -i ' . $instanceId]);
                Feedback::success(tr("Instance %0 scheduled to update in the background. You can check command output via <a href='tiki-admin_schedulers.php#contenttabs_admin_schedulers-3'>Scheduler logs</a>.", $instanceId));
            }
        } else {
            Feedback::error(tr('Unknown instance'));
        }
        if ($input->modal->int()) {
            return Services_Utilities::closeModal();
        } else {
            return [
                'FORWARD' => [
                    'action' => 'index',
                ],
            ];
        }
    }

    public function action_upgrade($input)
    {
        global $prefs;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $availbleInstances = TikiManager\Application\Instance::getInstances(true);
            $availbleInstancesIds = array_map(function ($element) {
                return $element->id;
            }, $availbleInstances);
            $instancesToUpdate = $input->instances->array();

            foreach ($instancesToUpdate as $instanceId) {
                if (! in_array($instanceId, $availbleInstancesIds)) {
                    Feedback::error(tr('Unknown instance ' . $instanceId));
                    return [
                        'FORWARD' => [
                            'action' => 'index'
                        ],
                    ];
                }
            }

            $instances = implode(',', $instancesToUpdate);
            $branch = $input->branch->text();
            $check = $input->check->int();
            $skipReindex = $input->skipReindex->int();
            $skipCacheWarmup = $input->skipCacheWarmup->int();
            $liveReindex = $input->skipReindex->int();
            $lag = $input->lag->int();
            $stash = $input->stash->int();
            $ignoreRequirements = $input->ignoreRequirements->int();

            $consoleCommand = 'manager:instance:upgrade -i ' . $instances . ' --branch=' . $branch . (($check) ? ' --check' : '') . (($skipReindex) ? ' --skip-reindex' : '') . (($skipCacheWarmup) ? ' --skip-cache-warmup' : '') . (($liveReindex) ? ' --live-reindex' : '') . ' --lag=' . $lag . (($stash) ? ' --stash' : '') . (($ignoreRequirements) ? ' --ignore-requirements' : '');

            if ($prefs['feature_realtime'] === 'y') {
                $this->addInteractiveJS($consoleCommand);
                return [
                    'override_action' => 'interactive',
                    'title' => tr('Tiki Manager Upgrade')
                ];
            } else {
                Scheduler_Manager::queueJob('Upgrade instance ' . $instances, 'ConsoleCommandTask', ['console_command' => $consoleCommand]);
                Feedback::success(tr("Instance %0 scheduled to upgrade in the background. You can check command output via <a href='tiki-admin_schedulers.php#contenttabs_admin_schedulers-3'>Scheduler logs</a>.", $instances));
            }
        } else {
            $instanceId = $input->instanceId->int();
            $instance = TikiManager\Application\Instance::getInstance($instanceId);

            if ($instance) {
                $cmd = new TikiManager\Command\UpgradeInstanceCommand();
                $boolOptions = '<option value="" disabled selected hidden></option>'
                               . '<option value="1">True</option>'
                               . '<option value="0">False</option>';

                $instancesIds = new JitFilter(['instancesIds' => [$instanceId]]);
                $versions = $this->action_get_instances_upper_versions($instancesIds);
                $upperVersions = $versions['upperVersions'];

                return [
                    'title' => tr('Instances Upgrade'),
                    'info' => '',
                    'instances' => TikiManager\Application\Instance::getInstances(true),
                    'selectedInstanceId' => $instanceId,
                    'branches' => $upperVersions,
                    'boolOptions' => $boolOptions,
                    'help' => $this->getCommandHelpTexts($cmd)
                ];
            } else {
                Feedback::error(tr('Unknown instance'));
                return [
                    'FORWARD' => [
                        'action' => 'index'
                    ],
                ];
            }
        }
    }

    // This function allows to get upgrade versions of selected instances for the instance:upgrade commande to prevent a downgrade(not suported by Tiki)
    public function action_get_instances_upper_versions($input)
    {
        $instancesIds = $input->instancesIds->array();
        $availableInstances = TikiManager\Application\Instance::getInstances(true);
        $instances = array_filter($availableInstances, function ($i) use ($instancesIds) {
            return in_array($i->id, $instancesIds);
        });

        $instancesMaxVersion = max(array_map(function ($i) {
            return $i->branch;
        }, $instances));

        // Excluding tags from tiki versions
        $tikiVersions = array_filter($this->getTikiBranches(), function ($i) {
            return ! preg_match("#^tags(.*)$#i", $i);
        });

        $instancesUpperVersions = array_filter($tikiVersions, function ($i) use ($instancesMaxVersion) {
            if ($i == 'master' || $instancesMaxVersion == 'master') {
                return $i > $instancesMaxVersion;
            } else {
                // In this scope v1 = $i and v2 = $instancesMaxVersion
                $v1_gt_v2 = false; // gt = greater than
                $v1Array = explode('.', $i);
                $v2Array = explode('.', $instancesMaxVersion);
                $v1MajorVersion = (int) $v1Array[0];
                $v1MinorVersion = $v1Array[1];  // This can be an integer or 'x'. Don't cast here (cause casting a string into integer return 0) as we'll compare minor versions differently to majors.
                $v2MajorVersion = (int) $v2Array[0];
                $v2MinorVersion = $v2Array[1];

                if ($v1MajorVersion > $v2MajorVersion) {
                    $v1_gt_v2 = true;
                } elseif ($v1MajorVersion == $v2MajorVersion) {
                    if ($v1MinorVersion == 'x' && $v2MinorVersion != 'x') {
                        $v1_gt_v2 = true;
                    } elseif ($v1MinorVersion != 'x' && $v2MinorVersion != 'x') {
                        $v1_gt_v2 = (int) $v1MinorVersion > (int) $v2MinorVersion;
                    }
                }

                return $v1_gt_v2 == true;
            }
        });

        return ['upperVersions' => $instancesUpperVersions];
    }

    public function action_fix($input)
    {
        $instanceId = $input->instanceId->int();
        if ($instance = TikiManager\Application\Instance::getInstance($instanceId)) {
            try {
                $instance->getApplication()->fixPermissions();
                Feedback::success(tr("Fixed permissions."));
            } catch (\Exception $e) {
                Feedback::error($e->getMessage());
            }
        } else {
            Feedback::error(tr('Unknown instance'));
        }
        $content = $this->manager_output->fetch();
        if ($content) {
            return [
                'override_action' => 'info',
                'title' => tr('Tiki Manager Instance Fix'),
                'info' => $content,
            ];
        } else {
            if ($input->modal->int()) {
                return Services_Utilities::closeModal();
            } else {
                return [
                    'FORWARD' => [
                        'action' => 'index',
                    ],
                ];
            }
        }
    }

    public function action_delete($input)
    {
        $cmd = new TikiManager\Command\DeleteInstanceCommand();
        $input = new ArrayInput([
            'command' => $cmd->getName(),
            '-i' => $input->instanceId->int(),
        ]);
        $this->runCommand($cmd, $input);
        return [
            'override_action' => 'info',
            'title' => tr('Tiki Manager Delete Instance'),
            'info' => $this->manager_output->fetch(),
            'refresh' => true,
        ];
    }

    public function action_watch($input)
    {
        $cmd = new TikiManager\Command\WatchInstanceCommand();
        $instances = TikiManager\Application\Instance::getInstances(true);
        $instance = TikiManager\Application\Instance::getInstance($input->instanceId->int());

        $IDs = [];

        foreach ($instances as $inst) {
            if ($inst->id != $input->instanceId->int()) {
                $IDs[] = $inst->id;
            }
        }

        $instanceIds = implode(',', $IDs);

        $input = new ArrayInput([
            'command' => $cmd->getName(),
                "--email" => $instance->contact,
                "--exclude" => $instanceIds,
        ]);

        if (empty($this->manager_output->fetch())) {
            try {
                $this->runCommand($cmd, $input);
                Feedback::success(tr('Successful Tiki Manager Watch Instance, Notifications will be sent to <b>%0</b>', htmlspecialchars($instance->contact)));
            } catch (\Exception $e) {
                Feedback::error($e->getMessage());
            }
            return [
                'FORWARD' => [
                    'action' => 'index',
                ],
            ];
        } else {
            return [
                'override_action' => 'info',
                'title' => tr('Tiki Manager Watch Instance'),
                'info' => $this->manager_output->fetch(),
                'refresh' => true,
            ];
        }
    }

    public function action_access($input)
    {
        $cmd = new TikiManager\Command\AccessInstanceCommand();
        $input = new ArrayInput([
            'command' => $cmd->getName(),
            '-i' => $input->instanceId->int(),
        ]);
        $this->runCommand($cmd, $input);

        return [
            'title' => tr('Tiki Manager Access Command'),
            'info' => $this->manager_output->fetch(),
            'refresh' => true,
        ];
    }


    public function action_detect($input)
    {
        $cmd = new TikiManager\Command\DetectInstanceCommand();
        $input = new ArrayInput([
            'command' => $cmd->getName(),
            '-i' => $input->instanceId->int(),
        ]);
        $this->runCommand($cmd, $input);
        return [
            'override_action' => 'info',
            'title' => tr('Tiki Manager Detect Instance'),
            'info' => $this->manager_output->fetch(),
            'refresh' => true,
        ];
    }

    public function action_create($input)
    {
        $cmd = new TikiManager\Command\CreateInstanceCommand();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input_array = [
                'command' => $cmd->getName(),
                "--type" => $input->connection_type->text(),
                "--host" => $input->host->text(),
                "--port" => $input->port->text(),
                "--user" => $input->user->text(),
                "--pass" => $input->pass->text(),
                "--url" => $input->url->text(),
                "--name" => $input->name->text(),
                "--email" => $input->email->text(),
                "--webroot" => $input->webroot->text(),
                "--tempdir" => $input->tempdir->text(),
                "--branch" => $input->branch->text(),
                "--backup-user" => $input->backup_user->text(),
                "--backup-group" => $input->backup_group->text(),
                "--backup-permission" => $input->backup_permission->text(),
                "--db-host" => $input->db_host->text(),
                "--db-user" => $input->db_user->text(),
                "--db-pass" => $input->db_pass->text(),
                "--db-prefix" => $input->db_prefix->text(),
                "--db-name" => $input->db_name->text(),
            ];

            if ($input->instance_type->text() == 'blank') {
                $input_array["--blank"] = true;
                unset($input_array["--branch"]);
            }

            $inputCommand = new ArrayInput($input_array);
            $lastInstanceId = Instance::getLastInstance()->id;

            if ($input->leavepassword->text() == 'yes' || $input->instance_type->text() == 'blank') {
                $this->runCommand($cmd, $inputCommand);
            } else {
                if ($this->validate_password($input->tikipassword->text())) {
                    $this->runCommand($cmd, $inputCommand);
                    $output = $this->manager_output->fetch();
                    $info = "[OK] Please test your site at " . $input->url->text();

                    if (str_contains($output, $info)) {
                        $instance = Instance::getLastInstance();
                        $command = "users:password admin " . $input->tikipassword->text();
                        $cmd = new TikiManager\Command\ConsoleInstanceCommand();
                        $inputCmd = new ArrayInput([
                            'command' => $cmd->getName(),
                            '-i' => $instance->getId(),
                            '-c' => $command,
                        ]);
                        try {
                            $this->runCommand($cmd, $inputCmd);
                        } catch (\Exception $e) {
                            Feedback::error($e->getMessage());
                        }
                    }
                } else {
                    Feedback::error(tr('Invalid password for admin user'));
                }
            }
            $newInstanceId = Instance::getLastInstance()->id;
            if ($lastInstanceId != $newInstanceId) {
                if ($input->profile->text()) {
                    $profile = $input->profile->text();
                    $repository = $input->repository->text();
                    $this->apply_profile($newInstanceId, $profile, $repository);
                }
            }
            return [
                'title' => tr('Create New Instance Result'),
                'info' => $this->manager_output->fetch(),
                'refresh' => true,
            ];
        } else {
            /** For form initialization */
            $inputValues = [
                'instance_types' => ['existing-tiki', 'blank'],
                'selected_instance_type' => 'existing-tiki',
                'connection_types' => ['local', 'ftp', 'ssh'],
                'selected_connection_type' => 'local',
                'host' => '',
                'port' => '',
                'user' => '',
                'pass' => '',
                'url' => '',
                'name' => "",
                'email' => '',
                'webroot' => '',
                'branches' => $this->getTikiBranches(),
                'selected_branch' => "21.x",
                'default_repository' => "profiles.tiki.org",
                'temp_dir' => '/tmp/trim_temp',
                'backup_user' => 'www-data',
                'backup_group' => 'www-data',
                'backup_permission' => '',
                'db_host' => '',
                'db_user' => '',
                'db_pass' => '',
                'db_prefix' => '',
                'db_name' => ''
            ];

            return [
                'title' => tr('Create New Instance'),
                'info' => '',
                'refresh' => true,
                'inputValues' => $inputValues,
                'help' => $this->getCommandHelpTexts($cmd),
                'sshPublicKey' => $_ENV['SSH_PUBLIC_KEY'],
            ];
        }
    }

    public function validate_password($password)
    {
        // Check if the password has at least 8 characters
        return preg_match('/^[a-zA-Z0-9*.!@#\$%^&()\[\]:;<>,?\/~_+-=|]{8,32}$/', $password);
    }

    public function action_edit($input)
    {
        $cmd = new TikiManager\Command\EditInstanceCommand();

        if ($input->edit->text()) {
            $inputCommand = new ArrayInput([
                'command' => $cmd->getName(),
                '-i' => $input->instance->int(),
                "--url" => $input->url->text(),
                "--name" => $input->name->text(),
                "--email" => $input->email->text(),
                "--webroot" => $input->webroot->text(),
                "--tempdir" => $input->tempdir->text(),
                "--backup-user" => $input->backup_user->text(),
                "--backup-group" => $input->backup_group->text(),
                "--backup-permission" => $input->backup_permission->text(),
            ]);

            $this->runCommand($cmd, $inputCommand);

            return [
                'title' => tr('Edit Instance Result'),
                'info' => $this->manager_output->fetch(),
                'refresh' => true,
            ];
        } else {
            $instanceId = $input->instanceId->int();
            $instance = Instance::getInstance($instanceId);

            if ($instance) {
                /** For form initialization */
                $inputValues = [
                    'instance' => $instanceId,
                    'url' => $instance->weburl,
                    'name' => $instance->name,
                    'email' => $instance->contact,
                    'webroot' => $instance->webroot,
                    'temp_dir' => $instance->tempdir,
                    'backup_user' => $instance->getProp('backup_user'),
                    'backup_group' => $instance->getProp('backup_group'),
                    'backup_permission' => decoct($instance->getProp('backup_perm')),
                ];

                return [
                    'title' => tr('Edit instance') . " " . $instance->backup_user,
                    'info' => '',
                    'refresh' => true,
                    'inputValues' => $inputValues,
                    'help' => $this->getCommandHelpTexts($cmd),
                ];
            } else {
                return [
                    'title' => tr('Edit instance (Instance not found)'),
                    'info' => "No Tiki instances available to edit",
                    'refresh' => true,
                ];
            }
        }
    }


    public function action_test_send_email($input)
    {
        $cmd = new TikiManager\Command\ManagerTestSendEmailCommand();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $inputCommand = new ArrayInput([
                'command' => $cmd->getName(),
                "to" => $input->email->text(),
            ]);

            $this->runCommand($cmd, $inputCommand);

            return [
                'title' => tr('Test Send Email Result'),
                'info' => $this->manager_output->fetch(),
                'refresh' => true,
            ];
        } else {
            $inputValues = [
                'email' => ""
            ];

            return [
                'title' => tr('Test send email'),
                'info' => '',
                'refresh' => true,
                'inputValues' => $inputValues
            ];
        }
    }

    public function action_virtualmin_create($input)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $source = $input->source->text();
            $domain = $input->domain->text();
            if (preg_match('/^([^\.]*)\./', $domain, $m)) {
                $remote_user = $m[1];
            } else {
                $remote_user = $domain;
            }
            $email = $input->email->text();
            $name = $input->name->text();
            $branch = $input->branch->text();
            $php_version = $input->php_version->text();

            try {
                $lastInstanceId = Instance::getLastInstance()->id;

                $output = $this->createVirtualminTikiInstance($source, $remote_user, $domain, $email, $name, $branch, $php_version);

                $newInstanceId = Instance::getLastInstance()->id;
                if ($lastInstanceId != $newInstanceId) {
                    if ($input->profile->text()) {
                        $profile = $input->profile->text();
                        $repository = $input->repository->text();
                        $this->apply_profile($newInstanceId, $profile, $repository);
                        $output .= "\n\n" . $this->manager_output->fetch();
                    }
                }
                return [
                    'title' => tr('Create Virtualmin Instance Result'),
                    'override_action' => 'info',
                    'info' => $output,
                    'refresh' => true,
                ];
            } catch (Services_Exception $e) {
                Feedback::error($e->getMessage());
            }
        }

        $cmd = new TikiManager\Command\CreateInstanceCommand();
        $sources_table = TikiDb::get()->table('tiki_source_auth', false);

        $sources = [];
        $records = $sources_table->fetchAll(['identifier', 'scheme', 'domain', 'path']);
        foreach ($records as $record) {
            $sources[$record['identifier']] = "{$record['identifier']}: {$record['scheme']}://{$record['domain']}{$record['path']}";
        }

        return [
            'title' => tr('Create New Virtualmin Instance'),
            'branches' => $this->getTikiBranches(),
            'help' => $this->getCommandHelpTexts($cmd),
            'input' => $input->asArray(),
            'sources' => $sources,
            'default_repository' => "profiles.tiki.org",
        ];
    }

    public function action_available_versions($input)
    {
        $result = [
            'php_versions' => [],
            'available_branches' => $this->getTikiBranches(),
        ];

        $source = $input->source->text();
        $php_version = $input->php_version->text();
        $selected_php_version = null;

        $params = [
            'program' => 'list-php-versions',
            'name-only' => '',
        ];
        $response = $this->virtualminRemoteCommand($source, $params);
        foreach ($response['data'] as $row) {
            $result['php_versions'][] = $row['name'];
            if ($row['name'] == $php_version) {
                $selected_php_version = $php_version;
            }
        }

        if ($selected_php_version) {
            $available_versions = [];
            $requirements = (new YamlFetcher())->getRequirements();
            foreach ($requirements as $requirement) {
                if ($requirement->getPhpVersion()->isValidVersion($selected_php_version)) {
                    $available_versions[] = $requirement->getVersion();
                }
            }
            $result['available_branches'] = array_values(array_filter($result['available_branches'], function ($branch) use ($available_versions) {
                if ($branch == 'master') {
                    return true;
                }
                foreach ($available_versions as $version) {
                    if (substr($branch, 0, strlen($version)) == $version || preg_match("/$version\.\d+/", $branch)) {
                        return true;
                    }
                }
                return false;
            }));
        }

        return $result;
    }

    public function action_clone($input)
    {
        if ($input->clone->text()) {
            $cmd = new TikiManager\Command\CloneInstanceCommand();
            $inputCommand = new ArrayInput(array_merge([
                'command' => $cmd->getName(),
            ], $input->options->asArray()));

            $this->runCommand($cmd, $inputCommand);

            return [
                'title' => tr('Clone Tiki Instance Result'),
                'info' => $this->manager_output->fetch(),
                'refresh' => true,
            ];
        } else {
            $instances = TikiManager\Application\Instance::getInstances(true);

            $cmd = new TikiManager\Command\CloneInstanceCommand();
            $definition = $cmd->getDefinition();

            $options = [];
            foreach ($definition->getOptions() as $option) {
                switch ($option->getName()) {
                    case 'source':
                    case 'target':
                        $type = 'select';
                        $values = [];
                        foreach ($instances as $i) {
                            $values[$i->id] = $i->name;
                        }
                        $selected = $input->instanceId->int() ? $input->instanceId->int() : '';
                        break;
                    case 'branch':
                        $type = 'select';
                        $values = array_combine($this->getTikiBranches(), $this->getTikiBranches());
                        $selected = 'master';
                        break;
                    default:
                        if ($option->acceptValue()) {
                            $type = 'text';
                        } else {
                            $type = 'checkbox';
                        }
                        $values = [];
                        $selected = $option->getDefault();
                }

                $options[] = [
                    'name' => $option->getName(),
                    'label' => ucwords(str_replace('-', ' ', $option->getName())),
                    'type' => $type,
                    'values' => $values,
                    'selected' => $selected,
                    'help' => $option->getDescription(),
                    'is_array' => $option->isArray(),
                ];
            }

            return [
                'title' => tr('Clone Tiki Instance'),
                'options' => $options,
            ];
        }
    }

    public function action_console($input)
    {
        $instanceId = $input->instanceId->int();
        if (TikiManager\Application\Instance::getInstance($instanceId)) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $cmd = new TikiManager\Command\ConsoleInstanceCommand();
                $inputCmd = new ArrayInput([
                    'command' => $cmd->getName(),
                    '-i' => $instanceId,
                    '-c' => $input->command->text(),
                ]);
                try {
                    $this->runCommand($cmd, $inputCmd);
                } catch (\Exception $e) {
                    Feedback::error($e->getMessage());
                }
                return [
                    'override_action' => 'info',
                    'title' => tr('Tiki Manager Console Command'),
                    'info' => $this->manager_output->fetch(),
                    'refresh' => true,
                ];
            } else {
                return [
                    'title' => tr('Tiki Manager Console Command'),
                    'info' => '',
                    'instanceId' => $input->instanceId->int()
                ];
            }
        } else {
            Feedback::error(tr('Unknown instance'));
            return [
                'FORWARD' => [
                    'action' => 'index',
                ],
            ];
        }
    }

    public function action_check($input)
    {
        $cmd = new TikiManager\Command\CheckInstanceCommand();
        $input = new ArrayInput([
            'command' => $cmd->getName(),
            '-i' => $input->instanceId->int()
        ]);
        $this->runCommand($cmd, $input);
        return [
            'override_action' => 'info',
            'title' => tr('Tiki Manager Check Instance'),
            'info' => $this->manager_output->fetch(),
            'refresh' => true,
        ];
    }

    public function action_requirements($input)
    {
        $this->runCommand(new TikiManager\Command\CheckRequirementsCommand());
        return [
            'override_action' => 'info',
            'title' => tr('Tiki Manager Check Requirements'),
            'info' => $this->manager_output->fetch()
        ];
    }

    public function action_clear_cache($input)
    {
        $this->runCommand(new TikiManager\Command\ClearCacheCommand());
        return [
            'override_action' => 'info',
            'title' => tr('Tiki Manager Clear Cache'),
            'info' => $this->manager_output->fetch()
        ];
    }

    public function loadEnv()
    {
        global $prefs, $user, $base_url, $tikipath;

        $this->loadManagerEnv();
        $this->setManagerOutput();

        // check current instance exist
        $existing = TikiManager\Application\Instance::getInstances(true);
        $found = false;
        foreach ($existing as $instance) {
            if ($instance->weburl == $base_url && $instance->type == 'local') {
                $found = true;
                break;
            }
        }

        // and import it if not
        if (! $found) {
            $instance = new TikiManager\Application\Instance();
            $instance->type = 'local';
            $access = $instance->getBestAccess();
            $discovery = $instance->getDiscovery();

            if ($type == 'local') {
                $access->host = 'localhost';
                $access->user = $discovery->detectUser();
            }

            $instance->name = $prefs['browsertitle'];
            $instance->contact = TikiLib::lib('user')->get_user_email($user);
            $instance->weburl = $base_url;
            $instance->webroot = rtrim($tikipath, '/');
            $instance->tempdir = $_ENV['TEMP_FOLDER'];
            $instance->backup_user = $access->user;
            $instance->backup_group = @posix_getgrgid(posix_getegid())['name'];
            $instance->backup_perm = 0770;
            $instance->save();
            $access->save();

            $instance->detectPHP();
            $instance->findApplication();
        }
    }

    public function getTikiBranches()
    {
        return Services_Manager_Utilities::getAvailableTikiVersions();
    }

    public function action_apply($input)
    {
        $instanceId = $input->instanceId->int();
        if (TikiManager\Application\Instance::getInstance($instanceId)) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $profile = $input->profile->text();
                $repository = $input->repository->text();
                $this->apply_profile($instanceId, $profile, $repository);

                return [
                    'title' => tr('Tiki Manager Apply Profile'),
                    'info' => $this->manager_output->fetch(),
                    'refresh' => true,
                ];
            } else {
                $input = ["repository" => "profiles.tiki.org"];
                $input = new JitFilter($input);
                return [
                    'title' => tr('Apply a profile'),
                    'info' => '',
                    'instanceId' => $input->instanceId->int(),
                    'profiles' => $this->action_get_profiles($input),
                    'default_repository' => "profiles.tiki.org",
                ];
            }
        } else {
            Feedback::error(tr('Unknown instance'));
            return [
                'FORWARD' => [
                    'action' => 'index',
                ],
            ];
        }
    }

    public function action_maintenance($input)
    {
        $cmd = new TikiManager\Command\MaintenanceInstanceCommand();
        $instanceId = $input->instanceId->int();
        $mode = $input->mode->text();

        $inputCommand = new ArrayInput([
            'command' => $cmd->getName(),
            '-i' => $instanceId,
            'status' => $mode
        ]);

        $this->runCommand($cmd, $inputCommand);

        return [
            'override_action' => 'info',
            'title' => tr('Tiki Manager Instance Maintenance'),
            'info' => $this->manager_output->fetch(),
            'refresh' => true,
        ];
    }

    public function action_tiki_versions($input)
    {
        $cmd = new TikiManager\Command\TikiVersionCommand();

        if ($input->filter->text()) {
            $inputCommand = new ArrayInput([
                'command' => $cmd->getName(),
                "--vcs" => $input->vcs->text(),
            ]);

            $this->runCommand($cmd, $inputCommand);

            return [
                'title' => tr('Tiki Versions'),
                'info' => $this->manager_output->fetch(),
                'refresh' => true,
            ];
        } else {
            /** Form initialization */
            $inputValues = [
                'vcs' => ['git', 'svn', 'src'],
                'selected_vcs' => 'git'
            ];

            return [
                'title' => tr('Tiki Versions'),
                'info' => '',
                'refresh' => true,
                'inputValues' => $inputValues,
                'help' => $this->getCommandHelpTexts($cmd)
            ];
        }
    }

    public function action_setup_watch($input)
    {
        $cmd = new TikiManager\Command\SetupWatchManagerCommand();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $exclude = implode(',', $input->exclude->array());
            $inputCommand = new ArrayInput([
                'command' => $cmd->getName(),
                "--email" => $input->email->text(),
                "--time" => $input->time->text(),
                "--exclude" => ! empty($exclude) ? $exclude : ''
            ]);

            $this->runCommand($cmd, $inputCommand);

            return [
                'title' => tr('Setup Watch Result'),
                'info' => $this->manager_output->fetch(),
                'refresh' => true,
            ];
        } else {
            $instances = TikiManager\Application\Instance::getInstances(true);

            return [
                'title' => tr('Setup Watch'),
                'info' => '',
                'refresh' => true,
                'instances' => $instances,
                'help' => $this->getCommandHelpTexts($cmd)
            ];
        }
    }

    public function action_setup_clone($input)
    {
        $cmd = new TikiManager\Command\SetupCloneManagerCommand();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input_array = [
                "command" => $cmd->getName(),
                "--time" => $input->crontime->text(),
                "--source" => $input->source->text(),
                "--target" => $input->target->text()
            ];

            if ($input->upgrade->text() == "yes") {
                $input_array["--upgrade"] = true;
            }

            if ($input->branch->text() == "yes") {
                $input_array["-b"] = $input->branch->text();
            }

            if ($input->direct->text() == "yes") {
                $input_array["-d"] = true;
            }

            if ($input->use_last_backup->text() == "yes") {
                $input_array["--use-last-backup"] = true;
            }

            if ($input->keep_backup->text() == "yes") {
                $input_array["--keep-backup"] = true;
            }

            if ($input->live_reindex->text() == "yes") {
                $input_array["--live-reindex"] = true;
            }

            if ($input->skip_reindex->text() == "yes") {
                $input_array["--skip-reindex"] = true;
            }

            if ($input->skip_cache_warmup->text() == "yes") {
                $input_array["--skip-cache-warmup"] = true;
            }

            $inputCommand = new ArrayInput($input_array);

            $this->runCommand($cmd, $inputCommand);

            return [
                'title' => tr('Manager Clone Instance Result'),
                'info' => $this->manager_output->fetch(),
                'refresh' => true,
            ];
        } else {
            $instances = TikiManager\Application\Instance::getInstances();

            if (count($instances) > 0) {
                /** For form initialization */
                $inputValues = [
                    'instances' => $instances,
                    'branches' => $this->getTikiBranches()
                ];

                return [
                    'title' => tr('Manager Clone Cron Job'),
                    'info' => '',
                    'refresh' => true,
                    'inputValues' => $inputValues,
                    'help' => $this->getCommandHelpTexts($cmd),
                ];
            } else {
                return [
                    'title' => tr('Clone Cron Job (No Instance Found)'),
                    'info' => "No Tiki instances available For clone",
                    'refresh' => true,
                ];
            }
        }
    }
    function action_manager_backup($input)
    {
        $cmd = new TikiManager\Command\SetupBackupManagerCommand();

        return $this->manager_setup($input, $cmd, "backup");
    }

    function action_manager_update($input)
    {
        $cmd = new TikiManager\Command\SetupUpdateCommand();

        return $this->manager_setup($input, $cmd, "update");
    }

    private function manager_setup($input, $cmd, $event)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input_array = [
                "command" => $cmd->getName(),
                "--time" => $input->time->text(),
            ];

            switch ($event) {
                case "update":
                    if (count($input->instance->array()) < 1) {
                        return $this->manager_setup_error($event);
                    }
                    $input_array['--instances'] = implode(',', $input->instance->array());
                    break;

                case "backup":
                    if (count($input->instance->array()) > 0) {
                        $input_array["-x"] = implode(",", $input->instance->array());
                    }

                    if ($input->number_backups_to_keep->int()) {
                        $input_array["-mb"] = $input->number_backups_to_keep->int();
                    }
                    break;
            }
            $input_array["-e"] = $input->email->text();
            $inputCommand = new ArrayInput($input_array);
            $this->runCommand($cmd, $inputCommand);

            return [
                'title' => tr(ucfirst($event) . ' Instance Result'),
                'info' => $this->manager_output->fetch(),
                'refresh' => true,
            ];
        } else {
            switch ($event) {
                case "update":
                    $instance_list = TikiManager\Application\Instance::getUpdatableInstances();
                    break;

                case "backup":
                    $instance_list = TikiManager\Application\Instance::getInstances(true);
                    break;
            }

            if (count($instance_list) > 0) {
                /** For form initialization */
                $inputValues = [
                    'instances' => $instance_list,
                    'time' => '',
                    'email' => '',
                    'event' => $event,
                    'action' => 'manager_' . $event
                ];

                if ($event == "backup") {
                    $inputValues ['number_backups_to_keep'] = '';
                }

                return [
                    'title' => tr(ucfirst($event) . ' Cron Job'),
                    'info' => '',
                    'refresh' => true,
                    'inputValues' => $inputValues,
                    'help' => $this->getCommandHelpTexts($cmd),
                ];
            } else {
                return $this->manager_setup_error($event);
            }
        }
    }

    private function manager_setup_error($event)
    {
        return [
            'title' => tr(ucfirst($event) . ' Cron Job (No Instance Found)'),
            'info' => "No Tiki instances available " . $event,
            'refresh' => true,
        ];
    }

    public function action_backup($input)
    {
        $cmd = new TikiManager\Command\BackupInstanceCommand();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $arrayInput = [
                'command' => $cmd->getName(),
                '-i' => $input->instanceId->int(),
                '-e' => $input->email->text(),
                '-mb' => $input->number_backups_to_keep->int()
            ];

            if ($input->backup_process->text() == "partial") {
                $arrayInput['--partial'] = $input->backup_process->text();
            }

            $inputCommand = new ArrayInput($arrayInput);

            $this->runCommand($cmd, $inputCommand);

            return [
                'title' => tr('Backup Instance Result'),
                'info' => $this->manager_output->fetch(),
                'refresh' => true,
            ];
        } else {
            $instanceId = $input->instanceId->int();
            $instance = Instance::getInstance($instanceId);

            if ($instance) {
                /** For form initialization */
                $inputValues = [
                    'instanceId' => $instanceId,
                    'email' => '',
                    'backup_process' => ['full backup','partial']
                ];

                return [
                    'title' => tr('Backup Instance') . ' ' . $instance->backup_user,
                    'info' => '',
                    'refresh' => true,
                    'inputValues' => $inputValues,
                    'help' => $this->getCommandHelpTexts($cmd)
                ];
            } else {
                return [
                    'title' => tr('Backup instance (Instance not found)'),
                    'info' => 'No Tiki instances selected for backup',
                    'refresh' => true,
                ];
            }
        }
    }

    public function action_checkout($input)
    {
        $cmd = new TikiManager\Command\CheckoutCommand();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $inputCommand = new ArrayInput([
                'command' => $cmd->getName(),
                '-i' => $input->instanceId->int(),
                '-f' => $input->folder->text(),
                '-u' => $input->url->text(),
                '-b' => $input->branch->text(),
                '-r' => $input->revision->text()
            ]);

            $this->runCommand($cmd, $inputCommand);

            return [
                'override_action' => 'info',
                'title' => tr('Tiki Manager Checkout'),
                'info' => $this->manager_output->fetch(),
                'refresh' => true
            ];
        } else {
            $inputValues = [
                'instanceId' => $input->instanceId->int(),
            ];

            return [
                'title' => tr('Tiki Manager Checkout'),
                'info' => '',
                'refresh' => true,
                'inputValues' => $inputValues,
                'help' => $this->getCommandHelpTexts($cmd)
            ];
        }
    }

    public function action_revert($input)
    {
        $cmd = new TikiManager\Command\RevertInstanceCommand();
        $input = new ArrayInput([
            'command' => $cmd->getName(),
            '-i' => $input->instanceId->int()
        ]);
        $this->runCommand($cmd, $input);
        return [
            'override_action' => 'info',
            'title' => tr('Tiki Manager - Revert Instance'),
            'info' => $this->manager_output->fetch(),
            'refresh' => true,
        ];
    }

    public function action_get_profiles($input)
    {
        $repository = $input->repository->text();
        $list = new Tiki_Profile_List();
        $sources = $list->getSources();
        $source_url = null;
        foreach ($sources as $source) {
            if ($source['domain'] == $repository) {
                $source_url = $source['url'];
            }
        }
        if ($source_url) {
            $list->refreshCache($source_url);
            $profiles = $list->getList();
            $profiles = array_map(function ($i) {
                return $i['name'];
            }, $profiles);

            return $profiles;
        }
        return [];
    }

    private function apply_profile($instanceId, $profile, $repository)
    {
        $cmd = new TikiManager\Command\ApplyProfileCommand();
        $inputCmd = new ArrayInput([
            'command' => $cmd->getName(),
            '-i' => $instanceId,
            '-p' => $profile,
            '-r' => $repository,
        ]);
        try {
            $this->runCommand($cmd, $inputCmd);
        } catch (\Exception $e) {
            Feedback::error($e->getMessage());
        }
    }

    public function addInteractiveJS($consoleCommand)
    {
        $utilities = new Services_Manager_Utilities();
        $utilities->addInteractiveJS($consoleCommand);
    }
}