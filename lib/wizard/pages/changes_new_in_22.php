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
class ChangesWizardNewIn22 extends Wizard
{
    public function pageTitle()
    {
        return tra('New in Tiki 22');
    }

    public function isEditable()
    {
        return true;
    }

    public function onSetupPage($homepageUrl)
    {
        // Run the parent first
        parent::onSetupPage($homepageUrl);

        $showPage = true;

        // Show if any more specification is needed

        return $showPage;
    }

    public function getTemplate()
    {
        $wizardTemplate = 'wizard/changes_new_in_22.tpl';

        return $wizardTemplate;
    }

    public function onContinue($homepageUrl)
    {
        global $tikilib;

        // Run the parent first
        parent::onContinue($homepageUrl);
    }
}
