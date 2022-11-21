<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

require_once('lib/wizard/wizard.php');

/**
 * Set up the file and file gallery settings
 */
class AdminWizardFiles extends Wizard
{
    public function pageTitle()
    {
        return tra('Set up File Gallery & Attachments');
    }
    public function isEditable()
    {
        return true;
    }

    public function onSetupPage($homepageUrl)
    {
        // Run the parent first
        parent::onSetupPage($homepageUrl);

        return true;
    }

    public function getTemplate()
    {
        $wizardTemplate = 'wizard/admin_files.tpl';
        return $wizardTemplate;
    }

    public function onContinue($homepageUrl)
    {
        $tikilib = TikiLib::lib('tiki');
        global $prefs;

        // Run the parent first
        parent::onContinue($homepageUrl);

        // If ElFinder is selected, set additional preferences
        if ($prefs['fgal_elfinder_feature'] === 'y') {
            // jQuery UI
            $tikilib->set_preference('feature_jquery_ui', 'y');
        }
    }
}
