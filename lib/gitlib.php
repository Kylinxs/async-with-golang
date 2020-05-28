
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

use Tiki\Process\Process;

/**
 * Provide methods to extract information from Git repository
 * available on `.git` folder.
 */
class GitLib extends TikiLib
{
    private $dir;
    private $bin;

    public function __construct($dir = null, $bin = null)
    {
        if (! empty($dir)) {
            $this->dir = $dir;
        } else {
            $this->dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.git';
        }

        if (! empty($bin)) {
            $this->bin = $bin;
        } else {
            $this->detect_git_binary();
        }
    }

    public function detect_git_binary()
    {
        $php_os = strtoupper(PHP_OS);

        if ($php_os === 'LINUX') {
            ob_start();
            $process = new Process('which git');
            $process->inheritEnvironmentVariables();
            $process->run();
            $git = trim($process->getOutput());
            ob_end_clean();
            if (! $process->getErrorOutput() && $git) {
                $this->bin = $git;
                return $git;
            }
        } else {
            $executableFinder = new \Symfony\Component\Process\ExecutableFinder();
            $git = $executableFinder->find('git');
            $this->bin = $git;
            return $git;
        }
    }

    public function run_git($args = [])
    {
        array_unshift($args, $this->bin);

        $process = new Process($args);
        $process->run();
        $return = $process->getExitCode();

        if ($return !== 0) {
            throw new Exception($process->getErrorOutput(), $return);
        }
        return $process->getOutput();
    }

    /**
     * Get relative path to current branch file pointer
     * Eg.: .git/refs/heads/19.x
     *
     * @return string
     */
    public function get_branch()
    {
        $filename = $this->dir . DIRECTORY_SEPARATOR . 'HEAD';
        if (! (file_exists($filename) && is_readable($filename))) {
            throw new Exception(tra('Not a git repository'));
        }

        $ref = file_get_contents($filename);
        $ref = trim($ref);
        $ref = explode(' ', $ref);
        return $ref[ 1 ];
    }

    /**
     * Get current commit hash value. If a branch is specified, it will return
     * hash value for last commit on branch informed.
     *
     * @param string $branch relative path to branch file
     * @return string
     */
    public function get_commit($branch = null)
    {
        $branch = $branch ?: $this->get_branch();

        $filename = $this->dir . DIRECTORY_SEPARATOR . $branch;
        if (file_exists($filename) && is_readable($filename)) {
            $hash = file_get_contents($filename);
            $hash = trim($hash);
            return $hash;
        }

        $filename = $this->dir . DIRECTORY_SEPARATOR . 'packed-refs';
        if (file_exists($filename) && is_readable($filename)) {
            $file = fopen($filename, 'r');
            while (! feof($file)) {
                $line = fgets($file);
                if (strpos($line, $branch)) {
                    return substr($line, 0, 40);
                }
            }
        }

        throw new Exception(sprintf(tra('Branch "%s" not found'), $branch));
    }

    /**
     * Return the content of object file for current commit or the
     * commit informed on parameter.
     *
     * @param string $commit hash
     * @return string
     */
    public function get_object($commit = null)
    {
        $commit = $commit ?: $this->get_commit();
        $filename = $this->dir . DIRECTORY_SEPARATOR
            . 'objects' . DIRECTORY_SEPARATOR
            . substr($commit, 0, 2) . DIRECTORY_SEPARATOR
            . substr($commit, 2);

        if (file_exists($filename) && is_readable($filename)) {
            $object = file_get_contents($filename);
            return zlib_decode($object);
        } elseif ($this->bin) {
            $object = $this->run_git(['cat-file', '-t', $commit]);
            $object = rtrim($object) . " " . $this->run_git(['cat-file', '-s', $commit]);
            $object = rtrim($object) . "\0" . $this->run_git(['cat-file', '-p', $commit]);
            return $object;
        }

        throw new Exception(tra("Can't get Git object file"));
    }

    /**
     * Return information about current commit on Git repository.
     * Eg.:
     *    [
     *      "commit" => [
     *        "size" => "245",
     *        "tree" => "84e891bd79bf8729be834f75ee9e3ce74da9c9b6",
     *        "hash" => "1b6852d92a11cc3df8bec1224cfdfcfd280cf31a",
     *      ],
     *      "author" => [
     *        "date" => 1554652946,
     *        "email" => "<xorti@localhost>",
     *        "name" => "xorti",
     *      ],
     *      "committer" => [
     *        "date" => 1554652946,
     *        "email" => "<xorti@localhost>",
     *        "name" => "xorti",
     *      ],
     *      "parent" => [
     *        "10c4999b34e28e994d51f0d17bc73dc4d1fa44e4",
     *      ],
     *      "message" => "[bp/r69651][FIX] Fix php requirements in tiki-check",
     *      "branch" => "19.x",
     *    ]
     *
     * @return array
     */
    public function get_info()
    {
        $branch = $this->get_branch();
        $commit = $this->get_commit($branch);
        $object = $this->get_object($commit);
        $object = $this->parse_object($object);

        $object['branch'] = substr($branch, strrpos($branch, '/') + 1);
        $object['commit']['hash'] = $commit;
        return $object;
    }

    /**
     * Return structured array for object informed as parameter.
     *
     * @param string $object
     * @return array
     */
    public function parse_object($object)
    {
        $type = substr($object, 0, strpos($object, ' '));

        if ($type === 'commit') {
            return $this->parse_object_commit($object);
        }

        throw new Exception('Not implemented');
    }

    /**
     * Return structured array with commit information for the object
     * informed as parameter
     *
     * @param string $object
     * @return array
     */
    public function parse_object_commit($object)
    {
        $info = [
            'commit' => [],
            'author' => '',
            'committer' => '',
            'parent' => [],
        ];

        $message_pos = strpos($object, "\n\n");
        $info['message'] = substr($object, $message_pos + 2);

        $object = substr($object, 0, $message_pos);
        $object = explode("\n", $object);

        foreach ($object as $line) {
            $line = explode(' ', $line);
            $temp = null;

            if ($line[ 0 ] === 'commit') {
                $temp = explode("\0", $line[ 1 ]);
                $info['commit']['size'] = $temp[0];

                if (isset($temp[1]) && $temp[1] === 'tree') {
                    $info['commit']['tree'] = $line [ 2 ];
                }
            } elseif (in_array($line[0], ['author', 'committer'])) {
                $key = $line[0];
                $info[$key] = [];

                $temp = array_slice($line, -2);
                $temp = array_map('intval', $temp);
                $info[$key]['date'] = $temp[0] + ($temp[1] * 6 * 6);

                $temp = array_slice($line, -3, -2);
                $info[$key]['email'] = $temp[0];

                $temp = array_slice($line, 1, -3);
                $info[$key]['name'] = implode(' ', $temp);
            } elseif ($line [ 0 ] === 'parent') {
                $info['parent'][] = $line[ 1 ];
            }
        }
        $info['mdate'] = filemtime('.git/FETCH_HEAD');

        return $info;
    }
}