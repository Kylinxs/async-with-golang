
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function prefs_openidconnect_list()
{
    return [
        'openidconnect_name' => [
            'name' => tr('Provider name'),
            'type' => 'text',
            'default' => '',
        ],
        'openidconnect_issuer' => [
            'name' => tr('Issuer URL'),
            'type' => 'text',
            'default' => '',
        ],
        'openidconnect_auth_url' => [
            'name' => tr('Provider URL authorization'),
            'description' => tr('Authorization URL from the OpenId provider.'),
            'type' => 'text',
            'default' => '',
        ],
        'openidconnect_access_token_url' => [
            'name' => tr('Provider URL user access token url'),
            'description' => tr('URL from the OpenId provider to fetch the access_token'),
            'type' => 'text',
            'default' => '',
        ],
        'openidconnect_details_url' => [
            'name' => tr('Provider URL resource owner details'),
            'description' => tr('URL from the OpenId provider that provides information on the granted user.'),
            'type' => 'text',
            'default' => '',
        ],
        'openidconnect_client_id' => [
            'name' => tr('Client ID'),
            'description' => tr('OAuth 2.0 Client Identifier valid at the Authorization Server'),
            'type' => 'text',
            'default' => '',
        ],
        'openidconnect_client_secret' => [
            'name' => tr('Client Secret'),
            'description' => tr('OAuth 2.0 Client Secret valid at the Authorization Server'),
            'type' => 'text',
            'default' => '',
        ],
        'openidconnect_verify_method' => [
            'name' => tra('Verification method'),
            'type' => 'list',
            'options' => [
                'jwks' => tra('JWKS'),
                'cert' => tra('Certificate')
            ],
            'default' => 'jwks',
        ],
        'openidconnect_create_user_tiki' => [
            'name' => tra('Create user if not registered in Tiki'),
            'type' => 'flag',
            'default' => 'n',
        ],
        'openidconnect_jwks_url' => [
            'name' => tr('JWKS URL'),
            'description' => tr('Read-only endpoint that contains the public keys information in the JWKS format'),
            'type' => 'text',
            'default' => '',
        ],
        'openidconnect_cert' => [
            'name' => tr('Public certificate'),
            'type' => 'textarea',
            'default' => '',
        ],
    ];
}