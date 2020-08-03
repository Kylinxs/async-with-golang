
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
 * Class Services_Manager_PatchController
 */
class Services_Manager_PatchController
{
    use Services_Manager_Trait;

    public function action_index($input)
    {
        $instanceId = $input->instanceId->int();
        if ($instance = TikiManager\Application\Instance::getInstance($instanceId)) {
            $patches = TikiManager\Application\Patch::getPatches($instanceId);
            return [
                'title' => tr('Tiki Manager Instance Patches'),
                'instance' => $instance,
                'patches' => $patches,
            ];
        } else {
            Feedback::error(tr('Unknown instance'));
        }
        if ($input->modal->int()) {
            return Services_Utilities::closeModal();
        } else {
            return [
                'FORWARD' => [
                    'controller' => 'manager',
                    'action' => 'index',
                ],
            ];
        }
    }

    public function action_apply($input)
    {
        $instanceId = $input->instanceId->int();
        if ($instance = TikiManager\Application\Instance::getInstance($instanceId)) {
            $cmd = new TikiManager\Command\ApplyPatchCommand();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $inputCommand = new ArrayInput(array_merge([
                    'command' => $cmd->getName(),
                    '--instances' => $instanceId,
                ], $input->options->asArray()));

                $this->runCommand($cmd, $inputCommand);

                return [
                    'info' => $this->manager_output->fetch(),
                    'instanceId' => $instanceId
                ];
            } else {
                $definition = $cmd->getDefinition();

                $options = [];
                foreach ($definition->getOptions() as $option) {
                    if ($option->getName() == 'instances') {
                        continue;
                    }

                    if ($option->acceptValue()) {
                        $type = 'text';
                    } else {
                        $type = 'checkbox';
                    }
                    $selected = $option->getDefault();

                    $options[] = [
                        'name' => $option->getName(),
                        'label' => ucwords(str_replace('-', ' ', $option->getName())),
                        'type' => $type,
                        'selected' => $selected,
                        'help' => $option->getDescription(),
                        'is_array' => $option->isArray(),
                        'required' => $option->isValueRequired(),
                    ];
                }
                return [
                    'title' => tr('Tiki Manager Instance Apply Patch'),
                    'instance' => $instance,
                    'options' => $options,
                ];
            }
        } else {
            Feedback::error(tr('Unknown instance'));
        }
        return Services_Utilities::closeModal();
    }

    public function action_delete($input)
    {
        $cmd = new TikiManager\Command\DeletePatchCommand();
        $input = new ArrayInput([
            'command' => $cmd->getName(),
            '-p' => $input->patchId->int(),
        ]);
        $this->runCommand($cmd, $input);
        Feedback::success($this->manager_output->fetch());
        return Services_Utilities::closeModal();
    }

    public function loadEnv()
    {
        $this->loadManagerEnv();
        $this->setManagerOutput();
    }
}