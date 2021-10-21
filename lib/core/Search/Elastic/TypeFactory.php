<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Search_Elastic_TypeFactory implements Search_Type_Factory_Interface
{
    public function plaintext($value)
    {
        // Elasticsearch does not like boolean values
        if (is_bool($value)) {
            $value = (int) $value;
        }
        return new Search_Type_PlainText($value);
    }

    public function plainmediumtext($value)
    {
        // Elasticsearch does not like boolean values
        if (is_bool($value)) {
            $value = (int)$value;
        }
        return new Search_Type_PlainMediumText($value);
    }

    public function wikitext($value)
    {
        return new Search_Type_WikiText($value);
    }

    public function timestamp($value, $dateOnly = false)
    {
        return new Search_Type_DateTime($value, $dateOnly);
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
        return new Search_Type_MultivaluePlain(array_values((array) $values));
    }

    public function object($value)
    {
        return new Search_Type_Object($value);
    }

    public function nested($value)
    {
        return new Search_Type_Nested($value);
    }

    public function geopoint($value)
    {
        return new Search_Type_GeoPoint($value);
    }

    public function sortable($value)
    {
        return new Search_Type_PlainShortText($value);
    }

    public function simpletext($value)
    {
        return new Search_Type_SimpleText($value);
    }

    public function json($value)
    {
        return new Search_Type_Json($value);
    }
}
