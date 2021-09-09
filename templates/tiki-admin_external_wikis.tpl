{title help="External Wikis"}{tr}Admin External Wikis{/tr}{/title}

<h2>{tr}Create/Edit External Wiki{/tr}</h2>
<form action="tiki-admin_external_wikis.php" method="post" role="form">
    {ticket}
    <input type="hidden" name="extwikiId" value="{$extwikiId|escape}">
    <div class="mb-3 row">
        <label for="name" class="col-sm-3 col-form-label">{tr}Name{/tr}</label>
        <div class="col-sm-9">
            <input type="text" maxlength="255" class="form-control" name="name" value="{$info.name|escape}">
        </div>
    </div>
    <div class="mb-3 row">
        <label for="extwiki" class="col-sm-3 col-form-label">{tr}URL{/tr}</label>
        <div class="col-sm-9">
            <input type="text" maxlength="255" class="form-control" name="extwiki" id="extwiki" value="{$info.extwiki|escape}">
            <p class="form-text">{tr}URL (use $page to be replaced by the page name in the URL example: http://www.example.com/tiki-index.php?page=$page):{/tr}</p>
        </div>
    </div>
    <div class="mb-3 row">
        <label for="indexname" class="col-sm-3 col-form-label">{tr}Index{/tr}</label>
        <div class="col-sm-9">
            <input type="text" maxlength="255" class="form-control" name="indexname" id="indexname" value="{$info.indexname|escape}">
            <p class="form-text">{tr}<em>[prefix]</em>main, such as tiki_main{/tr}</p>
        </div>
    </div>
    <div class="mb-3 row">
        <label for="groups" class="col-sm-3 col-form-label">{tr}Search as{/tr}</label>
        <div class="col-sm-9">
            {object_selector_multi _simplename=groups _simpleid=groups _simplevalue=$info.groups type="group" _separator=";"}
            <p class="form-text">{tr}Leave blank to search using currently active groups.{/tr}</p>
        </div>
    </div>
    <div class="mb-3 text-center">
        <input type="submit" class="btn btn-primary" name="save" value="{tr}Save{/tr}">
    </div>
</form>

<h2>{tr}External Wiki{/tr}</h2>
<div class="{if $js}table-responsive{/if}"> {* table-responsive class cuts off css drop-down menus *}
    <table class="table table-striped table-hover">
        <tr>
            <th>
                <a href="tiki-admin_external_wikis.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'name_desc'}name_asc{else}name_desc{/if}">{tr}Name{/tr}</a>
            </th>
            <th>
                <a href="tiki-admin_external_wikis.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'extwiki_desc'}extwiki_asc{else}extwiki_desc{/if}">{tr}ExtWiki{/tr}</a>
            </th>
            <th>
                <a href="tiki-admin_external_wikis.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'indexname_desc'}indexname_asc{else}indexname_desc{/if}">{tr}Index{/tr}</a>
            </th>
            <th>
                <a href="tiki-admin_external_wikis.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'groups_desc'}groups_asc{else}groups_desc{/if}">{tr}Search As{/tr}</a>
            </th>
            <th></th>
        </tr>

        {section name=user loop=$channels}
            <tr>
                <td class="text">{$channels[user].name}</td>
                <td class="text">{$channels[user].extwiki}</td>
                <td class="text">{$channels[user].indexname}</td>
                <td class="text">{$channels[user].groups}</td>
                <td class="action">
                    {actions}
                        {strip}
                            <action>
                                <a href="tiki-admin_external_wikis.php?offset={$offset}&amp;sort_mode={$sort_mode}&amp;extwikiId={$channels[user].extwikiId}">
                                    {icon name='edit' _menu_text='y' _menu_icon='y' alt="{tr}Edit{/tr}"}
                                </a>
                            </action>
                            <action>
                                <a href="tiki-admin_external_wikis.php?offset={$offset}&amp;sort_mode={$sort_mode}&amp;remove={$channels[user].extwikiId}" onclick="confirmPopup('{tr}Remove external wiki?{/tr}', '{ticket mode=get}')">
                                    {icon name='remove' _menu_text='y' _menu_icon='y' alt="{tr}Remove{/tr}"}
                                </a>
                            </action>
                        {/strip}
                    {/actions}
                </td>
            </tr>
        {sectionelse}
            {norecords _colspan=5}
        {/section}
    </table>
</div>

{pagination_links cant=$cant_pages step=$prefs.maxRecords offset=$offset}{/pagination_links}
