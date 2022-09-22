<?php

/**
 * @package tikiwiki
 */

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$


$inputConfiguration = [[
    'staticKeyFilters'  => [
        'use-default-prefs' => 'alnum',         // request
        'use-changes-wizard' => 'alnum',        // request
        'url'               => 'relativeurl',   // request
        'close'             => 'alnum',         // post
        'showOnLogin'       => 'alnum',         // post
        'wizard_step'       => 'int',           // post
        'stepNr'            => 'int',           // get
        'back'              => 'alnum',         // post
    ],
    'staticKeyFiltersForArrays' => [
        'lm_preference' => 'xss',
    ],
]];

require 'tiki-setup.php';

$headerlib = TikiLib::lib('header');
$headerlib->add_cssfile('themes/base_files/feature_css/admin.css');
$headerlib->add_cssfile('themes/base_files/feature_css/wizards.css');

// Hide the display of the preference dependencies in the wizard
$headerlib->add_css('.pref_dependency{display:none !important;}');

$accesslib = TikiLib::lib('access');
$accesslib->check_permission('tiki_p_admi