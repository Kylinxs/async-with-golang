
{* $Id$ *}
<form action="tiki-admin.php?page=general" class="admin" method="post">
    {ticket}
    <div class="t_navbar mb-4 clearfix">
        {button _class="btn btn-link tips" _type="text" href="tiki-install.php" _icon_name="database" _text="{tr}Tiki Installer{/tr}" _title="{tr}Reset or upgrade your database{/tr}"}
        {button _class="btn btn-link tips" _type="text" href="tiki-admin_menus.php" _icon_name="menu" _text="{tr}Menus{/tr}" _title="{tr}Create and edit menus{/tr}"}
        {button _class="btn btn-link tips" _type="text" href="tiki-check.php" _icon_name="heartbeat" _text="{tr}Server Fitness{/tr}" _title="{tr}Check if your server meets the requirements for running Tiki{/tr}"}
        {button _class="btn-sm tips" _type="secondary" href="tiki-admin.php?page=general&amp;forcecheck=1" _icon_name="search" _text="{tr}Check for Updates Now{/tr}" _title="|{tr}Check if a new Tiki version is available{/tr}"}
        {include file='admin/include_apply_top.tpl'}
    </div>
    {tabset name="admin_general"}
        {tab name="{tr}General Preferences{/tr}"}
            <br>
            <fieldset>
                <legend>{tr}Release check{/tr}</legend>
                {include file='admin/version_check.tpl'}
            </fieldset>
            <fieldset>
                <legend>{tr}Site identity{/tr}</legend>
                {preference name=browsertitle}
                {preference name=fallbackBaseUrl}
                {preference name=site_title_location}
                {preference name=site_title_breadcrumb}
                {remarksbox type="info" title="{tr}Themes{/tr}"}
                    {tr _0='<a href="tiki-admin.php?page=look" class="alert-link">' _1='</a>'}Go to the %0Look & Feel%1 section for additional site customization preferences{/tr}.
                {/remarksbox}
            </fieldset>
            <fieldset>
                <legend>{tr}Mail{/tr}</legend>
                {preference name=sender_email}
                {preference name=sender_name}
                {preference name=default_mail_charset}
                {preference name=mail_crlf}
                {preference name=mail_apply_css}
                {preference name=zend_mail_handler}
                <div class="adminoptionboxchild zend_mail_handler_childcontainer amazonSes">
                    {preference name=zend_mail_amazon_ses_key}
                    {preference name=zend_mail_amazon_ses_secret}
                    {preference name=zend_mail_amazon_ses_region}
                    {preference name=zend_mail_amazon_ses_version}
                </div>
                <div class="adminoptionboxchild zend_mail_handler_childcontainer elasticEmail">
                    {preference name=zend_mail_elastic_email_username}
                    {preference name=zend_mail_elastic_email_key}
                </div>
                <div class="adminoptionboxchild zend_mail_handler_childcontainer mailgun">
                    {preference name=zend_mail_mailgun_domain}
                    {preference name=zend_mail_mailgun_key}
                    {preference name=zend_mail_mailgun_api_endpoint}
                </div>
                <div class="adminoptionboxchild zend_mail_handler_childcontainer mandrill">
                    {preference name=zend_mail_mandrill_key}
                </div>
                <div class="adminoptionboxchild zend_mail_handler_childcontainer postage">
                    {preference name=zend_mail_postage_key}
                </div>
                <div class="adminoptionboxchild zend_mail_handler_childcontainer postmark">
                    {preference name=zend_mail_postmark_key}
                </div>
                <div class="adminoptionboxchild zend_mail_handler_childcontainer sendGrid">
                    {preference name=zend_mail_send_grid_username}
                    {preference name=zend_mail_send_grid_key}
                </div>
                <div class="adminoptionboxchild zend_mail_handler_childcontainer sparkPost">
                    {preference name=zend_mail_spark_post_key}
                </div>
                <div class="adminoptionboxchild zend_mail_handler_childcontainer smtp">
                    {preference name=zend_mail_smtp_server}
                    {preference name=zend_mail_smtp_auth}
                    <div class="adminoptionboxchild zend_mail_smtp_auth_childcontainer login plain crammd5">
                        <p>{tr}These values will be stored in plain text in the database:{/tr}</p>
                        {preference name=zend_mail_smtp_user}
                        {preference name=zend_mail_smtp_pass}
                    </div>
                    {preference name=zend_mail_smtp_port}
                    {preference name=zend_mail_smtp_security}
                    {preference name=zend_http_sslverifypeer}
                    {preference name=zend_mail_smtp_helo}
                    {preference name=zend_mail_queue}
                    {preference name=zend_mail_redirect}
                </div>
                <div class="adminoptionbox mb-3 row clearfix">
                    <label for="testMail" class="col-md-4 col-form-label">{tr}Email to send a test mail{/tr}</label>
                    <div class="col-md-8">
                        <input type="text" name="testMail" id="testMail" class="form-control">
                    </div>
                </div>
                {preference name=email_footer}
                {preference name=mail_template_custom_text}
            </fieldset>
            <fieldset>
                <legend>{tr}Newsletter{/tr}</legend>
                {preference name=newsletter_throttle}
                <div class="adminoptionboxchild" id="newsletter_throttle_childcontainer">
                    {preference name=newsletter_pause_length}
                    {preference name=newsletter_batch_size}
                </div>
                {preference name=newsletter_external_client}
            </fieldset>
            <fieldset>
                <legend>{tr}Logging and reporting{/tr}</legend>
                <div class="adminoptionbox">
                    {preference name=error_reporting_level}
                    <div class="adminoptionboxchild">
                        {preference name=error_reporting_adminonly label="{tr}Visible to admin only{/tr}"}
                        {preference name=smarty_notice_reporting label="{tr}Include Smarty notices{/tr}"}
                    </div>
                </div>
                {preference name=error_generic_non_admins}
                <div class="adminoptionboxchild" id="error_generic_non_admins_childcontainer">
                    {preference name=error_generic_message}
                </div>
                {preference name=disableJavascript}
                {preference name=log_mail}
                {preference name=log_sql}
                <div class="adminoptionboxchild" id="log_sql_childcontainer">
                    {preference name=log_sql_perf_min}
                </div>
            </fieldset>
            <fieldset>
                <legend>{tr}Error Tracking{/tr}</legend>
                <div class="adminoptionbox">
                    {preference name=error_tracking_dsn}
                    {preference name=error_tracking_sample_rate}
                    <div class="adminoptionboxchild">
                        {preference name=error_tracking_enabled_php}
                        {preference name=error_tracking_enabled_js}
                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend>{tr}Web Cron{/tr}</legend>
                <div class="adminoptionbox">
                    {preference name=webcron_enabled}
                    <div class="adminoptionboxchild" id="webcron_enabled_childcontainer">
                        {preference name=webcron_type}
                        {preference name=webcron_run_interval}
                        {preference name=webcron_token}
                        {if $prefs.webcron_type != 'js'}
                            {remarksbox type="note" title="{tr}Call Web Cron URL{/tr}"}
                            {$base_url}cron.php?token={$prefs.webcron_token|escape:url}
                            {/remarksbox}
                        {/if}
                    </div>
                </div>
            </fieldset>
            <fieldset id="Scheduler">
                <legend>{tr}Scheduler Settings{/tr}</legend>
                {preference name=feature_scheduler}
                {preference name=scheduler_delay}
                {preference name=scheduler_stalled_timeout}
                {preference name=scheduler_notify_on_stalled}
                <div class="adminoptionboxchild" id="scheduler_notify_on_stalled_childcontainer">
                    {preference name=scheduler_users_to_notify_on_stalled}
                </div>
                {preference name=scheduler_healing_timeout}
                {preference name=scheduler_notify_on_healing}
                <div class="adminoptionboxchild" id="scheduler_notify_on_healing_childcontainer">
                    {preference name=scheduler_users_to_notify_on_healed}
                </div>
                {preference name=scheduler_keep_logs}
            </fieldset>
        {/tab}
        {tab name="{tr}General Settings{/tr}"}
            <br>
            <fieldset>
                <legend>{tr}Server{/tr}</legend>
                {preference name=tmpDir}
                {preference name=use_proxy}
                <div class="adminoptionboxchild" id="use_proxy_childcontainer">
                    {preference name=proxy_host}
                    {preference name=proxy_port}
                    {preference name=proxy_user}
                    {preference name=proxy_pass}
                </div>
                {preference name=http_skip_frameset}
                {preference name=feature_loadbalancer}
                {preference name=feature_port_rewriting}
                {preference name=access_control_allow_origin}
                {preference name=zend_http_use_curl}
                {preference name=feature_realtime}
                <div class="adminoptionboxchild" id="feature_realtime_childcontainer">
                    {preference name=realtime_port}
                </div>
            </fieldset>
            <fieldset>
                <legend>{tr}Multi-domain{/tr}</legend>
                {preference name=multidomain_active}
                {preference name=multidomain_switchdomain}
                <div class="adminoptionboxchild" id="multidomain_switchdomain_childcontainer">
                    {preference name=multidomain_default_not_categorized}
                </div>
                <div class="adminoptionboxchild" id="multidomain_active_childcontainer">
                    {preference name=multidomain_config}
                </div>
            </fieldset>
            <fieldset>
                <legend>{tr}Sessions{/tr}</legend>
                {remarksbox type="note" title="{tr}Advanced configuration{/tr}"}
                    {tr}Note that storing session data in the database is an advanced systems administration option, and is for admins who have comprehensive access and understanding of the database, in order to deal with any unexpected effects.{/tr}
                {/remarksbox}
                {remarksbox type="warning" title="{tr}Warning{/tr}" close="n"}
                    {tr}Changing this feature will immediately log you out when you save this preference.{/tr} {if $prefs.forgotPass ne 'y'}If there is a chance you have forgotten your password, enable "Forget password" feature.<a href="tiki-admin.php?page=features" title="{tr}Features{/tr}" class="alert-link">{tr}Enable now{/tr}</a>.{/if}
                {/remarksbox}
                {preference name=session_storage}
                {preference name=session_lifetime}
                {preference name=session_cookie_name}
            </fieldset>
            <fieldset>
                <legend>{tr}Site terminal{/tr}</legend>
                {preference name=site_terminal_active}
                <div class="adminoptionboxchild" id="site_terminal_active_childcontainer">
                    {preference name=site_terminal_config}
                </div>
            </fieldset>
            <fieldset>
                <legend>{tr}Contact{/tr}</legend>
                {preference name=feature_contact}
                <div class="adminoptionboxchild" id="feature_contact_childcontainer">
                    {preference name=contact_anon}
                    {preference name=contact_priority_onoff}
                    {preference name=contact_user}
                </div>
            </fieldset>
            <fieldset>
                <legend>{tr}Stats{/tr}</legend>
                {preference name=feature_stats}
                {preference name=feature_referer_stats}
                {preference name=count_admin_pvs}
            </fieldset>
            <fieldset>
                <legend>{tr}Terms and conditions{/tr}</legend>
                {preference name=conditions_enabled}
                <div class="adminoptionboxchild" id="conditions_enabled_childcontainer">
                    {preference name=conditions_page_name}
                    {preference name=conditions_minimum_age}
                </div>
            </fieldset>
            <fieldset>
                <legend>{tr}Help{/tr}</legend>
                {preference name=feature_help}
                <div class="adminoptionboxchild" id="feature_help_childcontainer">
                    {preference name=helpurl}
                </div>
            </fieldset>
        {/tab}
        {tab name="{tr}Navigation{/tr}"}
            <br>
            <fieldset id="Menus">
                <legend>{tr}Menus{/tr}</legend>
                <div class="adminoptionbox">
                    {preference name=jquery_smartmenus_enable}
                    {preference name=feature_userlevels}
                    {preference name=feature_featuredLinks}
                    {preference name=feature_menusfolderstyle}
                    {preference name=menus_items_icons}
                    <div id="menus_items_icons_childcontainer">
                        {preference name='menus_items_icons_path'}
                    </div>
                    {preference name=menus_edit_icon}
                </div>
            </fieldset>
            <fieldset>
                <legend>{tr}Home page{/tr}</legend>
                <div class="adminoptionbox">
                    {preference name=useGroupHome}
                    <div id="useGroupHome_childcontainer">
                        {preference name=limitedGoGroupHome}
                    </div>
                </div>
                {preference name=tikiIndex defaul=$prefs.site_tikiIndex}
                {preference name=useUrlIndex}
                <div class="adminoptionboxchild" id="useUrlIndex_childcontainer">
                    {preference name=urlIndex}
                    {preference name=urlIndexBrowserTitle}
                </div>
                {preference name=wikiHomePage}
                {preference name=home_blog}
                {preference name=home_forum}
                {preference name=home_file_gallery}
                {preference name=home_gallery}
            </fieldset>
            <fieldset>
                <legend>{tr}Redirects{/tr}</legend>
                {preference name=tiki_domain_prefix}
                {preference name=tiki_domain_redirects}
                {preference name=feature_redirect_on_error}
                {preference name='feature_wiki_1like_redirection'}
                <hr>
                {preference name='permission_denied_login_box' mode='invert'}{* Is also in the login panel *}
                <div class="adminoptionboxchild" id="permission_denied_login_box_childcontainer">
                    <div style="text-indent: 28%">
                        <strong>{tr}or{/tr}</strong>
                    </div>
                    {preference name=permission_denied_url}
                </div>
                <hr>
                {preference name='url_anonymous_page_not_found'}
                {preference name='url_after_validation'}
                {preference name='feature_alternate_registration_page'}
                {preference name='feature_url_suffix_cleaner'}
            </fieldset>
            <fieldset>
                <legend>{tr}User{/tr}</legend>
                {preference name='urlOnUsername'}
            </fieldset>

            <fieldset>
                <legend>{tr}Sitemap protocol{/tr}</legend>
                {preference name=sitemap_enable}
            </fieldset>

            <fieldset>
                <legend>{tr}Site access{/tr}</legend>
                {preference name=site_closed}
                <div class="adminoptionboxchild" id="site_closed_childcontainer">
                    {preference name=site_closed_title}
                    {preference name=site_closed_msg}
                    <div class="col-sm-8 offset-sm-4">
                        {button _text='{tr}Test site closed message{/tr}' href="tiki-error_simple.php?title={$prefs.site_closed_title}&error="|cat:$prefs.site_closed_msg _class='btn-sm' _type='info'}
                    </div>
                </div>
                {preference name=use_load_threshold}
                <div class="adminoptionboxchild" id="use_load_threshold_childcontainer">
                    {preference name=load_threshold}
                    {preference name=site_busy_title}
                    {preference name=site_busy_msg}
                </div>
            </fieldset>
            <fieldset>
                <legend class="heading">{tr}Breadcrumbs{/tr}</legend>
                {preference name=feature_breadcrumbs}
                <div class="adminoptionboxchild" id="feature_breadcrumbs_childcontainer">
                    {preference name=feature_siteloclabel}
                    {preference name=feature_siteloc}
                    {preference name=feature_sitetitle}
                    {preference name=feature_sitedesc}
                </div>
            </fieldset>
            <fieldset>
                <legend class="heading">{tr}Namespace{/tr}</legend>
                {preference name=namespace_enabled}
                <div class="adminoptionboxchild" id="namespace_enabled_childcontainer">
                    {preference name=namespace_separator}
                    {remarksbox type="warning" title="{tr}Warning{/tr}" close="n"}
                        {tr}The namespace separator should not{/tr}
                        <ul>
                            <li>{tr}contain any of the characters not allowed in wiki page names, typically{/tr} /?#[]@$&amp;+;=&lt;&gt;</li>
                            <li>{tr}conflict with wiki syntax tagging{/tr}</li>
                        </ul>
                    {/remarksbox}
                    {preference name=namespace_indicator_in_structure}
                    {preference name=feature_use_three_colon_centertag}
                    {preference name=wiki_pagename_strip}
                    {remarksbox type="note" title="{tr}Information{/tr}"}
                        {tr}To use :: as a separator, you should also use ::: as the wiki center tag syntax{/tr}.<br/>
                        {tr}Note: a conversion of :: to ::: for existing pages must be done manually{/tr}.<br/>
                        {tr}If the page name display stripper conflicts with the namespace separator, the namespace is used and the page name display is not stripped.{/tr}
                    {/remarksbox}
                </div>
            </fieldset>
        {/tab}
        {tab name="{tr}Date and Time{/tr}"}
            {remarksbox type="info" title="doc.tiki.org"}
                <a class="alert-link" target="_blank" href="https://doc.tiki.org/Date-and-Time-Features">
                    {tr}Date and Time Format Help{/tr}
                </a>
            {/remarksbox}
            <fieldset>
                <legend>{tr}Time zone and format{/tr}{help url="Date+and+Time"}</legend>
                {preference name=server_timezone}
                {preference name=users_prefs_display_timezone}
                <div class="clearfix">
                    <span class="form-text col-sm-8 offset-sm-4 mt-4">
                        {tr}Sample:{/tr} {$now|tiki_long_date}
                    </span>
                    {preference name=long_date_format}
                </div>
                <div class="clearfix">
                    <span class="form-text col-sm-8 offset-sm-4 mt-4">
                        {tr}Sample:{/tr} {$now|tiki_short_date:'n'}
                    </span>
                    {preference name=short_date_format}
                </div>
                <div class="clearfix">
                    <span class="form-text col-sm-8 offset-sm-4 mt-4">
                        {tr}Sample:{/tr} {$now|tiki_long_time}
                    </span>
                    {preference name=long_time_format}
                </div>
                <div class="clearfix">
                    <span class="form-text col-sm-8 offset-sm-4 mt-4">
                        {tr}Sample:{/tr} {$now|tiki_short_time}
                    </span>
                    {preference name=short_time_format}
                </div>
                {preference name=short_date_format_js}
                {preference name=short_time_format_js}
            </fieldset>
            <fieldset>
                <legend>{tr}Date/time selectors{/tr}</legend>
                {preference name=display_field_order}
                {preference name=display_start_year}
                {preference name=display_end_year}
                {preference name=change_month}
                {preference name=change_year}
                {preference name=users_prefs_display_12hr_clock}
            </fieldset>
            {preference name=tiki_same_day_time_only}
            {preference name=jquery_timeago}
            {preference name=wikiplugin_now}
            {preference name=wikiplugin_countdown}
            {preference name=wikiplugin_convene}
        {/tab}
    {/tabset}
    {include file='admin/include_apply_bottom.tpl'}
</form>