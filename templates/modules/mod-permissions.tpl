{* $Id$ *}
{if isset($pagePermissions)}
    {tikimodule error=$module_params.error title=$tpl_module_title name="permissions" flip=$module_params.flip decorations=$module_params.decorations nobox=$module_params.nobox notitle=$module_params.notitle style=$module_params.style}
        {if $pagePermissions}
            <p>{tr}Permissions applied from:{/tr} <b>{$pagePermissions.from}</b> level</p>
            {foreach from=$pagePermissions.perms key=group item=perms}
                <b>{$group}</b><br/>
                {foreach from=$perms key=perm item=groups}
                    {$perm}
                    <span target="tikihelp" class="tikihelp" title="{', '|implode:$groups}">{icon name="help"}</span>
                    <br/>
                {/foreach}
                <br/>
            {/foreach}
        {else}
            {tr}No permissions to display{/tr}
        {/if}
    {/tikimodule}
{/if}
