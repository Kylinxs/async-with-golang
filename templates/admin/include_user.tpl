
{* $Id$ *}
{jq}
    var frm = $('form[id="UsrForm"]'),
        days_before_lock = $('input[name="users_admin_auto_lock_user_days_before_lock"]'),
        users_admin_auto_lock_user = $('input[name="users_admin_auto_lock_user"]'),
        warning_empty_period = $('#warning_empty_period');
        warning_empty_period.hide();

    // allow to type in only digits
    days_before_lock.keypress(function(e){
        return /^\d*$/i.test(e.key);
    });
    frm.submit(function(){
        if (users_admin_auto_lock_user.is(':checked') && days_before_lock.val() === "") {
            warning_empty_period.show();
            days_before_lock.focus();
            return false;
        }
    });
{/jq}
<form action="tiki-admin.php?page=user" class="admin" id="UsrForm" method="post">
    {ticket}
    <div class="t_navbar mb-4 clearfix">
        {button href="tiki-admingroups.php" _type="text" _class="btn btn-link tips" _icon_name="group" _text="{tr}Groups{/tr}" _title=":{tr}Group Administration{/tr}"}
        {button href="tiki-adminusers.php" _type="text" _class="btn btn-link tips" _icon_name="user" _text="{tr}Users{/tr}" _title=":{tr}User Administration{/tr}"}
        {permission_link mode=text label="{tr}Permissions{/tr}"}
        {include file='admin/include_apply_top.tpl'}
    </div>
    {tabset name="admin_user_setting"}
        {tab name="{tr}User Settings{/tr}"}
            <br>
            <fieldset>
                <legend>
                    {tr}Default user preferences{/tr}
                    {help url="UsersDefaultPrefs" desc="{tr}Users Default Preferences{/tr}"}
                </legend>
                <div class="adminoptionbox">
                    {preference name=feature_userPreferences}
                    {preference name=feature_mytiki}
                    {preference name=users_prefs_xmpp_password}
                    <div class="adminoptionboxchild" id="feature_mytiki_childcontainer">
                        <legend>{tr}My Account Items{/tr}</legend>
                        {preference name=users_prefs_mytiki_pages}
                        {preference name=users_prefs_mytiki_blogs}
                        {preference name=users_prefs_mytiki_msgs}
                        {preference name=users_prefs_mytiki_tasks}
                        {preference name=users_prefs_mytiki_forum_topics}
                        {preference name=users_prefs_mytiki_forum_replies}
                        {preference name=users_prefs_mytiki_items}
                    </div>
                    {preference name=users_prefs_display_timezone}
                    {preference name=users_prefs_display_12hr_clock}
                    {preference name=change_theme}
                    {preference name=users_prefs_userbreadCrumb}
                    {preference name=users_prefs_tasks_maxRecords}
                    {preference name=users_prefs_diff_versions}
                    {preference name=users_prefs_remember_closed_rboxes}
                </div>
                </fieldset>
                <fieldset>
                <legend>
                    {tr}Admin user preferences{/tr}
                </legend>
                <div class="adminoptionbox">
                    {preference name=users_admin_actions_require_validation}
                </div>
                <div class="adminoptionbox">
                    {preference name=users_admin_auto_lock_user}
                    <div class="adminoptionboxchild" id="users_admin_auto_lock_user_childcontainer">
                    <div id="warning_empty_period" style="display:none;">
                        {remarksbox type="warning" title="{tr}Empty period of inactivity{/tr}" close="n"}
                        {tr}Please set a period of inactivity before account lock.{/tr}
                        {/remarksbox}
                    </div>
                        {preference name=users_admin_auto_lock_user_days_before_lock}
                    </div>
                </div>
                </fieldset>
        {/tab}
        {tab name="{tr}User Features{/tr}"}
            <br>
            <fieldset>
                <legend>{tr}User Account Features{/tr}</legend>
                <div class="adminoptionbox">
                    {preference name=feature_wizard_user}
                    {preference name=feature_minical}
                    {preference name=feature_tasks}
                    {preference name=feature_notepad}
                    {preference name=feature_user_bookmarks}
                    {preference name=user_favorites}
                    {preference name=feature_contacts}
                    {preference name=feature_usermenu}
                    {preference name=feature_userlevels}
                    {preference name=feature_tag_users}
                    {preference name=feature_wiki_userpage}
                    <div class="adminoptionboxchild" id="feature_wiki_userpage_childcontainer">
                        {preference name=feature_wiki_userpage_prefix}
                    </div>
                </div>
            </fieldset>
        {/tab}
        {tab name="{tr}User Info and Picture{/tr}"}
            <br>
            <fieldset>
                <legend>{tr}User information display{/tr}</legend>
                <div class="adminoptionbox">
                    {preference name=users_prefs_user_information}
                    {preference name=user_show_realnames}
                    {preference name='urlOnUsername'}
                    {preference name=users_prefs_show_mouseover_user_info}
                    {preference name=user_in_search_result}
                    {preference name=highlight_group}
                    {preference name=feature_display_my_to_others}
                    {preference name=user_tracker_infos}
                    {preference name=user_who_viewed_my_stuff}
                    {preference name=user_who_viewed_my_stuff_days}
                    {preference name=user_who_viewed_my_stuff_show_others}
                    {preference name=feature_unified_user_details}
                </div>
            </fieldset>
            <fieldset>
                <legend>{tr}Profile picture{/tr}</legend>
                <div class="adminoptionbox">
                    {preference name=user_use_gravatar}
                    {preference name=user_store_file_gallery_picture}
                    {preference name=user_small_avatar_size}
                    {preference name=user_small_avatar_square_crop}
                    {preference name=user_picture_gallery_id}
                    {preference name=user_default_picture_id}
                </div>
            </fieldset>
        {/tab}
        {tab name="{tr}Messaging and Notifications{/tr}"}
            <br>
            <fieldset>
                <legend>{tr}Messages{/tr}</legend>
                <div class="adminoptionbox">
                    {preference name=users_prefs_mailCharset}
                    {preference name=feature_messages}
                    <div class="adminoptionboxchild" id="feature_messages_childcontainer">
                        <legend>
                            {tr}User messages{/tr}
                            {help url="Inter-User-Messages"}
                        </legend>
                        {preference name=users_prefs_mess_maxRecords}
                        {preference name=users_prefs_allowMsgs}
                        {preference name=users_prefs_mess_sendReadStatus}
                        {preference name=users_prefs_minPrio}
                        {preference name=users_prefs_mess_archiveAfter}
                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend>{tr}User notifications{/tr}</legend>
                <div class="adminoptionbox">
                    {preference name=monitor_enabled}
                    <div class="adminoptionboxchild" id="monitor_enabled_childcontainer">
                        {preference name=monitor_individual_clear}
                        {preference name=monitor_count_refresh_interval}
                        {preference name=monitor_reply_email_pattern}
                        {preference name=monitor_digest}
                        {remarksbox type="info" title="{tr}Information{/tr}"}
                        {tr}For the digest emails to be sent out, you will need to set-up a cron job.{/tr}</br>
                        {tr}Adjust the command parameters for your digest frequency. Default frequency is 7 days.{/tr}</br>
                            <strong>{tr}Sample command:{/tr}</strong>
                            <code>/usr/bin/php {$monitor_command|escape}</code>
                        {/remarksbox}
                    </div>
                </div>
                {preference name=feature_user_watches}
                {preference name=feature_group_watches}
                {preference name=feature_daily_report_watches}
                <div class="adminoptionboxchild" id="feature_daily_report_watches_childcontainer">
                    {preference name=dailyreports_enabled_for_new_users}
                </div>
                {preference name=feature_user_watches_translations}
                {preference name=feature_groupalert}
                {preference name=feature_webmail}
                {preference name=feature_notify_users_mention}
            </fieldset>
        {/tab}
        {tab name="{tr}User Files{/tr}"}
            <br>
            <fieldset>
                <legend>{tr}Activate the feature{/tr}</legend>
                {preference name=feature_userfiles visible="always"}
                <div class="adminoptionboxchild" id="feature_userfiles_childcontainer">
                    {preference name=feature_use_fgal_for_user_files mode=invert}
                    <div class="adminoptionboxchild" id="feature_use_fgal_for_user_files_childcontainer">
                        {preference name='uf_use_db'}
                        <div class="adminoptionboxchild uf_use_db_childcontainer n">
                            {preference name='uf_use_dir'}
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>{tr}Settings{/tr}</legend>
                {preference name=userfiles_quota}
                {preference name=userfiles_private}
                {preference name=userfiles_hidden}
            </fieldset>
        {/tab}
    {/tabset}
    {include file='admin/include_apply_bottom.tpl'}
</form>