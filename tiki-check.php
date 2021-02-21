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
            'message' => tra('Not all errors will be reported as the error_reporting level is at ' . $e . '. ' . 'This is not necessarily a bad thing (and it may be appropriate for a production site) as critical errors will be reported, but sometimes it may be useful to get mo