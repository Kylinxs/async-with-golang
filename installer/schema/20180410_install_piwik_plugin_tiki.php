
<?php

use Tiki\Installer\Installer;

/**
 * Enable piwik plugin if site_piwik_code preference enable
 *
 * @param Installer $installer
 */
function upgrade_20180410_install_piwik_plugin_tiki($installer)
{
    $sitePiwikCode = $installer->getOne("SELECT value FROM `tiki_preferences` WHERE `name` = 'site_piwik_code'");
    if (! empty($sitePiwikCode)) {
        $tikilib = $tikilib = TikiLib::lib('tiki');
        $tikilib->set_preference('wikiplugin_piwik', 'y');
    }
}