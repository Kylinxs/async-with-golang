<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class DeclFilter_StaticKeyUnsetRule extends DeclFilter_UnsetRule
{
    private $keys;

    public function __construct($keys)
    {
        $this->keys = $keys;
    }

    public function match($key)
    {
        return in_array($key, $this->keys);
    }
}
