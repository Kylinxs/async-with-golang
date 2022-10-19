
<?php

namespace Tiki\Group\Roles;

use TikiDb;
use TikiDb_Table;
use TikiLib;
use WikiParser_PluginMatcher;

class RolesLib extends TikiLib
{
    /**
     * Apply roles to a category and sync all children
     * @param $categoryId int Category Id to apply roles
     * @param $rolesToApply array Roles to apply
     * @throws
     */
    public function applyRoles($categoryId, $rolesToApply)
    {
        $categoryRolesAval = $this->table('tiki_categories_roles_available');
        $categoryRolesAval->deleteMultiple(["categId" => $categoryId]);
        foreach ($rolesToApply as $item) {
            $categoryRolesAval->insert(
                [
                    "categId" => $categoryId,
                    "categRoleId" => $item
                ]
            );
        }

        $this->deleteSelectedParentCategoryRoleNotUsed($categoryId, array_values($rolesToApply));
    }

    /**
     * Delete roles without parent
     * @param $categoryId int Category id
     */
    public function deleteRolesWithoutParent($categoryId)
    {

        $categoryRoles = $this->table('tiki_categories_roles');

        $categoryRolesAval = $this->table('tiki_categories_roles_available');
        $rolesIds = $categoryRolesAval->fetchColumn("categRoleId", ["categId" => $categoryId]);

        if (empty($rolesIds)) {
            $categoryRoles->deleteMultiple(["categRoleId" => $categoryId]);
        } else {
            $notInRoles = $categoryRoles->notIn($rolesIds);
            $categoryRoles->deleteMultiple(["categRoleId" => $categoryId, "groupRoleId" => $notInRoles]);
        }
    }

    /**
     * @param $categoryId int Category id
     * @return array Selected category object
     */
    public function getSelectedCategoryRoles($categoryId)
    {
        $categoryRoles = $this->table('tiki_categories_roles');
        return $categoryRoles->fetchAll([], ['categId' => $categoryId]);
    }


    /**Insert roles in to a category role
     * @param $categId category id
     * @param $categRoleId parent category id
     * @param $groupRoleId group role id
     * @param $groupId group id selected
     */
    public function insertOrUpdateSelectedCategoryRole($categId, $categRoleId, $groupRoleId, $groupId)
    {
        $roles = [
            "categId" => $categId,
            "categRoleId" => $categRoleId,
            "groupRoleId" => $groupRoleId,
            "groupId" => $groupId
        ];
        $rolesKeys = [
            "categId" => $categId,
            "categRoleId" => $categRoleId,
            "groupRoleId" => $groupRoleId,
        ];
        $categoryRoles = $this->table('tiki_categories_roles');
        $categoryRoles->insertOrUpdate($roles, $rolesKeys);
    }


    /**
     * Delete all category roles defined with the category without available roles
     * @param $categoryId
     * @param array $categRoleIds roles available
     */
    public function deleteSelectedCategoryRoleNotUsed($categoryId, $categRoleIds = [])
    {
        $categoryRoles = $this->table('tiki_categories_roles');
        $inCondition = $categoryRoles->notIn($categRoleIds);
        $categoryRoles->deleteMultiple(["categId" => $categoryId, "groupRoleId" => $inCondition]);
    }

    /**
     * Delete all category roles defined with the category without available roles
     * @param $categoryId
     * @param array $categRoleIds roles available
     */
    public function deleteSelectedParentCategoryRoleNotUsed($categoryId, $categRoleIds = [])
    {
        $categoryRoles = $this->table('tiki_categories_roles');
        $inCondition = $categoryRoles->notIn($categRoleIds);
        $categoryRoles->deleteMultiple(["categRoleId" => $categoryId, "groupRoleId" => $inCondition]);
    }


    /**
     * @param $categoryId
     * @return array return ids of the available roles from a category
     */
    public function getAvailableCategoriesRolesIds($categoryId)
    {
        $categoryRolesAval = $this->table('tiki_categories_roles_available');

        return $categoryRolesAval->fetchColumn("categRoleId", ["categId" => $categoryId]);
    }

    /**
     * Get ids the available roles from a category
     * @param $categoryId
     * @return array return available roles from a category
     * @throws \Exception
     */
    public function getAvailableCategoriesRoles($categoryId)
    {
        $ids = $this->getAvailableCategoriesRolesIds($categoryId);
        $rolesGroup = TikiLib::lib('user')->list_role_groups();
        $rolesGroupSelected = [];
        foreach ($rolesGroup as $item) {
            if (in_array($item["id"], $ids)) {
                $rolesGroupSelected[] = $item;
            }
        }

        return $rolesGroupSelected;
    }
}