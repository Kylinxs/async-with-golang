<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Services_Performance_Controller
{
    /**
     * Load function ran on setup
     * @throws Services_Exception_Disabled
     */
    public function setUp()
    {
        Services_Exception_Disabled::check('tiki_monitor_performance');
    }

    /**
     * Beacon function used by boomerangjs to register load times and other possible parameters
     * @documentation https://developer.akamai.com/tools/boomerang/docs/BOOMR.plugins.RT.html
     * @param $input
     * @throws Exception
     */
    public function action_beacon($input)
    {
        $performanceLib = TikiLib::lib('performancestats');

        if (! $performanceLib->shouldLog($input['rt_start'], $input['u'])) {
            return;
        }

        return $performanceLib->addRecord($input['u'], $input['t_done']);
    }
}
