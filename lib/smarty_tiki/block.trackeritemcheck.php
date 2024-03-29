<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 *
 */
/***
 * Test if user can view/edit/delete a tracker item
 *
 * @param $params array
 *          itemId  = int       required
 *          mode    = string    view|edit|delete (view is default)
 *
 * @param $content string   Content to display if allowed
 * @param $smarty Smarty
 * @param $repeat bool
 *
 * @return string           Content to display if allowed
 */

function smarty_block_trackeritemcheck($params, $content, $smarty, $repeat)
{
    if ($repeat) {
        return;
    }

    if (empty($params['itemId'])) {
        return tra('itemId required');
    }
    if (empty($params['mode'])) {
        $params['mode'] = '';       // default is to view
    }

    $item = Tracker_Item::fromId($params['itemId']);

    $allowed = false;

    switch ($params['mode']) {
        case 'edit':
            $allowed = $item->canModify();
            break;
        case 'delete':
            $allowed = $item->canRemove();
            break;
        case 'view':
            $allowed = $item->canView();
            break;
        default:
            break;
    }

    if ($allowed) {
        return $content;
    } else {
        return '';
    }
}
