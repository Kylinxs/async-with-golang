<h1 class="pagetitle"><a href="tiki-admin_keywords.php">{tr}Admin keywords{/tr}</a></h1>

{if $edit_on}
    <div id="current_keywords" class="clearfix">
        <h2>{tr}Edit page keywords{/tr} ({$edit_keywords_page|escape})</h2>
        <form action="tiki-admin_keywords.php" method="post">
            {ticket}
            <input name="page" value="{$edit_keywords_page|escape}" type="hidden">
            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label">{tr}Keywords{/tr}</label>
                <div class="input-group col-sm-7 offset-sm-1 mb-3">
                    <input name="new_keywords" size="65" value="{$edit_keywords|escape}" class="form-control">
                    <input type="submit" class="btn btn-primary" name="save_keywords" value="{tr}Save{/tr}">
                </div>
            </div>
        </form>
    </div>
{/if}

<h2>{tr}Current Page Keywords{/tr}</h2>
<form method="get" action="tiki-admin_keywords.php">
    <div class="mb-3 row">
        <label class="col-sm-3 col-form-label">{tr}Search by page:{/tr}</label>
        <div class="input-group col-sm-7 offset-sm-1 mb-3">
            <input type="text" name="q" value="{if !empty($smarty.request.q)}{$smarty.request.q|escape}{/if}" class="form-control">
            <input type="submit" class="btn btn-primary" name="search" value="{tr}Go{/tr}">
        </div>
    </div>
</form>
{if $search_on}
    <strong>{$search_cant|escape} {tr}results found!{/tr}</strong>
{/if}

{if $existing_keywords}
    <div class="{if $js}table-responsive{/if}"> {* table-responsive class cuts off css drop-down menus *}
        <table class="table table-striped table-hover">
            <tbody>
                <tr>
                    <th>{tr}Page{/tr}</th>
                    <th>{tr}Keywords{/tr}</th>
                    <th></th>
                </tr>

                {section name=i loop=$existing_keywords}
                    <tr>
                        <td class="text"><a href="{$existing_keywords[i].page|sefurl}">{$existing_keywords[i].page|escape}</a></td>
                        <td class="text">{$existing_keywords[i].keywords|escape}</td>
                        <td class="action">
                            {actions}
                                {strip}
                                    <action>
                                        <a href="tiki-admin_keywords.php?page={$existing_keywords[i].page|escape:"url"}">
                                            {icon name='edit' _menu_text='y' _menu_icon='y' alt="{tr}Edit{/tr}"}
                                        </a>
                                    </action>
                                    <action>
                                        <form action="tiki-admin_keywords.php" method="post">
                                            {ticket}
                                            <input type="hidden" name="page" value="{$existing_keywords[i].page|escape:'attr'}">
                                            <button
                                                type="submit"
                                                name="remove_keywords"
                                                value="1"
                                                class="btn btn-link link-list"
                                                onclick="confirmPopup('{tr}Remove keywords for this page?{/tr}')"
                                            >
                                                {icon name='remove'} {tr}Remove{/tr}
                                            </button>
                                        </form>
                                    </action>
                                {/strip}
                            {/actions}
                        </td>
                    </tr>
                {/section}
            </tbody>
        </table>
    </div>
{else}
    <h2>{tr}No pages found{/tr}</h2>
{/if}

{pagination_links cant=$pages_cant step=$prefs.maxRecords offset=$offset}{/pagination_links}
