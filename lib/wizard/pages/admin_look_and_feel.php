<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

require_once('lib/wizard/wizard.php');

/**
 * The Wizard's editor type selector handler
 */
class AdminWizardLookAndFeel extends Wizard
{
    public function pageTitle()
    {
        return tra('Set up Look & Feel');
    }
    public function isEditable()
    {
        return true;
    }

    public function onSetupPage($homepageUrl)
    {
        global $prefs;
        $smarty = TikiLib::lib('smarty');
        $tikilib = TikiLib::lib('tiki');
        $themelib = TikiLib::lib('theme');
        $csslib = TikiLib::lib('css');
        $headerlib = TikiLib::lib('header');
        // Run the parent first
        parent::onSetupPage($homepageUrl);

//handle case when changing the themes in the Look and Feel settings panel
        $a_theme = $prefs['theme'];
        if (isset($_REQUEST['looksetup'])) {
            ask_ticket('admin-inc-look');
            if (isset($_REQUEST['theme'])) {
                check_ticket('admin-inc-general');

                if (! isset($_REQUEST['theme_option']) || $_REQUEST['theme_option'] = '') {
                    // theme has no options
                    $_REQUEST['theme_option'] = '';
                }
                check_ticket('admin-inc-general');
            }
        } else {
            // just changed theme menu, so refill options
            if (isset($_REQUEST['theme']) && $_REQUEST['theme'] != '') {
                $a_theme = $_REQUEST['theme'];
            }
        }

        $themes = $themelib->list_themes();
        $smarty->assign_by_ref('themes', $themes);
        $theme_options = $themelib->list_theme_options($a_theme);
        $smarty->assign('theme_options', $theme_options);

        $theme_layouts = TikiLib::lib('css')->list_layouts();
        $smarty->assign('theme_layouts', $theme_layouts);

// get thumbnail if there is one
        $thumbfile = $themelib->get_thumbnail_file($prefs['site_theme'], $prefs['site_theme_option']);
        if (empty($thumbfile)) {
            $thumbfile = $themelib->get_thumbnail_file($prefs['site_theme']);
        }
        if (empty($thumbfile)) {
            $thumbfile = 'img/trans.png';
        }
        $smarty->assign('thumbfile', $thumbfile);

// hash of themes and their options and their thumbnail images
        if ($prefs['feature_jquery'] == 'y') {
            $js = 'var theme_options = {';
            foreach ($themes as $theme => $value) {
                $js .= "\n'$theme':['" . $themelib->get_thumbnail_file($theme, '') . '\',{';
                $options = $themelib->list_theme_options($theme);
                if ($options) {
                    foreach ($options as $option) {
                        $js .= "'$option':'" . $themelib->get_thumbnail_file($theme, $option) . '\',';
                    }
                    $js = substr($js, 0, strlen($js) - 1) . '}';
                } else {
                    $js .= '}';
                }
                $js .= '],';
            }
            $js = substr($js, 0, strlen($js) - 1);
            $js .= '};';

            $js .= 'var theme_layouts = ';
            foreach ($themes as $theme => $value) {
                $theme_layouts[$theme] = $csslib->list_user_selectable_layouts($theme);
                $options = $themelib->list_theme_options($theme);
                if ($optio