
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Tiki_Profile_InstallHandler_GoalSet extends Tiki_Profile_InstallHandler
{
    private $goals = [];

    public function fetchData()
    {
        $data = $this->obj->getData();

        if (isset($data['goals']) && is_array($data['goals'])) {
            $this->goals = $data['goals'];
        }
    }

    public function canInstall()
    {
        $this->fetchData();

        return true;
    }

    public function doInstall()
    {
        $this->fetchData();
        $this->replaceReferences($this->goals);

        $lib = TikiLib::lib('goal');
        $lib->preserveGoals($this->goals);

        return true;
    }

    public static function export($writer)
    {
        $goallib = TikiLib::lib('goal');
        $goals = $goallib->listGoals();

        $ids = [];
        foreach ($goals as $goal) {
            if (Tiki_Profile_InstallHandler_Goal::export($writer, $goal['goalId'])) {
                $ids[] = $goal['goalId'];
            }
        }

        $writer->addObject(
            'goal_set',
            'set',
            [
                'goals' => $writer->getReference('goal', $ids),
            ]
        );

        return true;
    }
}