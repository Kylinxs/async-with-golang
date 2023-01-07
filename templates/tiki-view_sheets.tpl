{* $Id$ *}
{title help="Spreadsheet"}{$title}{/title}

<div class="description form-text">
    {$description|escape}
</div>
<div class="msg">
    {$msg}
</div>

{if $editconflict eq 'y' and $objectperms->edit_sheet}
    {remarksbox title='Edit Conflict Warning' type='warning'}
        {tr _0=$semUser|username}This sheet is already being edited by %0{/tr}
    {/remarksbox}
{/if}

{foreach from=$grid_content item=thisGrid}
    <div class="tiki_sheet"{if !empty($tiki_sheet_div_style)} style="{$tiki_sheet_div_style}"{/if}>
        {$thisGrid}
    </div>
{/foreach}


<div id="feedback" style="height: 1.5em; margin-left: .2em"><span></span></div>

<div class="t_navbar btn-group mb-3">
    <div>
        {if $page}
            {button href="tiki-index.php" page="$page" _class="btn btn-primary me-1" _text="{tr}Back to Page{/tr}"}
        {/if}

        {if $tiki_p_view_sheet eq 'y' || $tiki_p_admin eq 'y'}
            {button href="tiki-sheets.php" _class="btn btn-info me-1" _text="{tr}List Spreadsheets{/tr}"}
        {/if}
    </div>

    {if $objectperms->edit_sheet}
        {jq notonready=true}var editSheetButtonLabel2="{tr}Cancel{/tr}";{/jq}
    {/if}

    <span id="saveState">
        {if $objectperms->edit_sheet}
            {button _id="save_but