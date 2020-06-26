
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Perms_Reflection_Object implements Perms_Reflection_Container
{
    protected $factory;
    protected $type;
    protected $object;
    protected $parentId;

    public function __construct($factory, $type, $object, $parentId = null)
    {
        $this->factory = $factory;
        $this->type = $type;
        $this->object = $object;
        $this->parentId = $parentId;
    }

    public function add($group, $permission)
    {
        $userlib = TikiLib::lib('user');
        $userlib->assign_object_permission($group, $this->object, $this->type, $permission);
    }

    public function remove($group, $permission)
    {
        $userlib = TikiLib::lib('user');
        $userlib->remove_object_permission($group, $this->object, $this->type, $permission);
    }

    public function getDirectPermissions()
    {
        $userlib = TikiLib::lib('user');
        $set = new Perms_Reflection_PermissionSet();

        $permissions = $userlib->get_object_permissions($this->object, $this->type);
        foreach ($permissions as $row) {
            $set->add($row['groupName'], $row['permName']);
        }

        return $set;
    }

    public function getParentPermissions()
    {
        if ($permissions = $this->getCategoryPermissions()) {
            return $permissions;
        } elseif ($this->parentId) {
            $parentType = Perms::parentType($this->type);
            $parentObject = $this->factory->get($parentType, $this->parentId);
            $permissions = $parentObject->getDirectPermissions();
            if (! $permissions->getPermissionArray()) {
                $permissions = $parentObject->getParentPermissions();
            }
            return $permissions;
        } else {
            return $this->factory->get('global', null)->getDirectPermissions();
        }
    }

    private function getCategoryPermissions()
    {
        $categories = $this->getCategories();

        $set = new Perms_Reflection_PermissionSet();
        $count = 0;
        foreach ($categories as $category) {
            $category = $this->factory->get('category', $category);
            foreach ($category->getDirectPermissions()->getPermissionArray() as $group => $perms) {
                foreach ($perms as $perm) {
                    $set->add($group, $perm);
                    ++$count;
                }
            }
        }

        if ($count != 0) {
            return $set;
        }
    }

    private function getCategories()
    {
        $categlib = TikiLib::lib('categ');

        return $categlib->get_object_categories($this->type, $this->object);
    }
}