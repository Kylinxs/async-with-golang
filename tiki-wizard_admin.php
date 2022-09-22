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
$accesslib->check_permission('tiki_p_admin');

// Create the template instances
$pages = [];

/////////////////////////////////////
// BEGIN Wizard page section
/////////////////////////////////////

// Always show the first page
require_once('lib/wizard/pages/admin_wizard.php');
$pages[] = new AdminWizard();

// If $useDefaultPrefs is set, the "profiles wizard" should be run. Otherwise the "admin wizard".
$useDefaultPrefs = isset($_REQUEST['use-default-prefs']) ? true : false;
// If $useChangesWizard is set, the "Changes Wizard" should be run. Otherwise the "admin wizard".
$useChangesWizard = isset($_REQUEST['use-changes-wizard']) ? true : false;
if ($useDefaultPrefs) {
    // Store the default prefs selection in the wizard bar
    $smarty->assign('useDefaultPrefs', $useDefaultPrefs);

    require_once('lib/wizard/pages/profiles_featured_site_confs.php');
    $pages[] = new ProfilesWizardFeaturedSiteConfs();

    require_once('lib/wizard/pages/profiles_useful_micro_confs.php');
    $pages[] = new ProfilesWizardUsefulMicroConfs();

    require_once('lib/wizard/pages/profiles_useful_changes_in_display.php');
    $pages[] = new ProfilesWizardUsefulChangesInDisplay();

    require_once('lib/wizard/pages/profiles_useful_new_tech_confs.php');
    $pages[] = new ProfilesWizardUsefulNewTechConfs();

    require_once('lib/wizard/pages/profiles_useful_admin_confs.php');
    $pages[] = new ProfilesWizardUsefulAdminConfs();

    require_once('lib/wizard/pages/profiles_demo_common_confs.php');
    $pages[] = new ProfilesWizardDemoCommonConfs();

    require_once('lib/wizard/pages/profiles_demo_interesting_use_cases.php');
    $pages[] = new ProfilesWizardDemoInterestingUseCases();

    require_once('lib/wizard/pages/profiles_demo_other_interesting_use_cases.php');
    $pages[] = new ProfilesWizardDemoOtherInterestingUseCases();

    require_once('lib/wizard/pages/profiles_demo_more_advanced_confs.php');
    $pages[] = new ProfilesWizardDemoMoreAdvancedConfs();

    require_once('lib/wizard/pages/profiles_demo_cases_in_project_management.php');
    $pages[] = new ProfilesWizardDemoProjectManagement();

    require_once('lib/wizard/pages/profiles_demo_highly_specialized_confs.php');
    $pages[] = new ProfilesWizardHighlySpecializedConfs();

    require_once('lib/wizard/pages/profiles_completed.php');
    $pages[] = new AdminWizardProfilesCompleted();
} elseif ($useChangesWizard) {
    // Store the use Changes Wizard selection in the wizard bar
    $smarty->assign('useChangesWizard', $useChangesWizard);

/*
    require_once('lib/wizard/pages/changes_ui.php');
    $pages[] = new ChangesWizardUI();

    require_once('lib/wizard/pages/changes_novice_admin_assistance.php');
    $pages[] = new ChangesWizardNoviceAdminAssistance();

    require_once('lib/wizard/pages/changes_trackers.php');
    $pages[] = new ChangesWizardTrackers();

    require_once('lib/wizard/pages/changes_permissions_and_logs.php');
    $pages[] = new ChangesWizardPermissionsAndLogs();

    require_once('lib/wizard/pages/changes_others.php');
    $pages[] = new ChangesWizardOthers();

    require_once('lib/wizard/pages/changes_new_in_13.php');
    $pages[] = new ChangesWizardNewIn13();

    require_once('lib/wizard/pages/changes_new_in_14.php');
    $pages[] = new ChangesWizardNewIn14();

    require_once('lib/wizard/pages/changes_new_in_15.php');
    $pages[] = new ChangesWizardNewIn15();

    require_once('lib/wizard/pages/changes_new_in_16.php');
    $pages[] = new ChangesWizardNewIn16();

    require_once('lib/wizard/pages/changes_new_in_17.php');
    $pages[] = new ChangesWizardNewIn17();
*/

    require_once('lib/wizard/pages/changes_new_in_24.php');
    $pages[] = new ChangesWizardNewIn24();

    require_once('lib/wizard/pages/changes_new_in_23.php');
    $pages[] = new ChangesWizardNewIn23();

    require_once('lib/wizard/pages/changes_new_in_22.php');
    $pages[] = new ChangesWizardNewIn22();

    require_once('lib/wizard/pages/changes_new_in_21.php');
    $pages[] = new ChangesWizardNewIn21();

    require_once('lib/wizard/pages/changes_doc_page_iframe.php');
    $pages[] = new ChangesWizardDocPageIframe();

    require_once('lib/wizard/pages/changes_send_feedback.php');
    $pages[] = new ChangesWizardSendFeedback();

    require_once('lib/wizard/pages/changes_wizard_completed.php');
    $pages[] = new ChangesWizardCompleted();
} else {
    require_once('lib/wizard/pages/admin_language.php');
    $pages[] = new AdminWizardLanguage();

    require_once('lib/wizard/pages/admin_date_time.php');
    $pages[] = new AdminWizardDateTime();

    require_once('lib/wizard/pages/admin_login.php');
    $pages[] = new AdminWizardLogin();

    require_once('lib/wizard/pages/admin_look_and_feel.php');
    $pages[] = new AdminWizardLookAndFeel();

    require_once('lib/wizard/pages/admin_editor_type.php');
    $pages[] = new AdminWizardEditorType();

    require_once('lib/wizard/pages/admin_wysiwyg.php');
    $pages[] = new AdminWizardWysiwyg();

    require_once('lib/wizard/pages/admin_text_area.php');
    $pages[] = new AdminWizardTextArea();

    require_once('lib/wizard/pages/admin_wiki.php');
    $pages[] = new AdminWizardWiki();

    require_once('lib/wizard/pages/admin_auto_toc.php');
    $pages[] = new AdminWizardAutoTOC();

    require_once('lib/wizard/pages/admin_category.php');
    $pages[] = new AdminWizardCategory();

    require_once('lib/wizard/pages/admin_structures.php');
    $pages[] = new AdminWizardStructures();

    require_once('lib/wizard/pages/admin_files.php');
    $pages[] = new AdminWizardFiles();

    require_once('lib/wizard/pages/admin_files_storage.php');
    $pages[] = new AdminWizardFileStorage();

    require_once('lib/wizard/pages/admin_features.php');
    $pages[] = new AdminWizardFeatures();

    require_once('lib/wizard/pages/admin_search.php');
    $pages[] = new AdminWizardSearch();

    require_once('lib/wizard/pages/admin_community.php');
    $pages[] = new AdminWizardCommunity();

    require_once('lib/wizard/pages/admin_advanced.php');
    $pages[] = new AdminWizardAdvanced();

    require_once('lib/wizard/pages/admin_namespace.php');
    $pages[] = new AdminWizardNamespace();

    require_once('lib/wizard/pages/admin_wizard_completed.php');
    $pages[] = new AdminWizardCompleted();
}

/////////////////////////////////////
// END Wizard page section
/////////////////////////////////////


// Step the wizard pages
$wizardlib = TikiLib::lib('wizard');

// Show pages
$wizardlib->showPages($pages, true);

// Set the display flag
$showOnLogin = $wizardlib->get_preference('wizard_admin_hide_on_login') !== 'y';
$smarty->assign('showOnLogin', $showOnLogin);


// Build the TOC
$toc = '<div class="list-group list-group-flush wizard_toc">';
$stepNr = 0;
$reqStepNr = $wizardlib->wizard_stepNr;
$homepageUrl = $_REQUEST['url'];
foreach ($pages as $page) {
    global $base_url;
    $cssClasses = '';

    // Start the admin wizard
    $url = $base_url . 'tiki-wizard_admin.php?&amp;stepNr=' . $stepNr . '&amp;url=' . rawurlencode($homepageUrl);
    if ($useDefaultPrefs) {
        $url .= '&amp;use-default-prefs=1';
    }
    if ($useChangesWizard) {
        $url .= '&amp;use-changes-wizard=1';
    }
    $cnt = $stepNr + 1;
//  if ($stepNr == 1 && $useChangesWizard) {
//      $toc .= '<div class="list-group-item font-italic">' . tra("New in Tiki 12 (LTS)") . '</div>';
//  }
    if ($cnt <= 9) {
        $cnt = '&nbsp;&nbsp;' . $cnt;
    }
    $toc .= '<a ';
    $cssClasses .= 'list-group-item list-group-item-action ';
    if (preg_match('/ Tiki /', $page->pageTitle()) or $stepNr == 0) {
        $cssClasses .= 'font-italic ';
    }
    if ($stepNr == $reqStepNr) {
        $cssClasses .= 'active ';
    }
    if (! $page->isVisible()) {
        $cssClasses .= 'disabled disabledTOCSelection ';
    }
    $css = '';
    if (strlen($cssClasses) > 0) {
        $css = 'class="' . $cssClasses . '" ';
    }
    $toc .= $css;
    $toc .= 'href="' . $url . '">' . $page->pageTitle() . '</a>';
    $stepNr++;
}
$toc .= '</div>';

if ($reqStepNr > 0) {
    $smarty->assign('wizard_toc', $toc);
}


// disallow robots to index page:
$smarty->assign('metatag_robots', 'NOINDEX, NOFOLLOW');

$smarty->display('tiki-wizard_admin.tpl');
