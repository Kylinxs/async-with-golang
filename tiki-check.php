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
    $message = tra('OPcache is being used as 