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
                                <input id="moderator_user" class="form-control" type="text" name="moderator" value="{$moderator|escape}