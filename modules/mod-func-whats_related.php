
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * @return array
 */
function module_whats_related_info()
{
    return [
        'name' => tra('Related Items'),
        'description' => tra('Lists objects which share a category with the viewed object.'),
        'prefs' => ['feature_categories'],
        'params' => []
    ];
}

/**
 * @param $mod_reference
 * @param $module_params
 */
function module_whats_related($mod_reference, $module_params)
{
    $smarty = TikiLib::lib('smarty');
    $categlib = TikiLib::lib('categ');

    $WhatsRelated = $categlib->get_link_related($_SERVER["REQUEST_URI"]);
    $smarty->assign_by_ref('WhatsRelated', $WhatsRelated);
}