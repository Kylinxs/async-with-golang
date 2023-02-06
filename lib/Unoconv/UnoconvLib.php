
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Lib\Unoconv;

use Unoconv\Unoconv;

/**
 * Wrapper of the library Unoconv, for pdf convertion
 */
class UnoconvLib
{
    protected $unoconv;

    public static $homeEnvCopy;

    const DEFAULT_PORT = 2002;

    /**
     * UnoconvLib constructor.
     */
    public function __construct()
    {
        if (! static::isLibraryAvailable()) {
            return;
        }

        global $prefs;

        $config = [
            'unoconv.binaries' => $prefs['alchemy_unoconv_path'],
            'timeout' => $prefs['alchemy_unoconv_timeout'] ?: 60,
            'unoconv.port' => $prefs['alchemy_unoconv_port'],
        ];

        $this->unoconv = Unoconv::create($config);
    }

    /**
     * Check if Unoconv is available
     *
     * @return bool true if the base library is available
     */
    public static function isLibraryAvailable()
    {
        return class_exists('Unoconv\Unoconv');
    }

    /**
     * Convert any document from and to any LibreOffice supported format
     *
     * @param string $sourcePath the patch to read the file
     * @param string $destinationPath the path to store the result
     * @param string $type media type of the file to be converted
     * @throws \Exception
     */
    public function convertFile($sourcePath, $destinationPath, $type = 'pdf')
    {
        try {
            self::setHomeEnv();
            $this->unoconv->transcode($sourcePath, $type, $destinationPath);
            self::revertHomeEnv();
        } catch (\Exception $e) {
            self::revertHomeEnv();

            throw $e;
        }
    }

    /**
     * Sets home path to cache so unoconv scripts can write into directory
     */
    public static function setHomeEnv()
    {
        global $tikipath, $tikidomain;

        $envHomeDefined = isset($_ENV) && array_key_exists('HOME', $_ENV);
        if ($envHomeDefined) {
            self::$homeEnvCopy = $_ENV['HOME'];
        }

        // set a proper home folder
        $_ENV['HOME'] = implode(DIRECTORY_SEPARATOR, [$tikipath, 'temp', 'cache', $tikidomain]);
    }

    /**
     * Resets home env path after unoconv scripts run
     */
    protected function revertHomeEnv()
    {
        if (! empty(self::$homeEnvCopy)) {
            $_ENV['HOME'] = self::$homeEnvCopy;
            self::$homeEnvCopy = null;
        } else {
            unset($_ENV['HOME']);
        }
    }

    /**
     * Check if the port for alchemy lib is currently being used
     * @return bool True if port is not in use, False otherwise
     */
    public static function isPortAvailable()
    {
        global $prefs;

        $port = $prefs['alchemy_unoconv_port'] ?: self::DEFAULT_PORT;
        $socket = @fsockopen(gethostname(), $port, $error, $error_details, 10);

        if ($socket) {
            fclose($socket);
        }

        return $socket === false;
    }
}