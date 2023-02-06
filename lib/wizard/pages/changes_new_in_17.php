<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

require_once('lib/wizard/wizard.php');

/**
 * The Wizard's language handler
 */
class ChangesWizardNewIn17 extends Wizard
{
    public function pageTitle()
    {
        return tra('New in Tiki 17');
    }

    public function isEditable()
    {
        return true;
    }

    public function onSetupPage($homepageUrl)
    {
        global $prefs;
        $smarty = TikiLib::lib('smarty');
        $addonprefs = TikiLib::lib('prefs')->getPackagePrefs();
        $smarty->assign('addonprefs', $addonprefs);

        // Run the parent first
        parent::onSetupPage($homepageUrl);

        $showPage = true;

        // Show if any more specification is needed

        return $showPage;
    }

    public function getTemplate()
    {
        $wizardTemplate = 'wizard/changes_new_in_17.tpl';

        return $wizardTemplate;
    }

    public function onContinue($homepageUrl)
    {
        global $tikilib;

        // Run the parent first
        parent::onContinue($homepageUrl);
    }
}
