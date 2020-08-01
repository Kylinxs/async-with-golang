
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
 * PollLibShared
 *
 * @uses TikiLib
 */
class PollLibShared extends TikiLib
{
    /**
     * @param $pollId
     * @return bool
     */
    public function get_poll($pollId)
    {
        $query = "select * from `tiki_polls` where `pollId`=?";
        $result = $this->query($query, [(int)$pollId]);
        if (! $result->numRows()) {
            return false;
        }
        $res = $result->fetchRow();
        return $res;
    }

    /**
     * @param $optionId
     * @return array
     */
    public function get_poll_voters($optionId)
    {
        $query = "select user from `tiki_user_votings` where `optionId`=?";
        $result = $this->query($query, [(int)$optionId]);
        $ret = [];
        while ($res = $result->fetchRow()) {
            $ret[] = $res;
        }
        return $ret;
    }

    /**
     * @param $pollId
     * @param int $from
     * @param int $to
     * @return array
     */
    public function list_poll_options($pollId, $from = 0, $to = 0)
    {
        if (empty($from) && empty($to)) {
            $query = 'select * from `tiki_poll_options` where `pollId`=?';
            $bindVars = [(int) $pollId];
        } else {
            $query = 'select tpo.`pollId`, tpo.`optionId`, tpo.`title`, tpo.`position`, count(tuv.`id`) as votes' .
                            ' from `tiki_poll_options` tpo' .
                            ' left join `tiki_user_votings` tuv on (tpo.`optionId` = tuv.`optionId`)' .
                            ' where `pollId`=? and ((tuv.`time` >= ? and tuv.`time` <= ?) or tuv.`time` = ?)' .
                            ' group by `votes`, tpo.`pollId`, tpo.`optionId`, tpo.`title`, tpo.`position` ';
            $bindVars = [(int)$pollId, (int)$from, (int)$to, 0];
        }

        $query .= ' order by `position`';
        $result = $this->query($query, $bindVars);
        $ret = [];
        while ($res = $result->fetchRow()) {
            $ret[] = $res;
        }
        return $ret;
    }

    /**
     * @param string $active
     * @return int
     */
    public function get_random_poll($active = "a")
    {
        $bindvars = [(int)$this->now, $active];

        if ($active == "a") {
            $bindvars[] = "c"; // current;
            $mid = "or `active`=?";
        }

        $result = $this->query("select `pollId` from `tiki_polls` where `publishDate`<=? and (`active`=? $mid) ", $bindvars);
        $ret = [];

        while ($res = $result->fetchRow()) {
            $ret[] = $res;
        }

        if (count($res) == 0) {
            return 0;
        } elseif (count($ret) == 1) {
            return $ret[0]['pollId'];
        } else {
            $bid = mt_rand(0, count($ret) - 1);
            return $ret[$bid]['pollId'];
        }
    }

    /**
     * @param string $type
     * @param int $datestart
     * @param string $dateend
     * @param string $find
     * @return array
     */
    public function get_polls($type = 'a', $datestart = 0, $dateend = '', $find = '')
    {
        if (! $dateend) {
            $dateend = date('U');
        }
        $bindvars = [$type, (int)$datestart, (int)$dateend];

        if ($find) {
            $mid = 'and `title`=?';
            $bindvars[] = '%' . $find . '%';
        } else {
            $mid = '';
        }

        $query = "select * from `tiki_polls` where `active`=? and `publishDate`>=? and `publishDate`<=? $mid";
        $query_cant = "select count(*) from `tiki_polls` where `active`=? and `publishDate`>=? and `publishDate`<=? $mid";
        $result = $this->query($query, $bindvars);
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
     * @param $user
     * @param $pollId
     * @param $optionId
     * @param $previous_vote
     *
     * @return TikiDb_Pdo_Result|TikiDb_Adodb_Result|bool
     */
    public function poll_vote($user, $pollId, $optionId, $previous_vote)
    {
        if (! $previous_vote || $previous_vote == 0) {
            $query = "update `tiki_polls` set `votes`=`votes`+1 where `pollId`=?";
            $this->query($query, [(int) $pollId]);
            $query = "update `tiki_poll_options` set `votes`=`votes`+1 where `optionId`=?";
            return $this->query($query, [(int) $optionId]);
        } elseif ($previous_vote != $optionId) {
            $query = "update `tiki_poll_options` set `votes`=`votes`-1 where `optionId`=?";
            $this->query($query, [(int) $previous_vote]);
            $query = "update `tiki_poll_options` set `votes`=`votes`+1 where `optionId`=?";
            return $this->query($query, [(int) $optionId]);
        } else {
            return true;
        }
    }

    /**
     * @param $cat_type
     * @param $cat_objid