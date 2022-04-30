<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Exception;
use TikiLib;

/**
 * @package Tiki\Command
 *
 * Calculate translation percentage for each lang/xx/language.php files
 *
 */
class TranslationPercentageCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('translation:percentage')
            ->setDescription('Get the translation percentage for each language.php file')
            ->setHelp('Calculate translation percentage for each language.php file by scanning all Tiki files and output the result as wiki syntax.')
            ->addOption(
                'page',
                'p',
                InputOption::VALUE_REQUIRED,
                'Wiki Page name to output percentage results eg. --page=i18nStats'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        global $prefs;

        $tikilib = TikiLib::lib('tiki');
        $io = new SymfonyStyle($input, $output);
        $wikiPage = $input->getOption('page') ?: null;

        if (! empty($wikiPage) && ! $tikilib->page_exists($wikiPage)) {
            $io->error(sprintf('%s doesn\'t exist', $wikiPage));
            retur