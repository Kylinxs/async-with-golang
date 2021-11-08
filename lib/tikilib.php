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
                        $res['perm'] = $this->user_has_perm_on_obje