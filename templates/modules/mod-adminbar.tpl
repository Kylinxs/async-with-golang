
{* $Id$ *}
{tikimodule error=$module_params.error title=$tpl_module_title name="adminbar" flip=$module_params.flip decorations=$module_params.decorations nobox=$module_params.nobox notitle=$module_params.notitle}

{if $tiki_p_admin == "y"} {$main_admin_icons = [
"general" => [
'title' => tra('General'),
'description' => tra('Global site configuration, date formats, etc.'),
'help' => 'General-Admin'
],
"features" => [
'title' => tra('Features'),
'description' => tra('Switches for major features'),
'help' => 'Features-Admin'
],
"login" => [
'title' => tra('Log in'),
'description' => tra('User registration, remember me cookie settings and authentication methods'),
'help' => 'Login-Config'
],
"user" => [
'title' => tra('User Settings'),
'description' => tra('User related preferences like info and picture, features, messages and notification, files, etc'),
'help' => 'User-Settings'
],
"profiles" => [
'title' => tra('Profiles'),
'description' => tra('Repository configuration, browse and apply profiles'),
'help' => 'Profiles'
],
"look" => [
'title' => tra('Look & Feel'),
'description' => tra('Theme selection, layout settings and UI effect controls'),
'help' => 'Look-and-Feel'
],
"textarea" => [
'title' => tra('Editing & Plugins'),
'description' => tra('Text editing settings applicable to many areas. Plugin activation and plugin alias management'),
'help' => 'Text-area'
],
"module" => [
'title' => tra('Modules'),
'description' => tra('Module appearance settings'),
'help' => 'Module'
],
"performance" => [
'title' => tra('Performance'),
'description' => tra('Server performance settings'),
'help' => 'Performance'
],
"security" => [
'title' => tra('Security'),
'description' => tra('Site security settings'),
'help' => 'Security'
],
"print" => [
'title' => tra('Print Settings'),
'description' => tra('Settings and features for print versions and pdf generation'),
'help' => 'Print-Setting-Admin'
],
"packages" => [
'title' => tra('Packages'),
'description' => tra('External packages installation and management'),
'help' => 'Packages'
]
]}
    <a class="js-admin-bar link-admin-bar float-sm-end me-auto btn btn-link">{icon name='cog'}</a>
    <div class="sliding-panel-admin-bar js-sliding-panel-admin-bar card-header  invisible">
        <div class="card-header left"></div>
        <div class="container-fluid container-sliding-panel d-flex flex-column h-100 justify-content-center">

            <div class="row">
                <div class="col-md-4 align-self-center col-left-sliding-panel mb-2 mb-md-0 ps-md-0">
                    <div class="col-md-12 col-search mb-2 px-0">
                        <form method="post" action="tiki-admin.php" class="d-flex flex-row flex-wrap align-items-start w-100 justify-content-around my-2 my-md-0 ms-auto" role="form" target="_blank">
                            <div><label class="col-form-control me-md-3 text-start">Admin Features </label> </div>
                            <div class="d-flex">
                                <input type="text" name="lm_criteria" value="{$smarty.request.lm_criteria|escape}" class="form-control form-control-sm me-2 col-md-auto" placeholder="Search preferences...">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <span class="icon icon-search fas fa-search fa-fw "></span>
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-12 px-0">
                        <div class="row">
                            <div class="col-md-12  d-flex justify-content-around text-center">
                                <div id="adminbar" class="btn-group">
                                    <div class="btn-group">
                                        {if ! $js}
                                        <ul>
                                            <li>{/if}
                                                <a class="btn btn-link" data-bs-toggle="dropdown" data-hover="dropdown" href="#">
                                                    {icon name="history"} Recent Actions </a>
                                                <div class="dropdown-menu" role="menu">
                                                    {foreach $recent_prefs as $p}
                                                        <a class="dropdown-item" href="tiki-admin.php?lm_criteria={$p|escape}&amp;exact">{$p|stringfix}</a>
                                                        {foreachelse}
                                                        <div class="dropdown-item">{tr}None{/tr}</div>
                                                    {/foreach}
                                                </div>
                                                {if ! $js}</li>
                                        </ul>{/if}
                                    </div>
                                    <div class="btn-group">
                                        {if ! $js}
                                        <ul>
                                            <li>{/if}
                                                <a class="btn btn-link" data-bs-toggle="dropdown" data-hover="dropdown" href="#">
                                                    {icon name='menu-extra'} Quick Links </a>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="tiki-wizard_admin.php?stepNr=0&amp;url=index.php">
                                                        {icon name="wizard"} {tr}Wizards{/tr}
                                                    </a> <a class="dropdown-item" href="tiki-admin.php">
                                                        {icon name="cog"} {tr}Control panels{/tr}
                                                    </a> <a class="dropdown-item" href="tiki-admin.php?page=look">
                                                        {icon name="image"} {tr}Themes{/tr}
                                                    </a> <a class="dropdown-item" href="tiki-adminusers.php">
                                                        {icon name="user"} {tr}Users{/tr}
                                                    </a> <a class="dropdown-item" href="tiki-admingroups.php">
                                                        {icon name="group"} {tr}Groups{/tr}
                                                    </a>
                                            <li class="dropdown-item">
                                                {permission_link mode=text}
                                            </li>
                                            <a class="dropdown-item" href="tiki-admin_menus.php">
                                                {icon name="menu"} {tr}Menus{/tr}
                                            </a>
                                            {if $prefs.lang_use_db eq "y"}
                                                {if isset($smarty.session.interactive_translation_mode) && $smarty.session.interactive_translation_mode eq "on"}
                                                    <a class="dropdown-item" href="tiki-interactive_trans.php?interactive_translation_mode=off">
                                                        {icon name="translate"} {tr}Turn off interactive translation{/tr}
                                                    </a>
                                                {else}
                                                    <a class="dropdown-item" href="tiki-interactive_trans.php?interactive_translation_mode=on">
                                                        {icon name="translate"} {tr}Turn on interactive translation{/tr}
                                                    </a>
                                                {/if}
                                            {/if}
                                            {if $prefs.feature_comments_moderation eq "y"}
                                                <a class="dropdown-item" href="tiki-list_comments.php">
                                                    {icon name="comments"} {tr}Comment moderation{/tr}
                                                </a>
                                            {/if}
                                            <a class="dropdown-item" href="tiki-admin_system.php?do=all">
                                                {icon name="trash"} {tr}Clear all caches{/tr}
                                            </a>
                                            <a class="dropdown-item" href="{bootstrap_modal controller=search action=rebuild}">
                                                {icon name="index"} {tr}Rebuild search index{/tr}
                                            </a> <a class="dropdown-item" href="tiki-plugins.php">
                                                {icon name="plugin"} {tr}Plugin approval{/tr}
                                            </a> <a class="dropdown-item" href="tiki-syslog.php">
                                                {icon name="log"} {tr}Logs{/tr}
                                            </a> <a class="dropdown-item" href="tiki-admin_modules.php">
                                                {icon name="module"} {tr}Modules{/tr}
                                            </a>
                                            {if $prefs.feature_scheduler eq "y"}
                                                <a class="dropdown-item" href="tiki-admin_schedulers.php">
                                                    {icon name="calendar"} {tr}Scheduler{/tr}
                                                </a>
                                            {/if}
                                            {if $prefs.feature_sefurl_routes eq "y"}
                                                <a class="dropdown-item" href="tiki-admin_routes.php">
                                                    {icon name="random"} {tr}Custom Routes{/tr}
                                                </a>
                                            {/if}
                                            {if $prefs.feature_debug_console eq 'y'}
                                                <a class="dropdown-item" href="{query _type='relative' show_smarty_debug=1}">
                                                    {icon name="bug"} {tr}Smarty debug window{/tr}
                                                </a>
                                            {/if}
                                    </div>
                                    {if ! $js}</li></ul>{/if}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="offset-md-1 col-md-7 mb-2 mb-md-0 pe-md-0 d-flex align-items-center">
                {* Add prev arrow *}
                <div class="swiper-button-prev">

                </div>

                <div class="swiper-container js-admin-bar-slider admin-bar-slider">
                    <div class="swiper-wrapper">
                        {foreach from=$main_admin_icons key=page item=info}

                            {if !empty($info.disabled)}
                                {assign var=class value="admbox advanced btn btn-primary disabled"}
                            {else}
                                {assign var=class value="admbox basic btn btn-primary"}
                                <div class="swiper-slide">
                                    {* TODO: Buttons are forced to be squares, not fluid. Labels which exceed 2 lines will be cut. *}
                                    <a href="{if !empty($info.url)}{$info.url}{else}tiki-admin.php?page={$page}{/if}" alt="{$info.title} {$info.description}" class="d-flex flex-column justify-content-center align-items-center btn-primary  {if $info.disabled}disabled-clickable{/if}" title="{$info.title|escape}{if $info.disabled} ({tr}Disabled{/tr}){/if}|{$info.description}">
                                        {icon name="admin_$page"}
                                        <span class="title">{$info.title|escape}</span> </a>
                                </div>
                            {/if}


                        {/foreach}
                    </div>
                </div>
                {* Add next Arrow *}
                <div class="swiper-button-next">

                </div>

            </div>
        </div>
    </div>
    <div class="card-header right"></div>
    </div>


{literal}
    <link rel="stylesheet" href="vendor_bundled/vendor/nolimits4web/swiper/dist/css/swiper.css" type="text/css">
    <script type="text/javascript" src="vendor_bundled/vendor/nolimits4web/swiper/dist/js/swiper.min.js"></script>
    <style type="text/css">
        @media screen and (prefers-reduced-motion: reduce) {
            body {
                transition: none;
            }
        }

        body.tiki.open {
            padding-top: 0px !important;
        }

        body {

            transition: transform ease-in 0.15s;
        }

        body.tiki.open {
            -webkit-transform: translate(0, 7rem);
            -moz-transform: translate(0, 7rem);
            -ms-transform: translate(0, 7rem);
            -o-transform: translate(0, 7rem);
            transform: translate(0, 7rem);
            transition: transform ease-out 0.15s;
        }

        @media (max-width: 767px) {
            body.tiki.open {
                -webkit-transform: translate(0, 14rem);
                -moz-transform: translate(0, 14rem);
                -ms-transform: translate(0, 14rem);
                -o-transform: translate(0, 14rem);
                transform: translate(0, 14rem);
                transition: transform ease-out 0.15s;
            }
        }

        .sliding-panel-admin-bar {
            -webkit-transform: translate(0, -7rem);
            -moz-transform: translate(0, -7rem);
            -ms-transform: translate(0, -7rem);
            -o-transform: translate(0, -7rem);
            transform: translate(0, -7rem);
            left: 0;
            right: 0;
            position: fixed;
            top: 0;
            left: 0;
            margin: 0 auto;
            right: 0;
            height: 7rem;
        }

        @media (max-width: 767px) {
            .sliding-panel-admin-bar {
                -webkit-transform: translate(0, -14rem);
                -moz-transform: translate(0, -14rem);
                -ms-transform: translate(0, -14rem);
                -o-transform: translate(0, -14rem);
                transform: translate(0, -14rem);
            }
        }

        body {
            overflow-x: hidden;
        }

        .page-header {
            transition: padding ease-in-out 0.3s;
        }

        .page-header.has-admin-bar-sliding-panel {
            /*padding-top: 7rem;*/
            position: relative;
            z-index: 1;
        }

        .box-logo {
            order: 0;
        }

        .link-admin-bar {
            margin-left: auto;
            order: 0;
        }

        .link-admin-bar .icon-admin-bar {
            padding: 0.8rem 0.5rem 0.5rem;
            cursor: pointer;
            transition: all ease-in-out 0.3s;
            border-radius: 0 0 3px 3px;
        }

        .link-admin-bar .icon-admin-bar.card-header:first-child {
            border-radius: 0 0 3px 3px;
        }

        .top_modules .module:nth-child(2) {
            margin-left: 0;
            order: 2;
        }

        @media (max-width: 767px) {
            .sliding-panel-admin-bar {
                height: 14rem;
            }
        }

        .sliding-panel-admin-bar .card-header.left {
            display: block;
            position: absolute;
            left: -999em;
            top: 0;
            bottom: 0;
            right: 100%;
            border-bottom: 0;
            padding: 0;
        }

        .sliding-panel-admin-bar .card-header.left:first-child {
            border-radius: 0;
        }

        @media (max-width: 767px) {
            .sliding-panel-admin-bar .card-header.left {
                display: none;
            }
        }

        .sliding-panel-admin-bar .card-header.right {
            display: block;
            position: absolute;
            left: 100%;
            top: 0;
            bottom: 0;
            right: -999em;
            border-bottom: 0;
            padding: 0;
        }

        .sliding-panel-admin-bar .card-header.right:first-child {
            border-radius: 0;
        }

        @media (max-width: 767px) {
            .sliding-panel-admin-bar .card-header.right {
                display: none;
            }
        }

        .sliding-panel-admin-bar.card-header {
            padding: 0;
            border-bottom: 0;
        }

        .sliding-panel-admin-bar .container-sliding-panel {
            position: relative;
            z-index: 1;
            max-width: 1140px;
        }
        //adding safe colors for nav bar dark
          .navbar-dark #adminbar a {
            color:#222 !important;
        }

        .navbar-dark .sliding-panel-admin-bar .container-sliding-panel .btn-primary:hover{
            background-color:#333;
            border-color:#000;
        }
        .sliding-panel-admin-bar .box-adminbar {
            position: relative;
            z-index: 1;
        }

        .sliding-panel-admin-bar .col-search {
            position: relative;
            z-index: 1;
        }

        @media (min-width: 768px) {
            .sliding-panel-admin-bar .btn-group > .dropdown-menu,
            .sliding-panel-admin-bar .btn-group > .dropdown-menu.show,
            .sliding-panel-admin-bar .btn-group.show > .dropdown-menu,
            .sliding-panel-admin-bar .btn-group.show > .dropdown-menu.show {
                display: none;
                margin-top: 0;
            }
        }

        @media (min-width: 768px) {
            .sliding-panel-admin-bar .btn-group:hover > .dropdown-menu,
            .sliding-panel-admin-bar .btn-group:hover > .dropdown-menu.show,
            .sliding-panel-admin-bar .btn-group.show:hover > .dropdown-menu,
            .sliding-panel-admin-bar .btn-group.show:hover > .dropdown-menu.show {
                display: block;
            }
        }

        @media (max-width: 767px) {
            .sliding-panel-admin-bar .col-left-sliding-panel {
                position: relative;
                z-index: 11;
            }
        }

        .swiper-container.admin-bar-slider {
            width: 90%;
        }

        @media (max-width: 767px) {
            .swiper-container.admin-bar-slider {
                width: 80%;
            }
        }

        .swiper-container.admin-bar-slider a {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 3px;
            text-align: center;
            min-height: 5.5rem;
            line-height: 1.2;
        }

        .swiper-container.admin-bar-slider a:hover {
            text-decoration: none;
        }

        /*Override the swipper.css definitions*/

        .swiper-button-prev, .swiper-button-next {
            position : relative !important;
            width : 17px !important;
            top :inherit !important;
        }

        .swiper-button-prev, .swiper-container-rtl .swiper-button-next {
            background-image: url(img/arrow-inverse-left.svg) !important;
            z-index:10000;
            margin-top:0.1rem;
        }

        .swiper-button-next, .swiper-container-rtl .swiper-button-prev {
            background-image: url(img/arrow-inverse-right.svg) !important;
            z-index:10000;
            margin-top:0.1rem;
        }

        @media (min-width: 768px) {
            .swiper-button-next, .swiper-container-rtl .swiper-button-prev {
                right: 0 !important;
            }
        }

        #swiper-container1 {
            z-index: 4;
        }

        @media (min-width:768px){
            .swiper-wrapper{
                margin-left:1em !important;
            }
        }
    </style>
{/literal}
{/if}
{/tikimodule}