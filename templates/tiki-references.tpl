{* $Id$ *}
{title help="References" admpage="wiki" url="tiki-references.php"}{tr}References{/tr}{/title}
<div class="t_navbar mb-4">
    {if isset($referenceinfo.ref_id)}
        {button href="?add=1" class="btn btn-primary" _text="{tr}Add a new library reference{/tr}"}
    {/if}
</div>
{tabset name='tabs_admin_references'}

    {* ---------------------- tab with list -------------------- *}
{if $references|count > 0}
    {tab name="{tr}References{/tr}"}
        <h2>{tr}References{/tr}</h2>
        <form method="get" class="small" action="tiki-references.php">
            <div class="mb-3 row">
                <label class="col-form-label col-sm-4" for="find">{tr}Find{/tr}</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control form-control-sm" id="find" name="find" value="{$find|escape}">
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-form-label col-sm-4" for="numrows">{tr}Number of displayed rows{/tr}</label>
                <div class="col-sm-8">
                    <input class="form-control form-control-sm" type="number" id="maxRecords" name="maxRecords" value="{$maxRecords|escape}">
                </div>
            </div>
            <div class="mb-3 row">
                <div class="col-sm-8 offset-sm-4">
                    <input type="submit" class="btn btn-primary btn-sm" value="{tr}Find{/tr}" name="search">
                </div>
            </div>
        </form>
        <div id="admin_references-div">
            <div class="{if $js}table-responsive {/if}ts-wrapperdiv">
                <table id="admin_references" class="table normal table-striped table-hover" data-count="{$references|count}">
                    <thead>
                    <tr>
                        <th>
                            {tr}Biblio Code{/tr}
                        </th>
                        <th>
                            {tr}Author{/tr}
                        </th>
                        <th>
                            {tr}Year{/tr}
                        </th>
                        <th>
                            {tr}Title{/tr}
                        </th>
                        <th id="actions"></th>
                    </tr>
                    </thead>
                    <tbody>
                    {section name=reference loop=$references}
                        {$reference_code = $references[reference].biblio_code|escape}
                        <tr>
                            <td class="reference_code">
                                <a class="link tips" href="tiki-references.php?referenceId={$references[reference].ref_id}&details=1{if $prefs.feature_tabs ne 'y'}#tab2{/if}" title="{$reference_code}:{tr}Edit reference settings{/tr}">
                                    {$reference_code}
                                </a>
                            </td>
                            <td class="reference_author">
                                {$references[reference].author|truncate:60|escape}
                            </td>
                            <td class="reference_year">
                                {$references[reference].year|escape}
                            </td>
                            <td class="reference_title">
                                {$references[reference].title|truncate:60|escape}
                            </td>
                            <td class="action">
                                {actions}
                                    {strip}
                                        <action>
                                            <a href="{query _noauto='y' _type='relative' referenceId=$references[reference].ref_id details='1'}">
                                                {icon name="edit" _menu_text='y' _menu_icon='y' alt="{tr}Edit{/tr}"}
                                            </a>
                                        </action>
                                        <action>
                                            <a href="{query _noauto='y' _type='relative' referenceId=$references[reference].ref_id usage='1'}">
                                                {icon name="link" _menu_text='y' _menu_icon='y' alt="{tr}Reference usage{/tr}"}
                                            </a>
                                        </action>
                                        <action>
                                            <a href="{query _noauto='y' _type='relative' referenceId=$references[reference].ref_id action=delete}" onclick="confirmPopup('{tr}Delete reference?{/tr}', '{ticket mode=get}')">
                                                {icon name="remove" _menu_text='y' _menu_icon='y' alt="{tr}Delete{/tr}"}
                                            </a>
                                        </action>
                                    {/strip}
                                {/actions}
                            </td>
                        </tr>
                    {/section}
                    </tbody>
                </table>
            </div>
        </div>
    {pagination_links cant=$cant step=$maxRecords offset=$offset}
        tiki-references.php?find={$find}&maxRecords={$maxRecords}
    {/pagination_links}
    {/tab}
{/if}
    {* ---------------------- tab with form -------------------- *}
    <a id="tab2"></a>
{if isset($referenceinfo.ref_id) && $referenceinfo.ref_id}
    {$add_edit_reference_tablabel = "{tr}Edit reference{/tr}"}
    {$schedulename = "<i>{$referenceinfo.biblio_code|escape}</i>"}
{else}
    {$add_edit_reference_tablabel = "{tr}Add a new library reference{/tr}"}
    {$schedulename = ""}
{/if}

{tab name="{$add_edit_reference_tablabel} {$schedulename}"}
    <br>
    <br>
{if isset($referenceinfo.id) && $referenceinfo.ref_id}
    <div class="row">
        <div class="offset-md-2 col-md-6">
            {remarksbox type="note" title="{tr}Information{/tr}"}
            {tr}If you change the value of Biblio Code, you might loose the link between references{/tr}
            {/remarksbox}
        </div>
    </div>
{/if}
    <form class="form" action="tiki-references.php" method="post" enctype="multipart/form-data" id="references-edit-form" name="RegForm" autocomplete="off">
        {ticket}
        {if empty($referenceinfo.biblio_code)}
            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label" for="add_ref_auto_biblio_code">{tr}Auto generate Biblio Code:{/tr}</label>
                <div class="col-sm-10">
                    <input type="checkbox" class="form-check wikiedit" name="ref_auto_biblio_code" id="add_ref_auto_biblio_code" checked="checked" />
                </div>
            </div>
        {/if}
        <div class="mb-3 row" id="ref_biblio_code_block" {if empty($referenceinfo.biblio_code)}style="display: none;"{/if}>
            <label class="col-sm-3 col-md-2 col-form-label" for="ref_biblio_code">{tr}Biblio Code{/tr}</label>
            <div class="col-sm-7 col-md-6">
                <input type="text" id='ref_biblio_code' class="form-control" name='ref_biblio_code' value="{$referenceinfo.biblio_code|escape}">
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-sm-3 col-md-2 col-form-label" for="ref_author">{tr}Author{/tr}</label>
            <div class="col-sm-7 col-md-6">
                <input type="text" id='ref_author' class="form-control" name='ref_author' value="{$referenceinfo.author|escape}">
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-sm-3 col-md-2 col-form-label" for="ref_title">{tr}Title{/tr}</label>
            <div class="col-sm-7 col-md-6">
                <input type="text" id='ref_title' class="form-control" name='ref_title' value="{$referenceinfo.title|escape}">
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-sm-3 col-md-2 col-form-label" for="ref_title">{tr}Year{/tr}</label>
            <div class="col-sm-7 col-md-6">
                <input type="text" id='ref_year' class="form-control" name='ref_year' value="{$referenceinfo.year|escape}">
            </div>
        </div>
        <div class="mb-3 row">
    