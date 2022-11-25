<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * Tiki_ShareGroup
 *
 */
class Tiki_ShareGroup
{
    public $name;

    public $selectedValues;

    public $groupPerm;
    public $categPerm;
    public $objectPerm;

    /**
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->groupPerm = [];
        $this->categPerm = [];
        $this->objectPerm = [];
        $this->selectedValues = [];
    }

    /**
     * @param $permission
     */
    public function addGroupPermission($permission)
    {
        $this->groupPerm[$permission] = 'y';
    }

    /**
     * @param $source
     * @param $permission
     */
    public function addCategoryPermission($source, $permission)
    {
        if (! array_key_exists($permission, $this->categPerm)) {
            $this->categPerm[$permission] = [];
        }

        $this->categPerm[$permission][] = $source;
    }

    /**
     * @param $permission
     */
    public function addObjectPermission($permission)
    {
        $this->objectPerm[$permission] = 'y';
        $this->selectedValues[] = $permission;
    }

    /**
     * @param $permission
     * @return string
     */
    public function getSourceCategory($permission)
    {
        if (array_key_exists($permission, $this->categPerm)) {
            return implode(', ', $this->categPerm[$permission]);
        }

        return ''