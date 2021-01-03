<?php

/**
 * @package tikiwiki
 */

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER["SCRIPT_NAME"], basename(__FILE__)) !== false) {
    header("location: index.php");
    exit;
}

use Tiki\Command\ConsoleSetupException;

require_once('tiki-filter-base.php');

if (! isset($_SERVER['QUERY_STRING'])) {
    $_SERVER['QUERY_STRING'] = '';
}
if (empty($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
}
if (empty($_SERVER['SERVER_NAME'])) {
    $_SERVER['SERVER_NAME'] = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
}


// ---------------------------------------------------------------------
// basic php conf adjustment
// xhtml compliance
ini_set('arg_separator.output', '&amp;');
// URL session handling is not safe or pretty
// better avoid using trans_sid for security reasons
ini_set('session.use_only_cookies', 1);
// true, but you cannot change the url_rewriter.tags in safe mode ...
// its usually safe to leave it as is.
//ini_set('url_rewriter.tags', '');
// use shared memory for sessions (useful in shared space)
// ini_set('session.save_handler', 'mm');
// ... or if you use turck mmcache
// ini_set('session.save_handler', 'mmcache');
// ... or if you just cant to store sessions in file
// ini_set('session.save_handler', 'files');

$memory_limiter = new Tiki_MemoryLimit('128M'); // Keep in variable to hold scope

if (in_array('phar', stream_get_wrappers())) {
    stream_wrapper_unregister('phar');
}

// ---------------------------------------------------------------------
// inclusions of mandatory stuff and setup
require_once('lib/setup/tikisetup.class.php');
require_once('lib/tikiticketlib.php');
require_once('db/tiki-db.php');
require_once('lib/tikilib.php');
$tikilib = new TikiLib();
// Get tiki-setup_base needed preferences in one query
$prefs = [];
$needed_prefs = [
    'session_lifetime' => '0',
    'session_storage' => 'default',
    'session_silent' => 'n',
    'session_cookie_name' => session_name(),
    'session_protected' => 'n',
    'tiki_cdn' => '',
    'tiki_cdn_ssl' => '',
    'language' => 'en',
    'lang_use_db' => 'n',
    'feature_fullscreen' => 'n',
    'error_reporting_level' => 0,
    'error_tracking_dsn' => '',
    'error_tracking_enabled_php' => 'n',
    'error_tracking_enabled_js' => 'n',
    'error_tracking_sample_rate' => '1',
    'memcache_enabled' => 'n',
    'memcache_expiration' => 3600,
    'memcache_prefix' => 'tiki_',
    'memcache_compress' => 'y',
    'memcache_servers' => false,
    'redis_enabled' => 'n',
    'redis_host' => 'localhost',
    'redis_port' => '6379',
    'redis_timeout' => '3',
    'redis_prefix' => '',
    'redis_expiry' => 0,
    'min_pass_length' => 5,
    'pass_chr_special' => 'n',
    'cookie_consent_feature' => 'n',
    'cookie_consent_disable' => 'n',
    'cookie_consent_analytics' => 'n',
    'cookie_consent_name' => 'tiki_cookies_accepted',
    'allocate_memory_php_execution' => '',
    'allocate_time_php_execution' => '',
    'https_port' => '443',
];

$error = '';

/// check that tiki_preferences is there
if ($tikilib->query("SHOW TABLES LIKE 'tiki_preferences'")->numRows() == 0) {
    if (defined('TIKI_CONSOLE')) {
        throw new ConsoleSetupException($error, 1002);
    }
    // smarty not initialised at this point to do a polite message, sadly
    header('location: tiki-install.php');
    exit(1);
}
if (! $tikilib->getOne("SELECT COUNT(*) FROM `information_schema`.`character_sets` WHERE `character_set_name` = 'utf8mb4';")) {
    if (PHP_SAPI === 'cli') {
        $error = ("\033[31mYour database does not support the utf8mb4 character set required in Tiki19 and above\033[0m\n");
    } else {
        $error = (tr('Your database does not support the utf8mb4 character set required in Tiki19 and above. You need to upgrade your mysql or mariadb installation.'));
    }
}

$tikilib->get_preferences($needed_prefs, true, true);
global $systemConfiguration;
$prefs = $systemConfiguration->preference->toArray() + $prefs;

// Initialize ErrorTracking instance (Sentry/GlitchTip)
TikiLib::lib('errortracking')->init();

// Handle load balancers or reverse proxy (most reliable to do it early on as much code depends on these 2 server vars)

if (
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
    || (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https')
) {
        $_SERVER['HTTPS'] = 'on';
}

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    if (empty($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] === '80') {
        $_SERVER['SERVER_PORT'] = '443';
    }

    if (! empty($prefs['https_port']) && $prefs['https_port'] !== '443') {
        $_SERVER['SERVER_PORT'] = $prefs['https_port'];
    } elseif (! empty($_SERVER['HTTP_X_FORWARDED_PORT'])) {
        $_SERVER['SERVER_PORT'] = $_SERVER['HTTP_X_FORWARDED_PORT'];
    }
}

// mose : simulate strong var type checking for http vars
$patterns['int'] = "/^[0-9]*$/"; // *Id
$patterns['intSign'] = "/^[-+]?[0-9]*$/"; // *offset,
$patterns['char'] = "/^(pref:)?[-,_a-zA-Z0-9]*$/"; // sort_mode
$patterns['string'] = "/^<\/?(b|strong|small|br *\/?|ul|li|i)>|[^<>\";#]*$/"; // find, and such extended chars
$patterns['stringlist'] = "/^[^<>\"#]*$/"; // to, cc, bcc (for string lists like: user1;user2;user3)
$patterns['vars'] = "/^[-_a-zA-Z0-9]*$/"; // for variable keys
$patterns['dotvars'] = "/^[-_a-zA-Z0-9\.]*$/"; // same pattern as a variable key, but that may contain a dot
$patterns['hash'] = "/^[a-z0-9]*$/"; // for hash reqId in live support
$patterns['url'] = "/^(https?:\/\/)?[^<>\"]*$/";

// IIS always sets the $_SERVER['HTTPS'] value (on|off)
$noSSLActive = ! isset($_SERVER['HTTPS']) || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'off');
if (isset($prefs['session_protected']) && $prefs['session_protected'] == 'y' && $noSSLActive && php_sapi_name() != 'cli') {
    header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
    exit;
}

// set PHP Limits
if (! empty($prefs['allocate_memory_php_execution'])) {
    ini_set('memory_limit', $prefs['allocate_memory_php_execution']);
}
if (! empty($prefs['allocate_time_php_execution'])) {
    ini_set('max_execution_time', $prefs['allocate_time_php_execution']);
}

$cachelib = TikiLib::lib('cache');
$logslib = TikiLib::lib('logs');
include_once('lib/init/tra.php');
$tikidate = TikiLib::lib('tikidate');
// set session lifetime
if (isset($prefs['session_lifetime']) && $prefs['session_lifetime'] > 0) {
    ini_set('session.gc_maxlifetime', $prefs['session_lifetime'] * 60);
}
// is session data  stored in DB or in filesystem?
if (isset($prefs['session_storage']) && $prefs['session_storage'] == 'db') {
    $db = TikiDb::get();
    if ($db instanceof TikiDb_MasterSlaveDispatch) {
        $db->getReal();
    }

    if ($db instanceof TikiDb_AdoDb) {
        require_once('lib/tikisession-adodb.php');
    } elseif ($db instanceof TikiDb_Pdo) {
        require_once('lib/tikisession-pdo.php');
    }
} elseif (isset($prefs['session_storage']) && $prefs['session_storage'] == 'memcache' && TikiLib::lib("memcache")->isEnabled()) {
    require_once('lib/tikisession-memcache.php');
}

if (! isset($prefs['session_cookie_name']) || empty($prefs['session_cookie_name'])) {
    $prefs['session_cookie_name'] = session_name();
}

session_name($prefs['session_cookie_name']);

// Only accept PHP's session ID in URL when the request comes from the tiki server itself
// This is used by features that need to query the server to retrieve tiki's generated html and images (e.g. pdf export)
// It could be , that the server initiates his request with its own ip, so we check also if server == remote
// Note: this is an incomplete implemenation - the session handling does not really work this way. Session data is lost and not regenerated.
// Maybe better to use tokens: see i.e. the example in lib/pdflib.php
if (isset($_GET[session_name()]) && (($tikilib->get_ip_address() == '127.0.0.1') || ($_SERVER["SERVER_ADDR"] == $_SERVER["REMOTE_ADDR"]))) {
    $_COOKIE[session_name()] = $_GET[session_name()];
    session_id($_GET[session_name()]);
}

//Set tikiroot and tikidomain to blank string if not set.
if (empty($tikiroot)) {
    $tikiroot = "";
}
if (empty($tikidomain)) {
    $tikidomain = "";
}

if ($prefs['cookie_consent_feature'] === 'y' && empty($_COOKIE[$prefs['cookie_consent_name']]) && $prefs['cookie_consent_disable'] !== 'y') {
    // No consent yet
    $feature_no_cookie = true;
    $feature_no_cookie_analytics = true;
} else {
    // Cookie consent not implemented or consent given or consent forced with preference cookie_consent_disable
    $feature_no_cookie = false;
    if ($prefs['cookie_consent_analytics'] === 'y') {
        if (! empty($_COOKIE[$prefs['cookie_consent_name'] . '_analytics']) && $_COOKIE[$prefs['cookie_consent_name'] . '_analytics'] === 'y') {
            $feature_no_cookie_