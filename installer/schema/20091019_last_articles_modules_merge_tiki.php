<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * @param $installer
 */
function upgrade_20091019_last_articles_modules_merge_tiki($installer)
{
    $result = $installer->query("select moduleId, params from tiki_modules where name='last_articles'; ");
    while ($row = $result->fetchRow()) {
        $params = $row['params