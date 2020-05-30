{extends 'layout_view.tpl'}
{block name="title"}
    {title}{$title|escape}{/title}
{/block}
{block name="content"}
    {include file='access/include_items.tpl'}
    <form method="post" id="confirm-action" class="confirm-action px-3" action="{service controller=$confirmController action=$confirmAction}">
        {include file='access/include_hidden.tpl'}
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remove_users" name="remove_users" checked="checked" disabled="disabled">
                <label class="form-check-label" for="remove_users">{icon name='users'} {tr}Remove users{/tr}</label>
            </div>
        </div>
        {if $prefs.feature_wiki_userpage == 'y'}
            <div class="mb-3">
                <div class="h5">{icon name='admin_wiki'} {tr}Remove the users' pages{/tr}</div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remove_pages" name="remove_pages">
                    <label class="form-check-label" for="remove_pages">{tr}Remove the user pages belonging to these users{/tr}</label>
                </div>
            </div>
        {/if}
        {if $prefs.feature_trackers eq 'y'}
            <div class="mb-3">
                <label class="form-check-label" for="remove_items">{icon name='trackers'} {tr}Delete user items from these trackers{/tr}</label>
                <div>
                    <select name="remove_items[]" multiple="multiple" class="form-control">
                        <option></option>
                        {foreach $trackerIds as $trackerId => $info}
                            <option value="{$trackerId}">
                                {if $info.count eq 1}
                                    {tr _0=$info.name _1=$info.count}"%0" (%1 item){/tr}
                                {else}
                                    {tr _0=$info.name _1=$info.count}"%0" (%1 items){/tr}
                                {/if}
                            </option>
                        {/foreach}
                    </select>
                    <div class="form-text">
                        {tr}Select trackers here to have items in them which are "owned" by these users deleted{/tr}<br>
                        {tr}Important: If you set trackers to store user's information, "User" and "Group" tracker items related to this user will be deleted automatically{/tr}
                    </div>
                </div>
            </div>
        {/if}
        {if $prefs.feature_use_fgal_for_user_files eq 'y'}
            <div class="mb-3">
                <div class="h5">{icon name='file'} {tr}Delete user files{/tr}</div>
                <input class="form-check-input" type="checkbox" id="remove_files" name="remove_files">
                <label class="form-check-label" for="remove_files">{tr}Delete the users' file galleries and all the files in them{/tr}</label>
            </div>
        {/if}
        {if $prefs.feature_banning eq 'y'}
            <div class="mb-3">
                <div class="h5">{icon name='ban'} {tr}Ban users{/tr}</div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="ban_users" name="ban_users">
                    <label class="form-check-label">
                        {tr}Checking this option and clicking OK will redirect you to a form where the selected users are marked for IP Banning.{/tr}
                    </label>
                </div>
            </div>
        {/if}
        {include file='access/include_submit.tpl'}
    </form>
{/block}
