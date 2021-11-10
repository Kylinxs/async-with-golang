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
        $query = "select tb.`message`,tb.`user`,tb.`ip1`,tb.`ip2`,tb.`ip3`,tb.`ip4`,tb.`mode` from `tiki_banning` tb, `tiki_banning_sections` tbs where tbs.`banId`=tb.`banId` and tbs.`section`=? and ( (tb.`use_dates` = ?) or (tb.`date_from` <= FROM_UNIXTIME(?) and tb.`date_to` >= FROM_UNIXTIME(?)))";
        $result = $this->fetchAll($query, [$section,'n',(int)$this->now,(int)$this->now]);

        foreach ($result as $res) {
            if (! $res['message']) {
                $res['message'] = tra('You are banned from') . ': ' . $section;
            }

            if ($user && $res['mode'] == 'user') {
                // check user
                $pattern = '/' . $res['user'] . '/';

                if (preg_match($pattern, $user)) {
                    return $res['message'];
                }

                if ($prefs['feature_banning_email'] === 'y') {
                    $info = TikiLib::lib('user')->get_user_info($user);
                    if (preg_match($pattern, $info['email'])) {
                        return $res['message'];
                    }
                }
            } else {
                // check ip
                if (count($ips) == 4) {
                    if (
                        ($ips[0] == $res['ip1'] || $res['ip1'] == '*') && ($ips[1] == $res['ip2'] || $res['ip2'] == '*')
                            && ($ips[2] == $res['ip3'] || $res['ip3'] == '*') && ($ips[3] == $res['ip4'] || $res['ip4'] == '*')
                    ) {
                        return $res['message'];
                    }
                }
            }
        }
        return false;
    }

    // $noteId 0 means create a new note
    /**
     * @param $user
     * @param $noteId
     * @param $name
     * @param $data
     * @param null $parse_mode
     * @return mixed
     */
    public function replace_note($user, $noteId, $name, $data, $parse_mode = null)
    {
        $data = $this->convertAbsoluteLinksToRelative($data);
        $size = strlen($data);

        $queryData = [
            'user' => $user,
            'name' => $name,
            'data' => $data,
            'created' => $this->now,
            'lastModif' => $this->now,
            'size' => (int) $size,
            'parse_mode' => $parse_mode,
        ];

        $userNotes = $this->table('tiki_user_notes');
        if ($noteId) {
            $userNotes->update($queryData, ['noteId' => (int) $noteId,]);
        } else {
            $noteId = $userNotes->insert($queryData);
        }

        return $noteId;
    }

    /**
     * @param $offset
     * @param $maxRecords
     * @param $sort_mode
     * @param $find
     * @return array
     */
    public function list_watches($offset, $maxRecords, $sort_mode, $find)
    {
        $mid = '';
        $mid2 = '';
        $bindvars1 = $bindvars2 = [];
        if ($find) {
            $mid = ' where `event` like ? or `email` like ? or `user` like ? or `object` like ? or `type` like ?';
            $mid2 = ' where `event` like ? or `group` like ? or `object` like ? or `type` like ?';
            $bindvars1 = ["%$find%", "%$find%", "%$find%", "%$find%", "%$find%"];
            $bindvars2 = ["%$find%", "%$find%", "%$find%", "%$find%"];
        }
        $query = "select 'user' as watchtype, `watchId`, `user`, `event`, `object`, `title`, `type`, `url`, `email` from `tiki_user_watches` $mid
            UNION ALL
                select 'group' as watchtype, `watchId`, `group`, `event`, `object`, `title`, `type`, `url`, '' as `email`
                from `tiki_group_watches` $mid2
            order by " . $this->convertSortMode($sort_mode);
        $query_cant = 'select count(*) from `tiki_user_watches` ' . $mid;
        $query_cant2 = 'select count(*) from `tiki_group_watches` ' . $mid2;
        $ret = $this->fetchAll($query, array_merge($bindvars1, $bindvars2), $maxRecords, $offset);
        $cant = $this->getOne($query_cant, $bindvars1) + $this->getOne($query_cant2, $bindvars2);
        $retval = [];
        $retval["data"] = $ret;
        $retval["cant"] = $cant;
        return $retval;
    }


    /*shared*/
    /**
     * @param      $user
     * @param      $event
     * @param      $object
     * @param null $type
     * @param null $title
     * @param null $url
     * @param null $email
     *
     * @return int
     * @throws Exception
     */
    public function add_user_watch($user, $event, $object, $type = null, $title = null, $url = null, $email = null)
    {
        // Allow a warning when the watch won't be effective
        if (empty($email)) {
            $userlib = TikiLib::lib('user');

            $email = $userlib->get_user_email($user);
            if (empty($email)) {
                return false;
            }
        }

        if ($event != 'auth_token_called') {
            $this->remove_user_watch($user, $event, $object, $type, $email);
        }

        $userWatches = $this->table('tiki_user_watches');
        return $userWatches->insert(
            [
                'user' => $user,
                'event' => $event,
                'object' => $object,
                'email' => $email,
                'type' => $type,
                'title' => $title,
                'url' => $url,
            ]
        );
    }

    /**
     * @param $group
     * @param $event
     * @param $object
     * @param null $type
     * @param null $title
     * @param null $url
     * @return bool
     */
    public function add_group_watch($group, $event, $object, $type = null, $title = null, $url = null)
    {

        if ($type == 'Category' && $object == 0) {
            return false;
        } else {
            $this->remove_group_watch($group, $event, $object, $type);
            $groupWatches = $this->table('tiki_group_watches');
            $groupWatches->insert(
                [
                    'group' => $group,
                    'event' => $event,
                    'object' => $object,
                    'type' => $type,
                    'title' => $title,
                    'url' => $url,
                ]
            );
            return true;
        }
    }

    /**
     * get_user_notification: returns the owner (user) related to a watchId
     *
     * @param mixed $id watchId
     * @access public
     * @return the user login related to the watchId
     */
    public function get_user_notification($id)
    {

        return $this->table('tiki_user_watches')->fetchOne('user', ['watchId' => $id]);
    }
    /*shared*/
    /**
     * @param $id
     *
     * @return bool|TikiDb_Adodb_Result|TikiDb_Pdo_Result
     */
    public function remove_user_watch_by_id($id)
    {
        global $tiki_p_admin_notifications, $user;
        if ($tiki_p_admin_notifications === 'y' or $user === $this->get_user_notification($id)) {
            return $this->table('tiki_user_watches')->delete(['watchId' => (int) $id]);
        }

        return false;
    }

    /**
     * @param $id
     *
     * @return TikiDb_Adodb_Result|TikiDb_Pdo_Result
     */
    public function remove_group_watch_by_id($id)
    {
        return $this->table('tiki_group_watches')->delete(['watchId' => (int) $id,]);
    }

    /*shared*/
    /**
     * @param string $user
     * @param string $event
     * @param string $object
     * @param string $type  = 'wiki page'
     * @param string $email = ''
     *
     * @return TikiDb_Adodb_Result|TikiDb_Pdo_Result
     */
    public function remove_user_watch($user, $event, $object, $type = 'wiki page', $email = '')
    {
        $conditions = [
            'user' => $user,
            'event' => $event,
            'object' => $object,
            'type' => $type,
        ];

        if ($email) {
            $conditions['email'] = $email;
        }

        return $this->table('tiki_user_watches')->deleteMultiple($conditions);
    }

    /*token notification*/
    /**
     * @param $event
     * @param $object
     * @param string $type
     */
    public function remove_user_watch_object($event, $object, $type = 'wiki page')
    {
        $query = "delete from `tiki_user_watches` where `event`=? and `object`=? and `type` = ?";
        $this->query($query, [$event,$object,$type]);
    }

    public function remove_stale_comment_watches()
    {
        $query = "DELETE FROM `tiki_user_watches` WHERE `event` = 'thread_comment_replied' AND `object` NOT IN (SELECT `threadId` FROM `tiki_comments`)";
        $this->query($query);
    }

    /**
     * @param $group
     * @param $event
     * @param $object
     * @param string $type
     */
    public function remove_group_watch($group, $event, $object, $type = 'wiki page')
    {
        $conditions = [
            'group' => $group,
            'event' => $event,
            'object' => $object,
        ];
        if (isset($type)) {
            $conditions['type'] = $type;
        }

        $this->table('tiki_group_watches')->deleteMultiple($conditions);
    }

    /*shared*/
    /**
     * @param $user
     * @param string $event
     * @return mixed
     */
    public function get_user_watches($user, $event = '')
    {
        $userWatches = $this->table('tiki_user_watches');

        $conditions = [
            'user' => $userWatches->exactly($user),
        ];

        if ($event) {
            $conditions['event'] = $event;
        }

        return $userWatches->fetchAll($userWatches->all(), $conditions);
    }

    /*shared*/
    /**
     * @return array
     */
    public function get_watches_events()
    {
        $query = "select distinct `event` from `tiki_user_watches`";
        $result = $this->fetchAll($query, []);
        $ret = [];
        foreach ($result as $res) {
            $ret[] = $res['event'];
        }
        return $ret;
    }

    /*shared*/
    /**
     * @param $user
     * @param $event
     * @param $object
     * @param null $type
     * @return bool
     */
    public function user_watches($user, $event, $object, $type = null)
    {
        $userWatches = $this->table('tiki_user_watches');

        $conditions = [
            'user' => $user,
            'object' => $object,
        ];

        if ($type) {
            $conditions['type'] = $type;
        }

        if (is_array($event)) {
            $conditions['event'] = $userWatches->in($event);

            $ret = $userWatches->fetchColumn('event', $conditions);

            return empty($ret) ? false : $ret;
        } else {
            return $userWatches->fetchCount($conditions);
        }
    }

    /**
     * @param $object
     * @param $event
     * @param null $type
     * @return mixed
     */
    public function get_groups_watching($object, $event, $type = null)
    {
        $groupWatches = $this->table('tiki_group_watches');
        $conditions = [
            'object' => $object,
            'event' => $event,
        ];

        if ($type) {
            $conditions['type'] = $type;
        }

        return $groupWatches->fetchColumn('group', $conditions);
    }

    /*shared*/
    /**
     * @param $user
     * @param $event
     * @param $object
     * @return mixed
     */
    public function get_user_event_watches($user, $event, $object)
    {
        $userWatches = $this->table('tiki_user_watches');
        return $userWatches->fetchAll(
            $userWatches->all(),
            [
                'user' => $user,
                'event' => $event,
                'object' => is_array($object) ? $userWatches->in($object) : $object,
            ]
        );
    }

    /*shared*/
    /**
     * @param $event
     * @param $object
     * @param null $info
     * @return array
     */
    public function get_event_watches($event, $object, $info = null)
    {
        global $prefs;
        $ret = [];

        $mid = '';
        if ($prefs['feature_user_watches_translations'] == 'y'  && $event == 'wiki_page_changed') {
            // If $prefs['feature_user_watches_translations'] is turned on, also look for
            // pages in a translation group.
            $mid = "`event`=?";
            $bindvars[] = $event;
            $multilinguallib = TikiLib::lib('multilingual');
            $page_info = $this->get_page_info($object);
            $pages = $multilinguallib->getTranslations('wiki page', $page_info['page_id'], $object, '');
            foreach ($pages as $page) {
                $mids[] = "`object`=?";
                $bindvars[] = $page['objName'];
            }
            $mid .= ' and (' . implode(' or ', $mids) . ')';
        } elseif (
            $prefs['feature_user_watches_translations'] == 'y'
            && $event == 'wiki_page_created'
        ) {
            $page_info = $this->get_page_info($object);
            $mid = "`event`='wiki_page_in_lang_created' and `object`=? and `type`='lang'";
            $bindvars[] = $page_info['lang'];
        } elseif ($prefs['feature_user_watches_languages'] == 'y' && $event == 'category_changed') {
            $mid = "`object`=? and ((`event`='category_changed_in_lang' and `type`=? ) or (`event`='category_changed'))";
            $bindvars[] = $object;
            $bindvars[] = $info['lang'];
        } elseif ($event == 'forum_post_topic') {
            $mid = "(`event`=? or `event`=?) and `object`=?";
            $bindvars[] = $event;
            $bindvars[] = 'forum_post_topic_and_thread';
            $bindvars[] = $object;
        } elseif ($event == 'forum_post_thread') {
            $mid = "(`event`=? and `object`=?) or ( `event`=? and `object`=?)";
            $bindvars[] = $event;
            $bindvars[] = $object;
            $bindvars[] = 'forum_post_topic_and_thread';
            $forumId = $info['forumId'];
            $bindvars[] = $forumId;
        } else {
            $extraEvents = "";
            if (substr_count($event, 'article_')) {
                $extraEvents = " or `event`='article_*'";
            } elseif ($event == 'wiki_comment_changes') {
                $extraEvents = " or `event`='wiki_page_changed'";
            // Blog comment mail
            } elseif ($event == 'blog_comment_changes') {
                $extraEvents = " or `event`='blog_page_changed'";
            }
            $mid = "(`event`=?$extraEvents) and (`object`=? or `object`='*')";
            $bindvars[] = $event;
            $bindvars[] = $object;
        }

        // Obtain the list of watches on event/object for user watches
        // Union obtains all users member of groups being watched
        // Distinct union insures there are no duplicates
        $query = "select tuw.`watchId`, tuw.`user`, tuw.`event`, tuw.`object`, tuw.`title`, tuw.`type`, tuw.`url`, tuw.`email`,
                tup1.`value` as language, tup2.`value` as mailCharset
            from
                `tiki_user_watches` tuw
                left join `tiki_user_preferences` tup1 on (tup1.`user`=tuw.`user` and tup1.`prefName`='language')
                left join `tiki_user_preferences` tup2 on (tup2.`user`=tuw.`user` and tup2.`prefName`='mailCharset')
                where $mid
            UNION DISTINCT
            select tgw.`watchId`, uu.`login`, tgw.`event`, tgw.`object`, tgw.`title`, tgw.`type`, tgw.`url`, uu.`email`,
                tup1.`value` as language, tup2.`value` as mailCharset
            from
                `tiki_group_watches` tgw
                inner join `users_usergroups` ug on tgw.`group` = ug.`groupName`
                inner join `users_users` uu on ug.`userId` = uu.`userId` and uu.`email` is not null and uu.`email` <> ''
                left join `tiki_user_preferences` tup1 on (tup1.`user`=uu.`login` and tup1.`prefName`='language')
                left join `tiki_user_preferences` tup2 on (tup2.`user`=uu.`login` and tup2.`prefName`='mailCharset')
                where $mid
                ";
        $result = $this->fetchAll($query, array_merge($bindvars, $bindvars));

        if (count($result) > 0) {
            foreach ($result as $res) {
                if (empty($res['language'])) {
                    $res['language'] = $this->get_preference('site_language');
                }
                switch ($event) {
                    case 'wiki_page_changed':
                    case 'wiki_page_created':
                        $res['perm'] = ($this->user_has_perm_on_object($res['user'], $object, 'wiki page', 'tiki_p_view') ||
                                $this->user_has_perm_on_object($res['user'], $object, 'wiki page', 'tiki_p_admin_wiki'));
                        break;
                    case 'tracker_modified':
                        $res['perm'] = $this->user_has_perm_on_object($res['user'], $object, 'tracker', 'tiki_p_view_trackers');
                        break;
                    case 'tracker_item_modified':
                        $res['perm'] = $this->user_has_perm_on_object($res['user'], $object, 'trackeritem', 'tiki_p_view_trackers');
                        break;
                    case 'blog_post':
                        $res['perm'] = ($this->user_has_perm_on_object($res['user'], $object, 'blog', 'tiki_p_read_blog') ||
                                $this->user_has_perm_on_object($res['user'], $object, 'blog', 'tiki_p_admin_blog'));
                        break;
                    // Blog comment mail
                    case 'blog_comment_changes':
                        $res['perm'] = ($this->user_has_perm_on_object($res['user'], $object, 'blog', 'tiki_p_read_blog') ||
                                $this->user_has_perm_on_object($res['user'], $object, 'comments', 'tiki_p_read_comments'));
                        break;
                    case 'forum_post_topic':
                        $res['perm'] = ($this->user_has_perm_on_object($res['user'], $object, 'forum', 'tiki_p_forum_read') ||
                                $this->user_has_perm_on_object($res['user'], $object, 'forum', 'tiki_p_admin_forum'));
                        break;
                    case 'forum_post_thread':
                        $res['perm'] = ($this->user_has_perm_on_object($res['user'], $object, 'thread', 'tiki_p_forum_read') ||
                                $this->user_has_perm_on_object($res['user'], $object, 'forum', 'tiki_p_admin_forum'));
                        break;
                    case 'file_gallery_changed':
                        $res['perm'] = ($this->user_has_perm_on_object($res['user'], $object, 'file gallery', 'tiki_p_view_file_gallery') ||
                                $this->user_has_perm_on_object($res['user'], $object, 'file gallery', 'tiki_p_download_files'));
                        break;
                    case 'article_submitted':
                    case 'article_edited':
                    case 'article_deleted':
                        $userlib = TikiLib::lib('user');
                        $res['perm'] = (empty($object) && $userlib->user_has_permission($res['user'], 'tiki_p_read_article'))
                            || $this->user_has_perm_on_object($res['user'], $object, 'article', 'tiki_p_read_article');
                        break;
                    case 'topic_article_created':
                    case 'topic_article_edited':
                    case 'topic_article_deleted':
                        $userlib = TikiLib::lib('user');
                        $res['perm'] = (empty($object) && $userlib->user_has_permission($res['user'], 'tiki_p_read_article'))
                            || $this->user_has_perm_on_object($res['user'], $object, 'topic', 'tiki_p_read_article');
                        break;
                    case 'calendar_changed':
                        $res['perm'] = $this->user_has_perm_on_object($res['user'], $object, 'calendar', 'tiki_p_view_calendar');
                        break;
                    case 'category_changed':
                        $categlib = TikiLib::lib('categ');
                        $res['perm'] = $categlib->has_view_permission($res['user'], $object);
                        break;
                    case 'fgal_quota_exceeded':
                        global $tiki_p_admin_file_galleries;
                        $res['perm'] = ($tiki_p_admin_file_galleries == 'y');
                        break;
                    case 'article_commented':
                    case 'wiki_comment_changes':
                        $res['perm'] = $this->user_has_perm_on_object($res['user'], $object, 'comments', 'tiki_p_read_comments');
                        break;
                    case 'user_registers':
                        $userlib = TikiLib::lib('user');
                        $res['perm'] = $userlib->user_has_permission($res['user'], 'tiki_p_admin');
                        break;
                    case 'auth_token_called':
                        $res['perm'] = true;
                        break;
                    case 'user_joins_group':
                        $res['perm'] = $this->user_has_perm_on_object($res['user'], $object, 'group', 'tiki_p_group_view_members');
                        break;
                    case 'thread_comment_replied':
                        $res['perm'] = true;
                        break;
                    default:
                        // for security we deny all others.
                        $res['perm'] = false;
                        break;
                }

                if ($res['perm'] || empty($res['user']) && ! empty($res['email'])) {
                    // Allow admin created email (non-user) watches
                    $ret[] = $res;
                }
            }
        }

        // Also include users that are watching a category to which this object belongs to.
        if ($event != 'category_changed') {
            if ($prefs['feature_categories'] == 'y') {
                $categlib = TikiLib::lib('categ');
                $objectType = "";
                switch ($event) {
                    case 'wiki_page_changed':
                        $objectType = "wiki page";
                        break;
                    case 'wiki_page_created':
                        $objectType = "wiki page";
                        break;
                    case 'blog_post':
                        $objectType = "blog";
                        break;
                    // Blog comment mail
                    case 'blog_page_changed':
                        $objectType = "blog page";
                        break;
                    case 'map_changed':
                        $objectType = "map_changed";
                        break;
                    case 'forum_post_topic':
                        $objectType = "forum";
                        break;
                    case 'forum_post_thread':
                        $objectType = "forum";
                        break;
                    case 'file_gallery_changed':
                        $objectType = "file gallery";
                        break;
                    case 'article_submitted':
                        $objectType = "topic";
                        break;
                    case 'tracker_modified':
                        $objectType = "tracker";
                        break;
                    case 'tracker_item_modified':
                        $objectType = "tracker";
                        break;
                    case 'calendar_changed':
                        $objectType = "calendar";
                        break;
                }
                if ($objectType != "") {
                    // If a forum post was changed, check the categories of the forum.
                    if ($event == "forum_post_thread") {
                        $commentslib = TikiLib::lib('comments');
                        $object = $commentslib->get_comment_forum_id($object);
                    }

                    // If a tracker item was changed, check the categories of the tracker.
                    if ($event == "tracker_item_modified") {
                        $trklib = TikiLib::lib('trk');
                        $object = $trklib->get_tracker_for_item($object);
                    }

                    $categs = $categlib->get_object_categories($objectType, $object);

                    foreach ($categs as $category) {
                        $watching_users = $this->get_event_watches('category_changed', $category, $info);

                        // Add all users that are not already included
                        foreach ($watching_users as $wu) {
                            $included = false;
                            foreach ($ret as $item) {
                                if ($item['user'] == $wu['user']) {
                                    $included = true;
                                }
                            }
                            if (! $included) {
                                $ret[] = $wu;
                            }
                        }
                    }
                }
            }
        }
        return $ret;
    }

    /*shared*/
    /**
     * @return array
     */
    public function dir_stats()
    {
        $sites = $this->table('tiki_directory_sites');
        $categories = $this->table('tiki_directory_categories');
        $search = $this->table('tiki_directory_search');

        $aux = [];
        $aux["valid"] = $sites->fetchCount(['isValid' => 'y']);
        $aux["invalid"] = $sites->fetchCount(['isValid' => 'n']);
        $aux["categs"] = $categories->fetchCount([]);
        $aux["searches"] = $search->fetchOne($search->sum('hits'), []);
        $aux["visits"] = $search->fetchOne($sites->sum('hits'), []);
        return $aux;
    }

    /*shared*/
    /**
     * @param $offset
     * @param $maxRecords
     * @param $sort_mode
     * @param $find
     * @return array
     */
    public function dir_list_all_valid_sites2($offset, $maxRecords, $sort_mode, $find)
    {
        $sites = $this->table('tiki_directory_sites');
        $conditions = [
            'isValid' => 'y',
        ];

        if ($find) {
            $conditions['search'] = $sites->expr('(`name` like ? or `description` like ?)', ["%$find%", "%$find%"]);
        }

        return [
            'data' => $sites->fetchAll($sites->all(), $conditions, $maxRecords, $offset, $sites->expr($this->convertSortMode($sort_mode))),
            'cant' => $sites->fetchCount($conditions),
        ];
    }

    /*shared*/
    /**
     * @param $categId
     * @return mixed
     */
    public function get_directory($categId)
    {
        return $this->table('tiki_directory_categories')->fetchFullRow(['categId' => $categId]);
    }

    /*shared*/
    /**
     * @param $user
     * @return mixed
     */
    public function user_unread_messages($user)
    {
        $messages = $this->table('messu_messages');
        return $messages->fetchCount(
            [
                'user' => $user,
                'isRead' => 'n',
            ]
        );
    }

    /*shared*/
    /**
     * @return array
     */
    public function get_online_users()
    {
        if (! isset($this->online_users_cache)) {
            $this->update_session();
            $this->online_users_cache = [];
            $query = "select s.`user`, p.`value` as `realName`, `timestamp`, `tikihost` from `tiki_sessions` s left join `tiki_user_preferences` p on s.`user`<>? and s.`user` = p.`user` and p.`prefName` = 'realName' where s.`user` is not null;";
            $result = $this->fetchAll($query, ['']);
            foreach ($result as $res) {
                $res['user_information'] = $this->get_user_preference($res['user'], 'user_information', 'public');
                $res['allowMsgs'] = $this->get_user_preference($res['user'], 'allowMsgs', 'y');
                $this->online_users_cache[$res['user']] = $res;
            }
        }
        return $this->online_users_cache;
    }

    /*shared*/
    /**
     * @param $whichuser
     * @return bool
     */
    public function is_user_online($whichuser)
    {
        if (! isset($this->online_users_cache)) {
            $this->get_online_users();
        }

        return(isset($this->online_users_cache[$whichuser]));
    }

    /*
     * Score methods begin
     */
    // All information about an event type
    // shared
    /**
     * @param $event
     * @return mixed
     */
    public function get_event($event)
    {
        return $this->table('tiki_score')->fetchFullRow(['event' => $event]);
    }

    // List users by best scoring
    // shared
    /**
     * @param int $limit
     * @param int $start
     * @return mixed
     */
    public function rank_users($limit = 10, $start = 0)
    {
        global $prefs;
        $score_expiry_days = $prefs['feature_score_expday'];

        if (! $start) {
            $start = "0";
        }

        if (empty($score_expiry_days)) {
            // score does not expire
            $query = "select `recipientObjectId` as `login`,
                `pointsBalance` as `score`
                from `tiki_object_scores` tos
                where `recipientObjectType`='user'
                and tos.`id` = (select max(id) from `tiki_object_scores` where `recipientObjectId` = tos.`recipientObjectId` and `recipientObjectType`='user' group by `recipientObjectId`)
                group by `recipientObjectId`, `pointsBalance` order by `score` desc";

            $result = $this->fetchAll($query, null, $limit, $start);
        } else {
            // score expires
            $query = "select `recipientObjectId` as `login`,
                `pointsBalance` - ifnull((select `pointsBalance` from `tiki_object_scores`
                    where `recipientObjectId`=tos.`recipientObjectId`
                    and `recipientObjectType`='user'
                    and `date` < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ? DAY))
                    order by id desc limit 1), 0) as `score`
                from `tiki_object_scores` tos
                where `recipientObjectType`='user'
                and tos.`id` = (select max(id) from `tiki_object_scores` where `recipientObjectId` = tos.`recipientObjectId` and `recipientObjectType`='user' group by `recipientObjectId`)
                group by `recipientObjectId`, `pointsBalance` order by `score` desc";

            $result = $this->fetchAll($query, $score_expiry_days, $limit, $start);
        }

        foreach ($result as & $res) {
            $res['position'] = ++$start;
        }
        return $result;
    }

    // Returns html <img> tag to star corresponding to user's score
    // shared
    /**
     * @param $score
     * @return string
     */
    public function get_star($score)
    {
        global $prefs;
        $star = '';
        $star_colors = [0 => 'grey',
                100 => 'blue',
                500 => 'green',
                1000 => 'yellow',
                2500 => 'orange',
                5000 => 'red',
                10000 => 'purple'];
        foreach ($star_colors as $boundary => $color) {
            if ($score >= $boundary) {
                $star = 'star_' . $color . '.gif';
            }
        }
        if (! empty($star)) {
            $alt = sprintf(tra("%d points"), $score);
            if ($prefs['theme_iconset'] === 'legacy') {
                $star = "<img src='img/icons/$star' height='11' width='11' alt='$alt' />&nbsp;";
            } else {
                $smarty = TikiLib::lib('smarty');
                $smarty->loadPlugin('smarty_function_icon');
                $star = smarty_function_icon(['name' => 'star', 'istyle' => 'color:' . $color, 'iclass' => 'tips',
                    'ititle' => ':' . $alt], $smarty->getEmptyInternalTemplate()) . "&nbsp;";
            }
        }
        return $star;
    }

    /*
     * Score methods end
     */
    //shared
    // \todo remove all hardcoded html in get_user_avatar()
    /**
     * @param $user
     * @param string $float
     * @return string
     */
    public function get_user_avatar($user, $float = '')
    {
        global $prefs;

        if (empty($user)) {
            return '';
        }

        if (is_array($user)) {
            $res = $user;
            $user = $user['login'];
        } else {
            $res = $this->table('users_users')->fetchRow(['login', 'avatarType', 'avatarLibName', 'email'], ['login' => $user]);
        }

        if (! $res) {
            return '';
        }

        if ($prefs['user_use_gravatar'] == 'y' && $res['email']) {
            $https_mode = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on';
            $hash = md5(strtolower(trim($res['email'])));

            if ($https_mode) {
                $url = "https://secure.gravatar.com/avatar/$hash?s=45";
            } else {
                $url = "http://www.gravatar.com/avatar/$hash?s=45";
            }
            $type = 'g';
        } else {
            $type = $res["avatarType"] ? $res["avatarType"] : 'u';
            $libname = $res["avatarLibName"];
            $ret = '';
        }

        $style = '';

        if (strcasecmp($float, "left") == 0) {
            $style = "style='float:left;margin-right:5px;'";
        } elseif (strcasecmp($float, "right") == 0) {
            $style = "style='float:right;margin-left:5px;'";
        }

        $username = htmlspecialchars(
            TikiLib::lib('user')->clean_user($user),
            ENT_COMPAT
        );

        switch ($type) {
            case 'l':
                if ($libname) {
                    $ret = '<img class="user-profile-picture rounded" width="45" height="45" src="' . $libname . '" ' . $style . ' alt="' . $username . '">';
                }
                break;
            case 'u':
                $userprefslib = TikiLib::lib('userprefs');
                $path = $userprefslib->get_public_avatar_path($user);

                if ($path) {
                    $url = $this->tikiUrlOpt($path);
                    $ret = '<img class="user-profile-picture rounded" src="' . htmlspecialchars($url, ENT_NOQUOTES) . '" ' . $style . ' alt="' . $username . '">';
                }
                break;
            case 'g':
                $ret = '<img class="user-profile-picture rounded" src="' . htmlspecialchars($url, ENT_NOQUOTES) . '" ' . $style . ' alt="' . $username . '">';
                break;
            case 'n':
            default:
                $ret = '';
                break;
        }
        return $ret;
    }

    /**
     * Return user avatar as Base64 encoded inline image.
     */
    public function get_user_avatar_inline($user)
    {
        global $prefs;

        if (empty($user)) {
            return '';
        }

        if (is_array($user)) {
            $res = $user;
            $user = $user['login'];
        } else {
            $res = $this->table('users_users')->fetchRow(['login', 'avatarType', 'avatarFileType', 'avatarData', 'avatarLibName', 'email'], ['login' => $user]);
        }

        if (! $res) {
            return '';
        }

        if ($prefs['user_use_gravatar'] == 'y' && $res['email']) {
            $hash = md5(strtolower(trim($res['email'])));
            $url = "https://secure.gravatar.com/avatar/$hash.jpg?s=45";
            $data = file_get_contents($url);
            $mime = 'image/jpeg';
        } elseif ($res['avatarType'] == 'l') {
            $url = $this->tikiUrlOpt($res['avatarLibName']);
            $data = file_get_contents($url);
            if (class_exists('finfo')) {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->buffer($data);
            } else {
                $mime = 'image/jpeg';
            }
        } else {
            $data = $res['avatarData'];
            $mime = $res['avatarFileType'];
        }

        if ($data && $mime) {
            return "data:$mime;base64," . base64_encode($data);
        } else {
            return '';
        }
    }

    /*shared*/
    /**
     * @return array
     */
    public function get_forum_sections()
    {
        $query = "select distinct `section` from `tiki_forums` where `section`<>?";
        $result = $this->fetchAll($query, ['']);
        $ret = [];
        foreach ($result as $res) {
            $ret[] = $res["section"];
        }
        return $ret;
    }

    /* Referer stats */
    /*shared*/
    /**
     * @param $referer
     * @param $fullurl
     */
    public function register_referer($referer, $fullurl)
    {
        $refererStats = $this->table('tiki_referer_stats');

        $cant = $refererStats->fetchCount(['referer' => $referer]);

        if ($cant) {
            $refererStats->update(
                [
                    'hits' => $refererStats->increment(1),
                    'last' => $this->now,
                    'lasturl' => $fullurl,
                ],
                ['referer' => $referer]
            );
        } else {
            $refererStats->insert(
                [
                    'last' => $this->now,
                    'referer' => $referer,
                    'hits' => 1,
                    'lasturl' => $fullurl,
                ]
            );
        }
    }

    // File attachments functions for the wiki ////
    /*shared*/
    /**
     * @param $id
     * @return bool
     */
    public function add_wiki_attachment_hit($id)
    {
        global $prefs, $user;
        if (StatsLib::is_stats_hit()) {
            $wikiAttachments = $this->table('tiki_wiki_attachments');
            $wikiAttachments->update(
                ['hits' => $wikiAttachments->increment(1)],
                ['attId' => (int) $id]
            );
        }
        return true;
    }

    /*shared*/
    /**
     * @param $attId
     * @return mixed
     */
    public function get_wiki_attachment($attId)
    {
        return $this->table('tiki_wiki_attachments')->fetchFullRow(['attId' => (int) $attId]);
    }

    // Last visit module ////
    /*shared*/
    /**
     * @param $user
     * @return array|bool
     */
    public function get_news_from_last_visit($user)
    {
        if (! $user) {
            return false;
        }

        $last = $this->table('users_users')->fetchOne('lastLogin', ['login' => $user]);

        $ret = [];
        if (! $last) {
            $last = time();
        }
        $ret["lastVisit"] = $last;
        $ret["pages"] = $this->getOne("select count(*) from `tiki_pages` where `lastModif`>?", [(int)$last]);
        $ret["files"] = $this->getOne("select count(*) from `tiki_files` where `created`>?", [(int)$last]);
        $ret["comments"] = $this->getOne("select count(*) from `tiki_comments` where `commentDate`>?", [(int)$last]);
        $ret["users"] = $this->getOne("select count(*) from `users_users` where `registrationDate`>? and `provpass`=?", [(int)$last, '']);
        $ret["trackers"] = $this->getOne("select count(*) from `tiki_tracker_items` where `lastModif`>?", [(int)$last]);
        $ret["calendar"] = $this->getOne("select count(*) from `tiki_calendar_items` where `lastmodif`>?", [(int)$last]);
        return $ret;
    }

    /**
     * @return mixed|string
     */
    public function pick_cookie()
    {
        $cant = $this->getOne("select count(*) from `tiki_cookies`", []);
        if (! $cant) {
            return '';
        }

        $bid = rand(0, $cant - 1);
        //$cookie = $this->getOne("select `cookie`  from `tiki_cookies` limit $bid,1"); getOne seems not to work with limit
        $result = $this->query("select `cookie`  from `tiki_cookies`", [], 1, $bid);
        if ($res = $result->fetchRow()) {
            $cookie = str_replace("\n", "", $res['cookie']);
            return preg_replace('/^(.+?)(\s*--.+)?$/', '<em>"$1"</em>$2', $cookie);
        } else {
            return "";
        }
    }

    public function get_usage_chart_data()
    {
        TikiLib::lib('quiz')->compute_quiz_stats();

        $data['xdata'][] = tra('wiki');
        $data['ydata'][] = $this->getOne('select sum(`hits`) from `tiki_pages`', []);

        $data['xdata'][] = tra('file-g');
        $data['ydata'][] = $this->getOne('select sum(`hits`) from `tiki_file_galleries`', []);

        $data['xdata'][] = tra('FAQs');
        $data['ydata'][] = $this->getOne('select sum(`hits`) from `tiki_faqs`', []);

        $data['xdata'][] = tra('quizzes');
        $data['ydata'][] = $this->getOne('select sum(`timesTaken`) from `tiki_quiz_stats_sum`', []);

        $data['xdata'][] = tra('arts');
        $data['ydata'][] = $this->getOne('select sum(`nbreads`) from `tiki_articles`', []);

        $data['xdata'][] = tra('blogs');
        $data['ydata'][] = $this->getOne('select sum(`hits`) from `tiki_blogs`', []);

        $data['xdata'][] = tra('forums');
        $data['ydata'][] = $this->getOne('select sum(`hits`) from `tiki_forums`', []);

        return $data;
    }

    // User assigned modules ////
    /*shared*/
    /**
     * @param $id
     * @return mixed
     */
    public function get_user_login($id)
    {
        return $this->table('users_users')->fetchOne('login', ['userId' => (int) $id]);
    }

    /**
     * @param $u
     * @return int
     */
    public function get_user_id($u)
    {
        // Anonymous is not in db
        if ($u == '') {
            return -1;
        }

        // If we ask for the current user id and if we already know it in session
        $current = ( isset($_SESSION['u_info']) && $u == $_SESSION['u_info']['login'] );
        if (isset($_SESSION['u_info']['id']) && $current) {
            return $_SESSION['u_info']['id'];
        }

        // In other cases, we look in db
        $id = $this->table('users_users')->fetchOne('userId', ['login' => $u]);
        $id = ($id === false) ? -1 : $id;
        if ($current) {
            $_SESSION['u_info']['id'] = $id;
        }
        return $id;
    }

    /*shared*/
    /**
     * @param $group
     * @return array
     */
    public function get_groups_all($group)
    {
        $result = $this->table('tiki_group_inclusion')->fetchColumn('groupName', ['includeGroup' => $group]);
        $ret = $result;
        foreach ($result as $res) {
            $ret = array_merge($ret, $this->get_groups_all($res));
        }
        return array_unique($ret);
    }

    /*shared*/
    /**
     * @param $group
     * @return array
     */
    public function get_included_groups($group)
    {
        $result = $this->table('tiki_group_inclusion')->fetchColumn('includeGroup', ['groupName' => $group]);
        $ret = $result;
        foreach ($result as $res) {
            $ret = array_merge($ret, $this->get_included_groups($res));
        }
        return array_unique($ret);
    }

    /*shared*/
    /**
     * @param string  $user              username
     * @param bool    $included_groups   include inherited/included groups
     *
     * @return array
     */
    public function get_user_groups($user, $included_groups = true)
    {
        global $prefs;
        $userlib = TikiLib::lib('user');
        if (empty($user) || $user === 'Anonymous') {
            $ret = [];
            $ret[] = "Anonymous";
            return $ret;
        }
        if ($prefs['feature_intertiki'] == 'y' and empty($prefs['feature_intertiki_mymaster']) and strstr($user, '@')) {
            $realm = substr($user, strpos($user, '@') + 1);
            if (isset($prefs['interlist'][$realm])) {
                $user = substr($user, 0, strpos($user, '@'));
                $groups = $prefs['interlist'][$realm]['groups'] . ',Anonymous';
                return explode(',', $groups);
            }
        }
        $cachekey = $user . ($included_groups ? '' : '_direct');
        if (! isset($this->usergroups_cache[$cachekey])) {
            $userid = $this->get_user_id($user);
            $result = $this->table('users_usergroups')->fetchColumn('groupName', ['userId' => $userid]);
            $ret = $result;
            if ($included_groups) {
                foreach ($result as $res) {
                    $ret = array_merge($ret, $userlib->get_included_groups($res));
                }
            }
            if ($ret) { // only in Registereed if the user exists
                $ret[] = "Registered";
            }

            if (isset($_SESSION["groups_are_emulated"]) && $_SESSION["groups_are_emulated"] == "y") {
                if (in_array('Admins', $ret)) {
                    // Members of group 'Admins' can emulate being in any list of groups
                    $ret = unserialize($_SESSION['groups_emulated']);
                } else {
                    // For security purposes, user can only emulate a subset of user's list of groups
                    // This prevents privilege escalation
                    $ret = array_intersect($ret, unserialize($_SESSION['groups_emulated']));
                }
            }
            $ret = array_values(array_unique($ret));
            if ($ret) {
                $this->usergroups_cache[$cachekey] = $ret;
            }
            return $ret;
        } else {
            return $this->usergroups_cache[$cachekey];
        }
    }

    /**
     * @param $user
     */
    public function invalidate_usergroups_cache($user)
    {
        unset($this->usergroups_cache[$user]);
        unset($this->usergroups_cache[$user . '_direct']);
    }

    /**
     * @param $user
     * @return string
     */
    public function get_user_cache_id($user)
    {
        $groups = $this->get_user_groups($user);
        sort($groups, SORT_STRING);
        $cacheId = implode(":", $groups);
        if ($user == 'admin') {
            // in this case user get permissions from no group
            $cacheId = 'ADMIN:' . $cacheId;
        }
        return $cacheId;
    }

    /*shared*/
    /**
     * @return string
     * @see UsersLib::genPass(), which generates passwords easier to remember
     * TODO: Merge with above
     */
    public static function genPass()
    {
        global $prefs;
        $length = max($prefs['min_pass_length'], 8);
        $list = ['aeiou', 'AEIOU', 'bcdfghjklmnpqrstvwxyz', 'BCDFGHJKLMNPQRSTVWXYZ', '0123456789'];
        $list[] = $prefs['pass_chr_special'] == 'y' ? '_*&+!*-=$@' : '_';
        shuffle($list);
        $r = '';
        for ($i = 0; $i < $length; $i++) {
            $ch = $list[$i % count($list)];
            $r .= $ch[rand(0, strlen($ch) - 1)];
        }
        return $r;
    }

    // generate a random string (for unsubscription code etc.)
    /**
     * @param string $base
     * @return string
     */
    public function genRandomString($base = "")
    {
        if ($base == "") {
            $base = $this->genPass();
        }
        $base .= microtime();
        return md5($base);
    }

    // This function calculates the pageRanks for the tiki_pages
    // it can be used to compute the most relevant pages
    // according to the number of links they have
    // this can be a very interesting ranking for the Wiki
    // More about this on version 1.3 when we add the pageRank
    // column to tiki_pages
    /**
     * @param int $loops
     * @return array
     */
    public function pageRank($loops = 16)
    {
        $pagesTable = $this->table('tiki_pages');

        $ret = $pagesTable->fetchColumn('pageName', []);

        // Now calculate the loop
        $pages = [];

        foreach ($ret as $page) {
            $val = 1 / count($ret);

            $pages[$page] = $val;

            $pagesTable->update(['pageRank' => (int) $val], ['pageName' => $page]);
        }

        for ($i = 0; $i < $loops; $i++) {
            foreach ($pages as $pagename => $rank) {
                // Get all the pages linking to this one
                // Fixed query.  -rlpowell
                $query = "select `fromPage`  from `tiki_links` where `toPage` = ? and `fromPage` not like 'objectlink:%'";
                // page rank does not count links from non-page objects TODO: full feature allowing this with options
                $result = $this->fetchAll($query, [$pagename]);
                $sum = 0;

                foreach ($result as $res) {
                    $linking = $res["fromPage"];

                    if (isset($pages[$linking])) {
                        // Fixed query.  -rlpowell
                        $q2 = "select count(*) from `tiki_links` where `fromPage`= ? and `fromPage` not like 'objectlink:%'";
                        // page rank does not count links from non-page objects TODO: full feature allowing this with options
                        $cant = $this->getOne($q2, [$linking]);
                        if ($cant == 0) {
                            $cant = 1;
                        }
                        $sum += $pages[$linking] / $cant;
                    }
                }

                $val = (1 - 0.85) + 0.85 * $sum;
                $pages[$pagename] = $val;

                $pagesTable->update(['pageRank' => (int) $val], ['pageName' => $pagename]);
            }
        }
        arsort($pages);
        return $pages;
    }

    /**
     * @param $maxRecords
     * @return array
     */
    public function list_recent_forum_topics($maxRecords)
    {
        $bindvars = ['forum', 0];

        $query = 'select `threadId`, `forumId` from `tiki_comments`,`tiki_forums`'
              . " where `object`=`forumId` and `objectType`=? and `tiki_comments`.`parentId`=? order by " . $this->convertSortMode('commentDate_desc');
        $result = $this->fetchAll($query, $bindvars, $maxRecords * 3, 0); // Load a little more, for permission filters
        $res = $ret = $retids = [];
        $n = 0;

        foreach ($result as $res) {
            $objperm = $this->get_perm_object($res['threadId'], 'thread', '', false);
            if ($objperm['tiki_p_forum_read'] == 'y') {
                $retids[] = $res['threadId'];

                $n++;

                if ($n >= $maxRecords) {
                    break;
                }
            }
        }

        if ($n > 0) {
            $query = 'select * from `tiki_comments`'
              . ' where `threadId` in (' . implode(',', $retids) . ') order by ' . $this->convertSortMode('commentDate_desc');
            $ret = $this->fetchAll($query);
        }

        $retval = [];
        $retval['data'] = $ret;
        $retval['cant'] = $n;
        return $retval;
    }

    /*shared*/
    /**
     * @param $forumId
     * @param $offset
     * @param $maxRecords
     * @param $sort_mode
     * @param $find
     * @return array
     */
    public function list_forum_topics($forumId, $offset, $maxRecords, $sort_mode, $find)
    {
        $bindvars = [$forumId,$forumId,'forum',0];
        if ($find) {
            $findesc = '%' . $find . '%';
            $mid = " and (`title` like ? or `data` like ?)";
            $bindvars[] = $findesc;
            $bindvars[] = $findesc;
        } else {
            $mid = "";
        }

        $query = "select * from `tiki_comments`,`tiki_forums` where ";
        $query .= " `forumId`=? and `object`=? and `objectType`=? and `parentId`=? $mid order by " . $this->convertSortMode($sort_mode);
        $query_cant = "select count(*) from `tiki_comments`,`tiki_forums` where ";
        $query_cant .= " `forumId`=? and `object`=? and `objectType`=? and `parentId`=? $mid";
        $ret = $this->fetchAll($query, $bindvars, $maxRecords, $offset);
        $cant = $this->getOne($query_cant, $bindvars);

        $retval = [];
        $retval["data"] = $ret;
        $retval["cant"] = $cant;
        return $retval;
    }

    /*shared*/
    /**
     * @param $type
     * @param $id
     * @return bool
     */
    public function remove_object($type, $id)
    {
        global $prefs;
        $categlib = TikiLib::lib('categ');
        $objectlib = TikiLib::lib('object');
        $categlib->uncategorize_object($type, $id);

        // Now remove comments
        $threads = $this->table('tiki_comments')->fetchColumn('threadId', ['object' => $id, 'objectType' => $type]);
        if (! empty($threads)) {
            $commentslib = TikiLib::lib('comments');

            foreach ($threads as $threadId) {
                $commentslib->remove_comment($threadId);
            }
        }

        // Remove individual permissions for this object if they exist
        $object = $type . $id;
        $this->table('users_objectpermissions')->deleteMultiple(['objectId' => md5($object), 'objectType' => $type]);
        // remove links from this object to pages
        $linkhandle = "objectlink:$type:$id";
        $this->table('tiki_links')->deleteMultiple(['fromPage' => $linkhandle]);
        // remove fgal backlinks
        if ($prefs['feature_file_galleries'] == 'y') {
            $filegallib = TikiLib::lib('filegal');
            $filegallib->deleteBacklinks(['type' => $type, 'object' => $id]);
        }
        // remove object
        $objectlib->delete_object($type, $id);

        $objectAttributes = $this->table('tiki_object_attributes');
        $objectAttributes->deleteMultiple(['type' => $type,'itemId' => $id]);

        $objectRelations = $this->table('tiki_object_relations');
        $objectRelations->deleteMultiple(['source_type' => $type,   'source_itemId' => $id]);
        $objectRelations->deleteMultiple(['target_type' => $type,   'target_itemId' => $id]);

        return true;
    }

    /*shared*/
    /**
     * @param $offset
     * @param $maxRecords
     * @param $sort_mode
     * @param string $find
     * @param string $type
     * @param string $structureName
     * @return array
     */
    public function list_received_pages($offset, $maxRecords, $sort_mode, $find = '', $type = '', $structureName = '')
    {
        $bindvars = [];
        if ($type == 's') {
            $mid = ' `trp`.`structureName` is not null ';
        }
        if (! $sort_mode) {
            $sort_mode = '`structureName_asc';
        } elseif ($type == 'p') {
            $mid = ' `trp`.`structureName` is null ';
        }
        if (! $sort_mode) {
            $sort_mode = '`pageName_asc';
        } else {
            $mid = '';
        }

        if ($find) {
            $findesc = '%' . $find . '%';
            if ($mid) {
                $mid .= ' and ';
            }
            $mid .= '(`trp`.`pageName` like ? or `trp`.`structureName` like ? or `trp`.`data` like ?)';
            $bindvars[] = $findesc;
            $bindvars[] = $findesc;
            $bindvars[] = $findesc;
        }
        if ($structureName) {
            if ($mid) {
                $mid .= ' and ';
            }
            $mid .= ' `trp`.`structureName`=? ';
            $bindvars[] = $structureName;
        }
        if ($mid) {
            $mid = "where $mid";
        }

        $query = "select trp.*, tp.`pageName` as pageExists from `tiki_received_pages` trp left join `tiki_pages` tp on (tp.`pageName`=trp.`pageName`) $mid order by `structureName` asc, `pos` asc," . $this->convertSortMode($sort_mode);
        $query_cant = "select count(*) from `tiki_received_pages` trp $mid";
        $ret = $this->fetchAll($query, $bindvars, $maxRecords, $offset);
        $cant = $this->getOne($query_cant, $bindvars);

        $retval = [];
        $retval["data"] = $ret;
        $retval["cant"] = $cant;
        return $retval;
    }

    // User voting system ////
    // Used to vote everything (polls,comments,files,submissions,etc) ////
    // Checks if a user has voted
    /*shared*/
    /**
     * @param $user
     * @param $id
     * @return bool
     */
    public function user_has_voted($user, $id)
    {
        global $prefs;

        $ret = false;

        if (isset($_SESSION['votes'])) {
            $votes = $_SESSION['votes'];
            if (is_array($votes) && in_array($id, $votes)) { // has already voted in the session (logged or not)
                return true;
            }
        }

        if (! $user) {
            if ($prefs['ip_can_be_checked'] != 'y' && ! isset($_COOKIE[ session_name() ])) {// cookie has not been activated too bad for him
                $ret = true;
            } elseif (isset($_COOKIE[md5("tiki_wiki_poll_$id")])) {
                $ret = true;
            }
            // we have no idea if cookie was deleted  or if really he has not voted
        } else {
            $query = "select count(*) from `tiki_user_votings` where `user`=? and `id`=?";
            if ($this->getOne($query, [$user,(string) $id]) > 0) {
                $ret = true;
            }
        }
        if ($prefs['ip_can_be_checked'] == 'y') {
            $query = 'select count(*) from `tiki_user_votings` where `ip`=? and `id`=?';
            if ($this->getOne($query, [$this->get_ip_address(), $id]) > 0) {
                return true; // IP has already voted logged or not
            }
        }
        return $ret;
    }

    // Registers a user vote
    /*shared*/
    /**
     * @param $user
     * @param $id
     * @param bool $optionId
     * @param array $valid_options
     * @param bool $allow_revote
     * @return bool
     */
    public function register_user_vote($user, $id, $optionId = false, array $valid_options = [], $allow_revote = false)
    {
        global $prefs;

        // If an option is specified and the valid options are specified, skip the vote entirely if not valid
        if (false !== $optionId && count($valid_options) > 0 && ! in_array($optionId, $valid_options)) {
            return false;
        }

        if ($user && ! $allow_revote && $this->user_has_voted($user, $id)) {
            return false;
        }

        $userVotings = $this->table('tiki_user_votings');

        $ip = $this->get_ip_address();
        $_SESSION['votes'][] = $id;
        setcookie(md5("tiki_wiki_poll_$id"), $ip, time() + 60 * 60 * 24 * 300);
        if (! $user) {
            if ($prefs['ip_can_be_checked'] == 'y') {
                $userVotings->delete(['ip' => $ip, 'id' => $id]);
                if ($optionId !== false && $optionId != 'NULL') {
                    $userVotings->insert(
                        [
                            'user' => '',
                            'ip' => $ip,
                            'id' => (string) $id,
                            'optionId' => (int) $optionId,
                            'time' => $this->now,
                        ]
                    );
                }
            } elseif (isset($_COOKIE[md5("tiki_wiki_poll_$id")])) {
                Feedback::error(tr('You need to enable ip_can_be_checked feature before to change vote as anonymous. If you can\'t, please contact the administrator'));
                return false;
            } elseif ($optionId !== false && $optionId != 'NULL') {
                $userVotings->insert(
                    [
                        'user' => '',
                        'ip' => $ip,
                        'id' => (string) $id,
                        'optionId' => (int) $optionId,
                        'time' => $this->now,
                    ]
                );
            }
        } else {
            if ($prefs['ip_can_be_checked'] == 'y') {
                $userVotings->delete(['user' => $user,'id' => $id]);
                $userVotings->delete(['ip' => $ip,'id' => $id]);
            } else {
                $userVotings->delete(['user' => $user,'id' => $id]);
            }
            if ($optionId !== false  && $optionId !== 'NULL') {
                $userVotings->insert(
                    [
                        'user' => $user,
                        'ip' => $ip,
                        'id' => (string) $id,
                        'optionId' => (int) $optionId,
                        'time' => $this->now,
                    ]
                );
            }
        }

        return true;
    }

    /**
     * @param $id
     * @param $user
     * @return null
     */
    public function get_user_vote($id, $user)
    {
        global $prefs;
        $vote = null;
        if ($user) {
            $vote = $this->getOne("select `optionId` from `tiki_user_votings` where `user` = ? and `id` = ? order by `time` desc", [ $user, $id]);
        }
        if ($vote == null && $prefs['ip_can_be_checked'] == 'y') {
            $ip = $this->get_ip_address();
            $vote = $this->getOne("select `optionId` from `tiki_user_votings` where `ip` = ? and `id` = ? order by `time` desc", [ $ip, $id]);
        }
        return $vote;
    }
    // end of user voting methods

    /**
     * @param int $offset
     * @param $maxRecords
     * @param string $sort_mode
     * @param string $find
     * @param bool $include_prefs
     * @return array
     */
    public function list_users($offset = 0, $maxRecords = -1, $sort_mode = 'pref:realName', $find = '', $include_prefs = false)
    {
        global $user, $prefs;
        $userprefslib = TikiLib::lib('userprefs');

        $bindvars = [];
        if ($find) {
            $findesc = '%' . $find . '%';
            $mid = 'where (`login` like ? or p1.`value` like ?)';
            $mid_cant = $mid;
            $bindvars[] = $findesc;
            $bindvars[] = $findesc;
            $bindvars2 = [$findesc, $findesc];
            $find_join = " left join `tiki_user_preferences` p1 on (u.`login` = p1.`user` and p1.`prefName` = 'realName')";
            $find_join_cant = $find_join;
        } else {
            $mid = '';
            $bindvars2 = [];
            $find_join = '';
            $find_join_cant = '';
            $mid_cant = '';
        }

        // This allows to use a sort_mode by prefs
        // In this case, sort_mode must have this syntax :
        //   pref:PREFERENCE_NAME[_asc|_desc]
        // e.g. to sort on country :
        //   pref:country  OR  pref:country_asc  OR  pref:country_desc

        if ($ppos = strpos($sort_mode, ':')) {
            $sort_value = substr($sort_mode, $ppos + 1);
            $sort_way = 'asc';

            if (preg_match('/^(.+)_(asc|desc)$/i', $sort_value, $regs)) {
                $sort_value = $regs[1];
                $sort_way = $regs[2];
                unset($regs);
            }

            if ($find_join != '' && $sort_value == 'realName') {
                // Avoid two joins if we can do only one
                $find_join = '';
                $mid = 'where (`login` like ? or p.`value` like ?)';
            }
            $sort_mode = "p.`value` $sort_way";
            $pref_where = ( ( $mid == '' ) ? 'where' : $mid . ' and' ) . " p.`prefName` = '$sort_value'";
            $pref_join = 'left join `tiki_user_preferences` p on (u.`login` = p.`user`)';
            $pref_field = ', p.`value` as sf';
        } else {
            $sort_mode = $this->convertSortMode($sort_mode);
            $pref_where = $mid;
            $pref_join = '';
            $pref_field = '';
        }

        if ($sort_mode != '') {
            $sort_mode = 'order by ' . $sort_mode;
        }

        $query = "select u.* $pref_field  from `users_users` u $pref_join $find_join $pref_where $sort_mode";

        $query_cant = "select count(distinct u.`login`) from `users_users` u $find_join_cant $mid_cant";
        $result = $this->fetchAll($query, $bindvars, $maxRecords, $offset);
        $cant = $this->getOne($query_cant, $bindvars2);

        $ret = [];
        foreach ($result as $res) {
            if ($include_prefs) {
                $res['preferences'] = $userprefslib->get_userprefs($res['login']);
            }
            $ret[] = $res;
        }

        return ['data' => $ret, 'cant' => $cant];
    }

    // CMS functions -ARTICLES- & -SUBMISSIONS- ////
    /*shared*/
    /**
     * @param int $max
     * @return mixed
     */
    public function get_featured_links($max = 10)
    {
        $query = "select * from `tiki_featured_links` where `position` > ? order by " . $this->convertSortMode("position_asc");
        return  $this->fetchAll($query, [0], (int)$max, 0);
    }

    /**
     * @param $sessionId
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * @return null
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @return bool
     */
    public function update_session()
    {
        static $uptodate = false;
        if ($uptodate === true || $this->sessionId === null) {
            return true;
        }

        global $user, $prefs;
        $logslib = TikiLib::lib('logs');

        if ($user === false) {
            $user = '';
        }
        // If pref login_multiple_forbidden is set, length of tiki_sessions must match real session length to be up to date so we can detect concurrent logins of same user
        if ($prefs['login_multiple_forbidden'] == 'y') {
            $delay = ini_get('session.gc_maxlifetime');
        } else {    // Low value so as to guess who actually is in front of the computer
            $delay = 5 * 60; // 5 minutes
        }
        $oldy = $this->now - $delay;
        if ($user != '') { // was the user timeout?
            $query = "select count(*) from `tiki_sessions` where `sessionId`=?";
            $cant = $this->getOne($query, [$this->sessionId]);
            if ($cant == 0) {
                if ($prefs['login_multiple_forbidden'] != 'y' || $user == 'admin') {
                    // Recover after timeout
                    $logslib->add_log("login", "back", $user, '', '', $this->now);
                } else {
                    // Prevent multiple sessions for same user
                    // Must check any user session, not only timed out ones
                    $query = "SELECT count(*) FROM `tiki_sessions` WHERE user = ?";
                    $cant = $this->getOne($query, [$user]);
                    if ($cant == 0) {
                        // Recover after timeout (no other session)
                        $logslib->add_log("login", "back", $user, '', '', $this->now);
                    } else {
                        // User has an active session on another browser
                        $userlib = TikiLib::lib('user');
                        $userlib->user_logout($user, false, '');
                    }
                }
            }
        }
        $query = "select * from `tiki_sessions` where `timestamp`<?";
        $result = $this->fetchAll($query, [$oldy]);
        foreach ($result as $res) {
            if ($res['user'] && $res['user'] != $user) {
                $logslib->add_log('login', 'timeout', $res['user'], ' ', ' ', $res['timestamp'] + $delay);
            }
        }

        $sessions = $this->table('tiki_sessions');

        $sessions->delete(['sessionId' => $this->sessionId]);
        $sessions->deleteMultiple(['timestamp' => $sessions->lesserThan($oldy)]);

        if ($user) {
            $sessions->delete(['user' => $user]);
        }

        $sessions->insert(
            [
                'sessionId' => $this->sessionId,
                'timestamp' => $this->now,
                'user' => $user,
                'tikihost' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost',
            ]
        );
        if ($prefs['session_storage'] == 'db') {
            // clean up adodb sessions as well in case adodb session garbage collection not working
            $sessions = $this->table('sessions');

            $sessions->deleteMultiple(['expiry' => $sessions->lesserThan($oldy)]);
        }

        $uptodate = true;
        return true;
    }

    // Returns the number of registered users which logged in or were active in the last 5 minutes.
    /**
     * @return mixed
     */
    public function count_sessions()
    {
        $this->update_session();
        return $this->table('tiki_sessions')->fetchCount([]);
    }

    // Returns a string-indexed array with all the hosts/servers active in the last 5 minutes. Keys are hostnames. Values represent the number of registered users which logged in or were active in the last 5 minutes on the host.
    /**
     * @return array
     */
    public function count_cluster_sessions()
    {
        $this->update_session();
        $query = "select `tikihost`, count(`tikihost`) as cant from `tiki_sessions` group by `tikihost`";
        return $this->fetchMap($query, []);
    }

    /**
     * @param $links
     * @return bool
     */
    public function cache_links($links)
    {
        global $prefs;
        if ($prefs['cachepages'] != 'y') {
            return false;
        }
        foreach ($links as $link) {
            if (! $this->is_cached($link)) {
                $this->cache_url($link);
            }
        }
    }

    /**
     * @param $data
     * @return array
     */
    public function get_links($data, $is_markdown = false)
    {
        $links = [];

        /// Prevent the substitution of link [] inside a <tag> ex: <input name="tracker[9]" ... >
        $data = preg_replace("/<[^>]*>/", "", $data);

        if ($is_markdown) {
            // markdown syntax needs to ignore ^[] inline footnotes, [^footnotes] and [foo](link)
            if (preg_match_all("/(?<![\[\^])\[([^\[\|\]\^]+)(?:\|?[^\[\|\]]*){0,2}\](?!\()/", $data, $r1)) {
                $res = $r1[1];
                $links = array_unique($res);
            }
        } else {
            /// Match things like [...], but ignore things like [[foo].
            // -Robin
            if (preg_match_all("/(?<!\[)\[([^\[\|\]]+)(?:\|?[^\[\|\]]*){0,2}\]/", $data, $r1)) {
                $res = $r1[1];
                $links = array_unique($res);
            }
        }

        return $links;
    }

    /**
     * Convert internal links from absolute to relative
     *
     * @param string $data
     * @return string
     */
    public function convertAbsoluteLinksToRelative($data)
    {
        global $prefs, $tikilib;

        preg_match_all('/\[(([^|\]]+)(\|([^|\]]+))?)\]/', $data, $matches);

        $counter = count($matches[0]);
        for ($i = 0; $i < $counter; $i++) {
            $label = ! empty($matches[3][$i]) ? ltrim($matches[3][$i], '|') : '';
            if (! empty($label) && $matches[2][$i] == $label) {
                $data = str_replace($matches[0][$i], '[' . $matches[2][$i] . ']', $data);
            }

            // Check if link part is valid url
            if (filter_var($matches[2][$i], FILTER_VALIDATE_URL) === false) {
                continue;
            }

            // Check if url matches tiki instance links
            if ($url = $this->getMatchBaseUrlSchema($matches[2][$i]) && $matches[2][$i] == $matches[4][$i]) {
                $newLink = '[' . $matches[2][$i] . ']';
                $data = str_replace($matches[0][$i], $newLink, $data);
            }
        }

        preg_match_all('/\(\((([^|)]+)(\|([^|)]+))?)\)\)/', $data, $matches);

        $counter = count($matches[0]);
        for ($i = 0; $i < $counter; $i++) {
            if ($matches[0][$i]) {
                $linkArray = explode('|', trim($matches[0][$i], '(())'));
                if (count($linkArray) == 2 && $linkArray[0] == $linkArray[1]) {
                    $newLink = '((' . $linkArray[0] . '))';
                    $data = str_replace($matches[0][$i], $newLink, $data);
                }
            }
        }

        if ($prefs['feature_absolute_to_relative_links'] != 'y') {
            return $data;
        }

        $notification = false;

        $from = 0;
        $to = strlen($data);
        $replace = [];
        foreach ($this->getWikiMarkers() as $marker) {
            while (false !== $open = $this->findText($data, $marker[0], $from, $to)) {
                // Wiki marker -+ begin should be proceeded by space or a newline
                if ($marker[0] == '-+' && $open != 0 && ! preg_match('/\s/', $data[$open - 1])) {
                    $from = $open + 1;
                    continue;
                }

                if (false !== $close = $this->findText($data, $marker[1], $open, $to)) {
                    $from = $close;
                    $size = ($close - $open) + strlen($marker[1]);
                    $markerBody = substr($data, $open, $size);
                    $key = "§" . md5($tikilib->genPass()) . "§" ;
                    $replace[$key] = $markerBody;
                    $data = str_replace($markerBody, $key, $data);
                } else {
                    break;
                }
            }
        }

        // convert absolute to relative links
        $pluginMatches = WikiParser_PluginMatcher::match($data);
        foreach ($pluginMatches as $pluginMatch) {
            $pluginBody = $pluginMatch->getBody();
            if (empty($pluginBody)) {
                $pluginBody = $pluginMatch->getArguments();
            }

            $key = "§" . md5($tikilib->genPass()) . "§" ;
            $replace[$key] = $pluginBody;
            $data = str_replace($pluginBody, $key, $data);
        }

        // Detect tiki internal links
        preg_match_all('/\(\((([^|)]+)(\|([^|)]+))?)\)\)/', $data, $matches);

        $counter = count($matches[0]);
        for ($i = 0; $i < $counter; $i++) {
            $linkArray = explode('|', trim($matches[0][$i], '(())'));
            if (count($linkArray) == 2 && $linkArray[0] == $linkArray[1]) {
                $newLink = '((' . $linkArray[0] . '))';
                $data = str_replace($matches[0][$i], $newLink, $data);
                $notification = true;
            }

            // Check if link part is valid url
            if (filter_var($matches[2][$i], FILTER_VALIDATE_URL) === false) {
                continue;
            }

            // Check if url matches tiki instance links
            if ($url = $this->getMatchBaseUrlSchema($matches[2][$i])) {
                $newPath = str_replace($url, '', $matches[2][$i]);
                // In case of a tikibase instance point link to Homepage
                if (empty($newPath) || $newPath == '/') {
                    $newPath = 'Homepage';
                }
                $newLink = '((' . $newPath . $matches[3][$i] . '))';
                $data = str_replace($matches[0][$i], $newLink, $data);
                $notification = true;
            }
        }

        // Detect external links
        preg_match_all('/\[(([^|\]]+)(\|([^|\]]+))?)\]/', $data, $matches);

        $counter = count($matches[0]);
        for ($i = 0; $i < $counter; $i++) {
            // Check if link part is valid url
            if (filter_var($matches[2][$i], FILTER_VALIDATE_URL) === false) {
                continue;
            }

            // Check if url matches tiki instance links
            if ($url = $this->getMatchBaseUrlSchema($matches[2][$i])) {
                $newPath = str_replace($url, '', $matches[2][$i]);
                if (! empty($newPath)) {
                    $newLink = '[' . $newPath . $matches[3][$i] . ']';

                    $newLinkArray = explode('|', trim($newLink, '[]'));
                    if (count($newLinkArray) === 2 && $newLinkArray[0] == str_replace($url, '', $newLinkArray[1])) {
                        $newLink = '[' . $newLinkArray[0] . ']';
                    }

                    $data = str_replace($matches[0][$i], $newLink, $data);
                    $notification = true;
                }
            }
        }

        // Detect links outside wikiplugin or wiki markers
        preg_match_all('/(?<!==)(http|https):\/\/([\w_-]+(?:(?:\.[\w_-]+)+))([\w.,@?^=%&:\/~+#-]*[\w@?^=%&\/~+#-])?/', $data, $matches);

        $counter = count($matches[0]);
        for ($i = 0; $i < $counter; $i++) {
            // Check if link part is valid url
            if (filter_var($matches[0][$i], FILTER_VALIDATE_URL) === false) {
                continue;
            }

            // Check if url matches tiki instance links
            if ($url = $this->getMatchBaseUrlSchema($matches[0][$i])) {
                $newPath = str_replace($url, '', $matches[0][$i]);
                $objectLink = $this->getObjectRelativeLink($newPath);
                if (! empty($newPath) && ! empty($objectLink)) {
                    $objStartPos = strpos($data, $matches[0][$i]);
                    $objLength = strlen($matches[0][$i]);
                    $data = substr_replace($data, $objectLink, $objStartPos, $objLength);
                    $notification = true;
                }
            }
        }

        foreach ($replace as $key => $body) {
            $data = str_replace($key, $body, $data);
        }

        if ($notification) {
            Feedback::note(tr('Tiki links converted to relative links'));
        }

        return $data;
    }

    /**
     * Return the base url in the matched link protocol (http or https)
     *
     * @param string $link The link to check
     *
     * @return string The tiki base url with the matched schema (http or https)
     */
    public function getMatchBaseUrlSchema($link)
    {
        global $base_url_http, $base_url_https;

        if (strpos($link, $base_url_http) !== false) {
            return $base_url_http;
        } elseif (strpos($link, rtrim($base_url_http, '/')) !== false) {
            return rtrim($base_url_http, '/');
        } elseif (strpos($link, $base_url_https) !== false) {
            return $base_url_https;
        } elseif (strpos($link, rtrim($base_url_https, '/')) !== false) {
            return rtrim($base_url_https, '/');
        } else {
            return null;
        }
    }

    /**
     * Returns the object internal link
     *
     * @param string $uri
     * @return string
     */
    public function getObjectRelativeLink($uri)
    {
        global $prefs;
        $objectLink = '';

        if (! empty($prefs['feature_sefurl']) && $prefs['feature_sefurl'] === 'y') {
            $slug = explode('-', $uri);
            $slug = $slug[0];

            switch ($slug) {
                case (substr($slug, 0, 7) === 'article' || substr($slug, 0, 3) === 'art'):
                    $articleId = substr($slug, 0, 7) === 'article' ? substr($slug, 7) : substr($slug, 3);
                    $artlib = TikiLib::lib('art');
                    $article = $artlib->get_article($articleId);
                    $objectLink = ! empty($article['title']) ? '[' . $uri . '|' . $article['title'] . ']' : '';
                    break;
                case substr($slug, 0, 8) === 'blogpost':
                    $blogPostId = substr($slug, 8);
                    $bloglib = TikiLib::lib('blog');
                    $blogPost = $bloglib->get_post($blogPostId);
                    $objectLink = ! empty($blogPost['title']) ? '[' . $uri . '|' . $blogPost['title'] . ']' : 