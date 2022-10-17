{if isset($template)}
    {title help="Edit Templates" url="tiki-edit_templates.php?mode=listing&template=$template"}
        {if $prefs.feature_edit_templates ne 'y' or $tiki_p_edit_templates ne 'y'}
            {tr}View template:{/tr}
        {else}
            {tr}Edit template:{/tr}
        {/if}
        {$template}
    {/title}
{else}
    {title help="Edit Templates"}{tr}Edit templates{/tr}{/title}
{/if}

<div class="t_navbar mb-4">
    {if $prefs.feature_editcss eq 'y'}
        {button href="tiki-edit_css.php" _text="{tr}Edit CSS{/tr}"}
    {/if}
    {if $mode eq 'editing'}
        {button href="tiki-edit_templates.php" _text="{tr}Template listing{/tr}"}
    {/if}
</div>

{if $mode eq 'listing'}
    <h2>
        {tr}Available templates:{/tr}
    </h2>
    <table border="1" cellpadding="0" cellspacing="0" >
        <tr>
            <th>{tr}Template{/tr}</th>
        </tr>

        {section name=user loop=$files}
        <tr>
            <td>
                <a class="link" href="tiki-edit_templates.php?template={$files[user]}">
                    {$files[user]}
                </a>
            </td>
        </tr>
        {sectionelse}
            {norecords _colspan=1}
        {/section}
    </table>
{/if}
{if $mode eq 'editing'}
    {if $prefs.feature_edit_templates eq 'y' and $tiki_p_edit_templates eq 'y'}
        {remarksbox 