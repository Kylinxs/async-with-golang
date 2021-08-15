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
            <label class="col-sm-3 col-md-2 col-form-label" for="ref_title">{tr}Part{/tr}</label>
            <div class="col-sm-7 col-md-6">
                <input type="text" id='ref_part' class="form-control" name='ref_part' value="{$referenceinfo.part|escape}">
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-sm-3 col-md-2 col-form-label" for="ref_title">{tr}URI{/tr}</label>
            <div class="col-sm-7 col-md-6">
                <input type="text" id='ref_uri' class="form-control" name='ref_uri' value="{$referenceinfo.uri|escape}">
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-sm-3 col-md-2 col-form-label" for="ref_title">{tr}Code{/tr}</label>
            <div class="col-sm-7 col-md-6">
                <input type="text" id='ref_code' class="form-control" name='ref_code' value="{$referenceinfo.code|escape}">
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-sm-3 col-md-2 col-form-label" for="ref_title">{tr}Publisher{/tr}</label>
            <div class="col-sm-7 col-md-6">
                <input type="text" id='ref_publisher' class="form-control" name='ref_publisher' value="{$referenceinfo.publisher|escape}">
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-sm-3 col-md-2 col-form-label" for="ref_title">{tr}Location{/tr}</label>
            <div class="col-sm-7 col-md-6">
                <input type="text" id='ref_location' class="form-control" name='ref_location' value="{$referenceinfo.location|escape}">
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-sm-3 col-md-2 col-form-label" for="ref_title">{tr}Style{/tr}</label>
            <div class="col-sm-7 col-md-6">
                <input type="text" id='ref_style' class="form-control" name='ref_style' value="{$referenceinfo.style|escape}">
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-sm-3 col-md-2 col-form-label" for="ref_title">{tr}Template{/tr}</label>
            <div class="col-sm-7 col-md-6">
                <input type="text" id='ref_template' class="form-control" name='ref_template' value="{$referenceinfo.template|escape}">
            </div>
        </div>
        <div class="mb-3 row">
            <div class="col-sm-7 col-md-6 offset-sm-3 offset-md-2">
                {if isset($referenceinfo.ref_id) && $referenceinfo.ref_id}
                    <input type="hidden" name="referenceId" value="{$referenceinfo.ref_id|escape}">
                    <input type="hidden" name="editreference" value="1">
                    <input type="submit" class="btn btn-primary" name="save" value="{tr}Save{/tr}">
                {else}
                    <input type="submit" class="btn btn-primary" name="addreference" value="{tr}Add{/tr}">
                {/if}
            </div>
        </div>
    </form>
{/tab}
    <a id="tab3"></a>
{if isset($referenceinfo.ref_id) && $referenceinfo.ref_id}
    {tab name="{tr}Reference usage{/tr}"}
        <h2>{tr _0=$referenceinfo.biblio_code|escape}Pages using reference %0{/tr}</h2>
        <table class="table normal table-striped table-hover">
            <thead>
            <tr>
                <th>Page Name</th>
            </tr>
            </thead>
            <tbody>
            {section name=page loop=$pagereferences}
                <tr>
                    <td>
                        <a href="{$pagereferences[page].pageName|sefurl}" class="link tips" title="{$pagereferences[page].pageName|escape}:{tr}View page{/tr}">
                            {$pagereferences[page].pageName|truncate:$prefs.wiki_list_name_len:"...":true|escape}
                        </a>
                    </td>
                </tr>
            {/section}
            </tbody>
        </table>
    {/tab}
{/if}
{/tabset}
{if empty($referenceinfo.biblio_code)}
    {jq}
        $('#add_ref_auto_biblio_code').click(function(){
        if ($('#add_ref_auto_biblio_code').is(':checked')) {
        $('#ref_biblio_code_block').hide();
        $('#ref_biblio_code').val('');
        } else {
        $('#ref_biblio_code_block').show();
        }
        });
    {/jq}
{/if}
{jq}
    $('#references-edit-form').submit(function(event){
    var ck_code = /^[A-Za-z0-9]+$/;
    {* var ck_uri = /^((https?|ftp|smtp):\/\/)?(www.)?[a-z0-9]+(\.[a-z]{2, }){1, 3}(#?\/?[a-zA-Z0-9#]+)*\/?(\?[a-zA-Z0-9-_]+=[a-zA-Z0-9-%]+&?)?$/; *}
    var ck_year = /^[1-2][0-9][0-9][0-9]$/;
    if (!$('#add_ref_auto_biblio_code').is(':checked') && $('#ref_biblio_code').val() == '') {
    alert('Please fill the biblio code field or enable biblio code auto generator');
    return false;
    }
    if(!$('#add_ref_auto_biblio_code').is(':checked') && !ck_code.test($('#ref_biblio_code').val())){
    alert('Biblio code is not valid');
    return false;
    }
    {* if(!$('#add_ref_uri').val() == '' &&  !ck_uri.test($('#add_ref_uri').val())){
        alert('uri no valid');
        return false;
    } *}
    if(!$('#ref_author').val().trim()){
    alert('Author is not valid');
    return false;
    }
    if(!$('#ref_year').val() == '' && !ck_year.test($('#ref_year').val())){
    alert('Year is not valid');
    return false;
    }
    return true;
    })
{/jq}
