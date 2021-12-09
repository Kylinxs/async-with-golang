<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * Class responsible for loading Scheduler object information from profile
 *
 * @since Class available since Tiki 19
 *
 */
class Tiki_Profile_InstallHandler_Scheduler extends Tiki_Profile_InstallHandler
{
    /**
     * Get profile data
     *
     * @return array
     */
    public function getData()
    {
        if ($this->data) {
            return $this->data;
        }

        $data = $this->obj->getData();

        $defaults = [
            'description' => '',
            'params' => [],
            'status' => 'active',
            're_run' => '0',
        ];

        $data = array_merge($defaults, $data);
        $this->replaceReferences($data);

        return $this->data = $data;
    }

    /**
     * Check if specific object from profile has the minimum required fields and valid values
     *
     * @return bool
     */
    public function canInstall()
    {
        $data = $this->getData();

        if (! isset($data['name'], $data['task'], $data['run_time']) || ! Scheduler_Utils::validate_cron_time_format($data['run_time'])) {
            return false;
        }

        return true;
    }

    /**
     * @return int
     */
    public function doInstall()
    {
        if ($this->canInstall()) {
            $params = [];
            $taskParamsError = false;
            $availableSchedulerTasks = array_keys(Scheduler_Item::getAvailableTasks());
            $data = $this->getData();

            if (in_array($data['task'], $availableSchedulerTasks)) {
                $className = 'Scheduler_Task_' . $data['task'];
                if (class_exists($className)) {
                    $logger = new Tiki_Log('Schedulers', \Psr\Log\LogLevel::ERROR);
                    $class = new $className($logger);
                    $taskParams = $class->getParams();

                    if (isset($taskParams)) {
                        foreach ($taskParams as $key => $taskParam) {
                            if (! empty($taskParam['required']) && ! array_key_exists($key, $data['params'])) {
                                $taskParamsError = true;
                                break;
                            }
                            $params[$key] = isset($data['params'][$key]) ? $data['params'][$key] : '';
                        }
                    }

                    if (! $taskParamsError) {
                        $data['params'] = json_encode($params, true);

                        $schedLib = TikiLib::lib('scheduler');
                        $schedulerId = $schedLib->set_scheduler($data['name'], $data['description'], $data['task'], $data['params'], $data['run_time'], $data['status'], $data['re_run'], $data['run_only_once']);

                        return $schedulerId;
                    }
                }
            }
        }
    }
}
