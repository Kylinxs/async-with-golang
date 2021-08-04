
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

declare(strict_types=1);

namespace Tiki\PSR12Migration;

use Composer\Autoload\ClassLoader;

class Autoload
{
    /**
     * @var string[] Map of classes to register / handle
     */
    protected static $aliasMap = [
        'TikiInit' => 'Tiki\\TikiInit',
        'Patch' => 'Tiki\\Installer\\Patch',
        'ProgressBar' => 'Tiki\\Installer\\ProgressBar',
        'Installer' => 'Tiki\\Installer\\Installer',
        'InstallerDatabaseErrorHandler' => 'Tiki\\Installer\\InstallerDatabaseErrorHandler',
        'LogsLib' => 'Tiki\\Lib\\Logs\\LogsLib',
        'LogsQueryLib' => 'Tiki\\Lib\\Logs\\LogsQueryLib',
    ];

    /**
     * @var ClassLoader pointer to composer autoloader
     */
    protected static $composerAutoloader = null;

    /**
     * Entry point to the autoload
     * Will try to use class_alias to map the class requested to the right class.
     *
     * @param string $class the name of the class to be autoloaded
     */
    public static function autoloadAlias($class): ?bool
    {
        // check if we can handle this class
        if (! isset(static::$aliasMap[$class])) {
            return null;
        }

        $realClass = static::$aliasMap[$class];

        $classExists = class_exists($realClass);

        // check if we need to autoload the real class, class_alias expect the real class to be autoload already
        if (! $classExists) {
            static::getComposerAutoloader()->loadClass($realClass);
            $classExists = class_exists($realClass);
        }

        if ($classExists) {
            Report::classAlias($class, $realClass);
            class_alias($realClass, $class);
            return true;
        }

        return null;
    }

    protected static function getComposerAutoloader(): ?ClassLoader
    {
        if (static::$composerAutoloader !== null) {
            return static::$composerAutoloader;
        }

        static::$composerAutoloader = require TIKI_PATH . '/vendor_bundled/vendor/autoload.php';
        return static::$composerAutoloader;
    }
}