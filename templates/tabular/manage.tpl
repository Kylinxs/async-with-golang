{extends "layout_view.tpl"}

{block name="title"}
    {title}{$title}{/title}
{/block}

{block name="navigation"}
    <div class="mb-3">
        {permission name=admin_trackers}
            <a class="btn btn-link" href="{service controller=tabular action=create}">{icon name=create} {tr}New{/tr}</a>
            <a class="btn btn-link" href="{service controller=tabular action=create_tracker}">{icon name=import} {tr}Create Tracker from File{/tr}</a>
        {/permission}
        <a class="btn btn-link" href={''|sefurl:'tracker'}>{icon name=trackers} {tr}Trackers{/tr}</a>
    </div>
{/block}

{block name="content"}
    <table class="table">
        <tr>
            <th>{tr}Name{/tr}</th>
            <th>{tr}Tracker{/tr}</th>
            <th></th>
        </tr>
        {foreach $list as $row}
            <tr>
                <td><a href="{service controller=tabular action=list tabularId=$row.tabularId}">{$row.name|escape}</a></td>
                <td>{object_title type=tracker id=$row.trackerId}</td>
                <td class="action">
                    {actions}{strip}
                        <action>
                            <a href="{service controller=tabular action=export_full_csv tabularId=$row.tabularId}">
                                {icon name=export _menu_text='y' _menu_icon='y' alt="{tr}Export Full{/tr}"}
                            </a>
                        </action>
                        <action>
                            <a href="{bootstrap_modal controller=tabular action=filter target=export tabularId=$row.tabularId}">
                                {icon name=export _menu_text='y' _menu_icon='y' alt="{tr}Export Partial{/tr}"}
                            </a>
                        </action>
                        <action>
                            <a href="tiki-searchindex.php?tabularId={$row.tabularId|escape}&amp;filter~tracker_id={$row.trackerId|escape}">
                                {icon name=export _menu_text='y' _menu_icon='y' alt="{tr}Export Custom{/tr}"}
                            </a>
                        </action>
                        <action>
                            <a href="{bootstrap_modal controller=tabular action=import_csv tabularId=$row.tabularId}">
                                {icon name=import _menu_text='y' _menu_icon='y' alt="{tr}Import{/tr}"}
                            </a>
                        </action>
                        <action>
                            <a href="{service controller=tabular action=edit tabularId=$row.tabularId}">
                                {icon name=edit _menu_text='y' _menu_icon='y' alt="{tr}Edit{/tr}"}
                            </a>
                        </action>
                        <action>
                            <a href="{bootstrap_modal controller=tabular action=duplicate tabularId=$row.tabularId}">
                                {icon name=copy _menu_text='y' _menu_icon='y' alt="{tr}Duplicate{/tr}"}
                            </a>
                        </action>
                        <action>
                            {permission_link type=tabular id=$row.tabularId title=$row.name mode=text}
                        </action>
                        <action>
                            <a class="text-danger" href="{bootstrap_modal controller=tabular action=delete tabularId=$row.tabularId}">
                                {icon name=delete _menu_text='y' _menu_icon='y' alt="{tr}Delete{/tr}"}
                            </a>
                        </action>
                    {/strip}{/actions}
                </td>
            </tr>
        {foreachelse}
            <tr>
                <td colspan="3">{tr}No import-export formats defined.{/tr}</td>
            </tr>
        {/foreach}
    </table>
{/block}
