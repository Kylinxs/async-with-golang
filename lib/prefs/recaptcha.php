<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function prefs_recaptcha_list()
{
    return  [
        'recaptcha_enabled' => [
            'name' => tra('Use reCAPTCHA'),
            'description' => tra('Use reCAPTCHA, a specialized captcha service, instead of default CAPTCHA'),
            'hint' => tra('You will need to register at [http://www.google.com/recaptcha]'),
            'help' => 'Spam-protection',
            'type' => 'flag',
            'default' => 'n',
        ],
        'recaptcha_pubkey' => [
            'name' => tra('Site key'),
            'type' => 'text',
            'description' => tra('reCAPTCHA public key obtained after registering.'),
            'size' => 60,
            'default' => '',
        ],
        'recaptcha_privkey' => [
            'name' => tra('Secret key'),
            'type' => 'text',
            'description' => tra('reCAPTCHA private key obtained after registering.'),
            'size' => 60,
            'default' => '',
        ],
        'recaptcha_theme' => [
            'name' => tra('reCAPTCHA theme'),
            'type' => 'list',
            'description' => tra('Choose a theme for the reCAPTCHA widget.'),
            'options' => [
                'clean' => tra('Clean'),
                'blackglass' => tra('Black Glass'),
                'red' => tra('Red'),
                'white' => tra('White'),
            ],
            'default' => 'clean',
        ],
        'recaptcha_version' => [
            'name' => tra('Version'),
            'type' => 'list',
            'description' => tra('reCAPTCHA version.'),
            'options' => [
                '1' => tra('1.0'),
                '2' => tra('2.0'),
                '3' => tra('3.0'),
            ],
            'default' => '2',
        ],
    ];
}
