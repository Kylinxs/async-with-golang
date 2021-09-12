<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\HelpCommand;
use Language;
use Language_FileType_Php;

/**
 * Add a singleton command "englishupdate" using the Symfony console component for this script
 *
 * Class EnglishUpdateCommand
 * @package Tiki\Command
 */

class EnglishUpdateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('translation:englishupdate')
            ->setDescription('Fix English strings after modifying them.')
            ->setHelp('Update translation files with updates made to English strings. Will compare working copy by default.')
            ->addOption(
                'scm',
                null,
                InputOption::VALUE_REQUIRED,
                'Source code management type: svn or git'
            )
            ->addOption(
                'revision',
                'r',
                InputOption::VALUE_REQUIRED,
                'Revision numbers may be selected eg. 63000:63010, or simply 63000 to update strings from 63000 onward.'
            )
            ->addOption(
                'lag',
                'l',
                InputOption::VALUE_REQUIRED,
                'Search through previous commits by X number of days, for updated translation strings. Working copy will be ignored.'
            )
            ->addOption(
                'audit',
                'a',
                InputOption::VALUE_NONE,
                'Reports any translation strings that have been broken. Will not change repository. '
            )
            ->addOption(
                'email',
                'e',
                InputOption::VALUE_REQUIRED,
                'Email address to send a message to if untranslated strings are found. Must be used in conjunction with "audit".'
            )
            ->addOption(
                'diff-command',
                null,
                InputOption::VALUE_REQUIRED,
                'Set a shell command to return the diff (ex. in case of a based git repository) override svn default diff. Options revision and lag will be ignored.'
            )
            ->addOption(
                'git',
                null,
                InputOption::VALUE_NONE,
                'Set thi if diff-command is based on git'
            );
    }

    /**
     * The total number of changed strings
     * @var int
     */
    private $stringCount = 0;


    /**
     * The number of identical original & changed pairs found.
     * @var int
     */
    private $duplicates = 0;

    /**
     * An array of all the language directories in Tiki
     * @var array
     */
    private $languages;
    /**
     *
     * Seperates svn diff output into changes made in PHP and TPL files
     *
     * @param $content string raw svn diff output
     * @param string $diff git or svn depending on the version control used to generate the diff.
     *
     * @return array with [0] containing PHP and [1] containing TPL strings
     */

    /**
     * Run svn diff command
     * @param array $revisions revisions to use in diff
     * @param int $lag number of days to search previously
     * @return mixed diff result
     */
    private function getSvnDiff($revisions, $lag = 0)
    {
        $rev = '';
        if ($lag > 0) {
            // current time minus number of days specified through lag
            $rev = date('{"Y-m-d H:i"}', time() - $lag * 60 * 60 * 24);
            $rev = '-r ' . $rev;
        } elseif ($revisions) {
            $rev = '-r ' . implode(":", $revisions);
        }

        $raw = shell_exec("svn diff $rev 2>&1");

        // strip any empty translation strings now to avoid complexities later
        $raw = preg_replace('/tra?\(["\'](\s*?)[\'"]\)/m', '', $raw);

//      $output->writeln($raw, OutputInterface::VERBOSITY_DEBUG);

        return $this->separatePhpTpl($raw);
    }

    /**
     * Run git diff command
     * @param array $revisions revisions to use in diff
     * @param int $lag number of days to search previously
     * @return mixed diff result
     */
    private function getGitDiff($revisions, $lag = 0)
    {
        if ($lag > 0) {
            // current time minus number of days specified through lag
            $rev = 'HEAD \'HEAD@{' . $lag . ' weeks ago}\'';
        } else {
            $rev = implode(' ', $revisions);
        }

        $raw = shell_exec("git diff $rev 2>&1");


        // strip any empty translation strings now to avoid complexities later
        $raw = preg_replace('/tra?\(["\'](\s*?)[\'"]\)/m', '', $raw);

//      $output->writeln($raw, OutputInterface::VERBOSITY_DEBUG);

        return $this->separatePhpTpl($raw, 'git');
    }


    private function separatePhpTpl($content, $diff = 'svn')
    {

        if ($diff === 'git') {
            preg_match_all('/^diff --git .+(php|tpl)$\nindex .+\n([\w\W]+?)(?=\n^diff --git.+\n|\n$)/m', $content, $phpTpl);
        } else {
            $content .= "\nIndex:  \n=";                            // used as a dummy to match the last entry

            // Separate php and tpl content
            preg_match_all('/^Index:\s.+(php|tpl)$\n={10}([\w\W]+?)(?=^Index:.+\n=)/m', $content, $phpTpl);
        }


        $changes['php'] = '';
        $changes['tpl'] = '';
        $count = 0;
        while ($count < count($phpTpl[1])) {
            if ($phpTpl[1][$count] === 'php') {
                $changes['php'] .= $phpTpl[2][$count];
            } elseif ($phpTpl[1][$count] === 'tpl') {
                $changes['tpl'] .= $phpTpl[2][$count];
            }
            $count++;
        }

        return $changes;
    }

    /**
     * @param $content string diff content to split into pairs of removed and added content
     *
     * @return array equal pairs of added and removed diff content
     */

    private function pairMatches($content)
    {

        /**
         * @var $pairedMatches array any changes that took away and added lines.
         */

        // strip some diff verbiage to prevent conflict in next match
        $content = preg_replace('/(?>---|\+\+\+)\s.*\)$/m', '', $content);
        // place in an array changes that have multiple lines changes
        preg_match_all('/(\n[-+].*){2,}/m', $content, $diffs);

        $content = $diffs[0];
        unset($diffs);

        $pairs = [];
        foreach ($content as $diff) {
            //now trim it down so its a - then + pair
            if (preg_match('/^-[\s\S]*^\+.*/m', $diff, $pair)) {
                // now extract a equally paired sets
                $count = min(preg_match_all('/^-/m', $pair[0]), preg_match_all('/^\+/m', $pair[0]));
                if ($count) {
                    preg_match('/(?>\n-.*){' . $count . '}(?>\n\+.*){' . $count . '}/', "\n" . $pair[0], $equilPair);
                    $pairs[] = $equilPair[0];
                }
            }
        }

        unset($content);
        $count = 0;
        $pairedMatches = [];

        foreach ($pairs as $pair) {
            if (preg_match_all('/^-(.*)/m', $pair, $negativeMatch)) {
                if (preg_match_all('/^\+(.*)/m', $pair, $positiveMatch)) {
                    $pairedMatches[$count]['-'] = implode(' ', $negativeMatch[1]);
                    $pairedMatches[$count]['+'] = implode(' ', $positiveMatch[1]);
                    $count++;
                }
            }
        }

        return $pairedMatches;
    }

    /**
     * Takes a semi-prepared list of commit changes (from a diff) and extracts pairs of original and changed translatoion strings
     *
     * @param $content array of equally paired diff content pairs of removed and added, previously precessed by pairMatches()
     * @param $file string can be 'php' or 'tpl'. Will determine how strings are extracted.
     *
     * @return array extracted strings
     */
    private function pairStrings($content, $file)
    {

        $count = 0;
        $pairedStrings = [];

        // set what regex to use depending on file type.
        if ($file === 'php') {
            $regex = '/\Wtra?\s*\(\s*([\'"])(.+?)\1\s*[\),]/';
            $php = new Language_FileType_Php();
        } else {
            $regex = '/\{(t)r(?:\s+[^\}]*)?\}(.+?)\{\/tr\}/';
        }

        foreach ($content as $pair) {
            if (preg_match_all($regex, $pair['-'], $negativeMatch)) {
                if (preg_match_all($regex, $pair['+'], $positiveMatch)) {
                    // strip out any changes that have a dissimilar number of translation strings. No way to match them properly :(
                    if (count($negativeMatch[1]) === count($positiveMatch[1])) {
                        // content needs post processing based on single or double quote matches
                        if (isset($negativeMatch[1][0])) {
                            if ($negativeMatch[1][0] == "'") {
                                $negativeMatch[2] = $php->singleQuoted($negativeMatch[2]);
                            } elseif ($negativeMatch[1][0] == '"') {
                                $negativeMatch[2] = $php->doubleQuoted($negativeMatch[2]);
                            }
                            if ($positiveMatch[1][0] == "'") {
                                $positiveMatch[2] = $php->singleQuoted($positiveMatch[2]);
                            } elseif ($positiveMatch[1][0] == '"') {
                                $positiveMatch[2] = $php->doubleQuoted($positiveMatch[2]);
                            }
                        }
                        $pairedStrings[$count]['-'] = $negativeMatch[2];
                        $pairedStrings[$count]['+'] = $positiveMatch[2];
                        $count++;
                    }
                }
            }
        }

        return $pairedStrings;
    }

    /**
     * Filters, formats & escapes paired translation strings to produce a final list of translation changes.
     *
     * @param $content array paired strings previously processed by pairStrings()
     *
     * @return array A final list of before and after translation strings to update.
     */

    private function filterStrings($content)
    {

        $updateStrings = [];
        foreach ($content as $strings) {
            $count = 0;
            while (isset($strings['-'][$count])) {
                // strip any end punctuation from both strings to support tikis punctuations translation functionality.
                if (in_array(substr($strings['-'][$count], -1), Language::PUNCTUATIONS)) {
                    $strings['-'][$count] = substr($strings['-'][$count], 0, -1);
                }
                if (in_array(substr($strings['+'][$count], -1), Language::PUNCTUATIONS)) {
                    $strings['+'][$count] = substr($strings['+'][$count], 0, -1);
                }

                if ($strings['-'][$count] !== $strings['+'][$count]) {
                    $updateStrings[$this->stringCount]['-'] = Language::addPhpSlashes($strings['-'][$count]);
                    $updateStrings[$this->stringCount]['+'] = Language::addPhpSlashes($strings['+'][$count]);
                    $this->stringCount++;
                }
                $count++;
            }
        }

        return $updateStrings;
    }

    /**
     * Takes a paired list of original and replacement strings and checks if they are identical
     *
     * @param $content array paired string, that has previously been processed by filterStrings()
     *
     * @return array return an array of paired strings with duplicate entries omitted
     */

    private function removeIdentical($content)
    {

        $filtered = [];
        foreach ($content as $array) {
            if (! in_array($array, $filtered)) {
                $filtered[] = $array;
            }
        }
        $this->duplicates = $this->stringCount - count($filtered);
        $this->stringCount -= $this->duplicates;

        return $filtered;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('*******************************************************');
        $output->writeln('*                     <info>Limitations</info>                     *');
        $output->writeln('* Will not find strings if they span multiple lines.  *');
        $output->writeln('*                                                     *');
        $output->writeln('* Will not match strings if a translation string has  *');
        $output->writeln('* been added or removed on the line above or below.   *');
        $output->writeln('*******************************************************');
        $output->writeln('');

        // check that email is being used in audit mode
        if ($input->getOption('email') && ! $input->getOption('audit')) {
            $help = new HelpCommand();
            $help->setCommand($this);
            $help->run($input, $output);

            return $output->writeln(' --email, only available when running in --audit mode.');
        }
        // check that scm is being used and validate
        $scm = $input->getOption('scm');
        if (! empty($scm) && ! in_array($scm, ['svn', 'git'])) {
            $help = new HelpCommand();
            $help->setCommand($this);
            $help->run($input, $output);

            return $output->writeln('<error> --scm, invalid value. ex: svn or git. </error>');
        }

        if (empty($scm)) {//detect if is svn or git repo
            if (file_exists(TIKI_PATH . DIRECTORY_SEPARATOR . '.git')) {
                $scm = 'git';
            } elseif (file_exists(TIKI_PATH . DIRECTORY_SEPARATOR . '.svn')) {
                $scm = 'svn';
            } else {
                return $output->writeln('<error>SCM not found in this tiki installation</error>');
            }
        }

        $lag = $input->getOption('lag');
        $revision = $input->getOption('revision');
        $revisions = [];
        // check that the --lag option is valid, and complain if its not.
        if ($lag) {
            if ($input->getOption('lag') < 0 || ! is_numeric($lag)) {
                $help = new HelpCommand();
                $help->setCommand($this);
                $help->run($input, $output);

                return $output->writeln('<error>Invalid option for --lag, must be a positive integer.</error>');
            }
        } elseif ($revision) {
            $revisions = explode(':', $revision);
            if (count($revisions) > 2) {
                return $output->writeln('<error>Invalid amount of revisions</error>');
            }
        }

        $this->languages = glob(TIKI_PATH . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);

        $progress = new ProgressBar($output, count($this->languages) + 7);
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $progress->setOverwrite(false);
        }
        $progress->setFormatDefinition('custom', ' %current%/%max% [%bar%] -- %message%');
        $progress->setFormat('custom');

        $progress->setMessage('Checking System');
        $progress->start();

        // die gracefully if shell_exec is not enabled;
        if (! is_callable('shell_exec')) {
            $progress->setMessage('<error>Translation string update Failed. Could not execute shell_exec()</error>');
            $progress->finish();

            return false;
        }

        $progress->setMessage('Getting String Changes');
        $progress->advance();

        if ($scm === 'git') {
            $diffs = $this->getGitDiff($revisions, $lag);
        } else {
            $diffs = $this->getSvnDiff($revisions, $lag);
        }

        $progress->setMessage('Finding Updated Strings');
        $progress->advance();

//      $output->writeln(var_export($diffs, true), OutputInterface::VERBOSITY_DEBUG);

        $diffs['php'] = $this->pairMatches($diffs['php']);
        $diffs['tpl'] = $this->pairMatches($diffs['tpl']);

        $progress->setMessage('Found ' . count($diffs['php']) . ' PHP and ' . count($diffs['tpl']) . ' TPL changes');
        $progress->advance();

//      $output->writeln(var_export($diffs, true), OutputInterface::VERBOSITY_DEBUG);

        $diffs['php'] = $this->pairStrings($diffs['php'], 'php');
        $diffs['tpl'] = $this->pairStrings($diffs['tpl'], 'tpl');
        $diffs = array_merge($diffs['php'], $diffs['tpl']);

        $progress->setMessage('Found ' . count($diffs) . ' String pairs');
        $progress->advance();

//      $output->writeln(var_export($diffs, true), OutputInterface::VERBOSITY_DEBUG);

        $diffs = $this->filterStrings($diffs);

        $progress->setMessage("Found $this->stringCount translation strings");
        $progress->advance();

        $diffs = $this->removeIdentical($diffs);

        $progress->setMessage('Found ' . $this->duplicates . ' duplicate translation strings');
        $progress->advance();

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $output->writeln("\n\n<info>Strings Being Updated</info>\n");
            foreach ($diffs as $diff) {
                $output->writeln('* ' . $diff['-']);
                $output->writeln('* ' . $diff['+'] . "\n");
            }
        }

        /**
         * Tokens indicating that the replacement string was found and replaced in the language file
         * @ver array
         */
        $string = [];

        /**
         * Tokens indicating that the replacement string was already present in the language file, so was skipped
         * @var array
         */
        $skipped = [];

        /**
         * Tokens indicating what language files have had changes made to them
         * @var array
         */
        $lang = [];

        // update the language files with the new strings

        if ($this->stringCount) {
            foreach ($this->languages as $directory) {
                $langNow = substr($directory, strrpos($directory, "/") + 1);
                if (is_writable($directory . '/language.php')) {
                    $file = file_get_contents($directory . '/language.php');
                    foreach ($diffs as $key => $entry) {
                        // if the original string is in the language file
                        if (preg_match('/^"' . preg_quote($entry['-'], '/') . '[' . implode('', Language::PUNCTUATIONS) . ']?".*/m', $file, $match)) {
                            // if the replacement string does not already exist
                            if (! strpos($file, "\n\"" . $entry['+'] . '"')) {
                                // then replace the original string with an exact copy and a 'updated' copy on the next line
                                $replace = preg_replace('/"' . preg_quote($entry['-'], '/') . '[' . implode('', Language::PUNCTUATIONS) . ']?"/', '"' . $entry['+'] . '"', $match[0], 1);
                                $file = str_replace($match[0], $match[0] . "\n" . $replace, $file);

                                // keep track of overall numbers
                                $string[$key] = true;
                                $lang[$langNow] = true;
                            } else {
                                $skipped[$key] = true;
                            }
                        }
                        // If the replacement string has context information, create a non-commented translation in the english language file
                        // Strings with context need to be translated in the 'en' language file
                        // See https://dev.tiki.org/Translations-Revamp
                        if ($langNow == 'en') {
                            if (preg_match('/^\/\/ "' . preg_quote($entry['-'], '/') . '[' . implode('', Language::PUNCTUATIONS) . ']?".*/m', $file, $match)) {
                                // if the replacement string does not already exist
                                if (! strpos($file, "\n\"" . $entry['+'] . '"')) {
                                    // then replace the original string with an exact copy and a 'updated' copy on the next line
                                    $replace = preg_replace('/\/\/ "' . preg_quote($entry['-'], '/') . '[' . implode('', Language::PUNCTUATIONS) . ']?"/', '"' . $entry['+'] . '"', $match[0], 1);
                                    $file = str_replace($match[0], $match[0] . "\n" . $replace, $file);

                                    // keep track of overall numbers
                                    $string[$key] = true;
                                    $lang[$langNow] = true;
                                    unset($skipped[$key]);
                                } else {
                                    $skipped[$key] = true;
                                }
                            }
                        }
                    }
                    if (isset($lang[$langNow])) {
                        $progress->setMessage($langNow . "\tStrings to update");
                        $progress->advance();
                        if (! $input->getOption('audit')) {
                            file_put_contents($directory . '/language.php', $file);
                        }
                    } else {
                        $progress->setMessage($langNow . "\tNo changes to make");
                        $progress->advance();
                    }
                } else {
                    $progress->setMessage($langNow . "\tSkipping <info>language.php not writable</info>");
                    $progress->advance();
                }
            }
        }
        $skippedMessage = '';
        if ($this->duplicates) {
            $skippedMessage = ' Skipped ' . $this->duplicates . ' duplicate strings.';
        }

        if ($input->getOption('audit')) {
            $updateMessage = 'Out of Sync';
        } else {
            $updateMessage = 'Updated';
        }
        $progress->setMessage(count($string) . " of $this->stringCount strings $updateMessage in " . count($lang) . ' of ' . count($this->languages) . ' language files.' . $skippedMessage);
        $progress->finish();

        if ($input->getOption('audit')) {
            if (count($string)) {
                $syncMessage = "\n";
                $output->writeln("\n\n<info>Updated Strings not found in Language Files</info>");
                foreach ($diffs as $key => $entry) {
                    if (isset($string[$key])) {
                        $syncMessage .= '* ' . $entry['-'] . "\n";
                    }
                }
                $output->writeln($syncMessage);
                if ($input->getOption('email')) {
                    mail($input->getOption('email'), 'Updated Strings not found in Language Files', wordwrap(TIKI_PATH . "\n" . $syncMessage, 70, "\r\n"));
                }
                exit(1);
            }
            $output->writeln("\n\n<info>English and Translations are in Sync</info>\n");
            // if were not in audit mode
        } else {
            if (count($string) < $this->stringCount) {
                $output->writeln("\n\n<info>Strings Not Translated</info>");
                foreach ($diffs as $key => $entry) {
                    if (! isset($string[$key]) && ! isset($skipped[$key])) {
                        $output->writeln('* ' . $entry['-']);
                    }
                }
            }
            $output->writeln("\n\nOptionally run php get_strings.php to remove any unused translation strings.");
            $output->writeln("Verify before committing.\n");
        }
        exit(0);
    }
}
