
{title help="Spreadsheet"}{tr}Spreadsheet History:{/tr} {$title}{/title}

<div>
    {$description|escape}
</div>
<form>
    {tabset}
        {if not $grid_content eq ''}
            {tab name="{tr}View{/tr}"}
                <h2>{tr}View{/tr}</h2>
                <div id="tiki_sheets_container">
                    <table style="width: 100%;" id="tiki_sheet_container">
                        <tr>
                            {section name=date loop=$grid_content}
                                <td class="sheet_sibling" style="width: 50%;">
                                    {pagination_links cant=$ver_cant itemname="{tr}Sheet{/tr}" offset_arg="idx_{$smarty.section.date.index}" offset=$sheetIndexes[$smarty.section.date.index] show_numbers=n}{/pagination_links}
                                </td>
                            {/section}
                        </tr>
                        <tr>
                            {section name=date loop=$grid_content}
                                <td class="sheet_sibling">
                                    <div style="font-size: 1.5em; text-align: center;">
                                        Revision: {$history[$sheetIndexes[$smarty.section.date.index]].prettystamp}
                                    </div>
                                </td>
                            {/section}
                        </tr>
                        <tr>
                            {section name=date loop=$grid_content}
                                <td>
                                    <div class="tiki_sheet" {if !empty($tiki_sheet_div_style)} style="{$tiki_sheet_div_style}"{/if}>
                                        {$grid_content[$smarty.section.date.index]}
                                    </div>
                                </td>
                            {/section}
                        </tr>
                        <tr>
                            {section name=date loop=$grid_content}
                                <td class="sheet_sibling">
                                    <div style="text-align: center;">
                                        {button _keepall='y' href="tiki-view_sheets.php" sheetId=$sheetId readdate=$history[$sheetIndexes[$smarty.section.date.index]].stamp parse="y" class="view_button" _text="{tr}View{/tr}" _htmlelement="role_main" _title="{tr}View{/tr}"}
                                        {button _keepall='y' href="tiki-view_sheets.php" sheetId=$sheetId readdate=$history[$sheetIndexes[$smarty.section.date.index]].stamp parse="clone" class="clone_button" _text="{tr}Clone{/tr}" _htmlelement="role_main" _title="{tr}Clone{/tr}"}
                                  