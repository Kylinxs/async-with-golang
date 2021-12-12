<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * @return array
 */
function module_domain_password_info()
{
    return [
        'name' => tra('Domain Password'),
        'description' => tra('Store personal passwords for other domains securely in Tiki'),
        'prefs' => ['feature_user_encryption'],
        'params' => [
            'domain' => [
                'name' => tra('Domain'),
                'description' => tra('System the credentials apply for. The name must match a defined Password Domain'),
            ],
            'use_currentuser' => [
                'name' => tra('Use current user'),
                'description' => tra('Use the currently logged-in user. The username is not editable. (y/n) Default: y'),
            ],
            'can_update' => [
                'name' => tra('Can Update'),
                'description' => tra('If "y" the user can update the values, otherwise the display is read-only (y/n). Default: n'),
            ],
            'show_domain_prompt' => [
                'name' => tra('Show domain prompt'),
                'description' => tra('If "y" the word "domain" is shown before the domain. Otherwise the domain name takes the full row (y/n). Default: y'),
            ],
        ],
        'common_params' => ['nonums', 'rows']
    ];
}

/**
 * @param $mod_reference
 * @param $module_params
 */
function module_domain_password($mod_reference, $module_params)
{
    global $prefs, $user;
    $smarty = TikiLib::lib('smarty');
    $tikilib = TikiLib::lib('tiki');

    // Allow for multiple modules on one page
    $moduleNr = $mod_reference['moduleId'];
    $moduleNr = str_replace('wikiplugin_', '', $moduleNr); // Remove the leading wikiplugin_ when used in a wiki page
    $cntModule = (int)$moduleNr;
    $dompwdCount = 0;
    if (isset($_REQUEST['dompwdCount'])) {
        $dompwdCount = (int)$_REQUEST['dompwdCount'];
    }
    $smarty->assign('dompwdCount', $cntModule);


    // Use a static array of smarty variables, to support multiple modules on a single page
    static $errors = [];
    $errors[$cntModule] = [];

    static $can_update = [];
    static $edit_option = [];
    static $use_currentuser = [];
    static $username = [];
    static $domainDisplayPrompt = [];

    $hasDomain = false;

    // Determine domain
    $domain = '';
    if (! empty($module_params['domain'])) {
        $domain = $module_params['domain'];
        $smarty->assign('domain', $domain);
    }

    // Domain display option
    $domainDisplayPrompt[$cntModule] = 'y';
    if (! empty($module_params['show_domain_prompt'])) {
        $domainDisplayPrompt[$cntModule] = $module_params['show_domain_prompt'];
    }
    $smarty->assign('domainDisplayPrompt', $domainDisplayPrompt);


    if (empty($user)) {
        $errors[$cntModule][] = tra('You are not logged in');
    } else {
        try {
            $cryptlib = TikiLib::lib('crypt');
            $cryptlib->init();

            // Determine domain
            if (! empty($domain)) {
                // Validate the domain
                $allDomains = $cryptlib->getPasswordDomains();
                if (! $allDomains) {
                    $errors[$cntModule][] = tra('No Password Domains found');
                } elseif (! in_array($domain, $allDomains)) {
                    $errors[$cntModule][] = tra('Domain is not valid');
                } else {
                    $hasDomain = true;
                }
            } else {
                $errors[$cntModule][] = tra('No domain specified');
            }

            // Determine if writable
            $can_update[$cntModule] = 'n';
            if (! empty($module_params['can_update'])) {
                $can_update[$cntModule] = $module_params['can_update'];
            }

            $isSaving = isset($_REQUEST['saveButton' . $cntModule]) ? true : false;

            // Determine user
            $use_currentuser[$cntModule] = 'y';
            if (! empty($module_params['use_currentuser'])) {
                $use_currentuser[$cntModule] = $module_params['use_currentuser'];
            }
            if ($use_currentuser[$cntModule] == 'y') {
                $username[$cntModule] = $user;
                $smarty->assign('currentuser', $use_currentuser);
                $smarty->assign('username', $username);
            } else {
                $smarty->assign('currentuser', $use_currentuser);
                $username[$cntModule] = $cryptlib->getUser