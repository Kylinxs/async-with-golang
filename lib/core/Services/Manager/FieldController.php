
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

/**
 * Class Services_Manager_FieldController
 */
class Services_Manager_FieldController
{
    use Services_Manager_Trait;

    public function action_create($input)
    {
        global $user;

        list($item, $handler) = $this->getItemAndFieldHandler($input);

        $type = $handler->getOption('newInstanceType');
        $conn_host = $handler->getOption('newInstanceHost');
        $conn_port = $handler->getOption('newInstancePort');
        $conn_user = $handler->getOption('newInstanceUser');
        $conn_pass = $handler->getOption('newInstancePass');
        $contact = TikiLib::lib('user')->get_user_email($user);
        $name = uniqid();
        $weburl = str_replace('{slug}', $name, $handler->getOption('newInstanceTemplateUrl'));
        $webroot = str_replace('{slug}', $name, $handler->getOption('newInstanceWebroot'));
        $tempdir = str_replace('{slug}', $name, $handler->getOption('newInstanceTempdir'));
        $backup_user = $handler->getOption('newInstanceBackupUser');
        $backup_group = $handler->getOption('newInstanceBackupGroup');
        $backup_perms = $handler->getOption('newInstanceBackupPerms');
        $branch = $input->version->text();
        $db_host = $handler->getOption('newInstanceDbHost');
        $db_user = $handler->getOption('newInstanceDbUser');
        $db_pass = $handler->getOption('newInstanceDbPass');
        $db_prefix = $handler->getOption('newInstanceDbPrefix');

        if ($db_prefix) {
            $db_prefix .= 'item' . $itemId . 'field' . $fieldId;
        }

        // TODO: see if some other validation is needed (note that most of the valiation is in the actual create command and that error output is already passed to the user)
        foreach (['type'] as $param) {
            if (empty($$param)) {
                throw new Services_Exception(tr('Missing required field parameter: %0', $param));
            }
        }

        $cmd = new TikiManager\Command\CreateInstanceCommand();
        $input = new ArrayInput([
            'command' => $cmd->getName(),
            '--type' => $type,
            '--host' => $conn_host,
            '--port' => $conn_port,
            '--user' => $conn_user,
            '--pass' => $conn_pass,
            '--url' => $weburl,
            '--name' => $name,
            '--email' => $contact,
            '--webroot' => $webroot,
            '--tempdir' => $tempdir,
            '--branch' => $branch,
            '--backup-user' => $backup_user,
            '--backup-group' => $backup_group,
            '--backup-permission' => octdec($backup_perms),
            '--db-host' => $db_host,
            '--db-user' => $db_user,
            '--db-pass' => $db_pass,
            '--db-prefix' => $db_prefix,
        ]);
        $this->runCommand($cmd, $input);

        $this->addInstanceToFieldValue($name, $handler, $item);

        return [
            'title' => tr('Tiki Manager Create Instance'),
            'info' => $this->manager_output->fetch(),
        ];
    }

    public function action_create_source($input)
    {
        global $user;

        list($item, $handler) = $this->getItemAndFieldHandler($input);

        $source = $handler->getOption('source');
        $remote_user = uniqid();
        $domain = str_replace('{slug}', $remote_user, $handler->getOption('newInstanceTemplateUrl'));
        $domain = str_replace('https://', '', $domain);
        $email = TikiLib::lib('user')->get_user_email($user);
        $name = $domain;
        $branch = $input->version->text();

        $output = $this->createVirtualminTikiInstance($source, $remote_user, $domain, $email, $domain, $branch);

        $this->addInstanceToFieldValue($name, $handler, $item);

        return [
            'title' => tr('Create Virtualmin Instance Result'),
            'override_action' => 'create',
            'info' => $output,
        ];
    }

    public function action_delete($input)
    {
        list($item, $handler) = $this->getItemAndFieldHandler($input);

        $cmd = new TikiManager\Command\DeleteInstanceCommand();
        $cmdInput = new ArrayInput([
            'command' => $cmd->getName(),
            '-i' => $input->instanceId->int(),
        ]);
        $this->runCommand($cmd, $cmdInput);

        $this->removeInstanceFromFieldValue($input->instanceId->int(), $handler, $item);

        return [
            'override_action' => 'create',
            'title' => tr('Tiki Manager Delete Instance'),
            'info' => $this->manager_output->fetch(),
        ];
    }

    public function loadEnv()
    {
        $this->loadManagerEnv();
        $this->setManagerOutput();
    }

    protected function getItemAndFieldHandler($input)
    {
        $trklib = TikiLib::lib('trk');
        $itemId = $input->itemId->int();
        $fieldId = $input->fieldId->int();

        if (empty($itemId) || empty($fieldId)) {
            throw new Services_Exception(tr("Missing itemId or fieldId."));
        }

        $field = $trklib->get_field_info($fieldId);

        if (! $field) {
            throw new Services_Exception(tr("Field not found: %0", $fieldId));
        }

        $item = $trklib->get_tracker_item($itemId);

        if (! $item) {
            throw new Services_Exception(tr("Item not found: %0", $itemId));
        }

        return [$item, $trklib->get_field_handler($field, $item)];
    }

    protected function addInstanceToFieldValue($instanceName, $handler, $item)
    {
        $instances = TikiManager\Application\Instance::getInstances(false);
        $instances = array_filter($instances, function ($i) use ($instanceName) {
            return $i->name == $instanceName;
        });
        $instance = array_shift($instances);
        if ($instance) {
            $value = $handler->addValue($instance->getId());
            $this->saveFieldValue($value, $handler, $item);
        }
    }

    protected function removeInstanceFromFieldValue($instanceId, $handler, $item)
    {
        $value = $handler->removeValue($instanceId);
        $this->saveFieldValue($value, $handler, $item);
    }

    protected function saveFieldValue($value, $handler, $item)
    {
        $field = $handler->getFieldDefinition();
        $utilities = new Services_Tracker_Utilities();
        $utilities->updateItem(
            Tracker_Definition::get($item['trackerId']),
            [
                'itemId' => $item['itemId'],
                'status' => $item['status'],
                'fields' => [
                    $field['fieldId'] => $value,
                ],
                'validate' => false,
            ]
        );
    }
}