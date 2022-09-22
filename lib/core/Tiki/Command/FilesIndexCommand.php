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
use Symfony\Component\Console\Question\ConfirmationQuestion;
use TikiLib;

/**
 * Class FilesIndexCommand
 * Responsible for adding or modifying file handlers
 * @package Tiki\Command
 */
class FilesIndexCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('files:index')
            ->setDescription(tr('Index files'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $searchTextReindexedFilesAmount = TikiLib::lib('filegal')->reindex_all_files_for_search_text();
        $output->writeln("<info>" . tr("The search text was reindexed for a total of %0 files.", $searchTextReindexedFilesAmount) . "</info>");
    }
}
