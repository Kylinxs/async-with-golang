
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * Simple resolver always providing the same answer. Primarly
 * used for testing purposes, but also used as the administrator
 * resolver.
 */
class Perms_Resolver_Default implements Perms_Resolver
{
    private $value;

    public function __construct($value)
    {
        $this->value = (bool) $value;
    }

    public function check($name, array $groups)
    {
        return $this->value;
    }

    public function from()
    {
        return 'system';
    }

    public function applicableGroups()
    {
        return ['Anonymous', 'Registered'];
    }

    public function dump()
    {
        $result = [
            'from' => $this->from(),
            'perms' => [],
        ];
        foreach ($this->applicableGroups as $group) {
            $result['perms'][$this->value ? 'all' : 'none'][] = $group;
        }
        return $result;
    }
}