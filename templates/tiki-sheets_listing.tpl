{* $Id$ *}
<tr>
    <td class="text">
        {if !empty($sheet.parentSheetId)}
            <span class="ui-icon ui-icon-grip-dotted-vertical" style="float: left;"></span>
            <span class="ui-icon ui-icon-grip-dotted-horizontal" style="float: left; margin-left: -9px;"></span>
        {/if}
        <a class="galname sheetLink" sheetId="{$sheet.sheetId}" href="tiki-view_sheets.php?sheetId={$sheet.sheetId}">{$sheet.title|escape}</a>
    </td>
    <td class="text">{$sheet.description|escape}</td>
    <td>{$sheet.created|tiki_short_date}</td>
    <td>{$sheet.lastModif|tiki_short_date}</td>
    <td class="username">{$sheet.author|escape}</td>
    <td class="action">
        {actions}
            {strip}
                {if $chart_enabled eq 'y'}
                    <action>
                        <a class="gallink" href="tiki-graph_sheet.php?sheetId={$sheet.sheetId}">
                            {icon name='chart' _menu_text='y' _menu_icon='y' alt="{tr}Graph{/tr}"}
                        </a>
                    </action>
                {/if}
                {if $tiki_p_view_sheet_history eq 'y'}
                    <action>
                        <a class="gallink" href="tiki-history_sheets.php?offset={$offset}&amp;sort_mode={$sort_mode}&amp;sheetId={$sheet.sheetId}">
                            {icon name='history' _menu_text='y' _menu_icon='y' alt="{tr}History{/tr}"}
                        </a>
                    </action>
                {/if}
                <action>
                    <a class="gallink tips" title=":{tr}Export{/tr}" href="tiki-export_sheet.php?offset={$offset}&amp;sort_mode={$sort_mode}&amp;sheetId={$sheet.sheetId}">
                        {icon name='export' _menu_text='y' _menu_icon='y' alt="{tr}Export{/tr}"}
                    </a>
                </action>
                {if $sheet.tiki_p_edit_sheet eq 'y'}
                    <action>
                        <a class="gallink tips" title=":{tr}Import{/tr}" href="tiki-import_sheet.php?offset={$offset}&amp;sort_mode={$sort_mode}&amp;sheetId={$sheet.sheetId}">
                            {icon name='import' _menu_text='y' _menu_icon='y' alt="{tr}Import{/tr}"}
                        </a>
                    </action>
                {/if}
                {if $tiki_p_admin_sheet eq 'y'}
                    <action>
                        {permission_link mode='text' type=sheet id=$sheet.sheetId title=$sheet.title}
                    </action>
                {/if}
                {if $sheet.tiki_p_edit_sheet eq 'y'}
                    <action>
                        <a class="gallink" href="tiki-sheets.php?offset={$offset}&amp;sort_mode={$sort_mode}&amp;edit_mode=1&amp;sheetId={$sheet.sheetId}">
                            {icon name='cog' _menu_text='y' _menu_icon='y' alt="{tr}Configure{/tr}"}
                        </a>
                    </action>
                    <action>
                        <a class="gallink" href="tiki-sheets.php?offset={$offset}&amp;sort_mode={$sort_mode}&amp;removesheet=y&amp;sheetId={$sheet.sheetId}">
                            {icon name='remove' _menu_text='y' _menu_icon='y' alt="{tr}Delete{/tr}"}
                        </a>
                    </action>
                {/if}
            {/strip}
        {/actions}
    </td>
</tr>
