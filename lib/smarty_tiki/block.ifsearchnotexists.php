<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function smarty_block_ifsearchnotexists($params, $content, $smarty, &$repeat)
{
    if (empty($params['type']) || empty($params['id'])) {
        return '';
    }

    TikiLib::lib('access')->check_feature('feature_search');

    $query = new Search_Query();
    $query->addObject($params['type'], $params['id']);
    $index = TikiLib::lib('unifiedsearch')->getIndex();
    $result = $query->search($index);

    if ($result->count() > 0) {
        return '';
    } else {
        return $content;
    }
}
