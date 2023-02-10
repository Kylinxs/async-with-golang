<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tiki\Lib\Logs\LogsLib;
use Tiki\Package\ComposerManager;
use Tiki\Package\ComposerPackage;
use Tiki\Package\PackageCommandHelper;
use TikiLib;

class PackageUpdateCommand extends Command
{
    private $output;

    /* @var SymfonyStyle */
    protected $io;

    /** @var ComposerManager */
    protected $composerManager;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('package:update')
            ->setDescription('Update package')
            ->setHelp('This command allows you to update packages.')
            ->addArgument(
                'package',
                InputArgument::OPTIONAL,
                'Package ID'
            )
            ->addOption(
                'all',
                'a',
                InputOption::VALUE_NONE,
                tr('Update all packages')
            )
            ->addOption(
                'handle-deprecated',
                null,
                InputOption::VALUE_NONE,
                tr('Handle deprecated packages')
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
        $this->io = new SymfonyStyle($input, $output);
        $this->output = $output;
        $this->composerManager = new ComposerManager($tikipath);

        /** @var LogsLib $logslib */
        $logslib = TikiLib::lib('logs');

        if (! $this->composerManager->composerIsAvailable()) {
            $output->writeln(
                '<error>' . tr('Composer could not be executed.') . '</error>'
            );
            return 1;
        }

        $installedPackages = $this->composerManager->getInstalled();
        if ($installedPackages === false) {
            $output->writeln(
                '<comment>' .
                tr('No packages found in composer.json in the root of the project.') .
                '</comment>'
            );
            return;
        }

        $handleDeprecated = $input->getOption('handle-deprecated');
        if ($handleDeprecated) {
            $this->handleDeprecatedPackages();
        }

        $updatablePackages = PackageCommandHelper::getUpdatablePackages($installedPackages);

        if (empty($updatablePackages)) {
            $output->writeln('<comment>' . tr('No packages available to be updated.') . '</comment>');
            return;
        }

        $packagesToUpdate = [];
        $all = $input->getOption('all');
        $packageKey = $input->getArgument('package');

        if (
            ! empty($packageKey)
            && ! in_array($packageKey, array_column($updatablePackages, 'key'))
        ) {
            $output->writeln('<error>' . tr('Package `%0` not available for update.', $packageKey) . '</error>');
            return;
        }

        if ($all) {
            $packagesToUpdate = array_map(
                function ($package) {
                    return $package['key'];
                },
                $updatablePackages
            );
        } elseif ($packageKey) {
            $packagesToUpdate[] = $packageKey;
        } else {
            $packagesToUpdate = $this->promptPackageUpdate($updatablePackages);
        }

        foreach ($packagesToUpdate as $package) {
            $this->updatePackage($package);
        }
        $logslib->add_action('package update', 'system', 'system', count($packagesToUpdate) . ' packages updated.');
    }

    protected function promptPackageUpdate($packages)
    {
        $packagesInfo = PackageCommandHelper::getInstalledPackagesInfo(
            $packages
        );

        $this->io->writeln(tr('Packages that can be updated'));
        PackageCommandHelper::renderInstalledPackagesTable(
            $this->output,
            $packagesInfo
        );
        PackageCommandHelper::listDeprecatedAndReplacedPackages($this->output, $packages);
        $validator = function ($answer) use ($packages) {
            return PackageCommandHelper::validatePackageSelection(
                $answer,
                $packages
            );
        };
        $packagesToUpdate[] = $this->io->ask(
            'Which package do you want to update',
            null,
            $validator
        );

        return $packagesToUpdate;
    }

    /**
     * @param $package
     */
    protected function updatePackage($package)
    {
        $this->io->writeln(
            '<info>' . tr('Updating package `%0`', $package) . '</info>'
        );

        $result = $this->composerManager->updatePackage($package);

        $error = ! preg_match('/composer\.json has been updated/', $result);

        if ($error || $this->io->isVerbose()) {
            $this->io->newLine();
            $this->io->writeln($result);
        }

        $message = '<info>' . tr('Package `%0` was updated', $package) . '</info>';

        if ($error) {
            $message = '<error>' . tr('Failed to update package `%0`', $package) . '</error>';
        }

        $this->io->writeln($message);
    }

    /**
     * Replaces packages with their replacements or removes if deprecated
     */
    protected function handleDeprecatedPackages()
    {
        $availablePackages = [];
        foreach ($this->composerManager->getAvailable(false) as $package) {
            $packageName = $package['name'];
            $availablePackages[$packageName] = $package;
        }

        $installedPackages = $this->composerManager->getInstalled();

        $composer = $this->composerManager->getComposer();
        $composerJson = $composer->getComposerConfigOrDefault();

        foreach ($installedPackages as $package) {
            $packageState = $package['state'] ?? ComposerPackage::STATE_ACTIVE;

            if ($packageState == ComposerPackage::STATE_ACTIVE) {
                continue;
            }

            if ($packageState == ComposerPackage::STATE_REPLACED) {
                $replacePackages = $package['replacedBy'] ?? [];
                array_walk($replacePackages, function ($replacePackageName) use (&$composerJson, $composer, $availablePackages) {

                    $package = $availablePackages[$replacePackageName] ?? null;

                    if (! $package) {
                        return;
                    }

                    $composerJson = $composer->addComposerPackageToJson(
                        $composerJson,
                        $package['name'],
                        $package['requiredVersion'],
                        $package['scripts']
                    );
                });
            }

            unset($composerJson['require'][$package['name']]);
        }

        $fileContent = json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($composer->getComposerConfigFilePath(), $fileContent);

        $this->output->writeln($this->composerManager->fixMissing());
    }
}
