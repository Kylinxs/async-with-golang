
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function prefs_oauthserver_list()
{
    return [
        'oauthserver_encryption_key' => [
            'name' => tra('Encryption key for OAuthServer'),
            'description' => tra('A key used to encrypt/decrypt authorization and refresh codes. This will be automatically generated if you leave it empty.'),
            'type' => 'text',
            'default' => '',
        ],
        'oauthserver_public_key' => [
            'name' => tra('Public key for OAuthServer'),
            'description' => tra('The public/private key pair is used to sign and verify JWTs transmitted. If you have openssl installed, this will be automatically generated when needed.'),
            'type' => 'textarea',
            'default' => '',
        ],
        'oauthserver_private_key' => [
            'name' => tra('Private key for OAuthServer'),
            'description' => tra('The public/private key pair is used to sign and verify JWTs transmitted. If you have openssl installed, this will be automatically generated when needed.'),
            'type' => 'textarea',
            'default' => '',
        ],
    ];
}