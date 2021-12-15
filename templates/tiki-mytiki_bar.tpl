
<div class="t_navbar btn-block mb-4">

    {if $prefs.feature_userPreferences eq 'y'}
        {button _icon_name="home" _type="link" _text="{tr}My Account{/tr}" href="tiki-my_tiki.php"}
    {/if}

    {if $prefs.feature_userPreferences eq 'y' or $prefs.change_password eq 'y'}
        {button _icon_name="cog" _type="link" _text="{tr}Preferences{/tr}" href="tiki-user_preferences.php"}
    {/if}

    {button _icon_name="information" _type="link" _text="{tr}My Info{/tr}" href="tiki-user_information.php"}

    {if $prefs.feature_user_watches eq 'y'}
        {button _icon_name="watch" _type="link" _text="{tr}My Watches{/tr}" href="tiki-user_watches.php"}
    {/if}

    <div class="btn-group">
        <button type="button" class="btn btn-link dropdown-toggle" data-bs-toggle="dropdown">
                {tr}More{/tr}
        </button>
        <div class="dropdown-menu" role="menu">
            {if $prefs.feature_messages eq 'y' and $tiki_p_messages eq 'y'}
                {if $unread}
                    <a class="dropdown-item" href="messu-mailbox.php"> {icon name="admin_messages"} {tr}Messages{/tr} ($unread)</a>
                {else}
                    <a class="dropdown-item" href="messu-mailbox.php"> {icon name="admin_messages"} {tr}Messages{/tr}</a>
                {/if}
            {/if}

            {if $prefs.feature_tasks eq 'y' and $tiki_p_tasks eq 'y'}
                <a class="dropdown-item" href="tiki-user_tasks.php"> {icon name="tasks"} {tr}Tasks{/tr}</a>
            {/if}

            {if $prefs.feature_user_bookmarks eq 'y' and $tiki_p_create_bookmarks eq 'y'}
                <a class="dropdown-item" href="tiki-user_bookmarks.php"> {icon name="book"} {tr}Bookmarks{/tr}</a>
            {/if}

            {if $prefs.user_assigned_modules eq 'y' and $tiki_p_configure_modules eq 'y'}
                <a class="dropdown-item" href="tiki-user_assigned_modules.php"> {icon name="admin_module"} {tr}Modules{/tr}</a>
            {/if}

            {if $prefs.feature_webmail eq 'y' and $tiki_p_use_webmail eq 'y'}
                <a class="dropdown-item" href="tiki-webmail.php"> {icon name="admin_webmail"} {tr}Webmail{/tr}</a>
            {/if}

            {if $prefs.feature_contacts eq 'y'}
                <a class="dropdown-item" href="tiki-user_contacts_prefs.php"> {icon name="user"} {tr}Contacts Preferences{/tr}</a>
            {/if}

            {if $prefs.feature_notepad eq 'y' and $tiki_p_notepad eq 'y'}
                <a class="dropdown-item" href="tiki-notepad_list.php"> {icon name="notepad"} {tr}Notepad{/tr}</a>
            {/if}

            {if $prefs.feature_userfiles eq 'y' and $tiki_p_userfiles eq 'y'}
                <a class="dropdown-item" href="tiki-userfiles.php"> {icon name="files"} {tr}MyFiles{/tr}</a>
            {/if}

            {if $prefs.feature_minical eq 'y' and $tiki_p_minical eq 'y'}
                <a class="dropdown-item" href="tiki-minical.php"> {icon name="calendar"} {tr}Mini Calendar{/tr}</a>
            {/if}


            {if $prefs.feature_actionlog == 'y' and !empty($user) and ($tiki_p_view_actionlog eq 'y' || $tiki_p_view_actionlog_owngroups eq 'y')}
                <a class="dropdown-item" href="tiki-admin_actionlog.php?selectedUsers[]=$user"> {icon name="calendar"} {tr}Action Log{/tr}</a>
            {/if}

            {if $prefs.feature_socialnetworks == 'y' and !empty($user) and ($tiki_p_socialnetworks eq 'y' or $tiki_p_admin_socialnetworks eq 'y')}
                <a class="dropdown-item" href="tiki-socialnetworks.php"> {icon name="admin_socialnetworks"} {tr}Social networks{/tr}</a>
            {/if}

            {if $prefs.feature_credits eq 'y' and !empty($user)}
                <a class="dropdown-item" href="tiki-user_credits.php"> {icon name="credit-card"} {tr}Credits{/tr}</a>
            {/if}

            {if $prefs.feature_mailin eq 'y' and !empty($user) and ($tiki_p_send_mailin eq 'y' or $tiki_p_admin_mailin eq 'y')}
                <a class="dropdown-item" href="tiki-user_mailin.php"> {icon name="reply"} {tr}Mail-in{/tr}</a>
            {/if}
        </div>
    </div>
</div>