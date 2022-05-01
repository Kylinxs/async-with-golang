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
            return 1;
        }

        try {
            $command = $this->getApplication()->find('translation:getstrings');
            $commandInput = new ArrayInput(['command' => 'translation:getstrings']);
            $command->run($commandInput, $output);
        } catch (Exception $e) {
            $io->error($e->getMessage());
            return 1;
        }

        require_once('lang/langmapping.php');
        require_once('lib/language/File.php');

        $outputData = [];
        $globalStats = [];
        $globalStats['70+'] = 0;
        $globalStats['30+'] = 0;
        $globalStats['0+'] = 0;
        foreach ($langmapping as $lang => $null) {
            $filePath = "lang/$lang/language.php";
            if (file_exists($filePath) && $lang != 'en') {
                $parseFile = new \Language_File($filePath);
                $stats = $parseFile->getStats();

                $outputData[$lang] = [
                    'total' => $stats['total'],
                    'untranslated' => $stats['untranslated'],
                    'translated' => $stats['translated'],
                    'percentage' => $stats['percentage'],
                ];

                if ($stats['percentage'] >= 70) {
                    $globalStats['70+']++;
                } elseif ($stats['percentage'] >= 30) {
                    $globalStats['30+']++;
                } elseif ($stats['percentage'] < 30) {
                    $globalStats['0+']++;
                }
            }
        }

        if (! isset($wikiPage)) {
            $output = "! Status of Tiki translations\n";
            $output .= "Page last modified on " . $tikilib->date_format($prefs['long_date_format']) . "\n\n";
            $output .= "This page is generated automatically. Please do not change it.\n\n";
            $output .= "The total number of strings is different for each language due to unused translations present in the language.php files.\n\n";
            $output .= "__Global stats:__\n* {$globalStats['70+']} languages with more than 70% translated\n* {$globalStats['30+']} languages with more than 30% translated\n* {$globalStats['0+']} languages with less than 30% translated\n\n";
        } else {
            $output = "{HTML()}  <h1 class='text-center text-info'> {HTML}{TR()}Status of Tiki translations{TR}{HTML()}</h1> {HTML}";
            $output .= "{HTML()} <p class='text-center text-info'>{HTML}{TR()}Page last modified on " . $tikilib->date_format($prefs['long_date_format']) . " {TR}{HTML()}</p><br/> {HTML}";
            $output .= "{HTML()} <p class='text-danger'>{HTML}{TR()}This page is generated automatically. Please do not change it. {TR}{HTML()}</p> {HTML}";
            $output .= "{HTML()} <p class='text-info'>{HTML}{TR()}The total number of strings is different for each language due to unused translations present in the language.php files. {TR}{HTML()}</p> {HTML}";
            $output .= " {HTML()} <h3 class='text-capitalize text-info'>{HTML}{TR()}Global stats : {TR}{HTML()}</h3> {HTML}";
            $output .= " {HTML()} <ul class='list-group col-6 mb-2'>
                     <li class='list-group-item'><span class='text-success'>{$globalStats['70+']}</span>  {HTML}{TR()} languages with more than {TR}{HTML()}<span class='text-success'> 70%</span> {HTML}{TR()} translated{TR}{HTML()}</li>
                     <li class='list-group-item'><span class='text-success'>{$globalStats['30+']} </span> {HTML}{TR()} languages with more than {TR}{HTML()}<span class='text-success'> 30%</span> {HTML}{TR()} translated{TR}{HTML()}</li>
                     <li class='list-group-item'><span class='text-success'>{$globalStats['0+']} </span> {HTML}{TR()} languages with less than {TR}{HTML()}<span class='text-success'> 30%</span>  {HTML}{TR()}translated{TR}{HTML()}</li>
                </ul>{HTML}";
        }

        $output .= "{FANCYTABLE(head=\"Language code (ISO)|English name|Native Name|Completion|Percentage|Number of strings\" sortable=\"y\")}\n";
        foreach ($outputData as $lang => $data) {
            $output .= "$lang | {$langmapping[$lang][1]} | {$langmapping[$lang][0]} | {Gauge value=\"{$data['percentage']}\" max=\"100\" size=\"200\" color=\"#00C851\" bgcolor=\"#eceff1\" height=\"20\" perc=\"true\" showvalue=\"false\"} | ";
            $output .= "{$data['percentage']}% | Total: {$data['total']} %%% Translated: {$data['translated']} %%% Untranslated: {$data['untranslated']} \n";
        }
        $output .= '{FANCYTABLE}';

        if (isset($wikiPage)) {
            $tikilib->update_page($wikiPage, $output, 'Updating translation stats', 'i18nbot', '127.0.0.1');
        } else {
            echo $output;
        }
    }
}
