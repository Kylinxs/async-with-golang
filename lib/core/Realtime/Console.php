<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Realtime;

use Perms;
use Ratchet\ConnectionInterface;
use React\ChildProcess\Process;

/**
 * Ratchet App executing Tiki console commands and returning output synchronously.
 * Integrates with UI with directly output of the command or ability to attach to
 * a running command later and still see its output.
 */
class Console extends SessionAwareApp
{
    protected $commands;

    public function __construct()
    {
        parent::__construct();
        $this->commands = [];
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        global $user;

        parent::onMessage($from, $msg);

        if (! Perms::get()->admin) {
            $from->send("You don't have permission to execute console commands.");
            return;
        }

        if ($msg == 'attach') {
            $from->send(count($this->commands[$user] ?? []));
            return;
        }

        $command = PHP_BINARY . ' console.php ' . implode(' ', array_map('escapeshellarg', preg_split('/\s+/', $msg)));

        $process = new Process($command);
        $process->start();

        $this->commands[$user][] = $process;

        $process->stdout->on('data', function ($chunk) use ($user) {
            $chunk = $this->formatHtml($chunk);
            $this->sendToInterestedParties($user, $chunk);
        });

        $process->stderr->on('data', function ($chunk) use ($user) {
            $chunk = $this->formatHtml($chunk);
            $this->sendToInterestedParties($user, $chunk);
        });

        $process->on('exit', function ($exitCode, $termSignal) use ($user, $process) {
            foreach ($this->commands[$user] as $i => $proc) {
                if ($proc->getPid() == $process->getPid()) {
                    unset($this->commands[$user][$i]);
                }
            }
            if ($termSignal === null) {
                if ($exitCode != 0) {
                    $this->sendToInterestedParties($user, 'Command exit with code ' . $exitCode);
                }
            } else {
                $from->sendToInterestedParties($user, 'Command terminated with signal ' . $termSignal);
            }
        });

        $process->stdin->end();
    }

    protected function formatHtml($text)
    {
        $formatter = \TikiManager\Config\App::get('ConsoleHtmlFormatter');
        return $formatter->format($text);
    }

    protected function sendToInterestedParties($user, $msg)
    {
        foreach ($this->clients as $client) {
            if ($this->clients[$client] == $user) {
                $client->send($msg);
            }
        }
    }
}
