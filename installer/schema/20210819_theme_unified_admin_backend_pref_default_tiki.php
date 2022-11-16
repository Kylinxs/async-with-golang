
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$
use Tiki\Installer\Installer;

/**
 * Set the default value on upgraded Tikis for theme_unified_admin_backend to 'n'
 *
 * @param Installer $installer
 */
function upgrade_20210819_theme_unified_admin_backend_pref_default_tiki($installer)
{
    $installer->preservePreferenceDefault('theme_unified_admin_backend', 'n');
}