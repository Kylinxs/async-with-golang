
{* $Id$ *}
{title help="System Log"}{tr}Tiki Logs{/tr}{/title}

<div class="t_navbar mb-4">
    {if $api_tiki == 'adodb'}
        <a href="tiki-sqllog.php" class="btn btn-link" title="{tr}Log SQL{/tr}">{icon name="pencil"} {tr}Log SQL{/tr}</a>
    {/if}
    {if $prefs.feature_actionlog == 'y'}
        <a href="tiki-admin_actionlog.php#Report" class="btn btn-link" title="{tr}Export through Action Log{/tr}">{icon name="upload"} {tr}Export through Action Log{/tr}</a>
    {/if}
{*    {button class="btn btn-primary" _text="{tr}Log SQL{/tr}" href="tiki-sqllog.php"}
    {button class="btn btn-primary" _text="{tr}Export through Action Log{/tr}" href="tiki-admin_actionlog.php#Report"}
*}</div>

<form method="get" action="tiki-syslog.php" class="mb-4">
    <div class="mb-3 row">
        <label class="col-sm-3 col-form-label" for="months">{tr}Clean logs older than{/tr}</label>
        <div class="col-sm-8 d-flex flex-row flex-wrap align-items-center">
            <input type="text" name="months" class="form-control mr-4"> {tr}months{/tr}
            <input type="submit" class="btn btn-primary btn-sm ms-4" value="{tr}Clean{/tr}" name="clean">
        </div>
    </div>
</form>

{include file='find.tpl'}

{pagination_links cant=$cant step=$maxRecords offset=$offset}{/pagination_links}

<div class="table-responsive syslog-table">
    <table class="table">
        <tr>
            <th>{self_link _sort_arg="sort_mode" _sort_field="actionid"}{tr}Id{/tr}{/self_link}</th>
            <th>{self_link _sort_arg="sort_mode" _sort_field="action"}{tr}Type{/tr}{/self_link}</th>
            <th>{self_link _sort_arg="sort_mode" _sort_field="lastModif"}{tr}Time{/tr}{/self_link}</th>
            <th>{self_link _sort_arg="sort_mode" _sort_field="user"}{tr}User{/tr}{/self_link}</th>
            <th>{self_link _sort_arg="sort_mode" _sort_field="comment"}{tr}Message{/tr}{/self_link}</th>
            <th>{self_link _sort_arg="sort_mode" _sort_field="ip"}{tr}IP{/tr}{/self_link}</th>
            <th>{self_link _sort_arg="sort_mode" _sort_field="client"}{tr}Client{/tr}{/self_link}</th>
        </tr>

        {section name=ix loop=$list}
            <tr>
                <td class="id">{$list[ix].actionId}</td>
                <td class="text">{$list[ix].object|escape}</td>
                <td class="date"><span title="{$list[ix].lastModif|tiki_long_datetime}">{$list[ix].lastModif|tiki_short_datetime}</span></td>
                <td class="username">{$list[ix].user|userlink}</td>
                <td class="text"><textarea class="form-control" readonly="readonly">{$list[ix].action|escape:'html'}</textarea></td>
                <td class="text">{$list[ix].ip|escape:"html"}</td>
                <td class="text"><span title="{$list[ix].client|escape:'html'}">{$list[ix].client|truncate:30:"..."|escape:'html'}</span></td>
                <td>
                    {if $list[ix].object == 'profile apply' && strpos($list[ix].log, 'reverted') === false}
                        <form method="post" action="tiki-syslog.php" onsubmit="return confirm('{tr}Are you sure you want to revert{/tr} &QUOT;{$list[ix].action|escape}&QUOT;?');">
                            {ticket}
                            <input type="hidden" name="page" value="profiles">
                            <input type="hidden" name="actionId" value="{$list[ix].actionId}">
                            <input type="submit" class="btn btn-secondary" name="revert" value="{tr}Revert{/tr}">
                        </form>
                    {/if}
                </td>
            </tr>
        {/section}
    </table>
</div>

{pagination_links cant=$cant step=$maxRecords offset=$offset}{/pagination_links}