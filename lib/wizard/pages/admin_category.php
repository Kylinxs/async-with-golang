<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

require_once('lib/wizard/wizard.php');

/**
 * Wizard page handler
 */
class AdminWizardCategory extends Wizard
{
    public function pageTitle()
    {
        return tra('Define Categories');
    }
    public function isEditable()
    {
        return false;
    }
    public function isVisible()
    {
        global  $prefs;
        return $prefs['feature_categories'] === 'y';
    }

    public function onSetupPage($homepageUrl)
    {
        global $prefs;
        // Run the parent first
        parent::onSetupPage($homepageUrl);

        if (! $this->isVisible()) {
            return false;
        }

        return true;
    }

    public function getTemplate()
    {
        $wizardTemplate = 'wizard/admin_category.tpl';
        return $wizardTemplate;
    }

    public function onContinue($homepageUrl)
    {
        // Run the parent first
        parent::onContinue($homepageUrl);
    }
}
