<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class MenuLib extends TikiLib
{
    public function empty_menu_cache($menuId = 0)
    {
        $cachelib = TikiLib::lib('cache');
        if ($menuId > 0) {
            $cachelib->empty_type_cache('menu_' . $menuId . '_');
        } else {
            $menus = $this->list_menus();
            foreach ($menus['data'] as $menu_info) {
                $cachelib->empty_type_cache('menu_' . $menu_info['menuId'] . '_');
            }
        }
    }

    public function list_menus($offset = 0, $maxRecords = -1, $sort_mode = 'menuId_asc', $find = '')
    {
        if ($find) {
            $findesc = '%' . $find . '%';
            $mid = " where (`name` like ? or `description` like ?)";
            $bindvars = [$findesc,$findesc];
        } else {
            $mid = "";
            $bindvars = [];
        }

        $query = "select * from `tiki_menus` $mid order by " . $this->convertSortMode($sort_mode);
        $result = $this->query($query, $bindvars, $maxRecords, $offset);
        $query_cant = "select count(*) from `tiki_menus` $mid";
        $cant = $this->getOne($query_cant, $bindvars);
        $ret = [];

        while ($res = $result->fetchRow()) {
            $query = "select count(*) from `tiki_menu_options` where `menuId`=?";
            $res["options"] = $this->getOne($query, [(int) $res["menuId"]]);
            $ret[] = $res;
        }

        $retval = [];
        $retval["data"] = $ret;
        $retval["cant"] = $cant;
        return $retval;
    }

    public function replace_menu($menuId, $name, $description = '', $type = 'd', $icon = null, $use_items_icons = 'n', $parse = 'n')
    {
        // Check the name
        if (isset($menuId) and $menuId > 0) {
            $query = "update `tiki_menus` set `name`=?,`description`=?,`type`=?, `icon`=?, `use_items_icons`=?, `parse`=? where `menuId`=?";
            $bindvars = [$name,$description,$type,$icon,$use_items_icons,$parse,(int) $menuId];
            $this->empty_menu_cache($menuId);
        } else {
            // was: replace into. probably we need a delete here
            $query = "insert into `tiki_menus` (`name`,`description`,`type`,`icon`,`use_items_icons`,`parse`) values(?,?,?,?,?,?)";
            $bindvars = [$name,$description,$type,$icon,$use_items_icons,$parse];
        }

        $result = $this->query($query, $bindvars);
        return true;
    }

    public function clone_menu($menuId, $name, $description = '')
    {
        $menus = $this->table('tiki_menus');
        $row = $menus->fetchFullRow([    'menuId' => $menuId ]);
        $row['menuId'] = null;
        $row['name'] = $name;
        $row['description'] = $description;
        $newId = $menus->insert($row);

        $menuoptions = $this->table('tiki_menu_options');
        $oldoptions = $menuoptions->fetchAll($menuoptions->all(), [ 'menuId' => $menuId ]);
        $row = null;

        foreach ($oldoptions as $row) {
            $row['optionId'] = null;
            $row['menuId'] = $newId;
            $menuoptions->insert($row);
        }
    }

    /*
     * Replace the current menu options for id 42 with what's in tiki.sql
     */
    public function reset_app_menu()
    {
        $tiki_sql = file_get_contents('db/tiki.sql');
        preg_match_all('/^(?:INSERT|UPDATE) (?:INTO )?`?tiki_menu_options`? .*$/mi', $tiki_sql, $matches);

        if ($matches && count($matches[0])) {
            $menuoptions = $this->table('tiki_menu_options');
            $menuoptions->deleteMultiple([ 'menuId' => 42 ]);

            foreach ($matches[0] as $query) {
                $thi