
<?php

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

/**
 *
 */
class FlinksLib extends TikiLib
{
    /**
     * @param        $url
     * @param        $title
     * @param string $description
     * @param int    $position
     * @param string $type
     *
     * @return TikiDb_Pdo_Result|TikiDb_Adodb_Result
     */
    public function add_featured_link($url, $title, $description = '', $position = 0, $type = 'f')
    {
        $query = "delete from `tiki_featured_links` where `url`=?";
        $this->query($query, [$url], -1, -1, false);
        $query = "insert into `tiki_featured_links`(`url`,`title`,`description`,`position`,`hits`,`type`) values(?,?,?,?,?,?)";
        return $this->query($query, [$url,$title,$description,$position,0,$type]);
    }

    /**
     * @param $url
     *
     * @return TikiDb_Pdo_Result|TikiDb_Adodb_Result
     */
    public function remove_featured_link($url)
    {
        $query = "delete from `tiki_featured_links` where `url`=?";
        return $this->query($query, [$url]);
    }

    /**
     * @param        $url
     * @param        $title
     * @param        $description
     * @param int    $position
     * @param string $type
     *
     * @return TikiDb_Pdo_Result|TikiDb_Adodb_Result
     */
    public function update_featured_link($url, $title, $description, $position = 0, $type = 'f')
    {
        $query = "update `tiki_featured_links` set `title`=?, `type`=?, `description`=?, `position`=? where `url`=?";
        return $this->query($query, [$title,$type,$description,$position,$url]);
    }

    /**
     * @param $url
     *
     * @return bool|TikiDb_Pdo_Result|TikiDb_Adodb_Result
     */
    public function add_featured_link_hit($url)
    {
        global $prefs, $user;

        if (StatsLib::is_stats_hit()) {
            $query = "update `tiki_featured_links` set `hits` = `hits` + 1 where `url` = ?";
            return $this->query($query, [$url]);
        } else {
            return false;
        }
    }

    /**
     * @param $url
     *
     * @return array|bool
     */
    public function get_featured_link($url)
    {
        $query = "select * from `tiki_featured_links` where `url`=?";

        $result = $this->query($query, [$url]);

        if (! $result->numRows()) {
            return false;
        }

        return $result->fetchRow();
    }

    /**
     * @return bool
     */
    public function generate_featured_links_positions()
    {
        $query = "select `url` from `tiki_featured_links` order by `hits` desc";
        $result = $this->query($query, []);
        $position = 1;

        while ($res = $result->fetchRow()) {
            $url = $res["url"];

            $query2 = "update `tiki_featured_links` set `position`=? where `url`=?";
            $result2 = $this->query($query2, [$position,$url]);
            $position++;
        }

        return true;
    }
}
$flinkslib = new FlinksLib();