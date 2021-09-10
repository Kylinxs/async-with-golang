<?php

/**
 * @package tikiwiki
 */

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

$section = 'cms';
require_once('tiki-setup.php');
include_once('lib/rankings/ranklib.php');
$access->check_feature(['feature_articles', 'feature_cms_rankings']);
$access->check_permission('tiki_p_read_article');

$allrankings = [
    [
    'name' => tra('Top Articles'),
    'value' => 'cms_ranking_top_articles'
    ],
    [
    'name' => tra('Top authors'),
    'value' => 'cms_ranking_top_authors'
    ]
];

$smarty->assign('allrankings', $allrankings);

if (! isset($_REQUEST["which