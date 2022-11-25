<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * Initialize sieve client (per imap mailbox) or generic Tiki-stored rules
 */
class Tiki_Hm_Sieve_Client_Factory
{
    public function init($user_config = null, $imap_account = null)
    {
        if ($imap_account && ! empty($imap_account['sieve_config_host'])) {
            $sieve_options = explode(':', $imap_account['sieve_config_host']);
            $client = new PhpSieveManager\ManageSieve\Client($sieve_options[0], $sieve_options[1]);
            $client->connect($imap_account['user'], $imap_account['pass'], false, "", "PLAIN");
        } else {
            $client = new Tiki_Hm_Sieve_Custom_Client($user_config, $imap_account['name'] ?? '');
        }
        return $client;
    }
}
