<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 *
 */

class AdminLib extends TikiLib
{
    /**
     * @param $offset
     * @param $maxRecords
     * @param $sort_mode
     * @param $find
     * @return array
     */
    public function list_dsn($offset, $maxRecords, $sort_mode, $find)
    {

        $bindvars = [];
        if ($find) {
            $findesc = '%' . $find . '%';

            $mid = " where (`dsn` like ?)";
            $bindvars[] = $findesc;
        } else {
            $mid = "";
        }

        $query = "select * from `tiki_dsn` $mid order by " . $this->convertSortMode($sort_mode);
        $query_cant = "select count(*) from `tiki_dsn` $mid";
        $result = $this->query($query, $bindvars, $maxRecords, $offset);
        $cant = $this->getOne($query_cant, $bindvars);
        $ret = [];

        while ($res = $result->fetchRow()) {
            $ret[] = $res;
        }

        $retval = [];
        $retval["data"] = $ret;
        $retval["cant"] = $cant;
        return $retval;
    }

    /**
     * @param $dsnId
     * @param $dsn
     * @param $name
     *
     * @return TikiDb_Pdo_Result|TikiDb_Adodb_Result
     */
    public function replace_dsn($dsnId, $dsn, $name)
    {
        // Check the name
        if ($dsnId) {
            $query = "update `tiki_dsn` set `name`=?,`dsn`=? where `dsnId`=?";
            $bindvars = [$name, $dsn, $dsnId];
            return $this->query($query, $bindvars);
        } else {
            $query = "delete from `tiki_dsn`where `name`=? and `dsn`=?";
            $bindvars = [$name, $dsn];
            $this->query($query, $bindvars);
            $query = "insert into `tiki_dsn`(`name`,`dsn`)
                        values(?,?)";
            return $this->query($query, $bindvars);
        }
    }

    /**
     * @param int $dsnId
     *
     * @return TikiDb_Pdo_Result|TikiDb_Adodb_Result
     */
    public function remove_dsn($dsnId)
    {
        $query = "delete from `tiki_dsn` where `dsnId`=?";
        return $this->query($query, [$dsnId]);
    }

    /**
     * @param int $dsnId
     * @return array|bool returns false on failure, or an array of values upon success
     */
    public function get_dsn($dsnId)
    {
        $query = "select * from `tiki_dsn` where `dsnId`=?";

        $result = $this->query($query, [$dsnId]);

        if (! $result->numRows()) {
            return false;
        }

        $res = $result->fetchRow();
        return $res;
    }

    /**
     * @param $dsnName
     * @return array|bool returns false on failure, or an array of values upon success
     */
    public function get_dsn_from_name($dsnName)
    {
        $query = "select * from `tiki_dsn` where `name`=?";

        $result = $this->query($query, [$dsnName]);

        if (! $result->numRows()) {
            return false;
        }

        $res = $result->fetchRow();
        return $res;
    }

    /**
     * @param $offset
     * @param $maxRecords
     * @param $sort_mode
     * @param $find
     * @return array
     */
    public function list_extwiki($offset, $maxRecords, $sort_mode, $find)
    {
        $bindvars = [];
        if ($find) {
            $findesc = '%' . $find . '%';

            $mid = " where (`extwiki` like ? )";
            $bindvars[] = $findesc;
        } else {
            $mid = "";
        }

        $query = "select * from `tiki_extwiki` $mid order by " . $this->convertSortMode($sort_mode);
        $query_cant = "select count(*) from `tiki_extwiki` $mid";
        $result = $this->fetchAll($query, $bindvars, $maxRecords, $offset);
        $cant = $this->getOne($query_cant, $bindvars);

        $retval = [];
        $retval["data"] = $result;
        $retval["cant"] = $cant;
        return $retval;
    }

    /**
     * @param int    $extwikiId
     * @param string $extwiki
     * @param        $name
     * @param string $indexName
     * @param array  $groups
     *
     * @return array|bool|mixed
     */
    public function replace_extwiki($extwikiId, $extwiki, $name, $indexName = '', $groups = [])
    {
        $table = $this->table('tiki_extwiki');
        $data = [
            'name' => $name,
            'extwiki' => $extwiki,
            'indexname' => $indexName,
            'groups' => json_encode(array_values($groups)),
        ];
        $withId = $data;
        $withId['extwikiId'] = $extwikiId;
        return $table->insertOrUpdate($withId, $data);
    }

    /**
     * Removes a configuration option of an external wiki
     *
     * @param $extwikiId int Id of the external wiki to be removed
     *
     * @return TikiDb_Pdo_Result|TikiDb_Adodb_Result
     */
    public function remove_extwiki($extwikiId)
    {
        $query = "delete from `tiki_extwiki` where `extwikiId`=?";
        return $this->query($query, [$extwikiId]);
    }

    /**
     * @param int $extwikiId
     * @return bool
     */
    public function get_extwiki($extwikiId)
    {
        $table = $this->table('tiki_extwiki');
        $row = $table->fetchFullRow(['extwikiId' => $extwikiId]);

        if (! empty($row['groups'])) {
            $row['groups'] = json_decode($row['groups']);
        }
        return $row;
    }


    /**
     * Remove unused wiki attachment pictures
     */
    public function remove_unused_pictures()
    {
        global $tikidomain;

        $query = "select `data` from `tiki_pages`";
        $result = $this->query($query, []);
        $pictures = [];

        while ($res = $result->fetchRow()) {
            preg_match_all("/\{(picture |img )([^\}]+)\}/ixs", $res['data'], $pics); //fixme: pick also the picture into ~np~

            foreach (array_unique($pics[2]) as $pic) {
                if (preg_match("/(src|file)=\"([^\"]+)\"/xis", $pic, $matches)) {
                    $pictures[] = $matches[2];
                }
                if (preg_match("/(src|file)=&quot;([^&]+)&quot;/xis", $pic, $matches)) {
                    $pictures[] = $matches[2];
                }
                if (preg_match("/(src|file)=([^&\"\s,]+)/xis", $pic, $matches)) {
                    $pictures[] = $matches[2];
                }
            }
        }
        $pictures = array_unique($pictures);

        $path = "img/wiki_up";
        if ($tikidomain) {
            $path .= "/$tikidomain";
        }
        $h = opendir($path);

        while (($file = readdir($h)) !== false) {
            if (is_file("$path/$file") && $file != 'license.txt' && $file != 'index.php' && $file != '.cvsignore' && $file != 'README') {
                $filename = "$path/$file";

                if (! in_array($filename, $pictures)) {
                    @unlink($filename);
                }
            }
        }

        closedir($h);
    }

    /**
     * Finds if a name given to a database dump is already in use
     *
     * @param string $tag
     * @return bool     false on no tag existing, true on tag already present
     */
    public function tag_exists($tag)
    {
        $query = "select distinct `tagName` from `tiki_tags` where `tagName` = ?";

        $result = $this->query($query, [$tag]);
        return (bool)$result->numRows();
    }

    /**
     *
     * Removes a database dump
     *
     * @param string $tagname
     * @return bool     Right now only returns true
     */
    public function remove_tag($tagname)
    {
        $query = "delete from `tiki_tags` where `tagName`=?";
        $this->query($query, [$tagname]);
        TikiLib::lib('logs')->add_log('dump', "removed tag: $tagname");
        return true;
        //fixme: This should return false on failure
    }

    /**
     * @return array
     */
    public function get_tags()
    {
        $query = "select distinct `tagName` from `tiki_tags`";

        $result = $this->query($query, []);
        $ret = [];

        while ($res = $result->fetchRow()) {
            $ret[] = $res["tagName"];
        }

        return $ret;
    }

    /**
     *
     * This function can be used to store the set of actual pages in the "tags"
     * table preserving the state of the wiki under a tag name.
     * @param $tagname
     * @see dump()
     */
    public function create_tag($tagname)
    {
        $query = "select * from `tiki_pages`";
        $result = $this->query($query, []);

        while ($res = $result->fetchRow()) {
            $data = $res["data"];
            $pageName = $res["pageName"];
            $description = $res["description"];
            $query = "delete from `tiki_tags`where `tagName`=? and `pageName`=?";
            $this->query($query, [$tagname, $pageName], -1, -1, false);
            $query = "insert into `tiki_tags`(`tagName`,`pageName`,`hits`,`data`,`lastModif`,`comment`,`version`,`user`,`ip`,`flag`,`description`)" .
                " values(?,?,?,?,?,?,?,?,?,?,?)";
            $this->query(
                $query,
                [
                    $tagname,
                    $pageName,
                    $res["hits"],
                    $data,
                    $res["lastModif"],
                    $res["comment"],
                    $res["version"],
                    $res["user"],
                    $res["ip"],
                    $res["flag"],
                    $description
                ]
            );
        }

        $logslib = TikiLib::lib('logs');
        $logslib->add_log('dump', "wiki database dump created: $tagname");
    }

    /**
     * This funcion recovers the state of the wiki using a tagName from the tags table
     *
     * @param string $tagname
     * @return bool     currenty only returns true
     */
    public function restore_tag($tagname)
    {

        $query = "update `tiki_pages` set `cache_timestamp`=0";
        $this->query($query, []);
        $query = "select * from `tiki_tags` where `tagName`=?";
        $result = $this->query($query, [$tagname]);

        while ($res = $result->fetchRow()) {
            $query = "update `tiki_pages`" .
                " set `hits`=?,`data`=?,`lastModif`=?,`comment`=?,`version`=`version`+1,`user`=?,`ip`=?,`flag`=?,`description`=?" .
                "  where `pageName`=?";

            $this->query(
                $query,
                [
                    $res["hits"],
                    $res["data"],
                    $res["lastModif"],
                    $res["comment"],
                    $res["user"],
                    $res["ip"],
                    $res["flag"],
                    $res["description"],
                    $res["pageName"]
                ]
            );
        }

        TikiLib::lib('logs')->add_log('dump', "recovered tag: $tagname");
        return true;
        // fixme: should return false on failure
    }

    /** Dumps wiki pages to a tar file
     * @see create_tag()
     */
    public function dump()
    {
        global $tikidomain, $prefs;
        $parserlib = TikiLib::lib('parser');

        $dumpPath = "storage";
        if ($tikidomain) {
            $dumpPath .= "/$tikidomain";
        }

        $dumpPath = $dumpPath . '/dump_wiki.tar';
        @unlink($dumpPath);
        $tar = new tar();

        // @fixme: Completely outdated. styles/ no longer exists.
        //$tar->addFile('styles/' . $prefs['theme']);

        // Foreach page
        $query = "select * from `tiki_pages`";
        $result = $this->query($query, []);

        while ($res = $result->fetchRow()) {
            $pageName = $res["pageName"] . '.html';

            $pageContents = $parserlib->parse_data($res["data"]);

            // Now change tiki-index.php?page=foo to foo.html
            // and tiki-index.php to HomePage.html
            $pageContents = preg_replace("/tiki-index.php\?page=([^\'\"\$]+)/", "$1.html", $pageContents);
            $pageContents = preg_replace("/tiki-editpage.php\?page=([^\'\"\$]+)/", "", $pageContents);
            //preg_match_all("/tiki-index.php\?page=([^ ]+)/",$dat,$cosas);
            //print_r($cosas);

            $data = "<html>";
            $data .= "<head>";
            $data .= "<title>" . $res["pageName"] . "</title>";
            // $data .= "<link rel='StyleSheet' href='styles/" . $prefs['style'] . "' type='text/css'>";
            $data .= '</head>';
            $data .= "<body><a class='wiki' href='" .
                $prefs['wikiHomePage'] .
                ".html'>home</a><br /><h1>" .
                $res["pageName"] .
                "</h1><div class='wikitext'>" .
                $pageContents .
                '</div></body>';
            $data .= '</html>';
            $tar->addData($pageName, $data, $res["lastModif"]);
        }

        $tar->toTar($dumpPath, false);
        unset($tar);
        $logslib = TikiLib::lib('logs');
        $logslib->add_log('dump', 'wiki file dump created in ' . $dumpPath);
    }

    /**
     * Validates if the php version is fully compatible with OPCache.
     * @return bool
     */
    public function checkOPCacheCompatibility()
    {
        return ! ((version_compare(PHP_VERSION, '7.1.0', '>=') && version_compare(PHP_VERSION, '7.2.0', '<')) //7.1.x
            || (version_compare(PHP_VERSION, '7.2.0', '>=') && version_compare(PHP_VERSION, '7.2.19', '<')) // >= 7.2.0 < 7.2.19
            || (version_compare(PHP_VERSION, '7.3.0', '>=') && version_compare(PHP_VERSION, '7.3.6', '<'))); // >= 7.3.0 < 7.3.6
    }

    public function getOpcodeCacheStatus()
    {
        $opcode_stats = [
            'opcode_cache' => null,
            'stat_flag' => null,
            'warning_check' => false,
            'warning_fresh' => false,
            'warning_ratio' => false,
            'warning_starve' => false,
            'warning_low' => false,
            'warning_xcache_blocked' => false,
        ];

        if (function_exists('apc_sma_info') && ini_get('apc.enabled')) {
            if ($_REQUEST['apc_clear'] && TikiLib::lib('access')->checkCsrf()) {
                $results['system'] = apc_clear_cache();
                $results['user'] = apc_clear_cache('user');
                $results['opcode'] = apc_clear_cache('opcode');
                if (! in_array(false, $results, true)) {
                    Feedback::success(tr('APC system, user and opcode caches cleared'));
                } else {
                    foreach ($results as $resultType => $result) {
                        if ($result === true) {
                            Feedback::success(tr('APC %0 cache cleared', $resultType));
                        } elseif ($result === false) {
                            Feedback::error(tr('APC %0 cache not cleared', $resultType));
                        }
                    }
                }
            }

            $sma = apc_sma_info();
            $mem_total = $sma['num_seg'] * $sma['seg_size'];

            $cache = apc_cache_info(null, true);
            $hit_total = $cache['num_hits'] + $cache['num_misses'];
            if (! $hit_total) {    // cheat for chart after cache clear
                $hit_total = 1;
                $cache['num_misses'] = 1;
            }

            $opcode_stats = [
                'opcode_cache' => 'APC',
                'stat_flag' => 'apc.stat',
                'memory_used' => ($mem_total - $sma['avail_mem']) / $mem_total,
                'memory_avail' => $sma['avail_mem'] / $mem_total,
                'memory_total' => $mem_total,
                'hit_hit' => $cache['num_hits'] / $hit_total,
                'hit_miss' => $cache['num_misses'] / $hit_total,
                'hit_total' => $hit_total,
                'type' => 'apc',
            ];
        } elseif (function_exists('xcache_info') && (ini_get('xcache.cacher') == '1' || ini_get('xcache.cacher') == 'On')) {
            if (ini_get('xcache.admin.enable_auth') == '1' || ini_get('xcache.admin.enable_auth') == 'On') {
                $opcode_stats['warning_xcache_blocked'] = true;
            } else {
                $opcode_stats = [
                    'stat_flag' => 'xcache.stat',
                    'memory_used' => 0,
                    'memory_avail' => 0,
                    'memory_total' => 0,
                    'hit_hit' => 0,
                    'hit_miss' => 0,
                    'hit_total' => 0,
                    'type' => 'xcache',
                ];

                foreach (range(0, xcache_count(XC_TYPE_PHP) - 1) as $index) {
                    $info = xcache_info(XC_TYPE_PHP, $index);

                    $opcode_stats['hit_hit'] += $info['hits'];
                    $opcode_stats['hit_miss'] += $info['misses'];
                    $opcode_stats['hit_total'] += $info['hits'] + $info['misses'];

                    $opcode_stats['memory_used'] += $info['size'] - $info['avail'];
                    $opcode_stats['memory_avail'] += $info['avail'];
                    $opcode_stats['memory_total'] += $info['size'];
                }

                $opcode_stats['memory_used'] /= $opcode_stats['memory_total'];
                $opcode_stats['memory_avail'] /= $opcode_stats['memory_total'];
                $opcode_stats['hit_hit'] /= $opcode_stats['hit_total'];
                $opcode_stats['hit_miss'] /= $opcode_stats['hit_total'];
            }
            $opcode_stats['opcode_cache'] = 'XCache';
        } elseif (function_exists('wincache_fcache_fileinfo')) {
            // Wincache is installed

            // Determine if version 1 or 2 is used. Version 2 does not support ocache

            if (function_exists('wincache_ocache_fileinfo')) {
                // Wincache version 1
                if (ini_get('wincache.ocenabled') == '1') {
                    $opcode_stats = [
                        'opcode_cache' => 'WinCache',
                        'stat_flag' => 'wincache.ocenabled',
                        'memory_used' => 0,
                        'memory_avail' => 0,
                        'memory_total' => 0,
                        'hit_hit' => 0,
                        'hit_miss' => 0,
                        'hit_total' => 0,
                        'type' => 'wincache',
                    ];

                    $info = wincache_ocache_fileinfo();
                }
            } else {
                // Wincache version 2 or higher
                if (ini_get('wincache.fcenabled') == '1') {
                    $opcode_stats = [
                        'opcode_cache' => 'WinCache',
                        'stat_flag' => 'wincache.fcenabled',
                        'memory_used' => 0,
                        'memory_avail' => 0,
                        'memory_total' => 0,
                        'hit_hit' => 0,
                        'hit_miss' => 0,
                        'hit_total' => 0,
                        'type' => 'wincache',
                    ];
                    $info = wincache_fcache_fileinfo();
                }
            }
            if (! empty($opcode_stats['stat_flag'])) {
                $opcode_stats['hit_hit'] = $info['total_hit_count'];
                $opcode_stats['hit_miss'] = $info['total_miss_count'];
                $opcode_stats['hit_total'] = $info['total_hit_count'] + $info['total_miss_count'];

                $memory = wincache_fcache_meminfo();
                $opcode_stats['memory_avail'] = $memory['memory_free'];
                $opcode_stats['memory_total'] = $memory['memory_total'];
                $opcode_stats['memory_used'] = $memory['memory_total'] - $memory['memory_free'];

                $opcode_stats['memory_used'] /= $opcode_stats['memory_total'];
                $opcode_stats['memory_avail'] /= $opcode_stats['memory_total'];
                $opcode_stats['hit_hit'] /= $opcode_stats['hit_total'];
                $opcode_stats['hit_miss'] /= $opcode_stats['hit_total'];
            }
        } elseif (function_exists('opcache_get_status') && ini_get('opcache.enable') == '1') {
            $opcode_stats['opcode_cache'] = 'OpCache';
            $status = opcache_get_status();

            $opcode_stats['hit_hit'] = $status['opcache_statistics']['hits'];
            $opcode_stats['hit_miss'] = $status['opcache_statistics']['misses'];
            $opcode_stats['hit_total'] = $status['opcache_statistics']['hits'] + $status['opcache_statistics']['misses'];

            $opcode_stats['memory_avail'] = $status['memory_usage']['free_memory'];
            $opcode_stats['memory_used'] = $status['memory_usage']['used_memory'];
            $opcode_stats['memory_total'] = $status['memory_usage']['used_memory'] + $status['memory_usage']['free_memory'];

            $opcode_stats['memory_used'] /= $opcode_stats['memory_total'];
            $opcode_stats['memory_avail'] /= $opcode_stats['memory_total'];
            $opcode_stats['hit_hit'] /= $opcode_stats['hit_total'];
            $opcode_stats['hit_miss'] /= $opcode_stats['hit_total'];
        }

        // Make results easier to read
        $opcode_stats['memory_used'] = round($opcode_stats['memory_used'], 2);
        $opcode_stats['memory_avail'] = round($opcode_stats['memory_avail'], 2);
        $opcode_stats['hit_hit'] = round($opcode_stats['hit_hit'], 2);
        $opcode_stats['hit_miss'] = round($opcode_stats['hit_miss'], 2);

        if (isset($opcode_stats['hit_total'])) {
            $opcode_stats = array_merge(
                $opcode_stats,
                [
                    'warning_fresh' => $opcode_stats['hit_total'] < 10000,
                    'warning_ratio' => $opcode_stats['hit_hit'] < 0.8,
                ]
            );
        }

        if (isset($opcode_stats['memory_total'])) {
            $opcode_stats = array_merge(
                $opcode_stats,
                [
                    'warning_starve' => $opcode_stats['memory_avail'] < 0.2,
                    'warning_low' => $opcode_stats['memory_total'] < 60 * 1024 * 1024,
                ]
            );
        }

        $stat_flag = $opcode_stats['stat_flag'];
        if ($stat_flag) {
            $opcode_stats['warning_check'] = (bool)ini_get($stat_flag);
        }

        return $opcode_stats;
    }

    /**
     * Check if System Configuration file has "ini" extension and is under the tiki installation (likely web accessible)
     *
     * @return bool
     */
    public function checkSystemConfigurationFile()
    {
        $show_warning = false;

        $db_file = 'db/local.php';
        if (file_exists($db_file)) {
            include($db_file);

            if (isset($system_configuration_file) && file_exists($system_configuration_file)) {
                $tikiPath = realpath(TIKI_PATH);
                $configPath = realpath($system_configuration_file);
                if (strncmp($tikiPath, $configPath, strlen($tikiPath)) == 0) {
                    $file_extension = pathinfo($system_configuration_file, PATHINFO_EXTENSION);
                    if ($file_extension == 'ini') {
                        $show_warning = true;
                    }
                }
            }
        }

        return $show_warning;
    }

    /**
     * Check if System Configuration file contains something unusable/invalid
     *
     * @return array|false
     */
    public function checkConfigurationFileErrors()
    {
        $defaultPrefs = get_default_prefs();
        // Retrieving all configuration file data
        $configData = [];
        try {
            $configData = $this->retrieveConfigFileData(true);
        } catch (Exception $e) {
            Feedback::error($e->getMessage());
        }

        if (! $configData) {
            return false;
        }
        $errors_data = [];
        foreach ($configData as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    // prefs/rules defined without section
                    if (
                        ($key == "preference" && ! array_key_exists($k, $defaultPrefs))
                        || ($key == "rules" && ! is_numeric($k))
                    ) {
                        $errors_data[] = $key . '.' . $k;
                    } else if ($key != "preference" && $key != "rules") {
                        // $v must be an array otherwise the user may have just introduced for example: feature_sefurl instead of preference.feature_sefurl
                        // also reports an error if someone tries to use a key other than rules or preference
                        if (! is_array($v) || ($k != "rules" && $k != "preference")) {
                            $errors_data[] = $k;
                        }
                        foreach ($v as $index => $val) {
                            // in case of pref, check if it is a valid preference
                            // in case of rules, avoid something like rules.e
                            if (
                                ($k == "preference" && ! array_key_exists($index, $defaultPrefs))
                                || ($k == "rules" && ! is_numeric($index))
                            ) {
                                $errors_data[] = $k . '.' . $index;
                            }
                        }
                    }
                }
            } else {
                $errors_data[] = $key;
            }
        }
        return $errors_data;
    }

    /**
     * Retrieving configuration file data
     *
     * @return array
     * @throws Exception If configuration file is not readable
     */
    public function retrieveConfigFileData($retrieve_all_data = false)
    {
        global $system_configuration_identifier, $system_configuration_file;

        if (! is_readable($system_configuration_file)) {
            throw new Exception(tr('%0 configuration file could not be read', $system_configuration_file));
        }
        $configData = [];
        if ($retrieve_all_data || ! isset($system_configuration_identifier)) {
            $system_configuration_identifier = null;
        }
        $configReader = new Tiki_Config_Ini();
        $configReader->setFilterSection($system_configuration_identifier);

        if (preg_match('/\.ini.php$/', $system_configuration_file)) {
            $retrieveIniContent = function ($system_configuration_file) {
                ob_start();
                include($system_configuration_file);
                $system_configuration_file_content = ob_get_contents();
                ob_end_clean();

                return $system_configuration_file_content;
            };

            $system_configuration_content = $retrieveIniContent($system_configuration_file);
            $configData = $configReader->fromString($system_configuration_content);
        } else {
            $configData = $configReader->fromFile($system_configuration_file);
        }
        return $configData;
    }
}
