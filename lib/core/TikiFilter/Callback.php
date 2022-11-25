<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class TikiFilter_Callback implements Laminas\Filter\FilterInterface
{
    private $callback;

    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    public function filter($value)
    {
        $f = $this->callback;

        return $f($value);
    }
}
