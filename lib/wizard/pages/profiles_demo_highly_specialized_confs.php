<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

require_once('lib/wizard/wizard.php');

/**
 * Show the profiles choices
 */
class ProfilesWizardHighlySpecializedConfs extends Wizard
{
    public function pageTitle()
    {
        return tra('Demo of Highly Specialized Configurations');
    }
    public function isEditable()
    {
        return false;
    }

    public function onSetupPage($homepageUrl)
    {
        global $prefs, $TWV;
        $smarty = TikiLib::lib('smarty');
        // Run the parent first
        parent::onSetupPage($homepageUrl);

        $smarty->assign('tikiMajorVersion', substr($TWV->version, 0, 2));

        return true;
    }

    public function getTemplate()
    {
        $wizardTemplate = 'wizard/profiles_demo_highly_specialized_confs.tpl';
        return $wizardTemplate;
    }

    public function onContinue($homepageUrl)
    {
        // Run the parent first
        parent::onContinue($homepageUrl);
    }
}
