<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

use Tiki\File\DiagramHelper;
use Tiki\TikiInit;

require_once(__DIR__ . '/../lib/debug/Tracer.php');

// this script may only be included - so its better to die if called directly.
if (strpos($_SERVER["SCRIPT_NAME"], basename(__FILE__)) !== false) {
    header("location: index.php");
    exit;
}

// This class is included by all the Tiki php scripts, so it's important
// to keep the class as small as possible to improve performance.
// What goes in this class:
// * generic functions that MANY scripts must use
// * shared functions (marked as /*shared*/) are functions that are
//   called from Tiki modules.

/**
 *
 */
class TikiLib extends TikiDb_Bridge
{
    public $buffer;
    public $flag;
    public $usergroups_cache = [];

    public $num_queries = 0;
    public $now;

    public $cache_page_info = [];
    public $sessionId = null;

    /**
     * Collection of Tiki libraries.
     * Populated by TikiLib::lib()
     * @var array
     */
    protected static $libraries = [];

    protected static $isExternalContext = false;

    /** Gets a library reference
     *
     * @param $name string        The name of the library as specified in the id attribute in db/config/tiki.xml
     *
     * @return object
     * @throws Exception
     */
    public static function lib(string $name): ?object
    {
        if (isset(self::$libraries[$name])) {
            return self::$libraries[$name];
        }

        $container = TikiInit::getContainer();

        //if no period in the lib name, default to tiki.lib prefix.
        if (strpos($name, ".") !== false) {
            $service = $name;
        } else {
            $service = "tiki.lib.$name";
        }

        if ($lib = $container->get($service, \Symfony\Component\DependencyInjection\ContainerInterface::NULL_ON_INVALID_REFERENCE)) {
            return $lib;
        }

        // One-time inits of the libraries provided
        switch ($name) {
            case 'tiki':
                global $tikilib;
                return self::$libraries[$name] = $tikilib;
        }

        if (file_exists(__DIR__ . '/../temp/cache/container.php')) {
            unlink(__DIR__ . '/../temp/cache/container.php'); // Remove the container cache to help transition
        }

        trigger_error(tr("%0 library not found. This may be due to a typo or caused by a recent update.", $name));
        return null;
    }

    /**
     * @return Tiki_Event_Manager
     * @throws Exception
     */
    public static function events()
    {
        return self::lib('events');
    }

    /**
     * @return Tiki_Profile_SymbolLoader
     * @throws Exception
     */
    public static function symbols()
    {
        return self::lib('symbols');
    }

    /**
     * @return mixed
     */
    public function get_site_hash()
    {
        global $prefs;

        if (! isset($prefs['internal_site_hash'])) {
            $hash = $this->generate_unique_sequence();

            $this->set_preference('internal_site_hash', $hash);
        }

        return $prefs['internal_site_hash'];
    }

    /**
     * Generates cryptographically secure pseudo-random sequence of bytes encoded into the base 64 character set
     *
     * @param int $entropy      Number of bytes to return
     * @param bool $urlSafe     If true, substitutes '-' and '_', for '+' and '_', and strips the '=' padding
     *                              character for url safe sequence.
     * @return string
     */
    public function generate_unique_sequence($entropy = 100, $urlSafe = false)
    {
        $random_value = \phpseclib\Crypt\Random::string($entropy);
        $encoded_value = base64_encode($random_value);
        return $urlSafe ? strtr(str_replace('=', '', $encoded_value), '+/', '-_')
            : $encoded_value;
    }

    // DB param left for interface compatibility, although not considered
    /**
     * @param null $db
     */
    public function __construct($db = null)
    {
        $this->now = time();
    }

    public function allocate_extra($type, $callback)
    {
        global $prefs;

        $memory_name = 'allocate_memory_' . $type;
        $time_name = 'allocate_time_' . $type;

        if (! empty($prefs[$memory_name])) {
            $memory_limit = new Tiki_MemoryLimit($prefs[$memory_name]);
        }

        if (! empty($prefs[$time_name])) {
            $time_limit = new Tiki_TimeLimit($prefs[$time_name]);
        }

        return call_user_func($callback);
    }

    /**
     * @param bool $url
     * @param array $options
     * @return mixed|Laminas\Http\Client
     */
    public function get_http_client($url = false, $options = null, $user = null)
    {
        global $prefs;

        $config = [
            'timeout' => 10,
            'keepalive' => true,
        ];

        if ($prefs['use_proxy'] == 'y') {
            $config['adapter'] = 'Laminas\Http\Client\Adapter\Proxy';
            $config["proxy_host"] = $prefs['proxy_host'];
            $config["proxy_port"] = $prefs['proxy_port'];

            if ($prefs['proxy_user'] || $prefs['proxy_pass']) {
                $config["proxy_user"] = $prefs['proxy_user'];
                $config["proxy_pass"] = $prefs['proxy_pass'];
            }
        } elseif (function_exists('curl_init') && $prefs['zend_http_use_curl'] === 'y') {
            // Laminas\Http\Client defaults to sockets, which aren't allowed in all environments so use curl when available if selected
            $config['adapter'] = 'Laminas\Http\Client\Adapter\Curl';
        }

        if ($prefs['zend_http_sslverifypeer'] == 'y') {
            $config['sslverifypeer'] = true;
        } else {
            $config['sslverifypeer'] = false;
        }


        if (is_array($options)) {
            foreach ($options as $key => $value) {
                $config[$key] = $value;
            }
        }

        $client = new Laminas\Http\Client(null, $config);
        $client->setArgSeparator('&');

        if ($url) {
            $client = $this->prepare_http_client($client, $url, $user);

            $client->setUri($this->urlencode_accent($url)); // Laminas\Http\Client seems to fail with accents in urls (jb june 2011)
        }

        return $client;
    }

    /**
     * @param $client
     * @param $url
     * @return mixed
     */
    private function prepare_http_client($client, $url, $user = null)
    {
        $info = parse_url($url);

        // Obtain all methods matching the scheme and domain
        $table = $this->table('tiki_source_auth');
        $authentications = $table->fetchAll(
            ['path', 'method', 'arguments'],
            ['scheme' => $info['scheme'], 'domain' => $info['host'], 'user' => $user]
        );

        if (! $authentications && $user) {
            // try system-wide authentications not constrainted to a specific user
            $authentications = $table->fetchAll(
                ['path', 'method', 'arguments'],
                ['scheme' => $info['scheme'], 'domain' => $info['host'], 'user' => null]
            );
        }

        // Obtain the method with the longest path matching
        $max = -1;
        $method = false;
        $arguments = false;
        foreach ($authentications as $auth) {
            if (0 === strpos($info['path'], $auth['path'])) {
                $len = strlen($auth['path']);
                if ($len > $max) {
                    $max = $len;
                    $method = $auth['method'];
                    $arguments = $auth['arguments'];
                }
            }
        }

        if ($method) {
            $functionName = 'prepare_http_auth_' . $method;
            if (method_exists($this, $functionName)) {
                $arguments = json_decode($arguments, true);
                return $this->$functionName($client, $arguments);
            }
        } else {
            // Nothing special to do
            return $client;
        }
    }

    /**
     * @param $client
     * @param $arguments
     * @return mixed
     */
    private function prepare_http_auth_basic($client, $arguments)
    {
        $client->setAuth($arguments['username'], $arguments['password'], Laminas\Http\Client::AUTH_BASIC);

        return $client;
    }

    /**
     * @param $client
     * @param $arguments
     * @return mixed
     */
    private function prepare_http_auth_get($client, $arguments)
    {
        $url = $arguments['url'];

        $client->setUri($this->urlencode_accent($url)); // Laminas\Http\Client seems to fail with accents in urls
        $client->setMethod(Laminas\Http\Request::METHOD_GET);
        $response = $client->send();
        $client->resetParameters();

        return $client;
    }

    /**
     * @param $client
     * @param $arguments
     * @return mixed
     */
    private function prepare_http_auth_post($client, $arguments)
    {
        $url = $arguments['post_url'];
        unset($arguments['post_url']);

        $client->setUri($this->urlencode_accent($url)); // Laminas\Http\Client seems to fail with accents in urls
        $client->setMethod(Laminas\Http\Request::METHOD_GET);
        $response = $client->send();
        $client->resetParameters();

        $client->setUri($this->urlencode_accent($url)); // Laminas\Http\Client seems to fail with accents in urls
        $client->setParameterPost($arguments);
        $client->setMethod(Laminas\Http\Request::METHOD_POST);
        $response = $client->send();
        $client->resetParameters();

        // check for oauth2 password post returning a Authorization: Bearer token
        if (! empty($arguments['grant_type']) && $arguments['grant_type'] === 'password') { // TODO other grant_types may need this too
            $body = json_decode($response->getBody());
            if ($body && $body->access_token) {
                $headers = $client->getRequest()->getHeaders();
                // add the Bearer token to the request headers
                $headers->addHeader(new Laminas\Http\Header\Authorization('Bearer ' . $body->access_token));
                $client->setHeaders($headers);
            }
        }

        return $client;
    }

    /**
     * Authorization header method
     *
     * @param $client     \Laminas\Http\Client
     * @param $arguments  array
     * @return \Laminas\Http\Client
     */
    private function prepare_http_auth_header($client, $arguments)
    {
        $url = $arguments['url'];

        $client->setUri($this->urlencode_accent($url)); // Laminas\Http\Client seems to fail with accents in urls
        $client->setMethod(Laminas\Http\Request::METHOD_GET);

        $headers = $client->getRequest()->getHeaders();
        if (empty($arguments['header_name'])) {
            $headers->addHeader(new Laminas\Http\Header\Authorization($arguments['header']));
        } else {
            $headers->addHeaderLine($arguments['header_name'], $arguments['header']);
        }
        $client->setHeaders($headers);

        return $client;
    }

    /**
     * Request body parameters method
     *
     * @param $client     \Laminas\Http\Client
     * @param $arguments  array
     * @return \Laminas\Http\Client
     */
    private function prepare_http_auth_body($client, $arguments)
    {
        $client->setParameterGet($arguments);
        $client->setParameterPost($arguments);
        return $client;
    }

    /**
     * @param $client
     * @return mixed
     */
    public function http_perform_request($client)
    {
        global $prefs;
        $response = $client->send();

        $attempts = 0;
        while ($response->isRedirect() && $attempts < 10) { // prevent redirect loop
            $client->setUri($client->getUri());
            $response = $client->send();
            $attempts++;
        }

        if ($prefs['http_skip_frameset'] == 'y') {
            if ($outcome = $this->http_perform_request_skip_frameset($client, $response)) {
                return $outcome;
            }
        }

        return $response;
    }

    /**
     * @param $client
     * @param $response
     * @return mixed
     */
    private function http_perform_request_skip_frameset($client, $response)
    {
        // Only attempt if document is declared as HTML
        if (0 === strpos($response->getHeaders()->get('Content-Type'), 'text/html')) {
            $use_int_errors = libxml_use_internal_errors(true); // suppress errors and warnings due to bad HTML
            $dom = new DOMDocument();
            if ($response->getBody() && $dom->loadHTML($response->getBody())) {
                $frames = $dom->getElementsByTagName('frame');

                if (count($frames)) {
                    // Frames were found
                    foreach ($frames as $f) {
                        // Request with the first frame where scrolling is not disabled (likely to be a menu or some other web 2.0 helper)
                        if ($f->getAttribute('scrolling') != 'no') {
                            $client->setUri($this->http_get_uri($client->getUri(), $this->urlencode_accent($f->getAttribute('src'))));
                            libxml_clear_errors();
                            libxml_use_internal_errors($use_int_errors);
                            return $client->send();
                        }
                    }
                }
            }
            libxml_clear_errors();
            libxml_use_internal_errors($use_int_errors);
        }
    }

    /**
     * @param Laminas\Uri\Http $uri
     * @param $relative
     * @return Laminas\Uri\Http
     */
    public function http_get_uri(Laminas\Uri\Http $uri, $relative)
    {
        if (strpos($relative, 'http://') === 0 || strpos($relative, 'https://') === 0) {
            $uri = new Laminas\Uri\Http($relative);
        } else {
            $uri = clone $uri;
            $uri->setQuery([]);
            $parts = explode('?', $relative, 2);
            $relative = $parts[0];

            if ($relative[0] === '/') {
                $uri->setPath($relative);
            } else {
                $path = dirname($uri->getPath());
                if ($path === '/') {
                    $path = '';
                }

                $uri->setPath("$path/$relative");
            }

            if (isset($parts[1])) {
                $uri->setQuery($parts[1]);
            }
        }

        return $uri;
    }

    /**
     * @param $url
     * @param string $reqmethod
     * @return bool
     */
    public function httprequest($url, $reqmethod = "GET")
    {
        // test url :
        // rewrite url if sloppy # added a case for https urls
        if (
            (substr($url, 0, 7) <> "http://") and
                (substr($url, 0, 8) <> "https://")
        ) {
            $url = "http://" . $url;
        }

        try {
            $client = $this->get_http_client($url);
            /* @var $response Laminas\Http\Response */
            $response = $this->http_perform_request($client);

            if (! $response->isSuccess()) {
                return false;
            }

            return $response->getBody();
        } catch (Laminas\Http\Exception\ExceptionInterface $e) {
            return false;
        }
    }

    /*shared*/
    /**
     * @param $name
     * @return bool
     */
    public function get_dsn_by_name($name)
    {
        if ($name == 'local') {
            return true;
        }
        return $this->table('tiki_dsn')->fetchOne('dsn', ['name' => $name]);
    }

    /**
     * @param $name
     * @return array
     */
    public function get_dsn_info($name)
    {
        $info = [];

        $dsnsqlplugin = $this->get_dsn_by_name($name);

        $parsedsn = $dsnsqlplugin;
        $info['driver'] = strtok($parsedsn, ":");
        $parsedsn = substr($parsedsn, strlen($info['driver']) + 3);
        $info['user'] = strtok($parsedsn, ":");
        $parsedsn = substr($parsedsn, strlen($info['user']) + 1);
        $info['password'] = strtok($parsedsn, "@");
        $parsedsn = substr($parsedsn, strlen($info['password']) + 1);
        $info['host'] = strtok($parsedsn, "/");
        $parsedsn = substr($parsedsn, strlen($info['host']) + 1);
        $info['database'] = $parsedsn;

        return $info;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function get_db_by_name($name)
    {
        include_once('tiki-setup.php');
        if ($name == 'local' || empty($name)) {
            return TikiDb::get();
        }

        try {
            static $connectionMap = [];

            if (! isset($connectionMap[$name])) {
                $connectionMap[$name] = false;

                $info = $this->get_dsn_info($name);
                $dbdriver = $info['driver'];
                $dbuserid = $info['user'];
                $dbpassword = $info['password'];
                $dbhost = $info['host'];
                $database = $info['database'];

                $api_tiki = null;
                require 'db/local.php';
                if (isset($api_tiki) &&  $api_tiki == 'adodb') {
                    // Force autoloading
                    if (! class_exists('ADOConnection')) {
                        return null;
                    }

                    $dbsqlplugin = ADONewConnection($dbdriver);
                    if ($dbsqlplugin->NConnect($dbhost, $dbuserid, $dbpassword, $database)) {
                        $connectionMap[$name] = new TikiDb_AdoDb($dbsqlplugin);
                    }
                } else {
                    $dbsqlplugin = new PDO("$dbdriver:host=$dbhost;dbname=$database", $dbuserid, $dbpassword);
                    $connectionMap[$name] = new TikiDb_Pdo($dbsqlplugin);
                }
            }
            return $connectionMap[$name];
        } catch (Exception $e) {
            Feedback::error($e->getMessage());
        }
    }

    /*shared*/
    // Returns IP address or IP address forwarded by the proxy if feature load balancer is set
    /**
     * @param $firewall true to detect ip behind a firewall
     * @return null|string
     */
    public function get_ip_address($firewall = 0)
    {
        global $prefs;
        if ($firewall || (isset($prefs['feature_loadbalancer']) && $prefs['feature_loadbalancer'] === "y")) {
            $header_checks = [
                'HTTP_CF_CONNECTING_IP',
                'HTTP_CLIENT_IP',
                'HTTP_PRAGMA',
                'HTTP_XONNECTION',
                'HTTP_CACHE_INFO',
                'HTTP_XPROXY',
                'HTTP_PROXY',
                'HTTP_PROXY_RENAMED',
                'HTTP_PROXY_CONNECTION',
                'HTTP_VIA',
                'HTTP_X_COMING_FROM',
                'HTTP_COMING_FROM',
                'HTTP_X_FORWARDED_FOR',
                'HTTP_X_FORWARDED',
                'HTTP_X_CLUSTER_CLIENT_IP',
                'HTTP_FORWARDED_FOR',
                'HTTP_FORWARDED',
                'HTTP_CACHE_CONTROL',
                'HTTP_X_REAL_IP',
                'REMOTE_ADDR'];

            foreach ($header_checks as $key) {
                if (array_key_exists($key, $_SERVER) === true) {
                    foreach (explode(',', $_SERVER[$key]) as $ip) {
                        $ip = trim($ip);

                        //filter the ip with filter functions
                        if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                            return $ip;
                        }
                    }
                }
            }
        }
        if (isset($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
            return $_SERVER['REMOTE_ADDR'];
        } else {
            return '0.0.0.0';
        }
    }

    /*shared*/
    /**
     * @param $user
     * @param $section
     * @return bool
     */
    public function check_rules($user, $section)
    {
        global $prefs;

        // Admin is never banned
        if ($user == 'admin') {
            return false;
        }

        $fullip = $this->get_ip_address();
        $ips = explode(".", $fullip);
        $query = "select tb.`message`,tb.`user`,tb.`ip1`,tb.`ip2`,tb.`ip3`,tb.`ip4`,tb.`mode` from `tiki_banning` tb, `tiki_banning_sections` tbs where tbs.`banId`=tb.`banId` and tbs.`section`=? and ( (tb.`use_dates` = ?) or (tb.`date_from` <= FROM_UNIXTIME(?)