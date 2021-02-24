<?php

/**
 * @package tikiwiki
 */

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$
/*
About the design:
tiki-check.php is designed to run in 2 modes
1) Regular mode. From inside Tiki, in Admin | General
2) Stand-alone mode. Used to check a server pre-Tiki installation, by copying (only) tiki-check.php onto the server and pointing your browser to it.
tiki-check.php should not crash but rather avoid running tests which lead to tiki-check crashes.
*/

use Tiki\Lib\Alchemy\AlchemyLib;
use Tiki\Lib\Unoconv\UnoconvLib;
use Tiki\Package\ComposerManager;

// TODO : Create sane 3rd mode for Monitoring Software like Nagios, Icinga, Shinken
// * needs authentication, if not standalone
isset($_REQUEST['nagios']) ? $nagios = true : $nagios = false;
file_exists('tiki-check.php.lock') ? $locked = true : $locked = false;
$font = 'lib/captcha/DejaVuSansMono.ttf';

$inputConfiguration = array(
    array(
        'staticKeyFilters' => array(
            'dbhost' => 'text',
            'dbuser' => 'text',
            'dbpass' => 'text',
            'email_test_to' => 'email',
        ),
    ),
);

// reflector for SefURL check
if (isset($_REQUEST['tiki-check-ping'])) {
    die('pong:' . (int)$_REQUEST['tiki-check-ping']);
}


function checkOPCacheCompatibility()
{
    return ! ((version_compare(PHP_VERSION, '7.1.0', '>=') && version_compare(PHP_VERSION, '7.2.0', '<')) //7.1.x
        || (version_compare(PHP_VERSION, '7.2.0', '>=') && version_compare(PHP_VERSION, '7.2.19', '<')) // >= 7.2.0 < 7.2.19
        || (version_compare(PHP_VERSION, '7.3.0', '>=') && version_compare(PHP_VERSION, '7.3.6', '<'))); // >= 7.3.0 < 7.3.6
}

function getTikiRequirements()
{
    return array(
        array(
            'name'    => 'Tiki 25.x',
            'version' => 25,
            'php'     => array(
                'min' => '7.4',
            ),
            'mariadb' => array(
                'min' => '5.5',
            ),
            'mysql'   => array(
                'min' => '5.7',
            ),
        ),
        array(
            'name'    => 'Tiki 24.x',
            'version' => 24,
            'php'     => array(
                'min' => '7.4',
            ),
            'mariadb' => array(
                'min' => '5.5',
            ),
            'mysql'   => array(
                'min' => '5.7',
            ),
        ),
        array(
            'name'    => 'Tiki 23.x',
            'version' => 23,
            'php'     => array(
                'min' => '7.4',
                'max' => '7.4',
            ),
            'mariadb' => array(
                'min' => '5.5',
            ),
            'mysql'   => array(
                'min' => '5.7',
            ),
        ),
        array(
            'name'    => 'Tiki 22.x',
            'version' => 22,
            'php'     => array(
                'min' => '7.4',
                'max' => '7.4',
            ),
            'mariadb' => array(
                'min' => '5.5',
            ),
            'mysql'   => array(
                'min' => '5.7',
            ),
        ),
        array(
            'name'    => 'Tiki 21.x LTS',
            'version' => 21,
            'php'     => array(
                'min' => '7.2',
                'max' => '7.3',
            ),
            'mariadb' => array(
                'min' => '5.5',
            ),
            'mysql'   => array(
                'min' => '5.7',
            ),
        ),
        array(
            'name'    => 'Tiki 20.x',
            'version' => 20,
            'php'     => array(
                'min' => '7.1',
                'max' => '7.2',
            ),
            'mariadb' => array(
                'min' => '5.5',
                'max' => '10.4',
            ),
            'mysql'   => array(
                'min' => '5.5.3',
                'max' => '5.7',
            ),
        ),
        array(
            'name'    => 'Tiki 19.x',
            'version' => 19,
            'php'     => array(
                'min' => '7.1',
                'max' => '7.2',
            ),
            'mariadb' => array(
                'min' => '5.5',
                'max' => '10.4',
            ),
            'mysql'   => array(
                'min' => '5.5.3',
                'max' => '5.7',
            ),
        ),
        array(
            'name'    => 'Tiki 18.x',
            'version' => 18,
            'php'     => array(
                'min' => '5.6',
                'max' => '7.2',
            ),
            'mariadb' => array(
                'min' => '5.1',
                'max' => '10.4',
            ),
            'mysql'   => array(
                'min' => '5.0',
                'max' => '5.7',
            ),
        ),
        array(
            'name'    => 'Tiki 15.x',
            'version' => 15,
            'php'     => array(
                'min' => '5.5',
                'max' => '5.6',
            ),
            'mariadb' => array(
                'min' => '5.0',
                'max' => '10.1',
            ),
            'mysql'   => array(
                'min' => '5.0',
                'max' => '5.6',
            ),
        ),
        array(
            'name'    => 'Tiki 12.x LTS',
            'version' => 12,
            'php'     => array(
                'min' => '5.3',
                'max' => '5.6',
            ),
            'mariadb' => array(
                'min' => '5.0',
                'max' => '5.5',
            ),
            'mysql'   => array(
                'min' => '5.0',
                'max' => '5.5',
            ),
        ),
    );
}

function checkServerRequirements($phpVersion, $dbEngine, $dbVersion)
{
    $dbEnginesLabels = array(
        'mysql'   => 'MySQL',
        'mariadb' => 'MariaDB',
    );

    $tikiRequirements = getTikiRequirements();

    $phpValid = false;
    $dbValid = false;

    foreach ($tikiRequirements as $tikiVersion) {
        if (version_compare($phpVersion, $tikiVersion['php']['min'], '<')) {
            continue;
        }
        if (
            isset($requirement['php']['max'])
            && $requirement['php']['max'] !== $requirement['php']['min']
            && version_compare(PHP_VERSION, $requirement['php']['max'], '>')
        ) {
            continue;
        }
        $phpValid = true;
        break;
    }

    $tiki_server_req['PHP'] = array(
        'value'   => PHP_VERSION,
        'fitness' => tra('good'),
        'message' => tra('PHP version is supported by one of Tiki versions'),
    );

    if (! $phpValid) {
        $tiki_server_req['PHP']['fitness'] = tra('bad');
        $tiki_server_req['PHP']['message'] = tra('PHP version is not supported by Tiki');
    }

    if ($dbEngine && $dbVersion) {
        foreach ($tikiRequirements as $tikiVersion) {
            if (version_compare($dbVersion, $tikiVersion[$dbEngine]['min'], '<')) {
                continue;
            }
            if (
                isset($requirement[$dbEngine]['max'])
                && $requirement[$dbEngine]['max'] !== $requirement[$dbEngine]['min']
                && version_compare($dbVersion, $requirement[$dbEngine]['max'], '>')
            ) {
                continue;
            }
            $dbValid = true;
            break;
        }

        $tiki_server_req['Database'] = array(
            'value'   => $dbEnginesLabels[$dbEngine] . ' ' . $dbVersion,
            'fitness' => tra('good'),
            'message' => tra('Database version is supported by one of Tiki Versions.'),
        );

        if (! $dbValid) {
            $tiki_server_req['Database']['fitness'] = tra('bad');
            $tiki_server_req['Database']['message'] = tra('Database version is not supported by Tiki.');
        }
    } else {
        $tiki_server_req['Database'] = array(
            'value'   => 'N/A',
            'fitness' => tra('unsure'),
            'message' => tra('Unable to determine database compatibility'),
        );
    }

    return $tiki_server_req;
}

/**
 * @param string $dbEngine
 * @param string $dbVersion
 *
 * @return array
 */
function getCompatibleVersions($dbEngine = '', $dbVersion = '')
{
    $tikiRequirements = getTikiRequirements();
    $compatibleVersions = array();

    $dbVersion = $dbVersion ?: 0;
    foreach ($tikiRequirements as $requirement) {
        if (version_compare(PHP_VERSION, $requirement['php']['min'], '<')) {
            continue;
        }

        if (
            isset($requirement['php']['max'])
            && $requirement['php']['max'] !== $requirement['php']['min']
            && version_compare(PHP_VERSION, $requirement['php']['max'], '>')
        ) {
            continue;
        }

        if ($dbEngine === 'mysql' || $dbEngine === 'mariadb') {
            if (version_compare($dbVersion, $requirement[$dbEngine]['min'], '<')) {
                continue;
            }
            if (
                isset($requirement[$dbEngine]['max'])
                && $requirement[$dbEngine]['max'] !== $requirement[$dbEngine]['min']
                && version_compare($dbVersion, $requirement[$dbEngine]['max'], '>')
            ) {
                continue;
            }
        }

        $requirement['fitness'] = tra('unsure');
        $requirement['message'] = tra('Unable to database requirements');

        if ($dbEngine && $dbVersion) {
            $requirement['fitness'] = tra('info');
            $requirement['message'] = tra('Supported version');

            if (count($compatibleVersions) == 0) {
                $requirement['fitness'] = tra('good');
                $requirement['message'] = tra('Recommended version');
            }
        }

        $compatibleVersions[] = $requirement;
    }
    return $compatibleVersions;
}

function checkTikiVersionCompatible($compatibleVersions, $majorVersion)
{
    foreach ($compatibleVersions as $tiki) {
        if ($tiki['version'] == $majorVersion) {
            return true;
        }
    }
    return false;
}

if (file_exists('./db/local.php') && file_exists('./templates/tiki-check.tpl')) {
    $standalone = false;
    require_once('tiki-setup.php');
    // TODO : Proper authentication
    $access->check_permission('tiki_p_admin');

    // This page is an admin tool usually used in the early stages of setting up Tiki, before layout considerations.
    // Restricting the width is contrary to its purpose.
    $prefs['feature_fixed_width'] = 'n';
} else {
    $standalone = true;
    $render = "";

    /**
     * @param $string
     * @return mixed
     */
    function tra($string)
    {
        return $string;
    }

    function tr($string)
    {
        return tra($string);
    }


    /**
      * @param $var
      * @param $style
      */
    function renderTable($var, $style = "")
    {
        global $render;
        $morestyle = "";
        if ($style == "wrap") {
            $morestyle = "overflow-wrap: anywhere;";
        }
        if (is_array($var)) {
            $render .= '<table class="table table-bordered" style="' . $morestyle . '">';
            $render .= "<thead><tr></tr></thead>";
            $render .= "<tbody>";
            foreach ($var as $key => $value) {
                $render .= "<tr>";
                $render .= '<th><span class="visible-on-mobile">Property:&nbsp;</span>';
                $render .= $key;
                $render .= "</th>";
                $iNbCol = 0;
                foreach ($var[$key] as $key2 => $value2) {
                    $render .= '<td data-th="' . $key2 . ':&nbsp;" style="';
                    if ($iNbCol != count(array_keys($var[$key])) - 1) {
                        $render .= 'text-align: center;white-space:nowrap;';
                    }
                    $render .= '"><span class="';
                    switch ($value2) {
                        case 'good':
                        case 'safe':
                        case 'unsure':
                        case 'bad':
                        case 'risky':
                        case 'info':
                            $render .= "button $value2";
                            break;
                    }
                    $render .= '">' . $value2 . '</span></td>';
                    $iNbCol++;
                }
                $render .= '</tr>';
            }
            $render .= '</tbody></table>';
        } else {
            $render .= 'Nothing to display.';
        }
    }

    /**
     * @param $var
     */
    function renderAvailableTikiTable($var)
    {
        global $render;

        $formatValue = function ($value, $property) {
            return $value[$property]['min'] .
                ((! empty($value[$property]['max']) && $value[$property]['max'] != $value[$property]['min'])
                    ? ' - ' . $value[$property]['max']
                    : (empty($value[$property]['max']) ? '+' : ''));
        };
        if (is_array($var) && ! empty($var)) {
            $render .= '<table class="table table-bordered"><thead>';
            $render .= '<tr><th>Version</th><th>PHP</th><th>MySQL</th><th>MariaDB</th>';
            $render .= '<th>Fitness</th><th>Explanation</th></tr>';
            foreach ($var as $value) {
                $phpReq = $formatValue($value, 'php');
                $mysqlReq = $formatValue($value, 'mysql');
                $mariadbReq = $formatValue($value, 'mariadb');
                $render .= '<th> ' . $value['name'] . ' </th>';
                $render .= '<td> ' . $phpReq . ' </td>';
                $render .= '<td> ' . $mysqlReq . ' </td>';
                $render .= '<td> ' . $mariadbReq . ' </td>';
                $render .= '<td><span class="button ' . $value['fitness'] . '">' . $value['fitness'] . '</span> </td>';
                $render .= '<td> ' . $value['message'] . ' </td></tr>';
            }
            $render .= '</tbody></table>';
        } else {
            $render .= 'Nothing to display.';
        }

        $render .= '<p>For more details, check the <a href="https://doc.tiki.org/Requirements" target="_blank">Tiki Requirements</a> documentation.</p>';
    }
}

// Get PHP properties and check them
$php_properties = false;

// Check error reporting level
$e = error_reporting();
$d = ini_get('display_errors');
$l = ini_get('log_errors');
if ($l) {
    if (! $d) {
        $php_properties['Error logging'] = array(
        'fitness' => tra('info'),
        'setting' => 'Enabled',
        'message' => tra('Errors will be logged, since log_errors is enabled. Also, display_errors is disabled. This is good practice for a production site, to log the errors instead of displaying them.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
        );
    } else {
        $php_properties = array();
        $php_properties['Error logging'] = array(
        'fitness' => tra('info'),
        'setting' => 'Enabled',
        'message' => tra('Errors will be logged, since log_errors is enabled, but display_errors is also enabled. Good practice, especially for a production site, is to log all errors instead of displaying them.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
        );
    }
} else {
    $php_properties['Error logging'] = array(
    'fitness' => tra('info'),
    'setting' => 'Full',
    'message' => tra('Errors will not be logged, since log_errors is not enabled. Good practice, especially for a production site, is to log all errors.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
}
if ($e == 0) {
    if ($d != 1) {
        $php_properties['Error reporting'] = array(
            'fitness' => tra('info'),
            'setting' => 'Disabled',
            'message' => tra('Errors will not be reported, because error_reporting and display_errors are both turned off. This may be appropriate for a production site but, if any problems occur, enable these in php.ini to get more information.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
        );
    } else {
        $php_properties['Error reporting'] = array(
            'fitness' => tra('info'),
            'setting' => 'Disabled',
            'message' => tra('No errors will be reported, although display_errors is On, because the error_reporting level is set to 0. This may be appropriate for a production site but, in if any problems occur, raise the value in php.ini to get more information.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
        );
    }
} elseif ($e > 0 && $e < 32767) {
    if ($d != 1) {
        $php_properties['Error reporting'] = array(
            'fitness' => tra('info'),
            'setting' => 'Disabled',
            'message' => tra('No errors will be reported, because display_errors is turned off. This may be appropriate for a production site but, in any problems occur, enable it in php.ini to get more information. The error_reporting level is reasonable at ' . $e . '.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
        );
    } else {
        $php_properties['Error reporting'] = array(
            'fitness' => tra('info'),
            'setting' => 'Partly',
            'message' => tra('Not all errors will be reported as the error_reporting level is at ' . $e . '. ' . 'This is not necessarily a bad thing (and it may be appropriate for a production site) as critical errors will be reported, but sometimes it may be useful to get more information. Check the error_reporting level in php.ini if any problems are occurring.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
        );
    }
} else {
    if ($d != 1) {
        $php_properties['Error reporting'] = array(
            'fitness' => tra('info'),
            'setting' => 'Disabled',
            'message' => tra('No errors will be reported although the error_reporting level is all the way up at ' . $e . ', because display_errors is off. This may be appropriate for a production site but, in case of problems, enable it in php.ini to get more information.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
        );
    } else {
        $php_properties['Error reporting'] = array(
            'fitness' => tra('info'),
            'setting' => 'Full',
            'message' => tra('All errors will be reported as the error_reporting level is all the way up at ' . $e . ' and display_errors is on. This is good because, in case of problems, the error reports usually contain useful information.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
        );
    }
}

// Now we can raise our error_reporting to make sure we get all errors
// This is especially important as we can't use proper exception handling with PDO as we need to be PHP 4 compatible
error_reporting(-1);

// Check if ini_set works
if (function_exists('ini_set')) {
    $php_properties['ini_set'] = array(
        'fitness' => tra('good'),
        'setting' => 'Enabled',
        'message' => tra('ini_set is used in some places to accommodate special needs of some Tiki features.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
    // As ini_set is available, use it for PDO error reporting
    ini_set('display_errors', '1');
} else {
    $php_properties['ini_set'] = array(
        'fitness' => tra('unsure'),
        'setting' => 'Disabled',
        'message' => tra('ini_set is used in some places to accommodate special needs of some Tiki features. Check disable_functions in your php.ini.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
}

// First things first
// If we don't have a DB-connection, some tests don't run
$s = extension_loaded('pdo_mysql');
if ($s) {
    $php_properties['DB Driver'] = array(
        'fitness' => tra('good'),
        'setting' => 'PDO',
        'message' => tra('The PDO extension is the suggested database driver/abstraction layer.')
    );
} elseif ($s = extension_loaded('mysqli')) {
    $php_properties['DB Driver'] = array(
        'fitness' => tra('unsure'),
        'setting' => 'MySQLi',
        'message' => tra('The recommended PDO database driver/abstraction layer cannot be found. The MySQLi driver is available, though, so the database connection will fall back to the AdoDB abstraction layer that is bundled with Tiki.')
    );
} elseif (extension_loaded('mysql')) {
    $php_properties['DB Driver'] = array(
        'fitness' => tra('unsure'),
        'setting' => 'MySQL',
        'message' => tra('The recommended PDO database driver/abstraction layer cannot be found. The MySQL driver is available, though, so the database connection will fall back to the AdoDB abstraction layer that is bundled with Tiki.')
    );
} else {
    $php_properties['DB Driver'] = array(
        'fitness' => tra('bad'),
        'setting' => 'Not available',
        'message' => tra('None of the supported database drivers (PDO/mysqli/mysql) is loaded. This prevents Tiki from functioning.')
    );
}

// Now connect to the DB and make all our connectivity methods work the same
$connection = false;
if ($standalone && ! $locked) {
    if (empty($_POST['dbhost']) && ! ($php_properties['DB Driver']['setting'] == 'Not available')) {
            $render .= <<<DBC
<h2>Database credentials</h2>
Couldn't connect to database, please provide valid credentials.
<form method="post" action="{$_SERVER['SCRIPT_NAME']}">
    <div class="containerform-group">
        <label for="dbhost">Database host</label>
        <input class="form-control" type="text" id="dbhost" name="dbhost" value="localhost" />
    </div>
    <div class="form-group">
        <label for="dbuser">Database username</label>
        <input class="form-control" type="text" id="dbuser" name="dbuser" />
    </div>
    <div class="form-group">
        <label for="dbpass">Database password</label>
        <input class="form-control" type="password" id="dbpass" name="dbpass" />
    </div>
    <div class="form-group">
    <input type="submit" class="btn btn-primary btn-sm" value=" Connect " />
    </div>
</form>
DBC;
    } else {
        try {
            switch ($php_properties['DB Driver']['setting']) {
                case 'PDO':
                    // We don't do exception handling here to be PHP 4 compatible
                    $connection = new PDO('mysql:host=' . $_POST['dbhost'], $_POST['dbuser'], $_POST['dbpass']);
                    /**
                      * @param $query
                       * @param $connection
                       * @return mixed
                      */
                    function query($query, $connection)
                    {
                        $result = $connection->query($query);
                        $return = $result->fetchAll();
                        return($return);
                    }
                    break;
                case 'MySQLi':
                    $error = false;
                    $connection = new mysqli($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpass']);
                    $error = mysqli_connect_error();
                    if (! empty($error)) {
                        $connection = false;
                        $render .= 'Couldn\'t connect to database: ' . htmlspecialchars($error);
                    }
                    /**
                     * @param $query
                     * @param $connection
                     * @return array
                     */
                    function query($query, $connection)
                    {
                        $result = $connection->query($query);
                        $return = array();
                        while ($row = $result->fetch_assoc()) {
                            $return[] = $row;
                        }
                        return($return);
                    }
                    break;
                case 'MySQL':
                    $connection = mysql_connect($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpass']);
                    if ($connection === false) {
                        $render .= 'Cannot connect to MySQL. Wrong credentials?';
                    }
                    /**
                     * @param $query
                     * @param string $connection
                     * @return array
                     */
                    function query($query, $connection = '')
                    {
                        $result = mysql_query($query);
                        $return = array();
                        while ($row = mysql_fetch_array($result)) {
                            $return[] = $row;
                        }
                        return($return);
                    }
                    break;
            }
        } catch (Exception $e) {
            $render .= 'Cannot connect to MySQL. Error: ' . htmlspecialchars($e->getMessage());
        }
    }
} else {
    /**
      * @param $query
      * @return array
      */
    function query($query)
    {
        global $tikilib;
        $result = $tikilib->query($query);
        $return = array();
        while ($row = $result->fetchRow()) {
            $return[] = $row;
        }
        return($return);
    }
}

// Basic Server environment
$server_information['Operating System'] = array(
    'value' => PHP_OS,
);

if (PHP_OS == 'Linux' && function_exists('exec')) {
    exec('lsb_release -d', $output, $retval);
    if ($retval == 0) {
        $server_information['Release'] = array(
            'value' => str_replace('Description:', '', $output[0])
        );
        # Check for FreeType fails without a font, i.e. standalone mode
        # Using a URL as font source doesn't work on all PHP installs
        # So let's try to gracefully fall back to some locally installed font at least on Linux
        if (! file_exists($font)) {
            $font = exec('find /usr/share/fonts/ -type f -name "*.ttf" | head -n 1', $output);
        }
    } else {
        $server_information['Release'] = array(
            'value' => tra('N/A')
        );
    }
}

$server_information['Web Server'] = array(
    'value' => $_SERVER['SERVER_SOFTWARE']
);

$server_information['Server Signature']['value'] = ! empty($_SERVER['SERVER_SIGNATURE']) ? $_SERVER['SERVER_SIGNATURE'] : 'off';

// Free disk space
if (function_exists('disk_free_space')) {
    $bytes = @disk_free_space('.');    // this can fail on 32 bit systems with lots of disc space so suppress the possible warning
    $si_prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
    $base = 1024;
    $class = min((int) log($bytes, $base), count($si_prefix) - 1);
    $free_space = sprintf('%1.2f', $bytes / pow($base, $class)) . ' ' . $si_prefix[$class];
    if ($bytes === false) {
        $server_properties['Disk Space'] = array(
            'fitness' => 'unsure',
            'setting' => tra('Unable to detect'),
            'message' => tra('Cannot determine the size of this disk drive.')
        );
    } elseif ($bytes < 200 * 1024 * 1024) {
        $server_properties['Disk Space'] = array(
            'fitness' => 'bad',
            'setting' => $free_space,
            'message' => tra('Less than 200MB of free disk space is available. Tiki will not fit in this amount of disk space.')
        );
    } elseif ($bytes < 250 * 1024 * 1024) {
        $server_properties['Disk Space'] = array(
            'fitness' => 'unsure',
            'setting' => $free_space,
            'message' => tra('Less than 250MB of free disk space is available. This would be quite tight for a Tiki installation. Tiki needs disk space for compiled templates and uploaded files.') . ' ' . tra('When the disk space is filled, users, including administrators, will not be able to log in to Tiki.') . ' ' . tra('This test cannot reliably check for quotas, so be warned that if this server makes use of them, there might be less disk space available than reported.')
        );
    } else {
        $server_properties['Disk Space'] = array(
            'fitness' => 'good',
            'setting' => $free_space,
            'message' => tra('More than 251MB of free disk space is available. Tiki will run smoothly, but there may be issues when the site grows (because of file uploads, for example).') . ' ' . tra('When the disk space is filled, users, including administrators, will not be able to log in to Tiki.') . ' ' . tra('This test cannot reliably check for quotas, so be warned that if this server makes use of them, there might be less disk space available than reported.')
        );
    }
} else {
        $server_properties['Disk Space'] = array(
            'fitness' => 'N/A',
            'setting' => 'N/A',
            'message' => tra('The PHP function disk_free_space is not available on your server, so the amount of available disk space can\'t be checked for.')
        );
}

if (! $standalone) {
    $tikiWikiVersion = new TWVersion();
    $tikiBaseVersion = $tikiWikiVersion->getBaseVersion();
}

/**
 * @param string $tikiBaseVersion
 * @param string $min The first minimum value in bounds, for example 15.0 if support 15.x or newer
 * @param string $max The first value out of bounds, for example 16.0 if only support up to 15.x
 *
 * @return bool
 */
function isVersionInRange($version, $min, $max)
{
    return version_compare($version, $min, '>=')
        && version_compare($version, $max, '<');
}

// PHP Version
if (version_compare(PHP_VERSION, '5.6.0', '<')) {
    $php_properties['PHP version'] = array(
        'fitness' => $standalone ?
            tra('unsure') :
            (isVersionInRange($tikiWikiVersion, '12.0', '16.0') ? tra('good') : tra('unsure')),
        'setting' => PHP_VERSION,
        'message' => 'Tiki 12.x LTS - Tiki 15.x LTS will work fine on this version of PHP. Please see http://doc.tiki.org/Requirements for details.'
    );
} elseif (version_compare(PHP_VERSION, '7.0.0', '<')) {
    $php_properties['PHP version'] = array(
        'fitness' => $standalone ?
            tra('unsure') :
            (isVersionInRange($tikiWikiVersion, '12.0', '19.0') ? tra('good') : tra('unsure')),
        'setting' => PHP_VERSION,
        'message' => 'Tiki 12.x LTS - TIki 18.x LTS will work fine on this version of PHP. Please see http://doc.tiki.org/Requirements for details.'
    );
} elseif (version_compare(PHP_VERSION, '7.1.0', '<')) {
    $php_properties['PHP version'] = array(
        'fitness' => $standalone ?
            tra('unsure') :
            (isVersionInRange($tikiWikiVersion, '18.0', '19.0') ? tra('good') : tra('unsure')),
        'setting' => PHP_VERSION,
        'message' => 'Tiki 18.x LTS will work fine on this version of PHP. Please see http://doc.tiki.org/Requirements for details.'
    );
} elseif (version_compare(PHP_VERSION, '7.2.0', '<')) {
    $php_properties['PHP version'] = array(
        'fitness' => $standalone ?
            tra('unsure') :
            (isVersionInRange($tikiWikiVersion, '18.0', '21.0') ? tra('good') : tra('unsure')),
        'setting' => PHP_VERSION,
        'message' => 'Tiki 18.x - Tiki 20.x will work fine on this version of PHP. Please see http://doc.tiki.org/Requirements for details.'
    );
} elseif (version_compare(PHP_VERSION, '7.3.0', '<')) {
    $php_properties['PHP version'] = array(
        'fitness' => $standalone ?
            tra('unsure') :
            (isVersionInRange($tikiWikiVersion, '18.0', '22.0') ? tra('good') : tra('unsure')),
        'setting' => PHP_VERSION,
        'message' => 'Tiki 18.x - Tiki 21.x will work fine on this version of PHP. Please see http://doc.tiki.org/Requirements for details.'
    );
} elseif (version_compare(PHP_VERSION, '7.4.0', '<')) {
    $php_properties['PHP version'] = array(
        'fitness' => $standalone ?
            tra('unsure') :
            (isVersionInRange($tikiWikiVersion, '21.0', '22.0') ? tra('good') : tra('unsure')),
        'setting' => PHP_VERSION,
        'message' => 'Tiki 21.x LTS will work fine on this version of PHP. Please see http://doc.tiki.org/Requirements for details.'
    );
} else {
    $php_properties['PHP version'] = array(
        'fitness' => $standalone ?
            tra('unsure') :
            (version_compare($tikiBaseVersion, '22.0', '>=') ? tra('good') : tra('unsure')),
        'setting' => PHP_VERSION,
        'message' => 'Tiki 22.x and newer will work fine on this version of PHP. Please see http://doc.tiki.org/Requirements for details.'
    );
}

// Check PHP command line version
if (function_exists('exec')) {
    $cliSearchList = array('php', 'php56', 'php5.6', 'php5.6-cli');
    $isUnix = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? false : true;
    $cliCommand = '';
    $cliVersion = '';
    foreach ($cliSearchList as $command) {
        if ($isUnix) {
            $output = exec('command -v ' . escapeshellarg($command) . ' 2>/dev/null');
        } else {
            $output = exec('where ' . escapeshellarg($command . '.exe'));
        }
        if (! $output) {
            continue;
        }

        $cliCommand = trim($output);
        exec(escapeshellcmd(trim($cliCommand)) . ' --version', $output);
        foreach ($output as $line) {
            $parts = explode(' ', $line);
            if ($parts[0] === 'PHP') {
                $cliVersion = $parts[1];
                break;
            }
        }
        break;
    }
    if ($cliCommand) {
        if (PHP_VERSION == $cliVersion) {
            $php_properties['PHP CLI version'] = array(
                'fitness' => tra('good'),
                'setting' => $cliVersion,
                'message' => 'The version of the command line executable of PHP (' . $cliCommand . ') is the same version as the web server version.',
            );
        } else {
            $php_properties['PHP CLI version'] = array(
                'fitness' => tra('unsure'),
                'setting' => $cliVersion,
                'message' => 'The version of the command line executable of PHP (' . $cliCommand . ') is not the same as the web server version.',
            );
        }
    } else {
        $php_properties['PHP CLI version'] = array(
            'fitness' => tra('unsure'),
            'setting' => '',
            'message' => 'Unable to determine the command line executable for PHP.',
        );
    }
}

// PHP Server API (SAPI)
if (substr(PHP_SAPI, 0, 3) === 'cgi') {
    $php_properties['PHP Server API'] = array(
        'fitness' => tra('info'),
        'setting' => PHP_SAPI,
        'message' => tra('PHP is being run as CGI. Feel free to use a threaded Apache MPM to increase performance.')
    );

    $php_sapi_info = array(
        'message' => tra('Looks like you are running PHP as FPM/CGI/FastCGI, you may be able to override some of your PHP configurations by add them to .user.ini files, see:'),
        'link' => 'http://php.net/manual/en/configuration.file.per-user.php'
    );
} elseif (substr(PHP_SAPI, 0, 3) === 'fpm') {
    $php_properties['PHP Server API'] = array(
        'fitness' => tra('info'),
        'setting' => PHP_SAPI,
        'message' => tra('PHP is being run using FPM (Fastcgi Process Manager). Feel free to use a threaded Apache MPM to increase performance.')
    );

    $php_sapi_info = array(
        'message' => tra('Looks like you are running PHP as FPM/CGI/FastCGI, you may be able to override some of your PHP configurations by add them to .user.ini files, see:'),
        'link' => 'http://php.net/manual/en/configuration.file.per-user.php'
    );
} else {
    if (substr(PHP_SAPI, 0, 6) === 'apache') {
        $php_sapi_info = array(
            'message' => tra('Looks like you are running PHP as a module in Apache, you may be able to override some of your PHP configurations by add them to .htaccess files, see:'),
            'link' => 'http://php.net/manual/en/configuration.changes.php#configuration.changes.apache'
        );
    }

    $php_properties['PHP Server API'] = array(
        'fitness' => tra('info'),
        'setting' => PHP_SAPI,
        'message' => tra('PHP is not being run as CGI. Be aware that PHP is not thread-safe and you should not use a threaded Apache MPM (like worker).')
    );
}

// ByteCode Cache
if (function_exists('apc_sma_info') && ini_get('apc.enabled')) {
    $php_properties['ByteCode Cache'] = array(
        'fitness' => tra('good'),
        'setting' => 'APC',
        'message' => tra('APC is being used as the ByteCode Cache, which increases performance if correctly configured. See Admin->Performance in the Tiki for more details.')
    );
} elseif (function_exists('xcache_info') && ( ini_get('xcache.cacher') == '1' || ini_get('xcache.cacher') == 'On' )) {
    $php_properties['ByteCode Cache'] = array(
        'fitness' => tra('good'),
        'setting' => 'xCache',
        'message' => tra('xCache is being used as the ByteCode Cache, which increases performance if correctly configured. See Admin->Performance in the Tiki for more details.')
    );
} elseif (function_exists('opcache_get_configuration') && (ini_get('opcache.enable') == 1 || ini_get('opcache.enable') == '1')) {
    $message = tra('OPcache is being used as the ByteCode Cache, which increases performance if correctly configured. See Admin->Performance in the Tiki for more details.');
    $fitness = tra('good');
    if (! checkOPCacheCompatibility()) {
        $message = tra('Some PHP versions may exhibit randomly issues with the OpCache leading to the server starting to fail to serve all PHP requests, your PHP version seems to
         be affected, despite the performance penalty, we would recommend disabling the OpCache if you experience random crashes.');
        $fitness = tra('unsure');
    }
    $php_properties['ByteCode Cache'] = array(
        'fitness' => $fitness,
        'setting' => 'OPcache',
        'message' => $message
    );
} elseif (function_exists('wincache_fcache_fileinfo')) {
    // Determine if version 1 or 2 is used. Version 2 does not support ocache

    if (function_exists('wincache_ocache_fileinfo')) {
        // Wincache version 1
        if (ini_get('wincache.ocenabled') == '1') {
            if (PHP_SAPI == 'cgi-fcgi') {
                $php_properties['ByteCode Cache'] = array(
                    'fitness' => tra('good'),
                    'setting' => 'WinCache',
                    'message' => tra('WinCache is being used as the ByteCode Cache, which increases performance if correctly configured. See Admin->Performance in the Tiki for more details.')
                );
            } else {
                $php_properties['ByteCode Cache'] = array(
                    'fitness' => tra('unsure'),
                    'setting' => 'WinCache',
                    'message' => tra('WinCache is being used as the ByteCode Cache, but the required CGI/FastCGI server API is apparently not being used.')
                );
            }
        } else {
            no_cache_found();
        }
    } else {
        // Wincache version 2 or higher
        if (ini_get('wincache.fcenabled') == '1') {
            if (PHP_SAPI == 'cgi-fcgi') {
                $php_properties['ByteCode Cache'] = array(
                    'fitness' => tra('info'),
                    'setting' => 'WinCache',
                    'message' => tra('WinCache version 2 or higher is being used as the FileCache. It does not support a ByteCode Cache.') . ' ' . tra('It is recommended to use Zend opcode cache as the ByteCode Cache.')
                );
            } else {
                $php_properties['ByteCode Cache'] = array(
                    'fitness' => tra('unsure'),
                    'setting' => 'WinCache',
                    'message' => tra('WinCache version 2 or higher is being used as the FileCache, but the required CGI/FastCGI server API is apparently not being used.') . ' ' . tra('It is recommended to use Zend opcode cache as the ByteCode Cache.')
                );
            }
        } else {
            no_cache_found();
        }
    }
} else {
    no_cache_found();
}


// memory_limit
$memory_limit = ini_get('memory_limit');
$s = trim($memory_limit);
$last = strtolower(substr($s, -1));
if (! is_numeric($last)) {
    $s = substr($s, 0, -1);
}
switch ($last) {
    case 'g':
        $s *= 1024;
        // no break
    case 'm':
        $s *= 1024;
        // no break
    case 'k':
        $s *= 1024;
}
if ($s >= 160 * 1024 * 1024) {
    $php_properties['memory_limit'] = array(
        'fitness' => tra('good'),
        'setting' => $memory_limit,
        'message' => tra('The memory_limit is at') . ' ' . $memory_limit . '. ' . tra('This is known to support smooth functioning even for bigger sites.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
} elseif ($s < 160 * 1024 * 1024 && $s > 127 * 1024 * 1024) {
    $php_properties['memory_limit'] = array(
        'fitness' => tra('unsure') ,
        'setting' => $memory_limit,
        'message' => tra('The memory_limit is at') . ' ' . $memory_limit . '. ' . tra('This will normally work, but the site might run into problems when it grows.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
} elseif ($s == -1) {
    $php_properties['memory_limit'] = array(
        'fitness' => tra('unsure') ,
        'setting' => $memory_limit,
        'message' => tra("The memory_limit is unlimited. This is not necessarily bad, but it's a good idea to limit this on productions servers in order to eliminate unexpectedly greedy scripts.") . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
} else {
    $php_properties['memory_limit'] = array(
        'fitness' => tra('bad'),
        'setting' => $memory_limit,
        'message' => tra('Your memory_limit is at') . ' ' . $memory_limit . '. ' . tra('This is known to cause issues! The memory_limit should be increased to at least 128M, which is the PHP default.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
}

// session.save_handler
$s = ini_get('session.save_handler');
if ($s != 'files') {
    $php_properties['session.save_handler'] = array(
        'fitness' => tra('unsure'),
        'setting' => $s,
        'message' => tra('The session.save_handler should be set to \'files\'.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
} else {
    $php_properties['session.save_handler'] = array(
        'fitness' => tra('good'),
        'setting' => $s,
        'message' => tra('Well set! The default setting of \'files\' is recommended for Tiki.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
}

// session.save_path
$s = ini_get('session.save_path');
if ($php_properties['session.save_handler']['setting'] == 'files') {
    if (empty($s) || ! is_writable($s)) {
        $php_properties['session.save_path'] = array(
            'fitness' => tra('bad'),
            'setting' => $s,
            'message' => tra('The session.save_path must be writable.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
        );
    } else {
        $php_properties['session.save_path'] = array(
            'fitness' => tra('good'),
            'setting' => $s,
            'message' => tra('The session.save_path is writable.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
        );
    }
} else {
    if (empty($s) || ! is_writable($s)) {
        $php_properties['session.save_path'] = array(
            'fitness' => tra('unsure'),
            'setting' => $s,
            'message' => tra('If you would be using the recommended session.save_handler setting of \'files\', the session.save_path would have to be writable. Currently it is not.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
        );
    } else {
        $php_properties['session.save_path'] = array(
            'fitness' => tra('info'),
            'setting' => $s,
            'message' => tra('The session.save_path is writable.') . tra('It doesn\'t matter though, since your session.save_handler is not set to \'files\'.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
        );
    }
}

$s = ini_get('session.gc_probability');
$php_properties['session.gc_probability'] = array(
    'fitness' => tra('info'),
    'setting' => $s,
    'message' => tra('In conjunction with gc_divisor is used to manage probability that the gc (garbage collection) routine is started.')
);

$s = ini_get('session.gc_divisor');
$php_properties['session.gc_divisor'] = array(
    'fitness' => tra('info'),
    'setting' => $s,
    'message' => tra('Coupled with session.gc_probability defines the probability that the gc (garbage collection) process is started on every session initialization. The probability is calculated by using gc_probability/gc_divisor, e.g. 1/100 means there is a 1% chance that the GC process starts on each request.')
);

$s = ini_get('session.gc_maxlifetime');
$php_properties['session.gc_maxlifetime'] = array(
    'fitness' => tra('info'),
    'setting' => $s . 's',
    'message' => tra('Specifies the number of seconds after which data will be seen as \'garbage\' and potentially cleaned up. Garbage collection may occur during session start.')
);

// test session work
@session_start();

if (empty($_SESSION['tiki-check'])) {
    $php_properties['session'] = array(
        'fitness' => tra('unsure'),
        'setting' => tra('empty'),
        'message' => tra('The session is empty. Try reloading the page and, if this message is displayed again, there may be a problem with the server setup.')
    );
    $_SESSION['tiki-check'] = 1;
} else {
    $php_properties['session'] = array(
        'fitness' => tra('good'),
        'setting' => 'ok',
        'message' => tra('This appears to work.')
    );
}

// zlib.output_compression
$s = ini_get('zlib.output_compression');
if ($s) {
    $php_properties['zlib.output_compression'] = array(
        'fitness' => tra('info'),
        'setting' => 'On',
        'message' => tra('zlib output compression is turned on. This saves bandwidth. On the other hand, turning it off would reduce CPU usage. The appropriate choice can be made for this Tiki.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
} else {
    $php_properties['zlib.output_compression'] = array(
        'fitness' => tra('info'),
        'setting' => 'Off',
        'message' => tra('zlib output compression is turned off. This reduces CPU usage. On the other hand, turning it on would save bandwidth. The appropriate choice can be made for this Tiki.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
}

// default_charset
$s = ini_get('default_charset');
if (strtolower($s) == 'utf-8') {
    $php_properties['default_charset'] = array(
        'fitness' => tra('good'),
        'setting' => $s,
        'message' => tra('Correctly set! Tiki is fully UTF-8 and so should be this installation.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
} else {
    $php_properties['default_charset'] = array(
        'fitness' => tra('unsure'),
        'setting' => $s,
        'message' => tra('default_charset should be UTF-8 as Tiki is fully UTF-8. Please check the php.ini file.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
}

// date.timezone
$s = ini_get('date.timezone');
if (empty($s)) {
    $php_properties['date.timezone'] = array(
        'fitness' => tra('unsure'),
        'setting' => $s,
        'message' => tra('No time zone is set! While there are a number of fallbacks in PHP to determine the time zone, the only reliable solution is to set it explicitly in php.ini! Please check the value of date.timezone in php.ini.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
} else {
    $php_properties['date.timezone'] = array(
        'fitness' => tra('good'),
        'setting' => $s,
        'message' => tra('Well done! Having a time zone set protects the site from related errors.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
}

// file_uploads
$s = ini_get('file_uploads');
if ($s) {
    $php_properties['file_uploads'] = array(
        'fitness' => tra('good'),
        'setting' => 'On',
        'message' => tra('Files can be uploaded to Tiki.')
    );
} else {
    $php_properties['file_uploads'] = array(
        'fitness' => tra('bad'),
        'setting' => 'Off',
        'message' => tra('Files cannot be uploaded to Tiki.')
    );
}

// max_execution_time
$s = ini_get('max_execution_time');
if ($s >= 30 && $s <= 90) {
    $php_properties['max_execution_time'] = array(
        'fitness' => tra('good'),
        'setting' => $s . 's',
        'message' => tra('The max_execution_time is at') . ' ' . $s . '. ' . tra('This is a good value for production sites. If timeouts are experienced (such as when performing admin functions) this may need to be increased nevertheless.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
} elseif ($s == -1 || $s == 0) {
    $php_properties['max_execution_time'] = array(
        'fitness' => tra('unsure'),
        'setting' => $s . 's',
        'message' => tra('The max_execution_time is unlimited.') . ' ' . tra('This is not necessarily bad, but it\'s a good idea to limit this time on productions servers in order to eliminate unexpectedly long running scripts.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
} elseif ($s > 90) {
    $php_properties['max_execution_time'] = array(
        'fitness' => tra('unsure'),
        'setting' => $s . 's',
        'message' => tra('The max_execution_time is at') . ' ' . $s . '. ' . tra('This is not necessarily bad, but it\'s a good idea to limit this time on productions servers in order to eliminate unexpectedly long running scripts.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
} else {
    $php_properties['max_execution_time'] = array(
        'fitness' => tra('bad'),
        'setting' => $s . 's',
        'message' => tra('The max_execution_time is at') . ' ' . $s . '. ' . tra('It is likely that some scripts, such as admin functions, will not finish in this time! The max_execution_time should be incresed to at least 30s.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
}

// max_input_time
$s = ini_get('max_input_time');
if ($s >= 30 && $s <= 90) {
    $php_properties['max_input_time'] = array(
        'fitness' => tra('good'),
        'setting' => $s . 's',
        'message' => tra('The max_input_time is at') . ' ' . $s . '. ' . tra('This is a good value for production sites. If timeouts are experienced (such as when performing admin functions) this may need to be increased nevertheless.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
} elseif ($s == -1 || $s == 0) {
    $php_properties['max_input_time'] = array(
        'fitness' => tra('unsure'),
        'setting' => $s . 's',
        'message' => tra('The max_input_time is unlimited.') . ' ' . tra('This is not necessarily bad, but it\'s a good idea to limit this time on productions servers in order to eliminate unexpectedly long running scripts.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
} elseif ($s > 90) {
    $php_properties['max_input_time'] = array(
        'fitness' => tra('unsure'),
        'setting' => $s . 's',
        'message' => tra('The max_input_time is at') . ' ' . $s . '. ' . tra('This is not necessarily bad, but it\'s a good idea to limit this time on productions servers in order to eliminate unexpectedly long running scripts.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
} else {
    $php_properties['max_input_time'] = array(
        'fitness' => tra('bad'),
        'setting' => $s . 's',
        'message' => tra('The max_input_time is at') . ' ' . $s . '. ' . tra('It is likely that some scripts, such as admin functions, will not finish in this time! The max_input_time should be increased to at least 30 seconds.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
}
// max_file_uploads
$max_file_uploads = ini_get('max_file_uploads');
if ($max_file_uploads) {
    $php_properties['max_file_uploads'] = array(
        'fitness' => tra('info'),
        'setting' => $max_file_uploads,
        'message' => tra('The max_file_uploads is at') . ' ' . $max_file_uploads . '. ' . tra('This is the maximum number of files allowed to be uploaded simultaneously.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
} else {
    $php_properties['max_file_uploads'] = array(
        'fitness' => tra('info'),
        'setting' => 'Not Available',
        'message' => tra('The maximum number of files allowed to be uploaded is not available')
    );
}
// upload_max_filesize
$upload_max_filesize = ini_get('upload_max_filesize');
$s = trim($upload_max_filesize);
$last = strtolower(substr($s, -1));
$s = substr($s, 0, -1);
switch ($last) {
    case 'g':
        $s *= 1024;
        // no break
    case 'm':
        $s *= 1024;
        // no break
    case 'k':
        $s *= 1024;
}
if ($s >= 8 * 1024 * 1024) {
    $php_properties['upload_max_filesize'] = array(
        'fitness' => tra('good'),
        'setting' => $upload_max_filesize,
        'message' => tra('The upload_max_filesize is at') . ' ' . $upload_max_filesize . '. ' . tra('Quite large files can be uploaded, but keep in mind to set the script timeouts accordingly.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
} elseif ($s == 0) {
    $php_properties['upload_max_filesize'] = array(
        'fitness' => tra('unsure'),
        'setting' => $upload_max_filesize,
        'message' => tra('The upload_max_filesize is at') . ' ' . $upload_max_filesize . '. ' . tra('Upload size is unlimited and this not advised. A user could mistakenly upload a very large file which could fill up the disk. This value should be set to accommodate the realistic needs of the site.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
} else {
    $php_properties['upload_max_filesize'] = array(
        'fitness' => tra('unsure'),
        'setting' => $upload_max_filesize,
        'message' => tra('The upload_max_filesize is at') . ' ' . $upload_max_filesize . '. ' . tra('This is not a bad amount, but be sure the level is high enough to accommodate the needs of the site.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
}

// post_max_size
$post_max_size = ini_get('post_max_size');
$s = trim($post_max_size);
$last = strtolower(substr($s, -1));
$s = substr($s, 0, -1);
switch ($last) {
    case 'g':
        $s *= 1024;
        // no break
    case 'm':
        $s *= 1024;
        // no break
    case 'k':
        $s *= 1024;
}
if ($s >= 8 * 1024 * 1024) {
    $php_properties['post_max_size'] = array(
        'fitness' => tra('good'),
        'setting' => $post_max_size,
        'message' => tra('The post_max_size is at') . ' ' . $post_max_size . '. ' . tra('Quite large files can be uploaded, but keep in mind to set the script timeouts accordingly.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
} else {
    $php_properties['post_max_size'] = array(
        'fitness' => tra('unsure'),
        'setting' => $post_max_size,
        'message' => tra('The post_max_size is at') . ' ' . $post_max_size . '. ' . tra('This is not a bad amount, but be sure the level is high enough to accommodate the needs of the site.') . ' <a href="#php_conf_info">' . tra('How to change this value') . '</a>'
    );
}

// PHP Extensions
// fileinfo
$s = extension_loaded('fileinfo');
if ($s) {
    $php_properties['fileinfo'] = array(
        'fitness' => tra('good'),
        'setting' => 'Loaded',
        'message' => tra("The fileinfo extension is needed for the 'Validate uploaded file content' preference.")
    );
} else {
    $php_properties['fileinfo'] = array(
        'fitness' => tra('unsure'),
        'setting' => 'Not available',
        'message' => tra("The fileinfo extension is needed for the 'Validate uploaded file content' preference.")
    );
}

// intl
$s = extension_loaded('intl');
if ($s) {
    $php_properties['intl'] = array(
        'fitness' => tra('good'),
        'setting' => 'Loaded',
        'message' => tra("The intl extension is required for Tiki 15 and newer.")
    );
} else {
    $php_properties['intl'] = array(
        'fitness' => tra('unsure'),
        'setting' => 'Not available',
        'message' => tra("intl extension is preferred for Tiki 15 and newer. Because is not available, the filters for text will not be able to detect the language and will use a generic range of characters as letters.")
    );
}

// GD
$s = extension_loaded('gd');
if ($s && function_exists('gd_info')) {
    $gd_info = gd_info();
    $im = $ft = null;
    if (function_exists('imagecreate')) {
        $im = @imagecreate(110, 20);
    }
    if (function_exists('imageftbbox')) {
        $ft = @imageftbbox(12, 0, $font, 'test');
    }
    if ($im && $ft) {
        $php_properties['gd'] = array(
            'fitness' => tra('good'),
            'setting' => $gd_info['GD Version'],
            'message' => tra('The GD extension is needed for manipulation of images and for CAPTCHA images.')
        );
        imagedestroy($im);
    } elseif ($im) {
        $php_properties['gd'] = array(
                'fitness' => tra('unsure'),
                'setting' => $gd_info['GD Version'],
                'message' => tra('The GD extension is loaded, and Tiki can create images, but the FreeType extension is needed for CAPTCHA text generation.')
            );
            imagedestroy($im);
    } else {
        $php_properties['gd'] = array(
            'fitness' => tra('unsure'),
            'setting' => 'Dysfunctional',
            'message' => tra('The GD extension is loaded, but Tiki is unable to create images. Please check your GD library configuration.')
        );
    }
} else {
    $php_properties['gd'] = array(
        'fitness' => tra('bad'),
        'setting' => 'Not available',
        'message' => tra('The GD extension is needed for manipulation of images and for CAPTCHA images.')
    );
}

// Image Magick
$s = class_exists('Imagick');
if ($s) {
    $image = new Imagick();
    $image->newImage(100, 100, new ImagickPixel('red'));
    if ($image) {
        $php_properties['Image Magick'] = array(
            'fitness' => tra('good'),
            'setting' => 'Available',
            'message' => tra('ImageMagick is used as a fallback in case GD is not available.')
        );
        $image->destroy();
    } else {
        $php_properties['Image Magick'] = array(
            'fitness' => tra('unsure'),
            'setting' => 'Dysfunctional',
            'message' => tra('ImageMagick is used as a fallback in case GD is not available.') . tra('ImageMagick is available, but unable to create images. Please check your ImageMagick configuration.')
            );
    }
} else {
    $php_properties['Image Magick'] = array(
        'fitness' => tra('info'),
        'setting' => 'Not Available',
        'message' => tra('ImageMagick is used as a fallback in case GD is not available.')
        );
}

// mbstring
$s = extension_loaded('mbstring');
if ($s) {
    $func_overload = ini_get('mbstring.func_overload');
    if ($func_overload == 0 && function_exists('mb_split')) {
        $php_properties['mbstring'] = array(
            'fitness' => tra('good'),
            'setting' => 'Loaded',
            'message' => tra('mbstring extension is needed for an UTF-8 compatible lower case filter, in the admin search for example.')
        );
    } elseif ($func_overload != 0) {
        $php_properties['mbstring'] = array(
            'fitness' => tra('unsure'),
            'setting' => 'Badly configured',
            'message' => tra('mbstring extension is loaded, but mbstring.func_overload = ' . ' ' . $func_overload . '.' . ' ' . 'Tiki only works with mbstring.func_overload = 0. Please check the php.ini file.')
        );
    } else {
        $php_properties['mbstring'] = array(
            'fitness' => tra('bad'),
            'setting' => 'Badly installed',
            'message' => tra('mbstring extension is loaded, but missing important functions such as mb_split(). Reinstall it with --enable-mbregex or ask your a server administrator to do it.')
        );
    }
} else {
    $php_properties['mbstring'] = array(
        'fitness' => tra('bad'),
        'setting' => 'Not available',
        'message' => tra('mbstring extension is needed for an UTF-8 compatible lower case filter.')
    );
}

// calendar
$s = extension_loaded('calendar');
if ($s) {
    $php_properties['calendar'] = array(
        'fitness' => tra('good'),
        'setting' => 'Loaded',
        'message' => tra('calendar extension is needed by Tiki.')
    );
} else {
    $php_properties['calendar'] = array(
        'fitness' => tra('bad'),
        'setting' => 'Not available',
        'message' => tra('calendar extension is needed by Tiki.') . ' ' . tra('The calendar feature of Tiki will not function without this.')
    );
}

// ctype
$s = extension_loaded('ctype');
if ($s) {
    $php_properties['ctype'] = array(
        'fitness' => tra('good'),
        'setting' => 'Loaded',
        'message' => tra('ctype extension is needed by Tiki.')
    );
} else {
    $php_properties['ctype'] = array(
        'fitness' => tra('bad'),
        'setting' => 'Not available',
        'message' => tra('ctype extension is needed by Tiki.')
    );
}

// libxml
$s = extension_loaded('libxml');
if ($s) {
    $php_properties['libxml'] = array(
        'fitness' => tra('good'),
        'setting' => 'Loaded',
        'message' => tra('This extension is needed for the dom extension (see below).')
    );
} else {
    $php_properties['libxml'] = array(
        'fitness' => tra('bad'),
        'setting' => 'Not available',
        'message' => tra('This extension is needed for the dom extension (see below).')
    );
}

// dom (depends on libxml)
$s = extension_loaded('dom');
if ($s) {
    $php_properties['dom'] = array(
        'fitness' => tra('good'),
        'setting' => 'Loaded',
        'message' => tra('This extension is needed by Tiki')
    );
} else {
    $php_properties['dom'] = array(
        'fitness' => tra('bad'),
        'setting' => 'Not available',
        'message' => tra('This extension is needed by Tiki')
    );
}

$s = extension_loaded('ldap');
if ($s) {
    $php_properties['LDAP'] = array(
        'fitness' => tra('good'),
        'setting' => 'Loaded',
        'message' => tra('This extension is needed to connect Tiki to an LDAP server. More info at: http://doc.tiki.org/LDAP ')
    );
} else {
    $php_properties['LDAP'] = array(
        'fitness' => tra('info'),
        'setting' => 'Not available',
        'message' => tra('Tiki will not be able to connect to an LDAP server as the needed PHP extension is missing. More info at: http://doc.tiki.org/LDAP')
    );
}

$s = extension_loaded('memcached');
if ($s) {
    $php_properties['memcached'] = array(
        'fitness' => tra('good'),
        'setting' => 'Loaded',
        'message' => tra('This extension can be used to speed up Tiki by saving sessions as well as wiki and forum data on a memcached server.')
    );
} else {
    $php_properties['memcached'] = array(
        'fitness' => tra('info'),
        'setting' => 'Not available',
        'message' => tra('This extension can be used to speed up Tiki by saving sessions as well as wiki and forum data on a memcached server.')
    );
}

$s = extension_loaded('redis');
if ($s) {
    $php_properties['redis'] = array(
        'fitness' => tra('good'),
        'setting' => 'Loaded',
        'message' => tra('This extension can be used to speed up Tiki by saving wiki and forum data on a redis server.')
    );
} else {
    $php_properties['redis'] = array(
        'fitness' => tra('info'),
        'setting' => 'Not available',
        'message' => tra('This extension can be used to speed up Tiki by saving wiki and forum data on a redis server.')
    );
}

$s = extension_loaded('ssh2');
if ($s) {
    $php_properties['SSH2'] = array(
        'fitness' => tra('good'),
        'setting' => 'Loaded',
        'message' => tra('This extension is needed for the show.tiki.org tracker field type, up to Tiki 17.')
    );
} else {
    $php_properties['SSH2'] = array(
        'fitness' => tra('info'),
        'setting' => 'Not available',
        'message' => tra('This extension is needed for the show.tiki.org tracker field type, up to Tiki 17.')
    );
}

$s = extension_loaded('soap');
if ($s) {
    $php_properties['soap'] = array(
        'fitness' => tra('good'),
        'setting' => 'Loaded',
        'message' => tra('This extension is used by Tiki for some types of web services.')
    );
} else {
    $php_properties['soap'] = array(
        'fitness' => tra('info'),
        'setting' => 'Not available',
        'message' => tra('This extension is used by Tiki for some types of web services.')
    );
}

$s = extension_loaded('carray');
if ($s) {
    $php_properties['carray'] = array(
        'fitness' => tra('good'),
        'setting' => 'Loaded',
        'message' => tra('High-performance scientific computing library for PHP')
    );
} else {
    $php_properties['carray'] = array(
        'fitness' => tra('info'),
        'setting' => 'Not available',
        'message' => tra('High-performance scientific computing library for PHP')
    );
}

$s = extension_loaded('curl');
if ($s) {
    $php_properties['curl'] = array(
        'fitness' => tra('good'),
        'setting' => 'Loaded',
        'message' => tra('This extension is required for H5P.')
    );
} else {
    $php_properties['curl'] = array(
        'fitness' => tra('bad'),
        'setting' => 'Not available',
        'message' => tra('This extension is required for H5P.')
    );
}

$s = extension_loaded('json');
if ($s) {
    $php_properties['json'] = array(
        'fitness' => tra('good'),
        'setting' => 'Loaded',
        'message' => tra('This extension is required for many features in Tiki.')
    );
} else {
    $php_properties['json'] = array(
        'fitness' => tra('bad'),
        'setting' => 'Not available',
        'message' => tra('This extension is required for many features in Tiki.')
    );
}

/*
*    If TortoiseSVN 1.7 is used, it uses an sqlite database to store the SVN info. sqlite3 extention needed to read svn info.
*/
if (is_file('.svn/wc.db')) {
    // It's an TortoiseSVN 1.7+ installation
    $s = extension_loaded('sqlite3');
    if ($s) {
        $php_properties['sqlite3'] = array(
            'fitness' => tra('good'),
            'setting' => 'Loaded',
            'message' => tra('This extension is used to interpret SVN information for TortoiseSVN 1.7 or higher.')
            );
    } else {
        $php_properties['sqlite3'] = array(
            'fitness' => tra('unsure'),
            'setting' => 'Not available',
            'message' => tra('This extension is used to interpret SVN information for TortoiseSVN 1.7 or higher.')
            );
    }
}

$s = extension_loaded('sodium');
$msg = tra('This extension is required to encrypt data such as CSRF ticket cookie and user data.') . PHP_EOL;
$msg .= tra('Enable safe, encrypted storage of data such as passwords. Since Tiki 22, Sodium lib (included in PHP 7.2 core) is used for the User Encryption feature and improves encryption in other features, when available');
if ($s) {
    $php_properties['sodium'] = array(
        'fitness' => tra('good'),
        'setting' => 'Loaded',
        'message' => $msg
    );
} else {
    $php_properties['sodium'] = array(
        'fitness' => tra('unsure'),
        'setting' => 'Not available',
        'message' => $msg
    );
}

$s = extension_loaded('openssl');
$msg = tra('Enable safe, encrypted storage of data such as passwords. Tiki 21 and earlier versions, require OpenSSL for the User Encryption feature and improves encryption in other features, when available.');
if (! $standalone) {
    $msg .= ' ' . tra('Tiki still uses OpenSSL to decrypt user data encrypted with OpenSSL, when converting that data to Sodium (PHP 7.2+).') . ' ' . tra('Please check the \'User Data Encryption\' section to see if there is user data encrypted with OpenSSL.');
}
if ($s) {
    $php_properties['openssl'] = array(
        'fitness' => tra('good'),
        'setting' => 'Loaded',
        'message' => $msg
    );
} else {
    $php_properties['openssl'] = array(
        'fitness' => tra('unsure'),
        'setting' => 'Not available',
        'message' => $msg
    );
}


$s = extension_loaded('mcrypt');
$msg = tra('MCrypt is abandonware and is being phased out. Starting in version 18 up to 21, Tiki uses OpenSSL where it previously used MCrypt, except perhaps via third-party libraries.');
if (! $standalone) {
    $msg .= ' ' . tra('Tiki still uses MCrypt to decrypt user data encrypted with MCrypt, when converting that data to OpenSSL.') . ' ' . tra('Please check the \'User Data Encryption\' section to see if there is user data encrypted with MCrypt.');
}
if ($s) {
    $php_properties['mcrypt'] = array(
        'fitness' => tra('info'),
        'setting' => 'Loaded',
        'message' => $msg
    );
} else {
    $php_properties['mcrypt'] = array(
        'fitness' => tra('info'),
        'setting' => 'Not available',
        'message' => $msg
    );
}


if (! $standalone) {
    // check Zend captcha will work which depends on \Laminas\Math\Rand
    $captcha = new Laminas\Captcha\Dumb();
    $math_random = array(
        'fitness' => tra('good'),
        'setting' => 'Available',
        'message' => tra('Ability to generate random numbers, useful for example for CAPTCHA and other security features.'),
    );
    try {
        $captchaId = $captcha->getId();    // simple test for missing random generator
    } catch (Exception $e) {
        $math_random['fitness'] = tra('unsure');
        $math_random['setting'] = 'Not available';
    }
    $php_properties['\Laminas\Math\Rand'] = $math_random;
}


$s = extension_loaded('iconv');
$msg = tra('This extension is required and used frequently in validation functions invoked within Zend Framework.');
if ($s) {
    $php_properties['iconv'] = array(
        'fitness' => tra('good'),
        'setting' => 'Loaded',
        'message' => $msg
    );
} else {
    $php_properties['iconv'] = array(
        'fitness' => tra('bad'),
        'setting' => 'Not available',
        'message' => $msg
    );
}

// Check for existence of eval()
// eval() is a language construct and not a function
// so function_exists() doesn't work
$s = eval('return 42;');
if ($s == 42) {
    $php_properties['eval()'] = array(
        'fitness' => tra('good'),
        'setting' => 'Available',
        'message' => tra('The eval() function is required by the Smarty templating engine.')
    );
} else {
    $php_properties['eval()'] = array(
        'fitness' => tra('bad'),
        'setting' => 'Not available',
        'message' => tra('The eval() function is required by the Smarty templating engine.') . ' ' . tra('You will get "Please contact support about" messages instead of modules. eval() is most probably disabled via Suhosin.')
    );
}

// Zip Archive class
$s = class_exists('ZipArchive');
if ($s) {
    $php_properties['ZipArchive class'] = array(
        'fitness' => tra('good'),
        'setting' => 'Available',
        'message' => tra('The ZipArchive class is needed for features such as XML Wiki Import/Export and PluginArchiveBuilder.')
        );
} else {
    $php_properties['ZipArchive class'] = array(
        'fitness' => tra('unsure'),
        'setting' => 'Not Available',
        'message' => tra('The ZipArchive class is needed for features such as XML Wiki Import/Export and PluginArchiveBuilder.')
        );
}

// DateTime class
$s = class_exists('DateTime');
if ($s) {
    $php_properties['DateTime class'] = array(
        'fitness' => tra('good'),
        'setting' => 'Available',
        'message' => tra('The DateTime class is needed for the WebDAV feature.')
        );
} else {
    $php_properties['DateTime class'] = array(
        'fitness' => tra('unsure'),
        'setting' => 'Not Available',
        'message' => tra('The DateTime class is needed for the WebDAV feature.')
        );
}

// Xdebug
$has_xdebug = function_exists('xdebug_get_code_coverage') && is_array(xdebug_get_code_coverage());
if ($has_xdebug) {
    $php_properties['Xdebug'] = array(
        'fitness' => tra('info'),
        'setting' => 'Loaded',
        'message' => tra('Xdebug can be very handy for a development server, but it might be better to disable it when on a production server.')
    );
} else {
    $php_properties['Xdebug'] = array(
        'fitness' => tra('info'),
        'setting' => 'Not Available',
        'message' => tra('Xdebug can be very handy for a development server, but it might be better to disable it when on a production server.')
    );
}

// Get MySQL properties and check them
$mysql_properties = false;
$mysql_variables = false;
if ($connection || ! $standalone) {
    // MySQL version
    $query = 'SELECT VERSION();';
    $result = query($query, $connection);
    $mysql_version = $result[0]['VERSION()'];
    $isMariaDB = preg_match('/mariadb/i', $mysql_version);
    $minVersion = $isMariaDB ? '5.5' : '5.7';
    $s = version_compare($mysql_version, $minVersion, '>=');
    $mysql_properties['Version'] = array(
        'fitness' => $s ? tra('good') : tra('bad'),
        'setting' => $mysql_version,
        'message' => tra('Tiki requires MariaDB >= 5.5 or MySQL >= 5.7')
    );

    // max_allowed_packet
    $query = "SHOW VARIABLES LIKE 'max_allowed_packet'";
    $result = query($query, $connection);
    $s = $result[0]['Value'];
    $max_allowed_packet = $s / 1024 / 1024;
    if ($s >= 8 * 1024 * 1024) {
        $mysql_properties['max_allowed_packet'] = array(
            'fitness' => tra('good'),
            'setting' => $max_allowed_packet . 'M',
            'message' => tra('The max_allowed_packet setting is at') . ' ' . $max_allowed_packet . 'M. ' . tra('Quite large files can be uploaded, but keep in mind to set the script timeouts accordingly.') . ' ' . tra('This limits the size of binary files that can be uploaded to Tiki, when storing files in the database. Please see: <a href="http://doc.tiki.org/File-Storage">file storage</a>.')
        );
    } else {
        $mysql_properties['max_allowed_packet'] = array(
            'fitness' => tra('unsure'),
            'setting' => $max_allowed_packet . 'M',
            'message' => tra('The max_allowed_packet setting is at') . ' ' . $max_allowed_packet . 'M. ' . tra('This is not a bad amount, but be sure the level is high enough to accommodate the needs of the site.') . ' ' . tra('This limits the size of binary files that can be uploaded to Tiki, when storing files in the database. Please see: <a href="http://doc.tiki.org/File-Storage">file storage</a>.')
        );
    }

    // UTF-8 MB4 test (required for Tiki19+)
    $query = "SELECT COUNT(*) FROM `information_schema`.`character_sets` WHERE `character_set_name` = 'utf8mb4';";
    $result = query($query, $connection);
    if (! empty($result[0]['COUNT(*)'])) {
        $mysql_properties['utf8mb4'] = array(
            'fitness' => tra('good'),
            'setting' => 'available',
            'message' => tra('Your database supports the utf8mb4 character set required in Tiki19 and above.')
        );
    } else {
        $mysql_properties['utf8mb4'] = array(
            'fitness' => tra('bad'),
            'setting' => 'not available',
            'message' => tra('Your database does not support the utf8mb4 character set required in Tiki19 and above. You need to upgrade your mysql or mariadb installation.')
        );
    }

    // UTF-8 Charset
    // Tiki communication is done using UTF-8 MB4 (required for Tiki19+)
    $charset_types = "client connection database results server system";
    foreach (explode(' ', $charset_types) as $type) {
        $query = "SHOW VARIABLES LIKE 'character_set_" . $type . "';";
        $result = query($query, $connection);
        foreach ($result as $value) {
            if ($value['Value'] == 'utf8mb4') {
                $mysql_properties[$value['Variable_name']] = array(
                    'fitness' => tra('good'),
                    'setting' => $value['Value'],
                    'message' => tra('Tiki is fully utf8mb4 and so should be every part of the stack.')
                );
            } else {
                $mysql_properties[$value['Variable_name']] = array(
                    'fitness' => tra('unsure'),
                    'setting' => $value['Value'],
                    'message' => tra('On a fresh install everything should be set to utf8mb4 to avoid unexpected results. For further information please see <a href="http://doc.tiki.org/Understanding-Encoding">Understanding Encoding</a>.')
                );
            }
        }
    }
    // UTF-8 is correct for character_set_system
    // Because mysql does not allow any config to change this value, and character_set_system is overwritten by the other character_set_* variables anyway. They may change this default in later versions.
    $query = "SHOW VARIABLES LIKE 'character_set_system';";
    $result = query($query, $connection);
    foreach ($result as $value) {
        if (substr($value['Value'], 0, 4) == 'utf8') {
            $mysql_properties[$value['Variable_name']] = array(
                'fitness' => tra('good'),
                'setting' => $value['Value'],
                'message' => tra('Tiki is fully utf8mb4 but some database underlying variables are set to utf8 by the database engine and cannot be modified.')
            );
        } else {
            $mysql_properties[$value['Variable_name']] = array(
                'fitness' => tra('unsure'),
                'setting' => $value['Value'],
                'message' => tra('On a fresh install everything should be set to utf8mb4 or utf8 to avoid unexpected results. For further information please see <a href="http://doc.tiki.org/Understanding-Encoding">Understanding Encoding</a>.')
            );
        }
    }
    // UTF-8 Collation
    $collation_types = "connection database server";
    foreach (explode(' ', $collation_types) as $type) {
        $query = "SHOW VARIABLES LIKE 'collation_" . $type . "';";
        $result = query($query, $connection);
        foreach ($result as $value) {
            if (substr($value['Value'], 0, 7) == 'utf8mb4') {
                $mysql_properties[$value['Variable_name']] = array(
                    'fitness' => tra('good'),
                    'setting' => $value['Value'],
                    'message' => tra('Tiki is fully utf8mb4 and so should be every part of the stack. utf8mb4_unicode_ci is the default collation for Tiki.')
                );
            } else {
                $mysql_properties[$value['Variable_name']] = array(
                    'fitness' => tra('unsure'),
                    'setting' => $value['Value'],
                    'message' => tra('On a fresh install everything should be set to utf8mb4 to avoid unexpected results. utf8mb4_unicode_ci is the default collation for Tiki. For further information please see <a href="http://doc.tiki.org/Understanding-Encoding">Understanding Encoding</a>.')
                );
            }
        }
    }

    // slow_query_log
    $query = "SHOW VARIABLES LIKE 'slow_query_log'";
    $result = query($query, $connection);
    $s = $result[0]['Value'];
    if ($s == 'OFF') {
        $mysql_properties['slow_query_log'] = array(
            'fitness' => tra('info'),
            'setting' => $s,
            'message' => tra('MySQL doesn\'t log slow queries. If performance issues are noticed, this could be enabled, but keep in mind that the logging itself slows MySQL down.')
        );
    } else {
        $mysql_properties['slow_query_log'] = array(
            'fitness' => tra('info'),
            'setting' => $s,
            'message' => tra('MySQL logs slow queries. If no performance issues are noticed, this should be disabled on a production site as it slows MySQL down.')
        );
    }

    // MySQL SSL
    $query = 'show variables like "have_ssl";';
    $result = query($query, $connection);
    if (empty($result)) {
        $query = 'show variables like "have_openssl";';
        $result = query($query, $connection);
    }
    $haveMySQLSSL = false;
    if (! empty($result)) {
        $ssl = $result[0]['Value'];
        $haveMySQLSSL = $ssl == 'YES';
    }
    $s = '';
    if ($haveMySQLSSL) {
        $query = 'show status like "Ssl_cipher";';
        $result = query($query, $connection);
        $isSSL = ! empty($result[0]['Value']);
    } else {
        $isSSL = false;
    }
    if ($isSSL) {
        $msg = tra('MySQL SSL connection is active');
        $s = tra('ON');
    } elseif ($haveMySQLSSL && ! $isSSL) {
        $msg = tra('MySQL connection is not encrypted');
        $s = tra('OFF');
    } else {
        $msg = tra('MySQL Server does not have SSL activated.');
        $s = 'OFF';
    }
    $fitness = tra('info');
    if ($s == tra('ON')) {
        $fitness = tra('good');
    }
    $mysql_properties['SSL connection'] = array(
        'fitness' => $fitness,
        'setting' => $s,
        'message' => $msg
    );

    // Strict mode
    $query = 'SELECT @@sql_mode as Value;';
    $result = query($query, $connection);
    $s = '';
    $msg = 'Unable to query strict mode';
    if (! empty($result)) {
        $sql_mode = $result[0]['Value'];
        $modes = explode(',', $sql_mode);

        if (in_array('STRICT_ALL_TABLES', $modes)) {
            $s = 'STRICT_ALL_TABLES';
        }
        if (in_array('STRICT_TRANS_TABLES', $modes)) {
            if (! empty($s)) {
                $s .= ',';
            }
            $s .= 'STRICT_TRANS_TABLES';
        }

        if (! empty($s)) {
            $msg = 'MySQL is using strict mode';
        } else {
            $msg = 'MySQL is not using strict mode.';
        }
    }
    $mysql_properties['Strict Mode'] = array(
        'fitness' => tra('info'),
        'setting' => $s,
        'message' => $msg
    );

    // MySQL Variables
    $query = "SHOW VARIABLES;";
    $result = query($query, $connection);
    foreach ($result as $value) {
        $mysql_variables[$value['Variable_name']] = array('value' => $value['Value']);
    }

    if (! $standalone) {
        $mysql_crashed_tables = array();
        // This should give all crashed tables (MyISAM at least) - does need testing though !!
        $query = 'SHOW TABLE STATUS WHERE engine IS NULL AND comment <> "VIEW";';
        $result = query($query, $connection);
        foreach ($result as $value) {
            $mysql_crashed_tables[$value['Name']] = array('Comment' => $value['Comment']);
        }
    }
}

// Apache properties

$apache_properties = false;
if (function_exists('apache_get_version')) {
    // Apache Modules
    $apache_modules = apache_get_modules();

    // mod_rewrite
    $s = false;
    $s = array_search('mod_rewrite', $apache_modules);
    if ($s) {
        $apache_properties = array();
        $apache_properties['mod_rewrite'] = array(
            'setting' => 'Loaded',
            'fitness' => tra('good') ,
            'message' => tra('Tiki needs this module for Search Engine Friendly URLs via .htaccess. However, it can\'t be checked if this web server respects configurations made in .htaccess. For further information go to Admin->SefURL in your Tiki.')
        );
    } else {
        $apache_properties = array();
        $apache_properties['mod_rewrite'] = array(
            'setting' => 'Not available',
            'fitness' => tra('unsure') ,
            'message' => tra('Tiki needs this module for Search Engine Friendly URLs. For further information go to Admin->SefURL in the Tiki.')
        );
    }

    if (! $standalone) {
        // work out if RewriteBase is set up properly
        global $url_path;
        $enabledFileName = '.htaccess';
        if (file_exists($enabledFileName)) {
            $enabledFile = fopen($enabledFileName, "r");
            $rewritebase = '/';
            while ($nextLine = fgets($enabledFile)) {
                if (preg_match('/^RewriteBase\s*(.*)$/', $nextLine, $m)) {
                    $rewritebase = substr($m[1], -1) !== '/' ? $m[1] . '/' : $m[1];
                    break;
                }
            }
            if ($url_path == $rewritebase) {
                $smarty->assign('rewritebaseSetting', $rewritebase);
                $apache_properties['RewriteBase'] = array(
                    'setting' => $rewritebase,
                    'fitness' => tra('good') ,
                    'message' => tra('RewriteBase is set correctly in .htaccess. Search Engine Friendly URLs should work. Be aware, though, that this test can\'t checked if Apache really loads .htaccess.')
                );
            } else {
                $apache_properties['RewriteBase'] = array(
                    'setting' => $rewritebase,
                    'fitness' => tra('bad') ,
                    'message' => tra('RewriteBase is not set correctly in .htaccess. Search Engine Friendly URLs are not going to work with this configuration. It should be set to "') . substr($url_path, 0, -1) . '".'
                );
            }
        } else {
            $apache_properties['RewriteBase'] = array(
                'setting' => tra('Not found'),
                'fitness' => tra('info') ,
                'message' => tra('The .htaccess file has not been activated, so this check cannot be  performed. To use Search Engine Friendly URLs, activate .htaccess by copying _htaccess into its place (or a symlink if supported by your Operating System). Then do this check again.')
            );
        }
    }

    if ($pos = strpos($_SERVER['REQUEST_URI'], 'tiki-check.php')) {
        $sef_test_protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) ? 'https://' : 'http://';
        $sef_test_base_url = $sef_test_protocol . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, $pos);
        $sef_test_ping_value = mt_rand();
        $sef_test_url = $sef_test_base_url . 'tiki-check?tiki-check-ping=' . $sef_test_ping_value;
        $sef_test_folder_created = false;
        $sef_test_folder_writable = true;
        if ($standalone) {
            $sef_test_path_current = __DIR__;
            $sef_test_dir_name = 'tiki-check-' . $sef_test_ping_value;
            $sef_test_folder = $sef_test_path_current . DIRECTORY_SEPARATOR . $sef_test_dir_name;
            if (is_writable($sef_test_path_current) && ! file_exists($sef_test_folder)) {
                if (mkdir($sef_test_folder)) {
                    $sef_test_folder_created = true;
                    copy(__FILE__, $sef_test_folder . DIRECTORY_SEPARATOR . 'tiki-check.php');
                    file_put_contents($sef_test_folder . DIRECTORY_SEPARATOR . '.htaccess', "<IfModule mod_rewrite.c>\nRewriteEngine On\nRewriteRule tiki-check$ tiki-check.php [L]\n</IfModule>\n");
                    $sef_test_url = $sef_test_base_url . $sef_test_dir_name . '/tiki-check?tiki-check-ping=' . $sef_test_ping_value;
                }
            } else {
                $sef_test_folder_writable = false;
            }
        }

        if (! $sef_test_folder_writable) {
            $apache_properties['SefURL Test'] = array(
            'setting' => tra('Not Working'),
            'fitness' => tra('info') ,
            'message' => tra('The automated test could not run. The required files could not be created  on the server to run the test. That may only mean that there were no permissions, but the Apache configuration should be checked. For further information go to Admin->SefURL in the Tiki.')
            );
        } else {
            $pong_value = get_content_from_url($sef_test_url);
            if ($pong_value != 'fail-no-request-done') {
                if ('pong:' . $sef_test_ping_value == $pong_value) {
                    $apache_properties['SefURL Test'] = array(
                        'setting' => tra('Working'),
                        'fitness' => tra('good') ,
                        'message' => tra('An automated test was done, and the server appears to be configured correctly to handle Search Engine Friendly URLs.')
                    );
                } else {
                    if (strncmp('fail-http-', $pong_value, 10) == 0) {
                        $apache_return_code = substr($pong_value, 10);
                        $apache_properties['SefURL Test'] = array(
                            'setting' => tra('Not Working'),
                            'fitness' => tra('info') ,
                            'message' => sprintf(tra('An automated test was done and, based on the results, the server does not appear to be configured correctly to handle Search Engine Friendly URLs. The server returned an unexpected HTTP code: "%s". This automated test may fail due to the infrastructure setup, but the Apache configuration should be checked. For further information go to Admin->SefURL in your Tiki.'), $apache_return_code)
                        );
                    } else {
                        $apache_properties['SefURL Test'] = array(
                            'setting' => tra('Not Working'),
                            'fitness' => tra('info') ,
                            'message' => tra('An automated test was done and, based on the results, the server does not appear to be configured correctly to handle Search Engine Friendly URLs. This automated test may fail due to the infrastructure setup, but the Apache configuration should be checked. For further information go to Admin->SefURL in your Tiki.')
                        );
                    }
                }
            }
        }
        if ($sef_test_folder_created) {
            unlink($sef_test_folder . DIRECTORY_SEPARATOR . 'tiki-check.php');
            unlink($sef_test_folder . DIRECTORY_SEPARATOR . '.htaccess');
            rmdir($sef_test_folder);
        }
    }

    // mod_expires
    $s = false;
    $s = array_search('mod_expires', $apache_modules);
    if ($s) {
        $apache_properties['mod_expires'] = array(
            'setting' => 'Loaded',
            'fitness' => tra('good') ,
            'message' => tra('With this module, the HTTP Expires header can be set, which increases performance. It can\'t be checked, though, if mod_expires is configured correctly.')
        );
    } else {
        $apache_properties['mod_expires'] = array(
            'setting' => 'Not available',
            'fitness' => tra('unsure') ,
            'message' => tra('With this module, the HTTP Expires header can be set, which increases performance. Once it is installed, it still needs to be configured correctly.')
        );
    }

    // mod_deflate
    $s = false;
    $s = array_search('mod_deflate', $apache_modules);
    if ($s) {
        $apache_properties['mod_deflate'] = array(
            'setting' => 'Loaded',
            'fitness' => tra('good') ,
            'message' => tra('With this module, the data the webserver sends out can be compressed, which reduced data transfer amounts and increases performance. This test can\'t check, though, if mod_deflate is configured correctly.')
        );
    } else {
        $apache_properties['mod_deflate'] = array(
            'setting' => 'Not available',
            'fitness' => tra('unsure') ,
            'message' => tra('With this module, the data the webserver sends out can be compressed, which reduces data transfer amounts and increases performance. Once it is installed, it still needs to be configured correctly.')
        );
    }

    // mod_security
    $s = false;
    $s = array_search('mod_security', $apache_modules);
    if ($s) {
        $apache_properties['mod_security'] = array(
            'setting' => 'Loaded',
            'fitness' => tra('info') ,
            'message' => tra('This module can increase security of Tiki and therefore the server, but be aware that it is very tricky to configure correctly. A misconfiguration can lead to failed page saves or other hard to trace bugs.')
        );
    } else {
        $apache_properties['mod_security'] = array(
            'setting' => 'Not available',
            'fitness' => tra('info') ,
            'message' => tra('This module can increase security of Tiki and therefore the server, but be aware that it is very tricky to configure correctly. A misconfiguration can lead to failed page saves or other hard to trace bugs.')
        );
    }

    // Get /server-info, if available
    if (function_exists('curl_init') && function_exists('curl_exec')) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'http://localhost/server-info');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        $apache_server_info = curl_exec($curl);
        if (curl_getinfo($curl, CURLINFO_HTTP_CODE) == 200) {
            $apache_server_info = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $apache_server_info);
        } else {
            $apache_server_info = false;
        }
        curl_close($curl);
    } else {
        $apache_server_info = 'nocurl';
    }
}


// IIS Properties
$iis_properties = false;

if (check_isIIS()) {
    // IIS Rewrite module
    if (check_hasIIS_UrlRewriteModule()) {
        $iis_properties['IIS Url Rewrite Module'] = array(
            'fitness' => tra('good'),
            'setting' => 'Available',
            'message' => tra('The URL Rewrite Module is required to use SEFURL on IIS.')
            );
    } else {
        $iis_properties['IIS Url Rewrite Module'] = array(
            'fitness' => tra('bad'),
            'setting' => 'Not Available',
            'message' => tra('The URL Rewrite Module is required to use SEFURL on IIS.')
            );
    }
}

// Check Tiki Packages
if (! $standalone) {
    global $tikipath, $base_host;

    $composerManager = new ComposerManager($tikipath);
    $installedLibs = $composerManager->getInstalled() ?: array();

    $packagesToCheck = array(
        array(
            'name' => 'jerome-breton/casperjs-installer',
            'commands' => array(
                'python'
            ),
            'preferences' => array(
                'casperjs_path' => array(
                    'name' => tra('casperjs path'),
                    'type' => 'path'
                )
            ),
        ),
        array(
            'name' => 'media-alchemyst/media-alchemyst',
            'preferences' => array(
                'alchemy_ffmpeg_path' => array(
                    'name' => tra('ffmpeg path'),
                    'type' => 'path'
                ),
                'alchemy_ffprobe_path' => array(
                    'name' => tra('ffprobe path'),
                    'type' => 'path'
                ),
                'alchemy_unoconv_path' => array(
                    'name' => tra('unoconv path'),
                    'type' => 'path'
                ),
         