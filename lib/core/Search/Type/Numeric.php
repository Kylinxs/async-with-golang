<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Search_Type_Numeric implements Search_Type_Interface
{
    private $value;

    public function __construct($value)
    {
        $this->value = (float) $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}
