<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Command\ProfileExport;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Forum extends ObjectWriter
{
    protected function configure()
    {
        $this
            ->setName('profile:export:forum')
            ->setDescription('Export a forum definition')
            ->addOption(
                'all',
                null,
                InputOption::VALUE_NONE,
                'Export all forums'
            )
            ->addArgument(
                'forum',
                InputArgument::OPTIONAL,
                'Forum ID'
            );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $forumId = $input->getArgument('forum');
        $all = $input->getOption('all');

        if (! $all && empty($forumId)) {
            $output->writeln('<error>' . tra('Not enough arguments (missing: "forum" or "--all" option)') . '</error>');
            return false;
        }

        $writer = $this->getProfileWriter($input);

        $result = \Tiki_Profile_InstallHandler_Forum::export($writer, $forumId, $all);

        if ($result) {
            $writer->save();
        } else {
            $output->writeln("Forum not found: $forumId");
        }
    }
}
