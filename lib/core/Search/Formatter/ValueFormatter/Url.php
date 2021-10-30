
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$


class Search_Formatter_ValueFormatter_Url extends Search_Formatter_ValueFormatter_Abstract
{
    public function render($name, $value, array $entry)
    {
        return urlencode($value);
    }
}