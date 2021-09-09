
{title help="Inter-User Messages" admpage="messages"}{tr}Message Archive{/tr}{/title}

{include file='tiki-mytiki_bar.tpl'}
{include file='messu-nav.tpl'}
{if $prefs.messu_archive_size gt '0'}

<div class="progress">
    <div class="progress-bar" role="progressbar" aria-valuenow="{$cellsize|escape:"attr"}" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em;">
        {$percentage}%
    </div>
</div>
<div class="mb-4">
[{$messu_archive_number} / {$prefs.messu_archive_size}] {tr}messages{/tr}. {if $messu_archive_number eq $prefs.messu_archive_size}{tr}Archive is full!{/tr}{/if}
</div>
    {/if}

<form action="messu-archive.php" method="get" class="d-flex flex-row flex-wrap align-items-center mb-4">
    <div class="mb-3 col-sm-3">
        <label for="mess-mailmessages">{tr}Messages:{/tr}</label>
        <select name="flags" id="mess-mailmessages" class="form-control">
            <option value="isRead_y" {if $flag eq 'isRead' and $flagval eq 'y'}selected="selected"{/if}>{tr}Read{/tr}</option>
            <option value="isRead_n" {if $flag eq 'isRead' and $flagval eq 'n'}selected="selected"{/if}>{tr}Unread{/tr}</option>
            <option value="isFlagged_y" {if $flag eq 'isFlagged' and $flagval eq 'y'}selected="selected"{/if}>{tr}Flagged{/tr}</option>
            <option value="isFlagged_y" {if $flag eq 'isflagged' and $flagval eq 'n'}selected="selected"{/if}>{tr}Unflagged{/tr}</option>
            <option value="" {if $flag eq ''}selected="selected"{/if}>{tr}All{/tr}</option>
        </select>
    </div>

    <div class="mb-3 col-sm-3">
        <label for="mess-mailprio">{tr}Priority:{/tr}</label>
        <select name="priority" id="mess-mailprio" class="form-control">
            <option value="" {if $priority eq ''}selected="selected"{/if}>{tr}All{/tr}</option>
            <option value="1" {if $priority eq 1}selected="selected"{/if}>1</option>
            <option value="2" {if $priority eq 2}selected="selected"{/if}>2</option>
            <option value="3" {if $priority eq 3}selected="selected"{/if}>3</option>
            <option value="4" {if $priority eq 4}selected="selected"{/if}>4</option>
            <option value="5" {if $priority eq 5}selected="selected"{/if}>5</option>
        </select>
    </div>

    <div class="mb-3 col-sm-4">
        <label for="mess-mailcont">{tr}Containing:{/tr}</label>
        <div class="input-group">
            <input type="text" name="find" id="mess-mailcont" value="{$find|escape:"attr"}" class="form-control">
            <input type="submit" class="btn btn-info btn-sm" name="filter" value="{tr}Filter{/tr}">
        </div>
    </div>
</form>


<form action="messu-archive.php" method="post" name="form_messu_archive">
    {ticket}
    <div class="mb-3">
        <input type="hidden" name="offset" value="{$offset|escape:"attr"}">
        <input type="hidden" name="find" value="{$find|escape:"attr"}">
        <input type="hidden" name="sort_mode" value="{$sort_mode|escape:"attr"}">
        <input type="hidden" name="flag" value="{$flag|escape:"attr"}">
        <input type="hidden" name="flagval" value="{$flagval|escape:"attr"}">
        <input type="hidden" name="priority" value="{$priority|escape:"attr"}">
        <input
            type="submit"
            class="btn btn-danger btn-sm"
            name="delete"
            value="{tr}Delete{/tr}"
            onclick="confirmPopup('{tr}Delete selected messages?{/tr}')"
        >
        <input type="submit" class="btn btn-primary btn-sm" name="unarchive" value="{tr}Unarchive{/tr}">
        <input type="submit" class="btn btn-primary btn-sm" name="download" value="{tr}Download{/tr}">
    </div>
{jq notonready=true}
var CHECKBOX_LIST = [{{section name=user loop=$items}'msg[{$items[user].msgId}]'{if not $smarty.section.user.last},{/if}{/section}}];
{/jq}
    <div class="table-responsive">
        <table class="table">
            <tr>
                <th><input type="checkbox" name="checkall" onclick="checkbox_list_check_all('form_messu_archive',CHECKBOX_LIST,this.checked);"></th>
                <th style="width:18px">&nbsp;</th>
                <th><a href="messu-archive.php?flag={$flag|escape:"url"}&amp;priority={$priority|escape:"url"}&amp;flagval={$flagval|escape:"url"}&amp;find={$find|escape:"url"}&amp;offset={$offset|escape:"url"}&amp;sort_mode={if $sort_mode eq 'user_from_desc'}user_from_asc{else}user_from_desc{/if}">{tr}Sender{/tr}</a></th>
                <th><a href="messu-archive.php?flag={$flag|escape:"url"}&amp;priority={$priority|escape:"url"}&amp;flagval={$flagval|escape:"url"}&amp;find={$find|escape:"url"}&amp;offset={$offset|escape:"url"}&amp;sort_mode={if $sort_mode eq 'subject_desc'}subject_asc{else}subject_desc{/if}">{tr}Subject{/tr}</a></th>
                <th><a href="messu-archive.php?flag={$flag|escape:"url"}&amp;priority={$priority|escape:"url"}&amp;flagval={$flagval|escape:"url"}&amp;find={$find|escape:"url"}&amp;offset={$offset|escape:"url"}&amp;sort_mode={if $sort_mode eq 'date_desc'}date_asc{else}date_desc{/if}">{tr}Date{/tr}</a></th>
                <th style="text-align:right">{tr}Size{/tr}</th>
            </tr>

            {section name=user loop=$items}
                <tr>
                    <td class="prio{$items[user].priority|escape:"attr"}"><input type="checkbox" name="msg[{$items[user].msgId|escape:"attr"}]"></td>
                    <td class="prio{$items[user].priority|escape:"attr"}">{if $items[user].isFlagged eq 'y'}{icon name='flag' alt="{tr}Flagged{/tr}"}{/if}</td>
                    <td {if $items[user].isRead eq 'n'}style="font-weight:bold"{/if} class="prio{$items[user].priority|escape:"attr"}">{$items[user].user_from|userlink}</td>
                    <td {if $items[user].isRead eq 'n'}style="font-weight:bold"{/if} class="prio{$items[user].priority|escape:"attr"}"><a class="readlink" href="messu-read_archive.php?offset={$offset|escape:"url"}&amp;flag={$flag|escape:"url"}&amp;priority={$items[user].priority|escape:"url"}&amp;flagval={$flagval|escape:"url"}&amp;sort_mode={$sort_mode|escape:"url"}&amp;find={$find|escape:"url"}&amp;msgId={$items[user].msgId|escape:"url"}">{$items[user].subject|escape}</a></td>
                    <td {if $items[user].isRead eq 'n'}style="font-weight:bold"{/if} class="prio{$items[user].priority|escape:"attr"}">{$items[user].date|tiki_short_datetime}</td>{*date_format:"%d %b %Y [%H:%I]"*}
                    <td style="text-align:right;{if $items[user].isRead eq 'n'}font-weight:bold;{/if}" class="prio{$items[user].priority|escape:"attr"}">{$items[user].len|kbsize|escape}</td>
                </tr>
            {sectionelse}
                <tr><td colspan="6">{tr}No messages to display{/tr}<td></tr>
            {/section}
        </table>
    </div>
</form>
{pagination_links cant=$cant_pages step=$prefs.maxRecords offset=$offset}{/pagination_links}