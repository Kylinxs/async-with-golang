
{title help="Toolbars"}{tr}Admin Toolbars{/tr}{/title}

<div class="toolbars-admin clearfix">
    <form name="toolbars" method="post" class="pb-4" action="tiki-admin_toolbars.php" onsubmit="return saveRows()">
        <div class="adminoptionbox mb-3 row">
            <label for="section" class="col-form-label col-sm-4">
                {tr}Section{/tr}
            </label>
            <div class="col-sm-8">
                <select id="section" name="section" onchange="$(this).form().tikiModal(tr('Loading...')).submit();" class="form-select">
                    {foreach from=$sections item=name key=skey}
                        <option value="{$skey}"{if $skey eq $loaded} selected="selected"{/if}>{$name|escape}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="adminoptionbox mb-3 row">
            <label for="comments" class="form-check-label col-sm-4">
                {tr}Comments{/tr}
            </label>
            <div class="col-sm-8">
                <input id="comments" name="comments" type="checkbox" class="form-check-input" onchange="$(this).form().tikiModal(tr('Loading...')).submit();" {if $comments eq 'on'}checked="checked" {/if}>
            </div>
        </div>
        <div class="adminoptionbox mb-3 row">
            <label for="view_mode" class="col-form-label col-sm-4">
                {tr}View mode{/tr}
            </label>
            <div class="col-sm-8">
                <select id="view_mode" name="view_mode" class="form-select">
                    {if $prefs.feature_wysiwyg eq 'y'}
                        <option value="both"{if $view_mode eq "both"} selected{/if}>
                        {tr}Wiki and WYSIWYG{/tr}
                        </option>
                    {/if}
                    <option value="wiki"{if $view_mode eq "wiki"} selected{/if}>
                        {tr}Wiki only{/tr}
                    </option>
                    {if $prefs.feature_wysiwyg eq 'y'}
                        <option value="wysiwyg"{if $view_mode eq "wysiwyg"} selected{/if}>
                            {tr}WYSIWYG (HTML mode){/tr}
                        </option>
                    {/if}
                    {if $prefs.feature_wysiwyg eq 'y' and $prefs.wysiwyg_htmltowiki eq 'y'}
                        <option value="wysiwyg_wiki"{if $view_mode eq "wysiwyg_wiki"} selected{/if}>
                            {tr}WYSIWYG (wiki mode){/tr}
                        </option>
                    {/if}
                    {if $prefs.feature_sheet eq 'y'}
                        <option value="sheet"{if $view_mode eq "sheet"} selected{/if}>
                            {tr}Spreadsheet{/tr}
                        </option>
                    {/if}
                </select>
            </div>
        </div>
        <div class="adminoptionbox mb-3 row">
            <div class="offset-sm-4 col-sm-8">
                <input type="submit" class="btn btn-primary" name="save" value="{tr}Save{/tr}">
                {if $loaded neq 'global' and $not_global}<input type="submit" class="btn btn-secondary" name="reset" value="{tr}Reset to Global{/tr}">{/if}
                {if $loaded eq 'global' and $not_default}<input type="submit" class="btn btn-secondary" name="reset_global" value="{tr}Reset to defaults{/tr}">{/if}
            </div>
        </div>
        <input id="qt-form-field" type="hidden" name="pref" value="">
    </form>

    <div class="row mx-0 pb-4">
        <div class="rows textarea-toolbar col-sm-12">
            {foreach from=$current item=line name=line}
                <ul id="row-{$smarty.foreach.line.iteration|escape}" class="navbar card d-flex flex-row justify-content-start p-1 mb-3">
                {foreach from=$line item=bit name=bit}
                    {foreach from=$bit item=tool name=tool}
                        {if !empty($qtelement[$tool].class)}
                            <li class="navbar-text {$qtelement[$tool].class} d-flex" {if $smarty.foreach.bit.index eq 1}style="float:right;"{/if}{if not $qtelement[$tool].visible} style="display:none"{/if}>
                                {$qtelement[$tool].html}
                            </li>
                        {/if}
                    {/foreach}
                {/foreach}
                {if $smarty.foreach.line.last and $rowCount gt 1}
                    {assign var=total value=$smarty.foreach.line.total+1}
                    </ul>
                    <label for="row-{$total|escape}">{tr}Row{/tr}&nbsp;{$total}</label>
                    <ul id="row-{$total|escape}" class="navbar card d-flex flex-row justify-content-start p-1 mb-3">
                {/if}
                </ul>
            {/foreach}
        </div>
    </div>

    <div class="row mx-0 pb-4">
        <div class="lists col-sm-4">
            <label for="full-list-w">{tr}Formatting Tools{/tr}</label>
            <ul id="full-list-w" class="full">
            {foreach from=$display_w item=tool}
                <li title=":{$qtelement[$tool].label|escape}" class="tips {$qtelement[$tool].class}">{$qtelement[$tool].html}</li>
            {/foreach}
            </ul>
        </div>
        <div class="lists col-sm-4">
            <label for="full-list-p">{tr}Plugin Tools{/tr}</label>
            <ul id="full-list-p" class="full">
            {foreach from=$display_p item=tool}
                <li title=":{$qtelement[$tool].label|escape}" class="tips {$qtelement[$tool].class}">{$qtelement[$tool].html}</li>
            {/foreach}
            </ul>
        </div>
        <div class="lists col-sm-4">
            <div id="toolbar_edit_div" class="p-4" style="display:none">
                <div class="modal-header">
                    {tr}Edit tool{/tr}
                </div>
                <form name="toolbar_edit_form" method="post" action="tiki-admin_toolbars.php" class="p-2">
                    <div class="modal-body">
                        <fieldset>
                            <div class="mb-3">
                                <label for="tool_name">{tr}Name:{/tr}</label>
                                <input type="text" name="tool_name" id="tool_name" class="form-control" minlength="2" maxlength="16">
                            </div>
                            <div class="mb-3">
                                <label for="tool_label">{tr}Label:{/tr}</label>
                                <input type="text" name="tool_label" id="tool_label" class="form-control" minlength="1" maxlength="80">
                            </div>
                            <div class="mb-3">
                                <label for="tool_icon">{tr}Icon:{/tr}</label>
                                <input type="text" name="tool_icon" id="tool_icon" class="form-control" placeholder="{tr}Search...{/tr}">
                            </div>
                            <div class="mb-3">
                                <label for="tool_type">{tr}Type:{/tr}</label>
                                <select name="tool_type" id="tool_type" class="form-control noselect2">
                                    <option value="Inline">Inline</option>
                                    <option value="Block">Block</option>
                                    <option value="LineBased">LineBased</option>
                                    <option value="Picker">Picker</option>
                                    <option value="Separator">Separator</option>
                                    {if $prefs.feature_wysiwyg eq 'y'}
                                        <option value="FckOnly">FckOnly</option>
                                    {/if}
                                    <option value="Fullscreen">Fullscreen</option>
                                    <option value="TextareaResize">TextareaResize</option>
                                    <option value="Helptool">Helptool</option>
                                    <option value="FileGallery">FileGallery</option>
                                    <option value="Wikiplugin">Wikiplugin</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="tool_syntax">{tr}Syntax:{/tr}</label>
                                <input type="text" name="tool_syntax" id="tool_syntax" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="tool_plugin">{tr}Plugin name:{/tr}</label>
                                <select name="tool_plugin" id="tool_plugin" class="form-control mb-2 noselect2">
                                    <option value="">{tr}None{/tr}</option>
                                    {foreach from=$plugins key=plugin item=info}
                                        <option value="{$plugin|escape}">{$info.name|escape}</option>
                                    {/foreach}
                                </select>
                            </div>
                            {if $prefs.feature_wysiwyg eq 'y'}
                                <div class="mb-3">
                                    <label for="tool_token">{tr}Wysiwyg Token:{/tr}</label>
                                    <input type="text" name="tool_token" id="tool_token" class="form-control" placeholder="{tr}Search...{/tr}">
                                    <div class="d-none">
                                        {* hidden ckeditor to laod the list of commands for the Token field options *}
                                        {textarea id='cked' wysiwyg='y'}{/textarea}
                                    </div>
                                </div>
                            {/if}
                            <input type="hidden" value="" name="save_tool" id="save_tool">
                            <input type="hidden" value="" name="delete_tool" id="delete_tool">
                            <input type="hidden" name="section" value="{$loaded}">
                            <input type="hidden" name="comments" value="{if $show_comments}on{/if}">
                        </fieldset>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger delete">Delete</button>
                        <button type="submit" class="btn btn-primary save">Save</button>
                    </div>
                </form>
            </div>
            <label for="full-list-c">{tr}Custom Tools{/tr}</label>
            <a href="#" id="toolbar_add_custom">{icon name="add" ititle=":{tr}Add a new custom tool{/tr}" iclass="tips"}</a>
            <ul id="full-list-c" class="full">
            {foreach from=$display_c item=tool}
                <li title=":{$qtelement[$tool].label|escape}" class="tips {$qtelement[$tool].class}">{$qtelement[$tool].html}</li>
            {/foreach}
            </ul>
        </div>
    </div>
</div>

<div class="clearfix">
{remarksbox title="{tr}Tips{/tr}"}
{tr}To configure the toolbars on the various text editing areas select the section, and optionally check the comments checkbox, you want to edit and drag the icons from the left hand box to the toolbars on the right.<br>
Drag icons back from the toolbar rows onto the full list to remove them.<br>
Icons with <strong>bold</strong> labels are for wiki text areas, those that are <em>italic</em> are for WYSIWYG mode, and those that are <strong><em>bold and italic</em></strong> are for both.<br>
To save the current set use the dropdown (and optionally check the comments checkbox) at the bottom of the page to set where you want these toolbars to appear, and click Save.{/tr}
{/remarksbox}
{remarksbox title='Note' type='note'}
    {tr}If you are experiencing problems with this page after upgrading from Tiki 4 please use this link to delete all your customised tools:{/tr}
    <strong>{self_link reset_all_custom_tools=y _class='alert-link'}{tr}Delete all custom tools{/tr}{/self_link}</strong>
    <em>{tr}Warning: There is no undo!{/tr}</em>
{/remarksbox}
</div>