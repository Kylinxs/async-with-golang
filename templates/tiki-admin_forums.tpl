{* $Id$ *}
{if !$ts.ajax}
    {title help="Forums" admpage="forums" url='tiki-admin_forums.php'}
        {tr}Admin Forums{/tr}{if isset($parent)}: {$parent.name}{/if}
    {/title}

    <div class="t_navbar mb-4">
        {if $tiki_p_admin_forum eq 'y' && $forumId > 0 or (isset($dup_mode) and $dup_mode eq 'y')}
            {button class="btn btn-primary" href="?" _icon_name="add" _text="{tr}Create Forum{/tr}"}
        {/if}
        {if $tiki_p_admin_forum eq 'y' && (!isset($dup_mode) or $dup_mode ne 'y')}
            {button class="btn btn-primary" href="tiki-admin_forums.php?dup_mode=y" _icon_name="copy" _text="{tr}Duplicate{/tr}"}
        {/if}
        {if $forumId > 0}
            {button _type="link" class="btn btn-link" href="tiki-view_forum.php?forumId=$forumId" _icon_name="view" _text="{tr}View{/tr}"}
        {/if}
        {if $tiki_p_admin_forum eq 'y'}
            {button _type="link" class="btn btn-link" href="tiki-forum_import.php" _icon_name="import" _text="{tr}Import{/tr}"}
        {/if}
        {if $tiki_p_admin_forum eq 'y'}
            {button _type="link" class="btn btn-link" href="tiki-admin.php?page=forums&cookietab=2&highlight=forums_ordering" _icon_name="cog" _text="{tr}Forum Ordering{/tr}"}
        {/if}
        {if $tiki_p_forum_read eq 'y'}
            {button _type="link" class="btn btn-link" href="tiki-forums.php" _icon_name="list" _text="{tr}List{/tr}"}
        {/if}
    </div>
{/if}
{tabset}

    {tab name="{tr}Forums{/tr}"}
        <h2>{tr}Forums{/tr}</h2>

        {if ($channels or ($find ne '')) && !$ts.enabled}
            {include file='find.tpl'}
        {/if}
        <form method='post' id="admin_forums">
            <div id="{$ts.tableid}-div" class="{if $js}table-responsive{/if} ts-wrapperdiv" {if !empty($ts.enabled)}style="visibility:hidden;"{/if}>
                <table id="{$ts.tableid}" class="table table-striped table-hover" data-count="{$cant|escape}">
                    {$numbercol = 0}
                    <thead>
                        <tr>
                            {$numbercol = $numbercol+1}
                            <th id="checkbox" style="text-align:center">
                                {select_all checkbox_names='checked[]' tablesorter="{$ts.enabled}"}
                            </th>
                            <th id="name">
                                {self_link _sort_arg='sort_mode' _sort_field='name'}{tr}Name{/tr}{/self_link}
                                {$numbercol = $numbercol+1}
                            </th>
                            <th id="threads">
                                {self_link _sort_arg='sort_mode' _sort_field='threads'}{tr}Topics{/tr}{/self_link}
                                {$numbercol = $numbercol+1}
                            </th>
                            <th id="threads">
                                {self_link _sort_arg='sort_mode' _sort_field='threads'}{tr}Order{/tr}{/self_link}
                                {$numbercol = $numbercol+1}
                            </th>
                            <th id="comments">
                                {self_link _sort_arg='sort_mode' _sort_field='comments'}{tr}Comments{/tr}{/self_link}
                                {$numbercol = $numbercol+1}
                            </th>
                            <th id="users">{tr}Users{/tr}</th>
                            {$numbercol = $numbercol+1}
                            <th id="age">{tr}Age{/tr}</th>
                            {$numbercol = $numbercol+1}
                            <th id="ppd">{tr}PPD{/tr}</th>
                            {$numbercol = $numbercol+1}
                            <th id="hits">
                                {self_link _sort_arg='sort_mode' _sort_field='hits'}{tr}Hits{/tr}{/self_link}
                                {$numbercol = $numbercol+1}
                            </th>
                            <th id="actions"></th>
                            {$numbercol = $numbercol+1}
                        </tr>
                    </thead>
                    <tbody>
                        {section name=user loop=$channels}
                            <tr>
                                <td style="text-align:center">
                                    <input type="checkbox" class="form-check-input" name="checked[]" value="{$channels[user].forumId|escape}" {if isset($smarty.request.checked) and $smarty.request.checked and in_array($channels[user].forumId,$smarty.request.checked)}checked="checked"{/if}>
                                </td>
                                <td>
                                    <a class="link" href="{$channels[user].forumId|sefurl:'forum'}" title="{tr}View{/tr}">{$channels[user].name|escape}</a>
                                </td>
                                <td class="integer"><span class="badge bg-secondary">{$channels[user].threads}<span></td>
                                <td class="integer">
                                    <input type="number" name="order[]" value="{$channels[user].forumOrder|escape}">
                                    <input type="hidden" name="forumsId[]" value="{$channels[user].forumId|escape}">
                                </td>
                                <td class="integer"><span class="badge bg-secondary">{$channels[user].comments}<span></td>
                                <td class="integer"><span class="badge bg-secondary">{$channels[user].users}<span></td>
                                <td class="integer"><span class="badge bg-secondary">{$channels[user].age}<span></td>
                                <td class="integer"><span class="badge bg-secondary">{$channels[user].posts_per_day|string_format:"%.2f"}<span></td>
                                <td class="integer"><span class="badge bg-secondary">{$channels[user].hits}<span></td>
                                <td class="action">
                                    {actions}
                                        {strip}
                                            <action>
                                                <a href="{$channels[user].forumId|sefurl:'forum'}">
                                                    {icon name='view' _menu_text='y' _menu_icon='y' alt="{tr}View{/tr}"}
                                                </a>
                                            </action>
                                                {if $channels[user].is_locked eq 'y'}
                                                    <action>
                                                        <form action="tiki-admin_forums.php" method="post">
                                                            {ticket}
                                                            <input type="hidden" name="lock" value="n">
                                                            <input type="hidden" name="forumId" value="{$channels[user].forumId|escape:'attr'}">
                                                            <button type="submit" class="btn btn-link link-list">
                                                                {icon name='unlock'} {tr}Unlock{/tr}
                                                            </button>
                                                        </form>
                                                    </action>
                                                {else}
                                                    <action>
                                                        <form action="tiki-admin_forums.php" method="post">
                                                            {ticket}
                                                            <input type="hidden" name="lock" value="y">
                                                            <input type="hidden" name="forumId" value="{$channels[user].forumId|escape:'attr'}">
                                                            <button type="submit" class="btn btn-link link-list">
                                                                {icon name='lock'} {tr}Lock{/tr}
                                                            </button>
                                                        </form>
                                                    </action>
                                                {/if}
                                            {if ($tiki_p_admin eq 'y')
                                            or ((isset($channels[user].individual) and $channels[user].individual eq 'n')
                                            and ($tiki_p_admin_forum eq 'y'))
                                            or ($channels[user].individual_tiki_p_admin_forum eq 'y')
                                            }
                                                <action>
                                                    {self_link _icon_name='edit' _menu_text='y' _menu_icon='y' cookietab='2' _anchor='anchor2' forumId=$channels[user].forumId}
                                                        {tr}Edit{/tr}
                                                    {/self_link}
                                                </action>
                                                <action>
                                                    {permission_link mode=text type=forum permType=forums id=$channels[user].forumId title=$channels[user].name}
                                                </action>
                                                <action>
                                                    <a href="{bootstrap_modal controller=forum action=delete_forum checked={$channels[user].forumId}}">
                                                        {icon name='remove' _menu_text='y' _menu_icon='y' alt="{tr}Delete{/tr}"}
                                                    </a>
                                                </action>
                                            {/if}
                                        {/strip}
                                    {/actions}
                                </td>
                            </tr>
                        {sectionelse}
                            {norecords _colspan=$numbercol _text="No forums found"}
                        {/section}
                    </tbody>
                </table>
            </div>
            {if !$ts.ajax}
                {if $channels}
                    <div class="text-start mb-3 row">
                        <br>
                        <label for="action" class="col-lg"></label>
                        <div class="col-sm-6 input-group">
                            <select name="action" class="form-control" onchange="show('groups');">
                                <option value="no_action">
                                    {tr}Select action to perform with checked{/tr}...
                                </option>
                                {if $tiki_p_admin_forum eq 'y'}
                                    <option value="delete_forum">{tr}Delete{/tr}</option>
                                    <option value="order_forum">{tr}Reorder forums{/tr}</option>
                                {/if}
                            </select>
                            <button
                                type="submit"
                                form='admin_forums'
                                formaction="{bootstrap_modal controller=forum}"
                                class="btn btn-primary"
                                onclick="confirmPopup()"
                            >
                                {tr}OK{/tr}
                            </button>
                        </div>
                    </div>
                {/if}
            {/if}
        </form>
    {if !$ts.enabled}
        {pagination_links cant=$cant step=$maxRecords offset=$offset}{/pagination_links}
    {/if}
    {/tab}
    {if !$ts.ajax}
        {tab name="{tr}Create/Edit Forums{/tr}"}

            {if !isset($dup_mode) or $dup_mode != 'y'}
                {if $forumId > 0}
                    <h2>{tr}Edit this Forum:{/tr} {$name|escape}</h2>
                    {include file='object_perms_summary.tpl' objectName=$name objectType='forum' objectId=$forumId permType=$permsType}
                {else}
                    <h2>{tr}Create New Forum{/tr}</h2>
                {/if}

                <form action="tiki-admin_forums.php" method="post" role="form">
                    {ticket}
                    <input type="hidden" name="forumId" value="{$forumId|escape}">
                    <input type="hidden" name="parentId" value="{$parentId|escape}">
                    <fieldset>
                        <legend>{tr}Main details{/tr}</legend>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label" for="name">{tr}Name{/tr}</label>
                            <div class="col-sm-8">
                                <input type="text" name="name" class="form-control" id="name" value="{$name|escape}">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label" for="name">{tr}Description{/tr}</label>
                            <div class="col-sm-8">
                                <textarea name="description" rows="4" class="form-control" id="description">{$description|escape}</textarea>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label" for="section">{tr}Section{/tr}</label>
                            <div class="col-sm-4">
                                <select name="section" id="section" class="form-control">
                                    <option value="" {if $forumSection eq ""}selected="selected"{/if}>{tr}None{/tr}</option>
                                    <option value="__new__">{tr}Create new{/tr}</option>
                                    {section name=ix loop=$sections}
                                        <option {if $forumSection eq $sections[ix]}selected="selected"{/if} value="{$sections[ix]|escape}">{$sections[ix]|escape}</option>
                                    {/section}
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <input name="new_section" class="form-control" type="text">
                            </div>
                        </div>

                        {include file='categorize.tpl' labelcol='4' inputcol='8'}
                        {if $prefs.feature_multilingual eq 'y'}
                            <div class="mb-3 row">
                                <label class="col-sm-4 col-form-label" for="forumLanguage">{tr}Language{/tr}</label>
                                <div class="col-sm-8">
                                    <select name="forumLanguage" id="forumLanguage" class="form-control">
                                        <option value="">{tr}Unknown{/tr}</option>
                                        {section name=ix loop=$languages}
                                            <option value="{$languages[ix].value|escape}"{if $forumLanguage eq $languages[ix].value or (empty($data.page_id) and $forumLanguage eq '' and $languages[ix].value eq $prefs.language)} selected="selected"{/if}>{$languages[ix].name}</option>
                                        {/section}
                                    </select>
                                </div>
                            </div>
                        {/if}
                        {if $prefs.feature_file_galleries eq 'y' && $prefs.forum_image_file_gallery}
                            <div class="mb-3 row">
                                <label class="col-sm-4 col-form-label" for="image">{tr}Image{/tr}</label>
                                <div class="col-sm-8">
                                    {file_selector name="image" value=$image type="image/*" galleryId=$prefs.forum_image_file_gallery}
                                    <div class="form-text">
                                        {tr}Image symbolizing the forum.{/tr}
                                    </div>
                                </div>
                            </div>
                        {/if}
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label" for="image">{tr}Forum Order{/tr}</label>
                            <div class="col-sm-8">
                                <div class="form-text">
                                    {tr}{$forumOrder|escape}{/tr}
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 form-check-label" for="is_flat">{tr}Only allow replies to the first message (flat forum){/tr}</label>
                            <div class="col-sm-8">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="is_flat" id="is_flat" {if $is_flat eq 'y'}checked="checked"{/if}>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label" for="moderator_user">{tr}Moderator user{/tr}</label>
                            <div class="col-sm-8">
                                <input id="moderator_user" class="form-control" type="text" name="moderator" value="{$moderator|escape}">
                                {autocomplete element='#moderator_user' type='username'}
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label" for="moderator_group">{tr}Moderator group{/tr}</label>
                            <div class="col-sm-8">
                                <input id="moderator_group" type="text" class="form-control" name="moderator_group" id="moderator_group" value="{$moderator_group|escape}">
                                {autocomplete element='#moderator_group' type='groupname'}
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label" for="forum_use_password">{tr}Password protected{/tr}</label>
                            <div class="col-sm-4">
                                {html_options name=forum_use_password class="form-control" options=$forum_use_password_options selected=$forum_use_password}
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label" for="forum_password">{tr}Forum password{/tr}</label>
                            <div class="col-sm-8">
                                <input type="text" name="forum_password" id="forum_password" class="form-control" value="{$forum_password|escape}">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 form-check-label" for="controlFlood">{tr}Prevent flooding{/tr}</label>
                            <div class="col-sm-8">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="controlFlood" id="controlFlood" {if $controlFlood eq 'y'}checked="checked"{/if}>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label" for="floodInterval">{tr}Minimum time between posts{/tr}</label>
                            <div class="col-sm-4">
                                {html_options name=floodInterval id=floodInterval class="form-control" options=$flood_options selected=$floodInterval}
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <div class="col-sm-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="useMail" id="useMail" {if $useMail eq 'y'}checked="checked"{/if}>
                                    <label class="form-check-label" for="useMail"> {tr}Send the posts of this forum to this email address{/tr} </label>
                                </div>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="mail" value="{$mail|escape}">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <div class="col-sm-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="usePruneUnreplied" id="usePruneUnreplied" {if $usePruneUnreplied eq 'y'}checked="checked"{/if}>
                                    <label class="form-check-label" for="usePruneUnreplied">{tr}Prune unreplied-to messages after{/tr}</label>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                {html_options name=pruneUnrepliedAge class="form-control" options=$pruneUnrepliedAge_options selected=$pruneUnrepliedAge}
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <div class="col-sm-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="usePruneOld" id="usePruneOld" {if $usePruneOld eq 'y'}checked="checked"{/if}>
                                    <label class="form-check-label" for="usePruneOld">{tr}Prune old messages after{/tr}</label>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                {html_options name=pruneMaxAge class="form-control" options=$pruneMaxAge_options selected=$pruneMaxAge}
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>{tr}Forum-mailing list synchronization{/tr}</legend>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label" for="outbound_address">{tr}Forward messages to this forum to this email address, in a format that can be used for sending back to the inbound forum email address{/tr}</label>
                            <div class="col-sm-8">
                                <input type="text" name="outbound_address" id="outbound_address" class="form-control" value="{$outbound_address|escape}">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 form-check-label" for="outbound_mails_for_inbound_mails">{tr}Send emails even when the post is generated by an inbound email{/tr}</label>
                            <div class="col-sm-8">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="outbound_mails_for_inbound_mails" id="outbound_mails_for_inbound_mails" {if $outbound_mails_for_inbound_mails eq 'y'}checked="checked"{/if}>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 form-check-label" for="outbound_mails_reply_link">{tr}Append a reply link to outbound mails{/tr}</label>
                            <div class="col-sm-8">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="outbound_mails_reply_link" id="outbound_mails_reply_link" {if $outbound_mails_reply_link eq 'y'}checked="checked"{/if}>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label" for="outbound_from">{tr}Originating email address for emails from this forum{/tr}</label>
                            <div class="col-sm-8">
                                <input type="text" name="outbound_from" id="outbound_from" class="form-control" value="{$outbound_from|escape}">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label">{tr}Add messages from this email to the forum{/tr}</label>
                            <div class="col-sm-8">
                                <div class="mb-3 row">
                                    <label class="col-sm-4 col-form-label" for="inbound_pop_server">{tr}POP3 server{/tr}</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="inbound_pop_server" id="inbound_pop_server" class="form-control" value="{$inbound_pop_server|escape}">
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label class="col-sm-4 col-form-label" for="inbound_pop_user">{tr}User{/tr}</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="inbound_pop_user" id="inbound_pop_user" class="form-control"value="{$inbound_pop_user|escape}" autocomplete="off">
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label class="col-sm-4 col-form-label" for="inbound_pop_password">{tr}Password{/tr}</label>
                                    <div class="col-sm-8">
                                        <input type="password" name="inbound_pop_password" id="inbound_pop_password" class="form-control" value="{$inbound_pop_password|escape}" autocomplete="new-password">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>{tr}Forums list{/tr}</legend>
                        <div class="mb-3 row">
                            <label class="col-sm-4 form-check-label" for="show_description">{tr}Show description{/tr}</label>
                            <div class="col-sm-8">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input position-static" name="show_description" id="show_description" {if $show_description eq 'y'}checked="checked"{/if}>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label" for="forum_last_n">{tr}Display last post titles{/tr}</label>
                            <div class="col-sm-4">
                                {html_options name=forum_last_n id=forum_last_n class="form-control" options=$forum_last_n_options selected=$forum_last_n}
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>{tr}Forum topics (threads) list{/tr}</legend>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label" for="topicOrdering">{tr}Default order of topics{/tr}</label>
                            <div class="col-sm-8">
                                {html_options name=topicOrdering id=topicOrdering class="form-control" options=$topicOrdering_options selected=$topicOrdering}
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label" for="topicsPerPage">{tr}Topics per page{/tr}</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="topicsPerPage" id="topicsPerPage" value="{$topicsPerPage|escape}">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label">{tr}Topic list configuration{/tr}</label>
                            <div class="col-sm-8">
                                <div class="form-check">
                                    <label class="form-check-label" for="topics_list_replies">
                                        <input type="checkbox" class="form-check-input" name="topics_list_replies" id="topics_list_replies" {if $topics_list_replies eq 'y'}checked="checked"{/if}> {tr}Replies{/tr}
                                    </label>
                                </div>
                                <div class="form-check">
                                    <label class="form-check-label" for="topics_list_reads">
                                        <input type="checkbox" class="form-check-input" name="topics_list_reads" id="topics_list_reads" {if $topics_list_reads eq 'y'}checked="checked"{/if}> {tr}Reads{/tr}
                                    </label>
                                </div>
                                <div class="form-check">
                                    <label class="form-check-label" for="topics_list_pts">
                                        <input type="checkbox" class="form-check-input" name="topics_list_pts" id="topics_list_pts" {if $topics_list_pts eq 'y'}checked="checked"{/if}> {tr}Points{/tr}
                                    </label>
                                </div>
                                <div class="form-check">
                                    <label class="form-check-label" for="topics_list_lastpost">
                                        <input type="checkbox" class="form-check-input" name="topics_list_lastpost" id="topics_list_lastpost" {if $topics_list_lastpost eq 'y'}checked="checked"{/if}> {tr}Last post{/tr}
                                    </label>
                                </div>
                                <div class="form-check">
                                    <label class="form-check-label" for="topics_list_lastpost_title">
                                        <input class="form-check-input" type="checkbox" name="topics_list_lastpost_title" id="topics_list_lastpost_title" {if $topics_list_lastpost_title eq 'y'}checked="checked"{/if}> {tr}Last post title{/tr}
                                    </label>
                                </div>
                                <div class="form-check">
                                    <label class="form-check-label" for="topics_list_lastpost_avatar">
                                        <input class="form-check-input" type="checkbox" name="topics_list_lastpost_avatar" id="topics_list_lastpost_avatar" {if $topics_list_lastpost_avatar eq 'y'}checked="checked"{/if}> {tr}Last post profile picture{/tr}
                                    </label>
                                </div>
                                <div class="form-check">
                                    <label class="form-check-label" for="topics_list_author">
                                        <input class="form-check-input" type="checkbox" name="topics_list_author" id="topics_list_author" {if $topics_list_author eq 'y'}checked="checked"{/if}> {tr}Author{/tr}
                                    </label>
                                </div>
                                <div class="form-check">
                                    <label class="form-check-label" for="topics_list_author_avatar">
                                        <input class="form-check-input" type="checkbox" name="topics_list_author_avatar" id="topics_list_author_avatar" {if $topics_list_author_avatar eq 'y'}checked="checked"{/if}> {tr}Author profile picture{/tr}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 form-check-label" for="topic_smileys">{tr}Use topic smileys{/tr}</label>
                            <div class="col-sm-8">
                                <div class="form-check">
                                    <input type="checkbox" name="topic_smileys" class="form-check-input" id="topic_smileys" {if $topic_smileys eq 'y'}checked="checked"{/if}>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 form-check-label" for="topic_summary">{tr}Show topic summary{/tr}</label>
                            <div class="col-sm-8">
                                <div class="form-check">
                                    <input type="checkbox" name="topic_summary" class="form-check-input" id="topic_summary" {if $topic_summary eq 'y'}checked="checked"{/if}>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>{tr}Forum threads{/tr}</legend>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label" for="threadOrdering">{tr}Default ordering of threads{/tr}</label>
                            <div class="col-sm-8">
                                {html_options name=threadOrdering id=threadOrdering class="form-control" options=$threadOrdering_options selected=$threadOrdering}
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label" for="threadStyle">{tr}Default style of threads{/tr}</label>
                            <div class="col-sm-8">
                                {html_options name=threadStyle id=threadStyle class="form-control" options=$threadStyle_options selected=$threadStyle}
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label" for="commentsPerPage">{tr}Default number of comments per page{/tr}</label>
                            <div class="col-sm-8">
                                {html_options name=commentsPerPage id=commentsPerPage class="form-control" options=$commentsPerPage_options selected=$commentsPerPage}
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>{tr}Posts{/tr}</legend>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label" for="approval_type">{tr}Approval type{/tr}</label>
                            <div class="col-sm-4">
                                {html_options name=approval_type for=approval_type id=approval_type class="form-control" options=$approval_options selected=$approval_type}
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label">{tr}User information display{/tr}</label>
                            <div class="col-sm-8">
                                <div class="form-check">
                                    <label class="form-check-label" for="ui_avatar">
                                        <input class="form-check-input" type="checkbox" name="ui_avatar" id="ui_avatar" {if $ui_avatar eq 'y'}checked="checked"{/if}> {tr}Profile picture{/tr}
                                    </label>
                                </div>
                                <div class="form-check">
                                    <label class="form-check-label" for="ui_rating_choice_topic">
                                        <input class="form-check-input" type="checkbox" name="ui_rating_choice_topic" id="ui_rating_choice_topic" {if $ui_rating_choice_topic eq 'y'}checked="checked"{/if}> {tr}Topic Rating{/tr}
                                    </label>
                                </div>
    