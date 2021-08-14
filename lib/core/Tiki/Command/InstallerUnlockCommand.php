
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallerUnlockCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('installer:unlock')
            ->setDescription('Enable the installer')
            ->setHelp('Unlock the installer so that users can re-install Tiki through the browser');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = 'db/lock';
        if (file_exists($file)) {
            if (unlink($file)) {
                $output->writeln("<info>Installer unlocked</info>");
            } else {
                $output->writeln("<error>Could not unlock installer</error>");
            }
        } else {
            $output->writeln("<info>Installer is already unlocked</info>");
        }
    }
}