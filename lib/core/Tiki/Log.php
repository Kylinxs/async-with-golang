
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

use Psr\Log\AbstractLogger;

class Tiki_Log extends AbstractLogger
{
    /**
     * Detailed debug information
     */
    public const DEBUG = 100;

    /**
     * Interesting events
     *
     * Examples: User logs in, SQL logs.
     */
    public const INFO = 200;

    /**
     * Uncommon events
     */
    public const NOTICE = 250;

    /**
     * Exceptional occurrences that are not errors
     *
     * Examples: Use of deprecated APIs, poor use of an API,
     * undesirable things that are not necessarily wrong.
     */
    public const WARNING = 300;

    /**
     * Runtime errors
     */
    public const ERROR = 400;

    /**
     * Critical conditions
     *
     * Example: Application component unavailable, unexpected exception.
     */
    public const CRITICAL = 500;

    /**
     * Action must be taken immediately
     *
     * Example: Entire website down, database unavailable, etc.
     * This should trigger the SMS alerts and wake you up.
     */
    public const ALERT = 550;

    /**
     * Urgent alert.
     */
    public const EMERGENCY = 600;

    protected static $levels = [
        'debug' => self::DEBUG,
        'info' => self::INFO,
        'notice' => self::NOTICE,
        'warning' => self::WARNING,
        'error' => self::ERROR,
        'critical' => self::CRITICAL,
        'alert' => self::ALERT,
        'emergency' => self::EMERGENCY,
    ];

    private $level;

    private $type;

    public function __construct($type, $level)
    {
        $this->type = $type;
        $this->level = $level;
    }

    public function log($level, $message, array $context = [])
    {
        if (self::$levels[$level] < self::$levels[$this->level]) {
            //Do not log
            return;
        }

        $msg = sprintf("[%s] %s", strtoupper(tra($level)), $message);
        if ($context) {
            if (count($context) == 1 && isset($context[0])) {
                $msg .= ' ' . $context[0];
            } else {
                $msg .= ' ' . print_r($context, 1);
            }
        }
        $logslib = TikiLib::lib('logs');
        $logslib->add_log($this->type, $msg);
    }
}