<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

// Warning: this script does not check the required and available PHP versions
// before doing an update. That might result in a broken Tiki installation.

namespace Tiki\Command;

use Tiki\Lib\Logs\LogsLib;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Command\HelpCommand;
use Exception;
use Tiki\Package\ComposerCli;

/**
 * Add a singleton command using the Symfony console component for this script
 *
 * @package Tiki\Command
 */

class VCSUpdateCommand extends Command
{
    /**
     * @var ConsoleLogger
     */
    protected $logger;

    protected function configure()
    {
        $this
            ->setName('vcs:update')
            ->setDescription('Update Tiki to latest version & perform tasks for a smooth update.')
            ->setHelp('Updates Tiki repository to latest version and performs necessary tasks in Tiki for a smooth update. Suitable for both development and production.')
            ->addOption(
                'no-secdb',
                's',
                InputOption::VALUE_NONE,
                'Skip updating the secdb database.'
            )
            ->addOption(
                'no-reindex',
                'r',
                InputOption::VALUE_NONE,
                'Skip re-indexing Tiki.'
            )
            ->addOption(
                'no-db',
                'd',
                InputOption::VALUE_NONE,
                'Make no changes to the database. (Dependencies and privilege checks only. Logging disabled.)'
            )
            ->addOption(
                'no-generate',
                'G',
                InputOption::VALUE_NONE,
                "Don't re-generate the caches. Can take a long time on a large site."
            )
            ->addOption(
                'conflict',
                'c',
                InputOption::VALUE_REQUIRED,
                'What would you like to do if a vcs conflict is found? SVN Options:abort, postpone, mine-conflict, theirs-conflict; Git Options: abort, ours, theirs',
                'abort'
            )
            ->addOption(
                'email',
                'e',
                InputOption::VALUE_REQUIRED,
                'Email address to send a message to if errors are encountered.'
            )
            ->addOption(
                'lag',
                'l',
                InputOption::VALUE_REQUIRED,
                'Time delay commits by X number of days. Useful for avoiding newly introduced bugs in automated updates.'
            )
            ->addOption(
                'user',
                'u',
                InputOption::VALUE_REQUIRED,
                'User account to run setup.sh with (for file permissions setting).'
            )
            ->addOption(
                'group',
                'g',
                InputOption::VALUE_REQUIRED,
                'User group to run setup.sh with (for file permissions setting).'
            )
            ->addOption(
                'no-https',
                null,
                InputOption::VALUE_NONE,
                'Run composer without https.'
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $verbosityLevelMap = [
            LogLevel::CRITICAL   => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::ERROR      => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::NOTICE     => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO       => OutputInterface::VERBOSITY_VERY_VERBOSE
        ];

        $this->logger = new ConsoleLogger($output, $verbosityLevelMap);
    }

    /**
     *
     * Determines if errors exist and outputs error messages.
     *
     * @param ConsoleLogger $logger
     * @param string $return            Info to print, in a level of elevated verbosity
     * @param string $errorMessage      Error message to log-display upon failure
     * @param array  $errors            Error messages to check for, sending a '' will produce an error if no output is
     *                                                  produced, handy as an extra check when output is expected.
     * @param bool  $log                If errors should be logged.
     */
    public function OutputErrors(ConsoleLogger $logger, $return, $errorMessage = '', $errors = [], $log = true)
    {
        $logger->info($return);

        // check for errors.
        foreach ($errors as $error) {
            if (($error === '' && ! $return) || ($error && strpos($return, $error))) {
                $logger->error($errorMessage);
                if ($log) {
                    $logs = new LogsLib();
                    $logs->add_action('VCS update', $errorMessage, 'system');
                }
            }
        }
    }

    /**
     * Calls database update command and handles verbiage.
     *
     * @param OutputInterface $output
     *
     * @throws Exception
     */

    protected function dbUpdate(OutputInterface $output)
    {
        $console = new Application();
        $console->add(new UpdateCommand());
        $console->setAutoExit(false);
        $console->setDefaultCommand('database:update');
        $input = null;
        if ($output->getVerbosity() <= OutputInterface::VERBOSITY_VERBOSE) {
            $input = new ArrayInput(['-q' => null]);
        } elseif ($output->getVerbosity() == OutputInterface::VERBOSITY_DEBUG) {
            $input = new ArrayInput(['-vvv' => null]);
        }
        $console->run($input);
    }

    /**
     * Get SVN revision
     *
     * @param OutputInterface $output
     * @return String
     */
    protected function getSvnRevision(OutputInterface $output)
    {
        $raw = $this->execCommand('svn info 2>&1');
        preg_match('/Revision: (\d+)/', $raw, $revision);
        if ($revision) {
            $revision = $revision[1];
        } else {
            $revision = ' unknown';
        }

        return $revision;
    }

    protected function getGitFollowUpBranch()
    {
        $raw = $this->execCommand('git rev-parse --abbrev-ref @{upstream}');

        $upstreamBranch = trim($raw);

        return $upstreamBranch;
    }

    /**
     * Get GIT revision
     *
     * @param string $branch
     * @param int $before A timestamp value
     * @return String
     */
    protected function getGitRevision(string $branch = null, int $before = 0)
    {
        $command = 'git log -n 1 --pretty=format:"%H"';

        if ($before) {
            $date = date('Y-m-d H:i', $before);
            $command .= ' --before=' . escapeshellarg($date);
        }

        if ($branch) {
            $command .= ' ' . $branch;
        }

        $command .= ' 2>&1';

        $hash = $this->execCommand($command);

        return ! empty($hash) ? trim($hash) : null;
    }

    /**
     * @param $rev
     * @param $svnConflict
     * @return string
     */
    protected function svnUpdate($rev, $svnConflict)
    {
        return $this->execCommand("svn update --revision $rev --accept $svnConflict 2>&1");
    }

    /**
     * @param string $commitHash
     * @param string $conflict Merge strategy
     * @param bool $commit
     * @return null;
     */
    protected function gitUpdate(string $commitHash = '', string $conflict = 'abort', $commit = true)
    {
        $this->gitFetch();

        if ($commitHash == 'HEAD') {
            $commitHash = $this->getGitFollowUpBranch();
        }

        // Git merge is better than pull, which allows to specify a commit hash (useful on lag)
        $command = 'git merge';
        $command .= ($conflict !== 'abort') ? ' -X ' . $conflict : '';
        $command .= ! $commit ? ' --no-commit --no-ff' : '';
        $command .= $commitHash ? ' ' . $commitHash : '';
        $command .= ' 2>&1';

        return $this->execCommand($command);
    }

    /**
     * @return null;
     */
    protected function gitFetch()
    {
        $command = 'git fetch';
        $command .= ' 2>&1';

        return $this->execCommand($command);
    }

    /**
     * Execute revert composer http mode
     *
     * @return null
     */
    protected function revertComposerHttp()
    {
        global $tikipath;
        $httpModeFile = $tikipath . 'doc/devtools/composer_http_mode.php';
        if (file_exists($httpModeFile)) {
            $this->execCommand("php $httpModeFile revert 2>&1");
        }
    }

    /**
     * Execute composer http mode
     *
     * @return null
     */
    protected function executeComposerHttp()
    {
        global $tikipath;
        $httpModeFile = $tikipath . 'doc/devtools/composer_http_mode.php';
        if (file_exists($httpModeFile)) {
            $this->execCommand("php $httpModeFile execute 2>&1");
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->logger;
        $errors = false;
        $isSvn = false;
        $isGit = false;
        $rev = 'HEAD';
        $email = $input->getOption('email');
        $conflict = $input->getOption('conflict');
        $noDb = $input->getOption('no-db');
        $lag = $input->getOption('lag');
        $noHttps = $input->getOption('no-https');

        if (is_dir('.git')) {
            $isGit = true;
        }
        if (is_dir('.svn')) {
            $isSvn = true;
        }

        if (! $isSvn && ! $isGit) {
            $logger->critical('Only SVN and GIT are supported at the moment.');
            die();
        }

        if ($isSvn && ! in_array($conflict, ['abort', 'postpone', 'mine-conflict', 'theirs-conflict'])) {
            $help = new HelpCommand();
            $help->setCommand($this);
            $help->run($input, $output);
            $logger->notice('Invalid option for --conflict, see usage above.');
            return;
        }

        if ($isGit && ! in_array($conflict, ['abort', 'ours', 'theirs'])) {
            $help = new HelpCommand();
            $help->setCommand($this);
            $help->run($input, $output);
            $logger->notice('Invalid option for --strategy-option, see usage above.');
            return;
        }

        // check that the --lag option is valid, and complain if its not.
        if ($lag) {
            if ($lag < 0 || ! is_numeric($lag)) {
                $help = new HelpCommand();
                $help->setCommand($this);
                $help->run($input, $output);
                $logger->notice('Invalid option for --lag, must be a positive integer.');
                return;
            }

            // current time minus number of days specified through lag
            $timestamp = time() - $lag * 60 * 60 * 24;
            $rev = date('{"Y-m-d H:i"}', $timestamp);

            if ($isGit) {
                $upstreamBranch = $this->getGitFollowUpBranch();
                $rev = $this->getGitRevision($upstreamBranch, $timestamp);

                if (! $rev) {
                    $logger->error('Failed to determine the commit hash to checkout before ' . date('Y-m-d H:i', $timestamp));
                    return 1;
                }
            }
        }

        // if were using a db, then configure it.
        if (! DB_STATUS && ! $noDb) {
            $input->setOption('no-db', true);
        }

        // if were using a db, then configure it.
        if (! $noDb) {
            $logslib = new LogsLib();
        }

        $action = 'VCS update';
        $prefix = $isSvn ? 'r' : '';

        // die gracefully if shell_exec is not enabled;
        if (! is_callable('shell_exec')) {
            if (! $noDb) {
                $logslib->add_action($action, 'Automatic update failed. Could not execute shell_exec()', 'system');
            }
            $logger->critical('Automatic update failed. Could not execute shell_exec()');
            die();
        }

        /** @var int The number of steps the progress bar will show */
        $max = 8;
        // now subtract steps depending on options elected
        if ($noDb) {
            $max -= 5;
        } else {
            if ($input->getOption('no-secdb')) {
                $max--;
            }
            if ($input->getOption('no-reindex')) {
                $max--;
            }
        }

        global $tikipath;
        $httpModeFile = $tikipath . 'doc/devtools/composer_http_mode.php';
        if ($noHttps && ! file_exists($httpModeFile)) {
            $logger->error('composer_http_mode.php file not found.');
            return 1;
        }

        $progress = new ProgressBar($output, $max);
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $progress->setOverwrite(false);
        }
        $progress::setFormatDefinition('custom', ' %current%/%max% [%bar%] -- %message%');
        $progress->setFormat('custom');


        $progress->setMessage('Pre-update checks');
        $progress->start();

        if ($isGit) {
            $startRev = $this->getGitRevision();
        } else {
            $startRev = $this->getSvnRevision($output);
        }

        if ($noHttps) {
            $this->revertComposerHttp();
        }

        // Set this before, so if 'abort' is used, it can be changed to a valid option later
        // start svn conflict checks
        if ($isSvn && $conflict === 'abort') {
            $raw = $this->execCommand("svn merge --dry-run -r BASE:$rev . 2>&1");

            if (strpos($raw, 'E155035:')) {
                $progress->setMessage('Working copy currently conflicted. Update Aborted.');
                if ($email) {
                    mail($email, 'Svn Up Aborted', wordwrap('Working copy currency conflicted. Update Aborted. ' . __FILE__, 70, "\r\n"));
                }
                if (! $noDb) {
                    $logslib->add_action($action, "Working copy currency conflicted. Update Aborted. r$startRev", 'system');
                }
                if ($noHttps) {
                    // Revert composer https changes
                    $this->executeComposerHttp();
                }

                $progress->advance();
                die("\n");
            }

            //  Check if working from from mixed revision, this happens when a commit is made and causes merges to fail.
            if (strpos($raw, 'E195020:')) {
                $progress->setMessage('Updating mixed revision working copy to single reversion');
                preg_match('/\[\d*:(\d*)]/', $raw, $mixedRev);
                $mixedRev = $mixedRev[1];

                // Now that we know the upper revision number, svn up to it.
                $errors = ['', 'Text conflicts'];
                $raw = $this->execCommand('svn update --accept postpone --revision ' . $mixedRev . ' 2>&1');
                $this->OutputErrors($logger, $raw, 'Problem with svn up, check for conflicts.', $errors, ! $noDb);
                if ($logger->hasErrored()) {
                    $progress->setMessage('Preexisting local conflicts exist. Update Aborted.');
                    if ($email) {
                        echo mail($email, 'Svn Up Aborted', wordwrap('Preexisting local conflicts exist. Update Aborted. ' . __FILE__, 70, "\r\n"));
                    }
                    if (! $noDb) {
                        $logslib->add_action($action, "Preexisting local conflicts exist. Update Aborted. r$startRev", 'system');
                    }
                    if ($noHttps) {
                        // Revert composer https changes
                        $this->executeComposerHttp();
                    }
                    $progress->advance();
                    die("\n"); // If custom mixed revision merges were made with local changes, this could happen.... (very unlikely)
                }
                // now re-check for conflicts
                $raw = $this->execCommand("svn merge --dry-run -r BASE:$rev .  2>&1");
            }
            if (strpos($raw, "\nC    ") !== false) {
                $progress->setMessage('Conflicts exist between working copy and repository. Update Aborted.');
                if ($email) {
                    echo mail($email, 'Svn Up Aborted', wordwrap('Conflicts exist between working copy and repository. Update Aborted. ' . __FILE__, 70, "\r\n"));
                }
                if (! $noDb) {
                    $logslib->add_action($action, "Conflicts exist between working copy and repository. Update Aborted. r$startRev", 'system');
                }
                if ($noHttps) {
                    // Revert composer https changes
                    $this->executeComposerHttp();
                }
                $progress->advance();
                die("\n");
            }
            // we need a valid option, even though it wil never be used.
            $conflict = 'postpone';
        }

        if ($isGit && $conflict === 'abort') {
            // Git does not support dry-run
            $raw = $this->gitUpdate($rev, $conflict, false);

            if (preg_match('/(Automatic merge failed|Aborting$|error:|fatal:)/', $raw)) {
                $progress->setMessage('Working copy currently conflicted. Update Aborted.');
                if ($email) {
                    mail($email, 'Git update aborted', wordwrap('Working copy currency conflicted. Update Aborted. ' . __FILE__, 70, "\r\n"));
                }
                if (! $noDb) {
                    $logslib->add_action($action, "Working copy currency conflicted. Update Aborted. $startRev", 'system');
                }
                if ($noHttps) {
                    // Revert composer https changes
                    $this->executeComposerHttp();
                }
                $progress->advance();
                die("\n");
            }

            // Revert merge changes, to keep the repository unchanged
            if (! preg_match('/Already up[- ]to[- ]date/', $raw)) {
                $this->execCommand("git merge --abort 2>&1");
            }
        }

        $update = $isGit ? 'GIT' : 'SVN';
        $progress->setMessage('Updating ' . $update);
        $progress->advance();

        if ($isGit) {
            $errors = ['','Automatic merge failed'];
            $commitHash = $rev ?: '';
            $gitUpdate = $this->gitUpdate($rev, $conflict);
            $this->OutputErrors($logger, $gitUpdate, 'Problem with git merge, check for conflicts.', $errors, ! $noDb);
            if ($logger->hasErrored()) {
                return 2;
            }
            $endRev = $this->getGitRevision();
            $this->execCommand('git gc 2>&1');
        } else {
            $errors = ['','Text conflicts'];
            $svnUpdate = $this->svnUpdate($rev, $conflict);
            $this->OutputErrors($logger, $svnUpdate, 'Problem with svn up, check for conflicts.', $errors, ! $noDb);
            $endRev = $this->getSvnRevision($output);
            $raw = $this->execCommand('svn cleanup  2>&1');
        }

        if (! $noDb) {
            $cacheLib = new \Cachelib();
            $progress->setMessage('Clearing all caches');
            $progress->advance();
            $cacheLib->empty_cache();
        }

        $progress->setMessage('Updating dependencies & setting file permissions');
        $progress->advance();
        $errors = ['', 'Please provide an existing command', 'you are behind a proxy', 'Composer failed', 'Wrong PHP version'];

        $setupParams = '';
        if ($input->getOption('user')) {
            $setupParams .= ' -u ' . $input->getOption('user');
        }
        if ($input->getOption('group')) {
            $setupParams .= ' -g ' . $input->getOption('group');
        }

        $composerHome = '';
        if (! getenv('COMPOSER_HOME')) {
            $composerHome = sprintf('COMPOSER_HOME="%s"', $tikipath . ComposerCli::COMPOSER_HOME);
        }

        if ($noHttps) {
            $this->executeComposerHttp();
        }

        $shellCom = sprintf("%s sh setup.sh %s -n fix", $composerHome, $setupParams);
        $raw = $this->execCommand($shellCom . ' 2>&1');
        $this->OutputErrors($logger, $raw, 'Problem running setup.sh', $errors, ! $input->getOption('no-db'));

        if (! $noDb) {
            // generate a secdb database so when database:update is run, it also gets updated.
            if (! $input->getOption('no-secdb')) {
                $progress->setMessage('Updating secdb');
                $progress->advance();

                $errors = ['is not writable', ''];
                $command = 'php doc/devtools/release.php --only-secdb --no-check-vcs';
                $command .= $isGit ? ' --use-git' : '';
                $raw = $this->execCommand($command);
                $this->OutputErrors($logger, $raw, 'Problem updating secdb', $errors);
            }

            // note: running database update also clears the cache
            $progress->setMessage('Updating database');
            $progress->advance();
            try {
                $this->dbUpdate($output);
            } catch (\Exception $e) {
                $logger->error('Database update error: ' . $e->getMessage());
                $logslib->add_action($action, 'Database update error: ' . $e, 'system');
            }


            // rebuild tiki index. Since this could take a while, make it optional.
            if (! $input->getOption('no-reindex')) {
                $progress->setMessage('Rebuilding search index');
                $progress->advance();
                $errors = ['', 'Fatal error'];
                $shellCom = 'php console.php index:rebuild';
                if ($output->getVerbosity() == OutputInterface::VERBOSITY_DEBUG) {
                    $shellCom .= ' -vvv';
                }

                putenv('SHELL_VERBOSITY'); // Clear the environment variable, since console.php (Symfony console application) will pick this value if set
                $raw = $this->execCommand($shellCom . ' 2>&1');
                $this->OutputErrors($logger, $raw, 'Problem Rebuilding Index', $errors, ! $noDb);   // 2>&1 suppresses all terminal output, but allows full capturing for logs & verbiage
            }

            /* generate caches */
            if (! $input->getOption('no-generate')) {
                $progress->setMessage('Generating caches');
                $progress->advance();
                try {
                    //$cacheLib->generateCache();    disable generating module cache until regression if fixed that causes premature termination.
                    $cacheLib->generateCache(['templates', 'misc']);
                } catch (\Exception $e) {
                    $logger->error('Cache generating error: ' . $e->getMessage());
                    $logslib->add_action($action, 'Cache generating error: ' . $e, 'system');
                }
            }
        }

        if ($logger->hasErrored()) {
            if (! $noDb) {
                $logslib->add_action($action, "Automatic update completed with errors, " . $prefix . $startRev . " -> " . $prefix . $endRev . ", Try again or debug.", 'system');
            }
            if ($email) {
                echo mail($email, $action . ' Aborted', wordwrap("Automatic update completed with errors, " . $prefix . $startRev . " -> " . $prefix . $endRev . ", Try again or debug." . __FILE__, 70, "\r\n"));
            }
            $progress->setMessage("Automatic update completed with errors, " . $prefix . $startRev . " -> " . $prefix . $endRev . ", Try again or ensure update functioning.");
        } elseif ($noDb) {
            $progress->setMessage("<comment>Automatic update completed in no-db mode, " . $prefix . $startRev . " -> " . $prefix . $endRev . ", Database not updated.</comment>");
        } else {
            $logslib->add_action($action, "Automatic update completed, " . $prefix . $startRev . " -> " . $prefix . $endRev, 'system');
            $progress->setMessage("<comment>Automatic update completed " . $prefix . $startRev . " -> " . $prefix . $endRev . "</comment>");
        }

        $progress->finish();
        if ($output->getVerbosity() > OutputInterface::VERBOSITY_QUIET) {
            $output->writeln('');
        }
    }

    /**
     * @param string command
     * @return string|null
     */
    protected function execCommand(string $command): string
    {
        $this->logger->debug('Command: ' . $command);
        $output = shell_exec($command);
        $output = trim($output);
        $this->logger->debug('Output: ' . $output);

        return $output;
    }
}
