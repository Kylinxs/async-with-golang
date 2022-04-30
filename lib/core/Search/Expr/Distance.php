<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Search_Expr_Distance implements Search_Expr_Interface
{
    private $distance;
    private $lat;
    private $lon;
    private $field;
    private $weight;

    public function __construct($distance, $lat, $lon, $field = 'geo_point', $weight = 1.0)
    {
        $this->distance = $distance;
        $this->lat = (float) $lat;
        $this->lon = (float) $lon;
        $this->field = $field;
        $this->weight = (float) $weight;
    }

    /**
     * @return string
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @return float
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @return float
     */
    public function getLon()
    {
        return $this->lon;
    }

    public function setType($type)
    {
    }

    public function setField($field = 'geo_point')
    {
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
