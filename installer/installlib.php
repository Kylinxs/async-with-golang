
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

use Tiki\Installer\InstallerDatabaseErrorHandler;

/**
 * @return bool
 */
function has_tiki_db()
{
    global $installer;
    return $installer->tableExists('users_users');
}

/**
 * @return bool
 */
function has_tiki_db_20()
{
    global $installer;
    return $installer->tableExists('tiki_pages_translation_bits');
}

/**
 * @param $dbb_tiki
 * @param $host_tiki
 * @param $user_tiki
 * @param $pass_tiki
 * @param $dbs_tiki
 * @param string $client_charset
 * @param string $api_tiki
 * @param string $dbversion_tiki
 */
function write_local_php($dbb_tiki, $host_tiki, $user_tiki, $pass_tiki, $dbs_tiki, $client_charset = '', $api_tiki = '', $dbversion_tiki = 'current')
{
    global $local;
    global $db_tiki;
    if ($dbs_tiki && $user_tiki) {
        $db_tiki = addslashes($dbb_tiki);
        $host_tiki = addslashes($host_tiki);
        $user_tiki = addslashes($user_tiki);
        $pass_tiki = addslashes($pass_tiki);
        $dbs_tiki = addslashes($dbs_tiki);
        $fw = fopen($local, 'w');
        $filetowrite = "<?php\n";
        $filetowrite .= "\$db_tiki='" . $db_tiki . "';\n";
        if ($dbversion_tiki == 'current') {
            require_once 'lib/setup/twversion.class.php';
            $twversion = new TWVersion();
            $dbversion_tiki = $twversion->getBaseVersion();
        }
        $filetowrite .= "\$dbversion_tiki='" . $dbversion_tiki . "';\n";
        $filetowrite .= "\$host_tiki='" . $host_tiki . "';\n";
        $filetowrite .= "\$user_tiki='" . $user_tiki . "';\n";
        $filetowrite .= "\$pass_tiki='" . $pass_tiki . "';\n";
        $filetowrite .= "\$dbs_tiki='" . $dbs_tiki . "';\n";
        if (! empty($api_tiki)) {
            $filetowrite .= "\$api_tiki='" . $api_tiki . "';\n";
        }
        if (! empty($client_charset)) {
            $filetowrite .= "\$client_charset='$client_charset';\n";
        }
        $filetowrite .= "// \$dbfail_url = '';\n";
        $filetowrite .= "// \$noroute_url = './';\n";
        $filetowrite .= "// If you experience text encoding issues after updating (e.g. apostrophes etc showing up as strange characters) \n";
        $filetowrite .= "// \$client_charset='latin1';\n";
        $filetowrite .= "// \$client_charset='utf8mb4';\n";
        $filetowrite .= "// See http://tiki.org/ReleaseNotes5.0#Known_Issues and http://doc.tiki.org/Understanding+Encoding for more info\n\n";
        $filetowrite .= "// If your php installation does not not have pdo extension\n";
        $filetowrite .= "// \$api_tiki = 'adodb';\n\n";
        $filetowrite .= "// Want configurations managed at the system level or restrict some preferences? http://doc.tiki.org/System+Configuration\n";
        $filetowrite .= "// \$system_configuration_file = '/etc/tiki.ini.php';\n";
        $filetowrite .= "// \$system_configuration_identifier = 'example.com';\n\n";
        fwrite($fw, $filetowrite);
        fclose($fw);
    }
}

/**
 * @param string $domain
 * @return string
 */
function create_dirs($domain = '')
{
    global $tikipath;
    $dirs = [
        'db',
        'dump',
        'img/wiki',
        'img/wiki_up',
        'img/trackers',
        'temp',
        'temp/cache',
        'temp/templates_c',
        'templates'];

    $ret = "";
    foreach ($dirs as $dir) {
        $dir = $dir . '/' . $domain;

        if (! is_dir($dir)) {
            $created = @mkdir($dir, 02775); // Try creating the directory
            if (! $created) {
                $ret .= "The directory '$tikipath$dir' could not be created.\n";
            }
        } elseif (! TikiInit::is_writeable($dir)) {
            @chmod($dir, 02775);
            if (! TikiInit::is_writeable($dir)) {
                $ret .= "The directory '$tikipath$dir' is not writeable.\n";
            }
        }
    }
    return $ret;
}

/**
 * @return bool
 */
function isWindows()
{
    static $windows;

    if (! isset($windows)) {
        $windows = substr(PHP_OS, 0, 3) == 'WIN';
    }

    return $windows;
}

function check_session_save_path()
{
    global $errors;
    if (ini_get('session.save_handler') == 'files') {
        $save_path = ini_get('session.save_path');
        // check if we can check it. The session.save_path can be outside
        // the open_basedir paths.
        $open_basedir = ini_get('open_basedir');
        if (empty($open_basedir)) {
            if (! is_dir($save_path)) {
                $errors .= "The directory '$save_path' does not exist or PHP is not allowed to access it (check open_basedir entry in php.ini).\n";
            } elseif (! TikiInit::is_writeable($save_path)) {
                $errors .= "The directory '$save_path' is not writeable.\n";
            }
        }

        if ($errors) {
            $save_path = sys_get_temp_dir();

            if (is_dir($save_path) && TikiInit::is_writeable($save_path)) {
                ini_set('session.save_path', $save_path);

                $errors = '';
            }
        }
    }
}

function get_webserver_uid()
{
    global $wwwuser;
    global $wwwgroup;
    $wwwuser = '';
    $wwwgroup = '';

    if (isWindows()) {
        $wwwuser = 'SYSTEM';

        $wwwgroup = 'SYSTEM';
    }

    if (function_exists('posix_getuid')) {
        $user = @posix_getpwuid(@posix_getuid());

        $group = @posix_getpwuid(@posix_getgid());
        $wwwuser = $user ? $user['name'] : false;
        $wwwgroup = $group ? $group['name'] : false;
    }

    if (! $wwwuser) {
        $wwwuser = 'nobody (or the user account the web server is running under)';
    }

    if (! $wwwgroup) {
        $wwwgroup = 'nobody (or the group account the web server is running under)';
    }
}

function error_and_exit()
{
    global $errors, $tikipath;

    $PHP_CONFIG_FILE_PATH = PHP_CONFIG_FILE_PATH;

    $httpd_conf = 'httpd.conf';
    /*
            ob_start();
            phpinfo (INFO_MODULES);

            if (preg_match('/Server Root<\/b><\/td><td\s+align="left">([^<]*)</', ob_get_contents(), $m)) {
                    $httpd_conf = $m[1] . '/' . $httpd_conf;
            }

            ob_end_clean();
    */

    print "<html><body>\n<h2><IMG SRC=\"img/tiki/Tiki_WCG.png\" ALT=\"\" BORDER=0><br /\>
    <font color='red'>Tiki Installer cannot proceed</font></h2>\n<pre>\n$errors";

    if (! isWindows()) {
        print "<br /><br />Your options:


1- With FTP access:
    a) Change the permissions (chmod) of the directories to 777.
    b) Create any missing directories
    c) <a href='tiki-install.php'>Execute the Tiki installer again</a> (Once you have executed these commands, this message will disappear!)

or

2- With shell (SSH) access, you can run the command below.

    a) To run setup.sh, follow the instructions:
        \$ cd $tikipath
        \$ sh setup.sh

        The script will offer you options depending on your server configuration.

    b) <a href='tiki-install.php'>Execute the Tiki installer again</a> (Once you have executed these commands, this message will disappear!)


<hr>
If you have problems accessing a directory, check the open_basedir entry in
$PHP_CONFIG_FILE_PATH/php.ini or $httpd_conf.

<hr>

<a href='http://doc.tiki.org/Installation' target='_blank'>Consult the tiki.org installation guide</a> if you need more help or <a href='http://tiki.org/tiki-forums.php' target='_blank'>visit the forums</a>

";
    }
    print "</pre></body></html>";
    exit;
}



// Try to see if we have an admin account
/**
 * @param $api_tiki
 * @return string
 */
function has_admin($api_tiki)
{
    $query = "select hash from users_users where login='admin'";
    $res = false;

    $db = TikiDb::get();
    $result = $db->fetchAll($query);

    if (is_array($result)) {
        $res = reset($result);
    }

    if ($res && isset($res['hash'])) {
        $admin_acc = 'y';
    } else {
        $admin_acc = 'n';
    }

    return $admin_acc;
}

/**
 * @param $dbTiki
 * @return bool
 */
function get_admin_email()
{
    global $installer;
    $query = "SELECT `email` FROM `users_users` WHERE `userId`=1";
    @$result = $installer->query($query);

    if ($result && $res = $result->fetchRow()) {
        return $res['email'];
    }

    return false;
}

/**
 * @param $dbTiki
 * @param $prefs
 * @return bool
 */
function update_preferences(&$prefs)
{
    global $installer;
    $query = "SELECT `name`, `value` FROM `tiki_preferences`";
    @$result = $installer->query($query);

    if ($result) {
        while ($res = $result->fetchRow()) {
            if (! isset($prefs[$res['name']])) {
                $prefs[$res['name']] = $res['value'];
            }
        }
        return true;
    }

    return false;
}

/**
 * @param $account
 */
function fix_admin_account($account)
{
    global $installer;

    $result = $installer->query('SELECT `id` FROM `users_groups` WHERE `groupName` = "Admins"');
    if (! $row = $result->fetchRow()) {
        $installer->query('INSERT INTO `users_groups` (`groupName`) VALUES("Admins")');
    }

    $installer->query('INSERT IGNORE INTO `users_grouppermissions` (`groupName`, `permName`) VALUES("Admins", "tiki_p_admin")');

    $result = $installer->query('SELECT `userId` FROM `users_users` WHERE `login` = ?', [ $account ]);
    if ($row = $result->fetchRow()) {
        $id = $row['userId'];
        $installer->query('INSERT IGNORE INTO `users_usergroups` (`userId`, `groupName`) VALUES(?, "Admins")', [ $id ]);
    }
}

/* possible error after upgrade 4 */
function fix_disable_accounts()
{
    global $installer;
    $installer->query('update `users_users` set `waiting`=NULL where `waiting` = ? and `valid` is NULL', ['a']);
}

/**
 * @return array
 */
function list_disable_accounts()
{
    global $installer;
    $result = $installer->query('select `login` from `users_users` where `waiting` = ? and `valid` is NULL', ['a']);
    $ret = [];
    if ($result) {
        while ($res = $result->fetchRow()) {
            $ret[] = $res['login'];
        }
    }
    return $ret;
}

/**
 * @param $api
 * @param $driver
 * @param $host
 * @param $user
 * @param $pass
 * @param $dbname
 * @param $client_charset
 * @param $dbTiki
 * @return bool|int
 */
function initTikiDB(&$api, &$driver, $host, $user, $pass, $dbname, $client_charset, &$dbTiki)
{
    $initializer = new TikiDb_Initializer();
    $initializer->setPreferredConnector($driver);
    $initializer->setInitializeCallback(
        function ($db) {
            $db->setServerType('pdo');
            $db->setErrorHandler(new InstallerDatabaseErrorHandler());
        }
    );

    $dbcon = false;
    try {
        $dbTiki = $initializer->getConnection(
            [
                'host' => $host,
                'user' => $user,
                'pass' => $pass,
                'dbs' => $dbname,
                'charset' => $client_charset,
            ]
        );
    } catch (Exception $e) {
        Feedback::error($e->getMessage());
    }
    $dbcon = ! empty($dbTiki);

    // Attempt to create database. This might work if the $user has create database permissions.
    if (! $dbcon) {
        // First first get a valid connection to the database
        try {
            $dbTiki = $initializer->getConnection(
                [
                    'host' => $host,
                    'user' => $user,
                    'pass' => $pass,
                    //'dbs' => $dbname,
                    'charset' => $client_charset,
                ]
            );
        } catch (Exception $e) {
            Feedback::error($e->getMessage());
        }
        $dbcon = ! empty($dbTiki);
        // First check that suggested database name will not cause issues
        $dbname_clean = preg_replace('/[^a-zA-Z0-9$_-]/', "", $dbname);
        if ($dbname_clean != $dbname) {
            Feedback::error(tra("Some invalid characters were detected in database name. Please use alphanumeric characters (A-Z a-z 0-9) or underscore (_) or hyphen (-).", '', false, [$dbname_clean]));
            $dbcon = false;
        } elseif ($dbcon) {
            $error = '';
            $sql = "CREATE DATABASE IF NOT EXISTS `$dbname_clean` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
            $dbTiki->queryError($sql, $error);
            if (empty($error)) {
                // assure the DB has the right default encoding (if the DB already existed)
                $dbTiki->query("ALTER DATABASE `$dbname_clean` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                Feedback::success(tra("Database `%0` was created.", '', false, [$dbname_clean]));
            } else {
                Feedback::error(tra("Database `%0` creation failed. You need to create the database.", '', false, [$dbname_clean]));
            }

            try {
                $dbTiki = $initializer->getConnection(
                    [
                        'host' => $host,
                        'user' => $user,
                        'pass' => $pass,
                        'dbs' => $dbname,
                        'charset' => $client_charset,
                    ]
                );
                $dbcon = ! empty($dbTiki);
            } catch (Exception $e) {
                Feedback::error($e->getMessage());
            }
        } else {
            Feedback::error(tra("Database `%0`. Unable to connect to database.", '', false, [$dbname_clean]));
        }
    }

    if (isset($dbTiki)) {
        TikiDb::set($dbTiki);
    }

    return $dbcon;
}


/**
 * Create an user to own created database
 *
 * @param $dbTiki  valid connection
 * @param $user  username for new db user
 * @param $pass  password for new db user
 * @param $dbname  database name
 * @return bool|int
 */
function createTikiDBUser(&$dbTiki, $host, $user, $pass, $dbname)
{
    if (preg_match('/^(127\.0\.\d{1,3}\.\d{1,3}|localhost)(:\d+)?$/', $host)) {
        $host = 'localhost';
    } else {
        $host = '%';
    }

    $pass = addslashes($pass);
    $sql = "GRANT ALL PRIVILEGES ON `$dbname`.* TO `$user`@`$host` IDENTIFIED BY '$pass';";
    $dbTiki->queryError($sql, $error);

    if (empty($error)) {
        Feedback::success(tra("User `%0` was created.", '', false, [$user]));
    } else {
        Feedback::error(tra("User `%0` creation failed.", '', false, [$user]));
    }

    return empty($error);
}

/**
 * @param $dbname
 */
function convert_database_to_utf8($dbname)
{
    $db = TikiDb::get();

    if ($result = $db->fetchAll('SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ?', $dbname)) {
        $db->query("ALTER DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        foreach ($result as $row) {
            $db->query("ALTER TABLE `{$row['TABLE_NAME']}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }
    } else {
        die('MySQL INFORMATION_SCHEMA not available. Your MySQL version is too old to perform this operation. (convert_database_to_utf8)');
    }
}

/**
 * @param $dbname
 * @param $previous
 */
function fix_double_encoding($dbname, $previous)
{
    $db = TikiDb::get();

    $text_fields = $db->fetchAll("SELECT TABLE_NAME, COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND CHARACTER_SET_NAME IS NOT NULL", [$dbname]);

    if ($text_fields) {
        foreach ($text_fields as $field) {
            $db->query("UPDATE `{$field['TABLE_NAME']}` SET `{$field['COLUMN_NAME']}` = CONVERT(CONVERT(CONVERT(CONVERT(`{$field['COLUMN_NAME']}` USING binary) USING utf8mb4) USING $previous) USING binary)");
        }
    } else {
        die('MySQL INFORMATION_SCHEMA not available. Your MySQL version is too old to perform this operation. (fix_double_encoding)');
    }
}