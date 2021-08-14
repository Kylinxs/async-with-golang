
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Command;

use Exception;

class CommandUnavailableException extends Exception
{
    /**
     * Error when Tiki is not running as a VCS
     * @see ConsoleApplicationBuilder::checkIsVCS() Throws this error
     */
    public const CHECK_VCS = 101;
    /**
     * Error when Tiki is not running in Dev Mode
     * @see ConsoleApplicationBuilder::checkIsDevMode() Throws this error
     */
    public const CHECK_DEV = 102;
    /**
     * Error when Tiki has not been installed
     * @see ConsoleApplicationBuilder::checkIsDbRunning() Throws this error
     */
    public const CHECK_RUNNING = 201;
    /**
     * Error for when the database is running but not populated with Tiki (or otherwise not functioning)
     * @see ConsoleApplicationBuilder::checkIsDatabaseInstalled() Throws this error
     */
    public const CHECK_INSTALLED = 202;
    /**
     * Error for when Tiki-setup did not entirely complete because of a database error. (normally database:update fixes this)
     * @see ConsoleApplicationBuilder::checkTikiSetupComplete() Throws this error
     */
    public const CHECK_TIKI_SETUP = 203;
    /**
     * Error for when the Database is out of sync, (it needs to be updated)
     * @see ConsoleApplicationBuilder::checkDatabaseUpToDate()
     */

    public const CHECK_UPDATED = 204;
    /**
     * Dummy error code denoting a default value.
     * Will never be returned as an error by CommandUnavailableException.
     * Is a higher value than any of the feature specific series (300x) error codes.
     */
    public const CHECK_DEFAULT = 399;
}