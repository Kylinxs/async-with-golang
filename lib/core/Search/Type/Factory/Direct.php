
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Search_Type_Factory_Direct implements Search_Type_Factory_Interface
{
    public function plaintext($value)
    {
        return new Search_Type_Whole($value);
    }

    public function plainmediumtext($value)
    {
        return new Search_Type_PlainMediumText($value);
    }

    public function wikitext($value)
    {
        return new Search_Type_PlainText($value);
    }

    public function timestamp($value, $dateOnly = false)
    {
        return new Search_Type_Whole($value);
    }

    public function identifier($value)
    {
        return new Search_Type_Whole($value);
    }

    public function numeric($value)
    {
        return new Search_Type_Numeric($value);
    }

    public function multivalue($values)
    {
        return new Search_Type_Whole((array) $values);
    }

    public function object($values)
    {
        return new Search_Type_Object($values);
    }

    public function nested($values)
    {
        return new Search_Type_Nested($values);
    }

    public function geopoint($values)
    {
        return new Search_Type_GeoPoint($values);
    }

    public function sortable($value)
    {
        return new Search_Type_Whole($value);
    }

    public function simpletext($value)
    {
        return new Search_Type_SimpleText($value);
    }

    public function json($value)
    {
        return new Search_Type_PlainText($value);
    }
}