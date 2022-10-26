
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * @return array
 */
function module_adminbar_info()
{
    return [
        'name' => tra('Quick Admin Bar'),
        'description' => tra('Consolidated admin bar with an easy access to quick admin links, recent changes, and important admin features'),
        'prefs' => [],
        'params' => [
            'mode' => [
                'name' => tra('Mode'),
                'description' => tra('Display mode: module or header. Leave empty for module mode'),
            ],
        ]
    ];
}

/**
 * @param $mod_reference
 * @param $module_params
 */
function module_adminbar($mod_reference, $module_params)
{
    global $tiki_p_admin;
    if ($tiki_p_admin != 'y') {
        return;
    }
    $headerlib = TikiLib::lib("header");
    $headerlib->add_css('div.contributors div br {clear: both;}');


    $headerlib->add_js('$(document).ready(function() {
            $(function() {
                $(".js-admin-bar").click(function() {
                    $(\'.js-sliding-panel-admin-bar\').toggleClass("open");
                    $(\'.js-sliding-panel-admin-bar\').toggleClass("invisible");
                    $(\'header.page-header\').toggleClass("has-admin-bar-sliding-panel");
                    $(\'.icon-admin-bar\').toggleClass("open");
                    $(\'body.tiki\').toggleClass("open");
                });
            });
            $(function() {
                $(".navbar-toggler").click(function() {
                    $(\'.navbar-toggler\').toggleClass("open");
                    $(\'.navbar-toggler\').toggleClass("invisible");
                });
            });

            var swiper = new Swiper(\'.js-admin-bar-slider\', {
                slidesPerView: 6,
                spaceBetween: 15,
                freeMode: true,
                pagination: {
                    el: \'.swiper-pagination\',
                    clickable: true,
                },
                navigation: {
                    nextEl: \'.swiper-button-next\',
                    prevEl: \'.swiper-button-prev\',
                },
                autoplay: false,
                breakpoints: {
                    1024: {
                        slidesPerView: 6,
                        spaceBetween: 15,
                    },
                    768: {
                        slidesPerView: 4,
                        spaceBetween: 15,
                    },
                    640: {
                        slidesPerView: 2,
                        spaceBetween: 15,
                    },
                    320: {
                        slidesPerView: 2,
                        spaceBetween: 15,
                    }
                }
            });
        });
');
    TikiLib::lib('smarty')->assign('recent_prefs', TikiLib::lib('prefs')->getRecent());
}