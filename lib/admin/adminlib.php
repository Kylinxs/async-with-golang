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
            //preg_match_