<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

use TikiManager\Application\Instance;
use TikiManager\Libs\VersionControl\Git;
use TikiManager\Libs\VersionControl\Svn;
use TikiManager\Libs\VersionControl\Src;

class Services_Manager_Utilities
{
    use Services_Manager_Trait;

    public function loadEnv($isWeb = true)
    {
        $this->loadManagerEnv($isWeb);
        $this->setManagerOutput();
    }

    public function getManagerOutput()
    {
        return $this->manager_output;
    }

    public static function getAvailableTikiVersions()
    {
        $dir = getcwd();
        chdir('..');

        $instances = Instance::getInstances(false);
        $instance = reset($instances);

        $available = [];
        $vcs = null;

        $output = `git --version`;
        if (strstr($output, 'version')) {
            $vcs = new Git($instance);
        }

        if (! $vcs) {
            $output = `svn --version`;
            if (strstr($output, 'version')) {
                $vcs = new Svn($instance);
            }
        }

        if (! $vcs) {
            $vcs = new Src($instance);
        }

        $versions = $vcs->getAvailableBranches();
        foreach ($versions as $key => $version) {
            preg_match('/(\d+\.|trunk|master)/', $version->branch, $matches);
            if (! array_key_exists(0, $matches)) {
                continue;
            }
            $available[] = $version->branch;
        }

        chdir($dir);

        return $available;
    }

    /**
     * Check if Tiki manager feature is enabled and Tiki manager package installed
     */
    public function tikiManagerCheck()
    {
        Services_Exception_Disabled::check('feature_tiki_manager');
        $this->ensureInstalled();
    }

    public function addInteractiveJS($consoleCommand)
    {
        $headerLib = TikiLib::lib('header');
            $js = '
                var WSResponseContainer = document.getElementById("ws-response-container");
                var tikiWS = tikiOpenWS("console");

                WSResponseContainer.removeAttribute("hidden");
                tikiWS.onmessage = function(e) {
                    $("#ws-response-container").append(e.data.trim().replaceAll("\n", "<br>\n") + "<br>");
                };
                tikiWS.onopen = function(e) {
                    tikiWS.send("' . $consoleCommand . '");
                };
                tikiWS.onerror = function(e) {
                    $("#ws-response-container").append("<span class=\"error\">Error connecting to realtime communication server. If it is not set up correctly, you can disable realtime setting from Tiki admin.</span>");
                };
            ';
            $headerLib->add_jq_onready($js);
    }

    public static function getAvailableActions()
    {
        $available_actions = ['access', 'backup', 'blank', 'check', 'clone', 'cloneandupgrade', 'console', 'copysshkey', 'create', 'delete', 'detect', 'edit', 'fixpermissions', 'import', 'list', 'maintenance', 'patch_apply', 'patch_delete', 'patch_list', 'profile_apply', 'restore', 'revert', 'setup-scheduler-cron', 'stats', 'update', 'upgrade', 'watch', 'info'];
        return $available_actions;
    }
}
