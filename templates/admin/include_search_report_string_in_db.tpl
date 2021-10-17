{* $Id$ *}
<h2 class="card-title">{tr}Occurrences of string in database{/tr}</h2>
<div class="adminoptionbox clearfix mb-3 row">
    <label for="string_in_db_search_table" class="col-form-label col-sm-4">
        {tr}Set default table:{/tr}
    </label>
    <div class="col-sm-7">
        <div class="input-group">
            <select name="string_in_db_search_table" class="form-select" id="string_in_db_search_table">
            <option value="">{tr}All tables{/tr}</option>
            <option disabled="disabled">─────{tr}Popular tables{/tr}─────</option>
            {foreach $popularTables as $popularTable}
                <option value="{$popularTable|escape}" {if isset($tableFilter) && $popularTable eq $tableFilter} selected="selected"{/if}>{$popularTable|escape}</option>
            {/foreach}
            <option disabled="disabled">─────{tr}Other tables{/tr}─────</option>
            {foreach $tables as $table}
                <option value="{$table|escape}" {if isset($tableFilter) && $table eq $tableFilter} selected="selected"{/if}>{$table|escape}</option>
            {/foreach}
            </select>
        </div>
    </div>
</div>
<div class="adminoptionbox clearfix mb-3 row">
    <label for="string_in_db_search" class="col-form-label col-sm-4">
        {tr}Text to search:{/tr}
    </label>
    <div class="col-sm-4">
        <div class="input-group">
            <input type="text" id="string_in_db_search" name="string_in_db_search" class="form-control" value="{$searchStringAgain|escape}" />
        </div>
    </div>
    <div class="col-sm-3">
        <div class="input-group">
            <input type="submit" id="string_in_db_search_button" class="btn btn-primary" value="{tr}Search{/tr}" />
        </div>
    </div>
    {jq}
        $('#string_in_db_search_button').click(function () {
            var string_in_db_search = $('#string_in_db_search').val();
            if( string_in_db_search == '')
            {
                $("[name='query']").val('');
            }else{
                $('#redirect').val('0');
            }
        });
        $('#string_in_db_search').keypress(function (e) {
            var key = e.which;
            if(key == 13)  // the enter key code
            {
                $('#string_in_db_search_button').click();
                return false;
            }
        });
    {/jq}
    <input type="hidden" id="redirect" name="redirect" value="1">
</div>

<hr/>
{if isset($errorMsg)}
    <span id="error">{$errorMsg}</span>
{else}
    {if isset($searchString)}
        {remarksbox}{tr}Results for {/tr}<b>{$searchString|escape}</b> {tr}in {if isset($tableFilter)}table <b>{$tableFilter|escape}</b>{else}all tables{/if}:{/tr}{/remarksbox}
        <p>

        <input type="hidden" name="query" value="{$searchString|escape}">
        <input type="hidden" id="table" name="table" value="">
        <input type="hidden" id="column" name="column" value="">

        <table class="string_in_db_search table normal">
        <tr>
        <th>{tr}Table{/tr}</th>
        <th>{tr}Column{/tr}</th>
        <th>{tr}Occurrences{/tr}</th>
        </tr>
        {$last = ''}
        {foreach from=$searchResult item=res}
            {$table = $res['table']}
            {if isset($tableFilter) && $table neq $tableFilter}
                {continue}
            {/if}
            <tr>
            {if $last eq '' || $last neq $table}
                {$span = $tableCount["$table"]}
                <td rowspan="{$span}">{$table|escape}</td>
            {/if}
            <td><input type="submit" class="btn btn-link" value="{$res['column']|escape}" title="{tr}View occurrences{/tr}" onClick="document.getElementById('table').value='{$res['table']}'; document.getElementById('column').value='{$res['column']}'; document.getElementById('redirect').value='0'; document.getElementById('string_in_db_search').value='';"></td>
            <td>{$res['occurrences']|escape}</td>
            </tr>
            {$last = $table}
        {/foreach}
        </table>
        </p>
        <hr>
    {/if}

    {if isset($tableHeaders)}
        {remarksbox}{tr}Results for {/tr}<b>{$searchStringAgain|escape}</b> {tr}in table {/tr} <b>{$tableName}</b>, {tr}column{/tr} <b>{$columnName}</b>:{/remarksbox}
        <table class="table-responsive">
        <tr>
        {foreach from=$tableHeaders item=hdr}
            <th>{if $hdr eq $columnName}<em>{$hdr}</em>{else}{$hdr}{/if}</th>
        {/foreach}
        </tr>

        {foreach from=$tableData item=row}
            <tr>
            {foreach from=$row key=column item=val}
                {$value = $val|truncate:30|escape}
                {if $tableName=='tiki_pages' && ($column=='pageName' || $column=='pageSlug' || $column=='data' || $column=='description') && $val}
                    {if $column=='data'}
                        <td><a tabindex='0' target='_blank' data-bs-trigger='hover' title='{tr}Page preview{/tr}' class="ajaxtips" data-ajaxtips="{service controller='wiki' action='get_page' page=$row['pageName']}">
                                 {$row['snippet']}
                            </a>
                        </td>
                    {else}
                        <td><a href="{$row['pageName']|sefurl:wiki}"  class="link tips" title="{$row['pageName']}: {tr}View page{/tr}" target="_blank">{$value}</a></td>
                        {* TODO: (but see note about object_link in templates/tiki-listpages_content.tpl) <td>{object_link type='wiki page' id={$row['pageName']|escape} class="link tips" title="{$val|escape}:{tr}View page{/tr}"}</td> *}
                    {/if}
                {elseif $column=='lastModif' || $column=='created' || $column=='commentDate' || $column=='publishDate' || $column=='expireDate'}
                    <td>{$value|tiki_short_datetime}</td>
                {elseif $tableName=='tiki_blog_posts' && ($column=='data' || $column=='title')}
                    <td><a href=tiki-view_blog_post.php?postId={$row['postId']} class="link tips" title="{$row['title']|escape}:{tr}View blog post{/tr}" target="_blank">{$value}</a></td>
                {elseif $tableName=='tiki_files' && ($column=='name' || $column=='description' || $column=='filename')}
                    <td><a href=tiki-download_file.php?fileId={$row['fileId']}&display class="link tips" title="{$row['name']|escape}:{tr}View file{/tr}" target="_blank">{$value}</a></td>
                {elseif $tableName=='tiki_file_galleries' && $column=='name'}
                    <td><a href=tiki-list_file_gallery.php?galleryId={$row['galleryId']} class="link tips" title="{$val|escape}:{tr}View gallery{/tr}" target="_blank">{$value}</a></td>
                {elseif $tableName=='tiki_categories' && ($column=='name'|| $column=='description')}
                    <td><a href=tiki-admin_categories.php?parentId={$row['parentId']}&categId={$row['categId']} class="link tips" title="{$row['name']|escape}:{tr}View category{/tr}" target="_blank">{$value}</a></td>
                {elseif $tableName=='tiki_articles' && ($column=='title'|| $column=='heading')}
                    <td><a href=tiki-read_article.php?articleId={$row['articleId']} class="link tips" title="{$row['title']|escape}:{tr}View article{/tr}" target="_blank">{$value}</a></td>
                {elseif $tableName=='tiki_forums' && ($column=='name'|| $column=='description')}
                    <td><a href=tiki-view_forum.php?forumId={$row['forumId']} class="link tips" title="{$row['name']|escape}:{tr}View forum{/tr}" target="_blank">{$value}</a></td>
                {elseif $tableName=='tiki_calendars' && ($column=='name'|| $column=='description')}
                    <td><a href=tiki-calendar.php?calIds[]={$row['calendarId']} class="link tips" title="{$row['name']|escape}:{tr}View calendar{/tr}" target="_blank">{$value}</a></td>
                {elseif $tableName=='tiki_calendar_items' && ($column=='name'|| $column=='description')}
                    <td><a href=tiki-calendar_edit_item.php?viewcalitemId={$row['calitemId']} class="link tips" title="{$row['name']|escape}:{tr}View calendar item{/tr}" target="_blank">{$value}</a></td>
                {elseif $tableName=='tiki_trackers' && ($column=='name'|| $column=='description')}
                    <td><a href=tiki-view_tracker.php?trackerId={$row['trackerId']} class="link tips" title="{$row['name']|escape}:{tr}View tracker{/tr}" target="_blank">{$value}</a></td>
                {elseif $tableName=='tiki_tracker_item_fields' && $column=='value'}
                    <td><a href=tiki-view_tracker_item.php?itemId={$row['itemId']} class="link tips" title="{$row['value']|escape}:{tr}View tracker item{/tr}" target="_blank">{$value}</a></td>
                {elseif $tableName=='tiki_comments'}
                    {if $row['objectType']=='blog post'}
                        {if ($column=='objectType' || $column=='data')}
                            <td><a href=tiki-view_blog_post.php?postId={$row['object']} class="link tips" title="{$row['data']|escape}:{tr}View blog post{/tr}" target="_blank">{$value}</a></td>
                        {else}
                            <td>{$value}</td>
                        {/if}
                    {elseif $row['objectType']=='forum'}
                        {if ($column=='objectType' || $column=='data' || $column=='title')}
                            {if $row['parentId']==0}
                                <td><a href=tiki-view_forum_thread.php?forumId={$row['object']}&comments_parentId={$row['threadId']}#threadId{$row['threadId']} class="link tips" title="{$row['title']|escape:'htmlall'}:{tr}View forum comment{/tr}" target="_blank">{$value}</a></td>
                            {else}
                                <td><a href=tiki-view_forum_thread.php?comments_parentId={$row['parentId']}#threadId{$row['threadId']} class="link tips" title="{$row['title']|escape:'htmlall'}:{tr}View forum comment{/tr}" target="_blank">{$value}</a></td>
                            {/if}
                        {else}
                            <td>{$value}</td>
                        {/if}
                    {elseif $row['objectType']=='article'}
                        {if ($column=='objectType' || $column=='data')}
                            <td><a href=tiki-read_article.php?articleId={$row['object']} class="link tips" title="{$row['data']|escape}:{tr}View article{/tr}" target="_blank">{$value}</a></td>
                        {else}
                            <td>{$value}</td>
                        {/if}
                    {elseif $row['objectType']=='wiki page'}
                        {if ($column=='objectType' || $column=='data' || $column=='object')}
                            <td><a href="{$row['object']|sefurl}?threadId={$row['threadId']|escape:"url"}&comzone=show#threadId{$row['threadId']|escape:"url"}" class="link tips" title="{$row['object']|escape}: {tr}View page{/tr}" target="_blank">{$value}</a></td>
                        {else}
                            <td>{$value}</td>
                        {/if}
                    {else}
                        <td>{$value}</td>
                    {/if}
                {else}
                    <td>{$value}</td>
                {/if}
            {/foreach}
            </tr>
        {/foreach}
        </table>
        <p><a href="javascript:void(0)" class="btn btn-primary btn-sm" title="{tr}Back to first level results{/tr}" onClick="document.getElementById('string_in_db_search').value='{$searchStringAgain|escape}'; document.getElementById('string_in_db_search_button').click();">{tr}Back to first level results{/tr}</a></p>
    {/if}
{/if}
