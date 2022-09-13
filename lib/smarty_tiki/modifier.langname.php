
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function smarty_modifier_langname($lang)
{
    if (empty($lang)) {
        return '';
    }

    include('lang/langmapping.php');
    return empty($langmapping[$lang]) ? $lang : tra($langmapping[$lang][0]);
}