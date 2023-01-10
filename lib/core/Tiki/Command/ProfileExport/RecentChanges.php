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

class RecentChanges extends ObjectWriter
{
    protected function configure()
    {
        $this
            ->setName('profile:export:recent-changes')
            ->setDescription('List the recent changes in prevision of export')
            ->addOption(
                'since',
                null,
                InputOption::VALUE_REQUIRED,
                'Date from which the actions should be read in the log, can either be a date or a relative time period'
            )
            ->addOption(
                'ignore',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Adds an object to the ignore list. Format: object_type:object_id'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($since = $input->getOption('since')) {
            $since = strtotime($since);
        }

        $ignoreList = [];
        foreach ($input->getOption('ignore') as $object) {
            if (preg_match("/^(?P<type>\w+):(?P<object>.+)$/", $object, $parts)) {
                $ignoreList