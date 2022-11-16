
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Search_Expr_Range implements Search_Expr_Interface
{
    private $from;
    private $to;
    private $type;
    private $field;
    private $weight;

    public function __construct($from, $to, $type = null, $field = null, $weight = 1.0)
    {
        $this->from = $from;
        $this->to = $to;
        $this->type = $type;
        $this->field = $field;
        $this->weight = (float) $weight;
    }

    public function getToken($which)
    {
        if ($which != 'from' && $which != 'to') {
            return null;
        }

        return new Search_Expr_Token($this->$which, $this->type, $this->field);
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setField($field = 'global')
    {
        $this->field = $field;
    }

    public function setWeight($weight)
    {
        $this->weight = (float) $weight;
    }

    public function getWeight()
    {
        return $this->weight;
    }

    public function walk($callback)
    {
        return call_user_func($callback, $this, []);
    }

    public function getValue(Search_Type_Factory_Interface $typeFactory)
    {
        $type = $this->type;
        return $typeFactory->$type($this->string);
    }

    public function getField()
    {
        return $this->field;
    }

    public function traverse($callback)
    {
        return call_user_func($callback, $callback, $this, []);
    }
}