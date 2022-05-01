
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace TikiDevTools;

/**
 * Script to check sql CREATE statements used MyISAM engine in ../../db/tiki.sql and ../../installer/schema sql files
 *
 * Available commands:
 *   -f: flag to fix or not
 *   -o and --output: fine path to save check result
 */
class CheckSqlEngine
{
    /**
     * Execute check
     */
    public function execute()
    {
        $result = $this->checkSqlFiles();
        return $result['error_count'] - $result['fixed_count'];
    }

    protected function getOpts()
    {
        $short_opts = "o:f::";
        $long_opts = [
            "output",
        ];
        $options = getopt($short_opts, $long_opts);
        return ($options);
    }

    protected function printMessage($message, $outputPath = null)
    {
        echo "\033[0;32m" . $message . "\033[0m" . PHP_EOL;
        if (! empty($outputPath)) {
            file_put_contents($outputPath, $message . PHP_EOL, FILE_APPEND);
        }
    }

    protected function printMessageError($message, $outputPath = null)
    {
        echo "\033[0;31m" . $message . "\033[0m" . PHP_EOL;
        if (! empty($outputPath)) {
            file_put_contents($outputPath, $message . PHP_EOL, FILE_APPEND);
        }
    }

    protected function checkFile($path, $should_fix, $output_path)
    {
        $sqlfile = file_get_contents($path);
        if ($sqlfile === false) {
            $this->printMessageError('Unable to open file: ' . $path);
            return 0;
        }

        $this->printMessage('Checking ' . $path, $output_path);

        $error_count = 0;
        $fixed_count = 0;
        $queries = explode(';', $sqlfile);
        $query_count = sizeof($queries);

        // check queries containing ';' within itself such as delimiter
        for ($i = 0; $i < $query_count - 1; $i++) {
            $cur_query = preg_replace('/\s*/m', '', $queries[$i]);
            $next_query = preg_replace('/\s*/m', '', $queries[$i + 1]);
            if (substr($cur_query, -1) === "'" and substr($next_query, 0, 1) === "'") {
                array_splice($queries, $i, 2, $queries[$i] . ";" . $queries[$i + 1]);
                $query_count--;
            }
        }

        for ($i = 0; $i < $query_count; $i++) {
            if (
                ! preg_match(
                    '/\s*CREATE\s+(?:TEMPORARY\s+){0,1}TABLE\s*(?:IF\s+NOT\s+EXISTS\s+){0,1}([^\s]+)\s*\(.*\)\s*(.*)[;\s]*/i',
                    str_replace(["\r", "\n"], ['', ' '], $queries[$i]),
                    $matches
                )
            ) {
                continue;
            }

            $tableName = str_replace('`', '', $matches[1]);
            $tableOptions = $matches[2];

            if (! preg_match('/(ENGINE)\s*=\s*(\w+)\s*/i', $tableOptions, $matches)) {
                $error_count++;
                $message = "\t-- CREATE TABLE `" . $tableName . "`: Missing ENGINE=MyISAM Statement";
                $this->printMessageError($message, $output_path);

                if ($should_fix) {
                    $queries[$i] = $queries[$i] . ' ENGINE=MyISAM';
                }
            } elseif (strcasecmp('MyISAM', $matches[2]) != 0) {
                $error_count++;
                $message = "\t-- CREATE TABLE `" . $tableName . "`: Wrong ENGINE specified '" . $matches[2] . "'' should be ENGINE=MyISAM";
                $this->printMessageError($message, $output_path);

                if ($should_fix) {
                    $queries[$i] = rtrim(str_replace($matches[0], 'ENGINE=MyISAM', $queries[$i]));
                }
            }

            if (preg_match('/((?:DEFAULT\s+){0,1}(?:CHARSET|CHARACTER\s+SET))\s*=\s*(\w*)\s*/i', $tableOptions, $matches)) {
                $error_count++;
                $message = "\t-- CREATE TABLE `" . $tableName . "`: Should not force a charset, currently forcing the usage of '" . $matches[2] . "''";
                $this->printMessageError($message, $output_path);

                if ($should_fix) {
                    $queries[$i] = rtrim(str_replace($matches[0], '', $queries[$i]));
                }
            }
        }

        if ($error_count > 0 and $should_fix) {
            $success = file_put_contents($path, implode(";", $queries));
            if ($success !== false) {
                $fixed_count = $error_count;
                $this->printMessage('Saved fixed content', $output_path);
            } else {
                $this->printMessage('Failed to save fixed content', $output_path);
            }
        }

        return ['error_count' => $error_count, 'fixed_count' => $fixed_count];
    }

    protected function checkSqlFiles()
    {
        $this->printMessage('Checking started...');

        $options = $this->getOpts();
        $should_fix = isset($options['f']);
        if ($should_fix) {
            $this->printMessage('Will fix problems automatically if exist');
        } else {
            $this->printMessage('Just check and output problems');
        }

        $output_path = null;
        if (! empty($options['o'])) {
            $output_path = $options['o'];
        } elseif (! empty($options['output'])) {
            $output_path = $options['output'];
        }
        if (! empty($output_path)) {
            file_put_contents($output_path, "");
        }

        $error_count = 0;
        $fixed_count = 0;
        $result = $this->checkFile(__DIR__ . '/../../db/tiki.sql', $should_fix, $output_path);
        $error_count += $result['error_count'];
        $fixed_count += $result['fixed_count'];
        $filenameList = scandir(__DIR__ . '/../../installer/schema');
        if ($filenameList === false) {
            $this->printMessageError('Scandir failed on installer/schema');
        } else {
            foreach ($filenameList as $filename) {
                $ext = substr($filename, -4);
                if ($ext === '.sql') {
                    $result = $this->checkFile(
                        __DIR__ . '/../../installer/schema/' . $filename,
                        $should_fix,
                        $output_path
                    );
                    $error_count += $result['error_count'];
                    $fixed_count += $result['fixed_count'];
                }
            }
        }

        $this->printMessageError(
            $error_count . " errors found" . ($should_fix ? ", " . $fixed_count . " errors fixed" : "")
        );
        $this->printMessage('Completed');
        return ['error_count' => $error_count, 'fixed_count' => $fixed_count];
    }
}

// Make sure script is run from a shell
if (PHP_SAPI !== 'cli') {
    die("Please run from a shell");
}

$checker = new CheckSqlEngine();
$errorCount = $checker->execute();

if ($errorCount > 0) {
    exit(1);
}
exit(0);