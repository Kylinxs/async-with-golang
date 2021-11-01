
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Process;

use Symfony\Component\Process\Process as SymfonyProcess;

class Process extends SymfonyProcess
{
    /**
     * @inheritdoc
     */
    public function __construct($commandline, $cwd = null, array $env = null, $input = null, $timeout = 60)
    {
        $env = $this->setEnvDefaults($env);
        parent::__construct($commandline, $cwd, $env, $input, $timeout);
    }

    protected function setEnvDefaults($env)
    {
        $env = empty($env) ? [] : $env;

        if (! isset($env['HTTP_ACCEPT_ENCODING'])) {
            $env['HTTP_ACCEPT_ENCODING'] = '';
        }

        return $env;
    }
}