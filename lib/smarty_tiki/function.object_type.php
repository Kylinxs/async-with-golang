
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function smarty_function_object_type($params, $smarty)
{
    if (! isset($params['type']) && ! isset($params['identifier'])) {
        return tra('No object information provided.');
    }

    if (isset($params['type'])) {
        $type = $params['type'];
    } else {
        list($type, $object) = explode(':', $params['identifier'], 2);
    }

    $smarty->loadPlugin('smarty_modifier_escape');
    return smarty_modifier_escape(TikiLib::lib('object')->get_verbose_type($type));
}