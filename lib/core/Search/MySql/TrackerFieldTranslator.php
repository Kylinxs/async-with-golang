
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Search_MySql_TrackerFieldTranslator
{
    private $unified_mysql_short_field_names;

    public function __construct()
    {
        global $prefs;
        $this->unified_mysql_short_field_names = isset($prefs['unified_mysql_short_field_names'])
            && $prefs['unified_mysql_short_field_names'] === 'y';
    }

    public function shortenize($fieldName)
    {
        if ($this->unified_mysql_short_field_names && substr($fieldName, 0, 14) === 'tracker_field_') {
            return 'tf_' . substr($fieldName, 14);
        } else {
            return $fieldName;
        }
    }

    public function normalize($fieldName)
    {
        if ($this->unified_mysql_short_field_names && substr($fieldName, 0, 3) === 'tf_') {
            return 'tracker_field_' . substr($fieldName, 3);
        } else {
            return $fieldName;
        }
    }
}