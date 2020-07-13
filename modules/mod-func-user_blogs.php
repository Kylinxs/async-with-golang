
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * @return array
 */
function module_user_blogs_info()
{
    return [
        'name' => tra('My Blogs'),
        'description' => tra('Displays to registered users their blogs.'),
        'prefs' => ['feature_blogs'],
        'params' => [],
        'common_params' => ["nonums"]
    ];
}

/**
 * @param $mod_reference
 * @param $module_params
 */
function module_user_blogs($mod_reference, $module_params)
{
    global $user;
    if ($user) {
        $smarty = TikiLib::lib('smarty');
        $bloglib = TikiLib::lib('blog');
        $ranking = $bloglib->list_user_blogs($user, false);

        $smarty->assign('modUserBlogs', $ranking);
        $smarty->assign('tpl_module_title', tra("My blogs"));
    }
}