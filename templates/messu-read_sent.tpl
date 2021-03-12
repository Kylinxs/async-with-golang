<div class="row"><div class="col-xs-12">{title url="messu-read.php?msgId=$msgId" admpage="messages"}{tr}Read message{/tr}{/title}</div></div>

<div class="row"><div class="col-xs-12">{include file='tiki-mytiki_bar.tpl'}</div></div>
<div class="row"><div class="col-xs-12">{include file='messu-nav.tpl'}</div></div>
<br>
{if $legend}
    {$legend}
{else}
<div class="row" style="padding-bottom:10px;">
    <div class="col-xs-4 col-sm-5">
        <div class="row">
            <div class="col-xs-4 col-sm-2">
                {if $prev}
                    <a class="btn btn-link" href="messu-read_sent.php?offset={$offset}&amp;msgId={$prev}&amp;sort_mode={$sort_mode}&amp;find={$find|escape:"url"}&amp;flag={$flag}&amp;priority={$priority}&amp;flagval={$flagval}">{tr}<i class="fas fa-arrow-left" aria-hidden="true"></i>{/tr}</a>
                {else}
                    <a class="btn btn-link disabled" href="messu-read_sent.php?offset={$offset}&amp;msgId={$prev}&amp;sort_mode={$sort_mode}&amp;find={$find|escape:"url"}&amp;flag={$flag}&amp;priority={$priority}&amp;flagval={$flagval}">{tr}<i class="fas fa-arrow-left" aria-hidden="true"></i>{/tr}</a>
                {/if}
            </div>
            <div class="col-xs-4 col-sm-2">
                {if $next}
                    <a class="btn btn-link" href="messu-read_sent.php?offset={$offset}&amp;msgId={$next}&amp;sort_mode={$sort_mode}&amp;find={$find|escape:"url"}&amp;flag={$flag}&amp;priority={$priority}&amp;flagval={$flagval}">{tr}<i class="fas fa-arrow-right" aria-hidden="true"></i>{/tr}</a>
                {else}
                    <a class="btn btn-link disabled" href="messu-read_sent.php?offset={$offset}&amp;msgId={$next}&amp;sort_mode={$sort_mode}&amp;find={$find|escape:"url"}&amp;flag={$flag}&amp;priority={$priority}&amp;flagval={$flagval}">{tr}<i class="fas fa-arrow-right" aria-hidden="true"></i>{/tr}</a>
                {/if}
            </div>
            <div class="col-xs-4 col-sm-2">
                <form method="post" action="messu-read_sent.php">
                    {ticket}
                    <input type="hidden" name="msgId" value="{$msgId|escape}">
                    <input type="hidden" name="offset" value="{$offset|escape}">
                    <input type="hidden" name="find" value="{$find|escape:'url'}">
                    <input type="hidden" name="sort_mode" value="{$sort_mode|escape}">
                    <input type="hidden" name="flag" value="{$flag|escape}">
                    <input type="hidden" name="priority" value="{$priority|escape}">
                    <input type="hidden" name="flagval" value="{$flagval|escape}">
                    <input type="hidden" name="action" value="isFlagged">
                    {if $msg.isFlagged eq 'y'}
                        <input type="hidden" name="actionval" value="n">
                        <button type="submit" class="btn btn-link">
                            <i class="fas fa-flag tips" aria-hidden="true" title="{tr}Flagged: Click to unflag{/tr}"></i>
                        </button>
                    {else}
                        <input type="hidden" name="actionval" value="y">
                        <button type="submit" class="btn btn-link">
                            <i class="far fa-flag tips" aria-hidden="true" title="{tr}Not flagged: Click to flag{/tr}"></i>
                        </button>
                    {/if}
                </form>
            </div>
        </div>
    </div>
    <div class="col-xs-3 offset-xs-4 col-md-2 offset-md-5" style="padding-top: 4px;">
        <form id="messu-read-sent-delete" method="post" action="messu-read_sent.php">
            {ticket}
            <input type="hidden" name="offset" value="{$offset|escape}">
            <input type="hidden" name="find" value="{$find|escape}">
            <input type="hidden" name="sort_mode" value="{$sort_mode|escape}">
            <input type="hidden" name="flag" value="{$flag|escape}">
            <input type="hidden" name="flagval" value="{$flagval|escape}">
            <input type="hidden" name="priority" value="{$priority|escape}">
            <input type="hidden" name="msgdel" value="{$msgId|escape}">
            {if $next}
                <input type="hidden" name="msgId" value="{$next|escape}">
            {elseif $prev}
                <input type="hidden" name="msgId" value="{$prev|escape}">
            {else}
                <input type="hidden" name="msgId" value="">
            {/if}
            <input
                type="submit"
                class="btn btn-primary btn-sm float-sm-end"
                name="delete"
                value="{tr}Delete{/tr}"
                onclick="confirmPopup('{tr}Delete sent message?{/tr}')"
            >
        </form>
    </div>
</div>
<div class="row">
    <div class="col-xs-12">
        <div>
            <table.table>
                <tr><td style="font-weight:bold;float: right">{tr}From:{/tr}</td><td style="padding-left: 10px">{$msg.user_from|username}</td></tr>
                <tr><td style="font-weight:bold;float: right">{tr}To:{/tr}</td><td style="padding-left: 10px">{$msg.user_to|escape}</td></tr>
                <tr><td style="font-weight:bold;float: right">{tr}Cc:{/tr}</td><td style="padding-left: 10px">{$msg.user_cc|escape}</td></tr>
                <tr><td style="font-weight:bold;float: right">{tr}Subject:{/tr}</td><td style="padding-left: 10px">{$msg.subject|escape}</td></tr>
                <tr><td style="font-weight:bold;float: right">{tr}Date:{/tr}</td><td style="padding-left: 10px">{$msg.date|tiki_short_datetime}</td></tr>{* date_format:"%a %b %Y [%H:%I]" *}
            </table.table>
        </div>
    </div>
    <div class="col-xs-12">
        <div class="messureadbody p-3">
            {$msg.parsed}
        </div>
    </div>
</div>
{/if}
