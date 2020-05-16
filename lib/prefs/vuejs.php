<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function prefs_vuejs_list()
{
    return [
        'vuejs_enable' => [
            'name'        => tra('Enable Vue.js'),
            'description' => tra('Loads the vue.js library.'),
            'type'        => 'flag',
            'default'     => 'n',
            'tags'        => ['experimental'],
            'keywords' => 'vue js vuejs vue_js',
        ],
        'vuejs_build_mode' => [
            'name'        => tra('Vue.js Deployment Mode'),
            'description' => tra('Selects which vue.js library is used.'),
            'type'        => 'list',
            'options' => [
                'vue.min.js' => tra('Production full (minified)'),
                'vue.runtime.min.js' => tra('Production runtime (minified)'),
                'vue.js' => tra('Development full'),
                'vue.runtime.js' => tra('Development runtime only'),
            ],
            'default'     => 'vue.min.js',
            'tags'        => ['advanced'],
            'dependencies' => [
                'vuejs_enable',
            ],
            'keywords' => 'vue js vuejs vue_js',
        ],
        'vuejs_always_load' => [
            'name'        => tra('Always Load Vue.js'),
            'description' => tra('Loads the vue.js library for every page.'),
            'type'        => 'flag',
            'default'     => 'n',
            'tags'        => ['advanced'],
            'dependencies' => [