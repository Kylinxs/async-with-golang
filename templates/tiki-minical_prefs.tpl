
{title help="User Calendar"}{tr}Mini Calendar: Preferences{/tr}{/title}

{include file='tiki-mytiki_bar.tpl'}

<div class="t_navbar mb-4">
    {button href="tiki-minical.php#add" class="btn btn-primary" _text="{tr}Add{/tr} "}
    {button href="tiki-minical_prefs.php" class="btn btn-info" _text="{tr}Prefs{/tr}"}
    {button href="tiki-minical.php?view=daily" class="btn btn-info" _text="{tr}Daily{/tr}"}
    {button href="tiki-minical.php?view=weekly" class="btn btn-info" _text="{tr}Weekly{/tr}"}
    {button href="tiki-minical.php?view=list" class="btn btn-info" _text="{tr}List{/tr}"}
</div>

<h2>{tr}Preferences{/tr}</h2>
<form action="tiki-minical_prefs.php" method="post">
    <div class="mb-3 row">
        <label class="col-sm-3 col-form-label">{tr}Calendar Interval in daily view{/tr}</label>
        <div class="col-sm-7">
            <select name="minical_interval" class="form-control">
                <option value="300" {if $minical_interval eq 300}selected="selected"{/if}>5 {tr}minutes{/tr}</option>
                <option value="600" {if $minical_interval eq 600}selected="selected"{/if}>10 {tr}minutes{/tr}</option>
                <option value="900" {if $minical_interval eq 900}selected="selected"{/if}>15 {tr}minutes{/tr}</option>
                <option value="1800" {if $minical_interval eq 1800}selected="selected"{/if}>30 {tr}minutes{/tr}</option>
                <option value="3600" {if $minical_interval eq 3600}selected="selected"{/if}>1 {tr}hour{/tr}</option>
            </select>
        </div>
    </div>
    <div class="mb-3 row">
        <label class="col-sm-3 col-form-label">{tr}Start hour for days{/tr}</label>
        <div class="col-sm-7">
            <select name="minical_start_hour" class="form-control">
                {html_options output=$hours values=$hours selected=$minical_start_hour}
            </select>
        </div>
    </div>
    <div class="mb-3 row">
        <label class="col-sm-3 col-form-label">{tr}End hour for days{/tr}</label>
        <div class="col-sm-7">
            <select name="minical_end_hour" class="form-control">
                {html_options output=$hours values=$hours selected=$minical_end_hour}
            </select>
        </div>
    </div>
    <div class="mb-3 row">
        <label class="col-sm-3 col-form-label">{tr}Upcoming Events{/tr}</label>
        <div class="col-sm-7">
            <select name="minical_upcoming" class="form-control">
                {html_options output=$upcoming values=$upcoming selected=$minical_upcoming}
            </select>
        </div>
    </div>
    <div class="mb-3 row">
        <label class="col-sm-3 col-form-label">{tr}Reminders{/tr}</label>
        <div class="col-sm-7">
            <select name="minical_reminders" class="form-control">
                <option value="0" {if $prefs.minical_reminders eq 0}selected="selected"{/if}>{tr}no reminders{/tr}</option>
                <option value="60" {if $prefs.minical_reminders eq 60}selected="selected"{/if}>1 {tr}min{/tr}</option>
                <option value="120" {if $prefs.minical_reminders eq 120}selected="selected"{/if}>2 {tr}min{/tr}</option>
                <option value="300" {if $prefs.minical_reminders eq 300}selected="selected"{/if}>5 {tr}min{/tr}</option>
                <option value="600" {if $prefs.minical_reminders eq 600}selected="selected"{/if}>10 {tr}min{/tr}</option>
                <option value="900" {if $prefs.minical_reminders eq 900}selected="selected"{/if}>15 {tr}min{/tr}</option>
            </select>
        </div>
    </div>
    <div class="mb-3 row">
        <label class="col-sm-3 col-form-label"></label>
        <div class="col-sm-7">
            <input type="submit" class="btn btn-primary" name="save" value="{tr}Save{/tr}">
        </div>
    </div>
</form>
<a id="import"></a>
<h2>{tr}Import CSV file{/tr}</h2>
<form enctype="multipart/form-data" action="tiki-minical_prefs.php" method="post">
    <div class="mb-3 row">
        <label class="col-sm-3 col-form-label">{tr}Upload file{/tr}</label>
        <div class="col-sm-7">
            <input type="hidden" name="MAX_FILE_SIZE" value="10000000000000">
            <input size="16" name="userfile1" type="file">
        </div>
    </div>
    <div class="mb-3 row">
        <label class="col-sm-3 col-form-label"></label>
        <div class="col-sm-7">
            <input type="submit" class="btn btn-primary btn-sm" name="import" value="{tr}import{/tr}">
        </div>
    </div>
</form>

<h2>{tr}Admin Topics{/tr}</h2>
<form enctype="multipart/form-data" action="tiki-minical_prefs.php" method="post">
    <div class="mb-3 row">
        <label class="col-sm-3 col-form-label">{tr}Name:{/tr}</label>
        <div class="col-sm-7">
            <input type="text" name="name" class="form-control">
        </div>
    </div>
    <div class="mb-3 row">
        <label class="col-sm-3 col-form-label">{tr}Upload file:{/tr}</label>
        <div class="col-sm-7">
            <input type="hidden" name="MAX_FILE_SIZE" value="10000000000000" /><input size="16" name="userfile1" type="file">
        </div>
    </div>
    <div class="mb-3 row">
        <label class="col-sm-3 col-form-label">{tr}Or enter path or URL:{/tr}</label>
        <div class="col-sm-7">
            <input type="text" name="path" class="form-control">
        </div>
    </div>
    <div class="mb-3 row">
        <label class="col-sm-3 col-form-label"></label>
        <div class="col-sm-7">
            <input type="submit" class="btn btn-primary btn-sm" name="addtopic" value="{tr}Add Topic{/tr}">
        </div>
    </div>
</form>
{if count($topics) > 0}
    <div class="card"><div class="card-body">
        <table>
            <tr>
                {section name=numloop loop=$topics}
                    <td>
                        {if $topics[numloop].isIcon eq 'y'}
                            <img src="{$topics[numloop].path}" alt="{tr}topic image{/tr}" class="me-2">
                        {else}
                            <img src="tiki-view_minical_topic.php?topicId={$topics[numloop].topicId}" alt="{tr}topic image{/tr}" class="me-2">
                        {/if}
                        {$topics[numloop].name}
                        <a class="close btn-close ms-2" title="{tr}Delete{/tr}" aria-label="Delete" href="tiki-minical_prefs.php?removetopic={$topics[numloop].topicId}"></a>
                    </td>
                    {* see if we should go to the next row *}
                    {if not ($smarty.section.numloop.rownum mod $cols)}
                        {if not $smarty.section.numloop.last}
                            </tr>
                            <tr>
                        {/if}
                    {/if}
                    {if !empty($smarty.section.numloop.last)}
                        {* pad the cells not yet created *}
                        {math equation = "n - a % n" n=$cols a=$data|@count assign="cells"}
                        {if $cells ne $cols}
                            {section name=pad loop=$cells}
                                <td>&nbsp;</td>
                            {/section}
                        {/if}
                        </tr>
                    {/if}
                {/section}
            </table>
        </div>
    </div>
{/if}