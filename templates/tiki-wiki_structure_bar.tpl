
{*<div class="tocnav row mx-0 justify-content-between">*}
<nav class="nav-breadcrumb" aria-label="breadcrumb">
        <ol class="breadcrumb mt-2 me-3 p-1 d-inline-flex justify-content-start align-content-center">
            {if $home_info}{if !empty($home_info.page_alias)}{assign var=icon_title value=$home_info.page_alias}{else}{assign var=icon_title value=$home_info.pageName}{/if}
                {if $prefs.feature_wiki_structure_drilldownmenu eq 'y'}
                    <span class="dropdown">
                        <a class="btn dropdown-toggle structure-home me-3" role="button" id="dropdownStructure" data-bs-toggle="dropdown" data-hover="dropdown">
                            {icon name="home"}
                        </a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownStructure">
                            {menu structureId=$page_info.structure_id page_id=$page_info.page_id page_name=$page_info.pageName drilldown="y" bootstrap="n" css="y"}
                        </ul>
                    </span>
                {else}
                    {self_link page=$home_info.pageName structure=$home_info.pageName page_ref_id=$home_info.page_ref_id _title="{tr}Structure:{/tr}$icon_title" _class="tips"}{icon name="home"}{/self_link}
                {/if}
            {/if}
            {if $prev_info and $prev_info.page_ref_id}{if !empty($prev_info.page_alias)}{assign var=icon_title value=$prev_info.page_alias}{else}{assign var=icon_title value=$prev_info.pageName}{/if}
                <a href="{sefurl page=$prev_info.pageName structure=$home_info.pageName page_ref_id=$prev_info.page_ref_id}" class="tips" title="{tr}Previous page:{/tr}{$icon_title}">
                    {icon name="caret-left"}
                </a>
            {/if}
            {if $parent_info}{if !empty($parent_info.page_alias)}{assign var=icon_title value=$parent_info.page_alias}{else}{assign var=icon_title value=$parent_info.pageName}{/if}
                <a href="{sefurl page=$parent_info.pageName structure=$home_info.pageName page_ref_id=$parent_info.page_ref_id}" class="tips" title="{tr}Parent page:{/tr}{$icon_title}">
                    {icon name="up"}
                </a>
            {/if}
            {if $next_info and $next_info.page_ref_id}{if !empty($next_info.page_alias)}{assign var=icon_title value=$next_info.page_alias}{else}{assign var=icon_title value=$next_info.pageName}{/if}
                <a href="{sefurl page=$next_info.pageName structure=$home_info.pageName page_ref_id=$next_info.page_ref_id}" class="tips" title="{tr}Next page:{/tr}{$icon_title}">
                    {icon name="caret-right"}
                </a>
            {/if}
        </ol>
        <ol class="breadcrumb mt-2 me-3 p-1 d-inline-flex align-content-center me-auto">
            {section loop=$structure_path name=ix}
                {if $structure_path[ix].parent_id}&nbsp;{$prefs.site_crumb_seper}&nbsp;{/if}
                    <li class="breadcrumb-item">

                        {if !empty($smarty.section.ix.last) }

                            {if $structure_path[ix].page_alias}
                                {$structure_path[ix].page_alias|escape}
                            {else}
                                {$structure_path[ix].stripped_pageName|pagename}
                            {/if}

                        {else}

                            <a href="{sefurl page=$structure_path[ix].pageName structure=$home_info.pageName page_ref_id=$structure_path[ix].page_ref_id}">
                                {if $structure_path[ix].page_alias}
                                    {$structure_path[ix].page_alias|escape}
                                {else}
                                    {$structure_path[ix].stripped_pageName|pagename}
                                {/if}
                            </a>

                        {/if}

                    </li>
            {/section}
        </ol>
        {if $struct_editable eq 'a'}
            <form action="tiki-editpage.php" method="post" role="form" class="d-flex flex-row flex-wrap align-items-center"  style="display: inline-flex;">
                    <div class="mb-3" style="flex-shrink: 1;">
                        <input type="hidden" name="current_page_id" value="{$page_info.page_ref_id}">
                        <div class="input-group">
                            <div class="input-group-text" style="font-size:50% !important">
                                {self_link _script="tiki-edit_structure.php" page_ref_id=$home_info.page_ref_id _class="tips" _title="{tr}Manage Stucture:{/tr}{$home_info.pageName} ($cur_pos)"}{icon name="structure"}{/self_link}
                            </div>
                            <input type="text" id="structure_add_page" name="page" class="form-control form-control-sm">
                            {autocomplete element='#structure_add_page' type='pagename'}
                            <input type="submit" class="btn btn-primary btn-sm" name="insert_into_struct" value="{tr}Add Page{/tr}">
                        </div>
                    </div>
                    <div class="mb-3">
                        {* Cannot add peers to head of structure *}
                        {if $page_info and !$parent_info}
                            <input type="hidden" name="add_child" value="checked">
                        {else}
                            <input type="checkbox" name="add_child" class="ms-2 me-1">{tr}Child{/tr}
                        {/if}
                    </div>
                </form>
        {else}
            {if $struct_editable eq 'y'}
                <span class="float-end">{self_link _script="tiki-edit_structure.php" page_ref_id=$home_info.page_ref_id _class="tips" _title="{tr}Manage Stucture:{/tr}{$home_info.pageName} ($cur_pos)"}{icon name="structure"}{/self_link}</span>
            {/if}
        {/if}
</nav>