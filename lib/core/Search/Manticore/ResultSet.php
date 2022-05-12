<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Search_Manticore_ResultSet extends Search_ResultSet
{
    public function highlight($content)
    {
        if (! empty($content['_highlight'])) {
            return strip_tags($content['_highlight'], '<em>');
        } elseif ($this->highlightHelper && isset($content['contents'])) {
            return $this->highlightHelper->filter($content['contents']);
        }
    }
}
