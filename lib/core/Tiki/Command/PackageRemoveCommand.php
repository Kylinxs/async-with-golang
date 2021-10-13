<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tiki\Package\ComposerManager;
use Tiki\Package\PackageCommandHelper;
use TikiLib;

class PackageRemoveCommand extends Command
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('package:remove')
            ->setDescription('Remove package')
            ->setHelp('This command allows you to remove packages.')
            ->addArgument(
                'package',
                InputArgument::OPTIONAL,
                'Package ID'
            );
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        global $tikipath;
        $composerManager = new ComposerManager($tikipath);

        $logslib = TikiLib::lib('logs');

        if ($composerManager->composerIsAvailable()) {
            $installedComposerPackages = $composerManager->getInstalled();

            if ($installedComposerPackages === false) {
                $output->writeln('<comment>' . tr('No packages found in composer.json in the root of the project.') . '</comment>');
            } else {
                $removablePackages = PackageCommandHelper::getRemovablePackages($installedComposerPackages);
                if (empty($installedComposerPackages) || empty($removablePackages)) {
                    $output->writeln('<comment>' . tr('No packages available to be removed.') . '</comment>');
                } else {
                    $io = new SymfonyStyle($input, $output);
                    $packageKey = $input->getArgument('package');
                    if (isset($packageKey) && ! empty($packageKey)) {
                        if (in_array($packageKey, array_column($removablePackages, 'key'))) {
                            $output->writeln('<info>' . tr('Removing package: ') . $packageKey . '</info>');
                            $result = $composerManager->removePackage($packageKey);
                            $io->newLine();
                            $output->writeln($result);
                        } else {
                            $output->writeln('<error>' . tr('Invalid Package: ') . $packageKey . '</error>');
                            return;
                        }
                    } else {
                        $io->newLine();

                        $removablePackagesInfo = PackageCommandHelper::getInstalledPackagesInfo($removablePackages);

                        $output->writeln(tr('Packages that can be removed'));
                        PackageCommandHelper::renderInstalledPackagesTable($output, $removablePackagesInfo);
                        PackageCommandHelper::listDeprecatedAndReplacedPackages($output, $removablePackages);

                        $io->newLine();

                        $helper = $this->getHelper('question');
                        $question = PackageCommandHelper::getQuestion('Which package do you want to remove', null, '?');
                        $question->setValidator(function ($answer) use ($removablePackages) {
                            return PackageCommandHelper::validatePackageSelection($answer, $removablePackages);
                        });

                        $packageKey = $helper->ask($input, $output, $question);

                        $output->writeln('<info>' . tr('Removing package: ') . $packageKey . '</info>');
                        $result = $composerManager->removePackage($packageKey);
                        $io->newLine();
                        $output->writeln($result);
                        $logslib->add_action('package remove', 'system', 'system', $packageKey . ' package removed.');
                    }
                }
            }
        } else {
            $output->writeln('<error>' . tr('Composer could not be executed.') . '</error>');
            return;
        }
    }
}
