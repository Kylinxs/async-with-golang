
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Tiki_Profile_InstallHandler_Menu extends Tiki_Profile_InstallHandler
{
    public function getData()
    {
        if ($this->data) {
            return $this->data;
        }

        $defaults = [
            'description' => '',
            'collapse' => 'collapsed',
            'icon' => '',
            'groups' => [],
            'items' => [],
            'cache' => 0,
        ];

        $data = array_merge($defaults, $this->obj->getData());

        $data['groups'] = serialize($data['groups']);

        $position = 0;
        foreach ($data['items'] as &$item) {
            $this->fixItem($item, $position);
        }

        $items = [];
        $this->flatten($data['items'], $items);
        $data['items'] = $items;

        return $this->data = $data;
    }

    public function flatten($entries, &$list)
    {
        foreach ($entries as $item) {
            $children = $item['items'];
            unset($item['items']);

            $list[] = $item;
            $this->flatten($children, $list);
        }
    }

    private function fixItem(&$item, &$position, $parent = null)
    {
        $position += 10;

        if (! isset($item['name'])) {
            $item['name'] = 'Unspecified';
        }
        if (! isset($item['url'])) {
            $item['url'] = 'tiki-index.php';
        }
        if (! isset($item['section'])) {
            $item['section'] = null;
        }
        if (! isset($item['level'])) {
            $item['level'] = 0;
        }
        if (! isset($item['permissions'])) {
            $item['permissions'] = [];
        }
        if (! isset($item['groups'])) {
            $item['groups'] = [];
        }
        if (! isset($item['items'])) {
            $item['items'] = [];
        }

        if (! isset($item['position'])) {
            $item['position'] = $position;
        }

        if (! isset($item['type'])) {
            $item['type'] = 's';

            if ($parent) {
                if ($parent['type'] === 's') {
                    $item['type'] = 1;
                } else {
                    $item['type'] = $parent['type'] + 1;
                }


                $item['level'] = $parent['level'] + 1;

                $item['permissions'] = array_unique(array_merge($parent['permissions'], $item['permissions']));
                $item['groups'] = array_unique(array_merge($parent['groups'], $item['groups']));
            }
        }

        foreach ($item['items'] as &$child) {
            $this->fixItem($child, $position, $item);
        }

        foreach ($item['permissions'] as &$perm) {
            if (strpos($perm, 'tiki_p_') !== 0) {
                $perm = 'tiki_p_' . $perm;
            }
        }
    }

    public function canInstall()
    {
        $data = $this->getData();
        if (! isset($data['name'])) {
            return false;
        }
        if (count($data['items']) == 0) {
            return false;
        }

        return true;
    }

    public function doInstall()
    {
        $tikilib = TikiLib::lib('tiki');
        $modlib = TikiLib::lib('mod');
        $menulib = TikiLib::lib('menu');

        $data = $this->getData();

        $this->replaceReferences($data);
        $data = Tiki_Profile::convertYesNo($data);

        $type = 'f';
        if ($data['collapse'] == 'collapsed') {
            $type = 'd';
        } elseif ($data['collapse'] == 'expanded') {
            $type = 'e';
        }
        if ($data['use_items_icons'] == null) {
            $data['use_items_icons'] = '';
        }
        if ($data['parse'] == null) {
            $data['parse'] = '';
        }

        $menulib->replace_menu(0, $data['name'], $data['description'], $type, $data['icon'], $data['use_items_icons'], $data['parse']);
        $result = $tikilib->query("SELECT MAX(`menuId`) FROM `tiki_menus`");
        $row = $result->fetchRow();
        $menuId = reset($row);

        foreach ($data['items'] as $item) {
            $menulib->replace_menu_option($menuId, 0, $item['name'], $item['url'], $item['type'], $item['position'], $item['section'], implode(',', $item['permissions']), implode(',', $item['groups']), $item['level'], $item['icon']);
        }

        // Set module title to menu_nn if it is not set by a parameter
        if (! isset($data['title'])) {
            $modtitle = "menu_$menuId";
        } else {
            $modtitle = $data['title'];
        }

        // Set up module only as a custom module if position is set to 'none'
        if ($data['position'] == 'none') {
            // but still allow module_arguments but keep it simple and don't include the $key=
            $extra = '';
            if (isset($data['module_arguments'])) {
                foreach ($data['module_arguments'] as $key => $value) {
                    $extra .= " $value";
                }
            }

            $content = "{menu id=$menuId$extra}";
            $modlib->replace_user_module($data['name'], $modtitle, $content);
        } elseif (isset($data['position'], $data['order'])) {// Set module as side menu if both position and order are specified and position is not 'none'
            $column = $data['position'] == 'left' ? 'l' : 'r';

            $extra = '';
            if (isset($data['module_arguments'])) {
                foreach ($data['module_arguments'] as $key => $value) {
                    $extra .= " $key=$value";
                }
            }

            $content = "{menu id=$menuId$extra}";

            $modlib->replace_user_module($data['name'], $modtitle, $content);
            $modlib->assign_module(0, "menu_$menuId", null, $column, $data['order'], $data['cache'], 10, $data['groups'], '');
        }

        return $menuId;
    }

    /**
     * Export menus
     *
     * @param Tiki_Profile_Writer $writer
     * @param int $menuId
     * @param bool $all
     * @return bool
     */
    public static function export(Tiki_Profile_Writer $writer, $menuId, $all = false)
    {
        $menulib = TikiLib::lib('menu');

        if (isset($menuId) && ! $all) {
            $listMenu = [];
            $listMenu[] = $menulib->get_menu($menuId);
        } else {
            $listMenu = $menulib->list_menus();
            $listMenu = $listMenu['data'];
        }

        if (empty($listMenu[0]['menuId'])) {
            return false;
        }

        foreach ($listMenu as $menu) {
            $menuId = $menu['menuId'];
            if ($menuId == 42) { // The system menu has a value hardcoded of 42
                continue;
            }
            $options = $menulib->list_menu_options($menuId, 0, -1, 'position_asc', '', true, 0, true);
            $menu['items'] = array_map(function ($entry) use ($writer) {
                unset($entry['menuId']);
                unset($entry['optionId']);
                if ($entry['perm']) {
                    $entry['permissions'] = [$entry['perm']];
                }

                return $entry;
            }, $options['data']);

            $writer->addObject('menu', $menuId, $menu);
        }

        return true;
    }

    /**
     * Remove menu
     *
     * @param string $menu
     * @return bool
     */
    public function remove($menu)
    {
        if (! empty($menu)) {
            $menulib = TikiLib::lib('menu');
            $menus = $menulib->list_menus(0, -1, 'menuId_desc', $menu);
            $menuId = ! empty($menus['data'][0]['menuId']) ? $menus['data'][0]['menuId'] : null;

            // The system menu has a value hardcoded of 42
            if ($menuId && $menuId != 42 && $menulib->remove_menu($menuId)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get current menu data
     *
     * @param array $menu
     * @return mixed
     */
    public function getCurrentData($menu)
    {
        $menuName = ! empty($menu['name']) ? $menu['name'] : '';
        $menulib = TikiLib::lib('menu');
        $menus = $menulib->list_menus(0, -1, 'menuId_desc', $menuName);
        if (! empty($menus['data'][0])) {
            $data = $menus['data'][0];
            $menuId = ! empty($data['menuId']) ? $data['menuId'] : 0;
            $menuOptions = $menulib->list_menu_options($menuId);
            if (! empty($menuOptions['data'])) {
                $data['items'] = $menuOptions['data'];
            }
            $defaults = [
                'menuId' => 0,
                'description' => '',
                'collapse' => 'collapsed',
                'icon' => '',
                'groups' => [],
                'items' => [],
                'cache' => 0,
            ];
            $data = array_merge($defaults, $data);
            $data['groups'] = serialize($data['groups']);
            $position = 0;
            foreach ($data['items'] as &$item) {
                $this->fixItem($item, $position);
            }
            $items = [];
            $this->flatten($data['items'], $items);
            $data['items'] = $items;
            return $data;
        }
        return false;
    }

    /**
     * Get menu changes
     *
     * @param array $before
     * @param array $after
     * @return mixed
     */
    public function getChanges($before, $after)
    {
        if (! empty($before['menuId']) && ! empty($after['menuId']) && $before['menuId'] === $after['menuId']) {
            return $before;
        }
        return false;
    }
}