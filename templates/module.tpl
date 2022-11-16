
{* $Id$ *}
{* Module layout with controls *}
{if !isset($module_position)}{assign var=module_position value=''}{/if}
{if !isset($module_ord)}{assign var=module_ord value=''}{/if}
{capture name=name}{$module_name|replace:"+":"_"|cat:$module_position|cat:$module_ord|escape}{/capture}

{if !empty($module_params.topclass)}<div class="{$module_params.topclass}">{/if}

{if $module_nobox neq 'y'}
{if !isset($moduleId)}{assign var=moduleId value=' '}{/if}
<div id="module_{$moduleId}"
    class="card box-{$module_name} module"{if !empty($tpl_module_style)} style="{$tpl_module_style}"{/if}>
    {if $module_decorations ne 'n'}
        <div class="card-header" {if !empty($module_params.bgcolor)} style="background-color:{$module_params.bgcolor};"{/if}>
            {if ($module_notitle ne 'y' && !empty($module_title)) || ($module_flip eq 'y' and $prefs.javascript_enabled ne 'n') || $prefs.menus_items_icons eq 'y'}
                <h3 class="card-title clearfix">
                    {if $module_notitle ne 'y' && !empty($module_title)}
                        <span class="moduletitle">{$module_title}</span>
                    {/if}
                    {if $module_flip eq 'y' and $prefs.javascript_enabled ne 'n'}
                        <div class="moduleflip" id="moduleflip-{$smarty.capture.name}">
                            {* Only show edit and delete options in module title if on tiki-admin_modules.php page *}
                            {if $smarty.server.SCRIPT_NAME == $url_path|cat:'tiki-admin_modules.php'}
                                <form action="tiki-admin_modules.php" method="post">
                                    {ticket}
                                    <input type="hidden" name="unassign" value="{$moduleId}">
                                    <button type="submit" class="btn btn-link close text-danger" title="{tr}Unassign module{/tr}">
                                        {icon name="remove"}
                                    </button>
                                </form>
                                <a href="#" title="{tr}Edit module{/tr}" class="btn-close" style="font-size: 16px" onclick="$(this).parents('.module:first').dblclick();">
                                    {icon name="edit"}
                                </a>
                            {/if}
                            <a title="{tr}Toggle module contents{/tr}" class="flipmodtitle close"
                                 href="javascript:icntoggle('mod-{$smarty.capture.name}','module.png');">
                                {icon id="icnmod-"|cat:$smarty.capture.name class="flipmodimage" name="bars" alt="[{tr}Toggle{/tr}]"}
                            </a>
                        </div>
                    {/if}
                </h3>
            {/if}
        </div>
    {elseif $module_notitle ne 'y'}{* means when module decorations are set to 'n' don't render the card-header wrapper as above *}
    {if $module_flip eq 'y' and $prefs.javascript_enabled ne 'n'}
    <h3 class="card-title"
         ondblclick="javascript:icntoggle('mod-{$smarty.capture.name}','module.png');"{if !empty($module_params.color)} style="color:{$module_params.color};"{/if}>
        {else}
        <h3 class="card-title"{if !empty($module_params.color)} style="color:{$module_params.color};"{/if}>
            {/if}
            {$module_title}
            {if $module_flip eq 'y' and $prefs.javascript_enabled ne 'n'}
                <div class="moduleflip" id="moduleflip-{$smarty.capture.name}">
                    {* Only show edit and delete options in module title if on tiki-admin_modules.php page *}
                    {if $smarty.server.SCRIPT_NAME == $url_path|cat:'tiki-admin_modules.php'}
                        <form action="tiki-admin_modules.php" method="post">
                            {ticket}
                            <input type="hidden" name="unassign" value="{$moduleId}">
                            <button type="submit" class="btn btn-link close" title="{tr}Unassign module{/tr}">
                                {icon name="remove"}
                            </button>
                        </form>
                        <a href="#" title="{tr}Edit module{/tr}" class="btn-close" style="font-size: 16px" onclick="$(this).parents('.module:first').dblclick();">
                            {icon name="edit"}
                        </a>
                    {/if}
                    <a title="{tr}Toggle module contents{/tr}" class="flipmodtitle"
                         href="javascript:icntoggle('mod-{$smarty.capture.name}','module.png');">
                        {icon id="icnmod-"|cat:$smarty.capture.name class="flipmodimage" name="module" alt="[{tr}Toggle{/tr}]"}
                    </a>
                </div>
            {/if}
        </h3>
        {/if}
        <div id="mod-{$smarty.capture.name}"
             style="display: {if (!isset($module_display) or $module_display) && (!isset($module_dstate) or $module_dstate == 'o')}block{else}none{/if};{$module_params.style}"
             class="clearfix card-body{if !empty($module_params.class)} {$module_params.class}{/if}">
            {else}{* $module_nobox eq 'y' *}
            <div id="module_{$moduleId}" style="{$module_params.style}{$tpl_module_style}"
                 class="module{if !empty($module_params.class)} {$module_params.class}{/if} box-{$module_name}">
                <div id="mod-{$smarty.capture.name}">
                    {/if}{* close $module_nobox *}
                    {$module_content}
                    {if $module_error}
                        {remarksbox type="warning" title="{tr}Error{/tr}"}
                        {$module_error}
                        {/remarksbox}
                    {/if}
                    {if $module_nobox neq 'y'}
                </div>{* close div id="mod-{$smarty.capture.name}" *}
                {* Module controls when module in a box *}
                {if $user and $prefs.user_assigned_modules == 'y' and $prefs.feature_modulecontrols eq 'y' && ($module_position === 'left' || $module_position === 'right')}
                    <form action="{$current_location|escape}" method="post" class="modcontrols">
                        {ticket}
                        <input type="hidden" name="redirect" value="1">
                        <div>
                            <button
                                type="submit"
                                name="mc_up"
                                value="{$moduleId}"
                                class="tips btn btn-link"
                                title=":{tr}Move up{/tr}"
                            >
                                {icon name="up"}
                            </button>
                            <button
                                type="submit"
                                name="mc_down"
                                value="{$moduleId}"
                                class="tips btn btn-link"
                                title=":{tr}Move down{/tr}"
                            >
                                {icon name="down"}
                            </button>
                            <button
                                type="submit"
                                name="mc_move"
                                value="{$moduleId}"
                                class="tips btn btn-link"
                                title=":{tr}Move to opposite side{/tr}"
                            >
                                {icon name="move"}
                            </button>
                            <button
                                type="submit"
                                name="mc_unassign"
                                value="{$moduleId}"
                                class="tips btn btn-link"
                                title=":{tr}Unassign{/tr}"
                            >
                                {icon name="remove"}
                            </button>
                        </div>
                    </form>
                {/if}
                <div class="card-footer"></div> {* Added because some themes use this div for styling purposes. *}
            </div>{* close div id="module_{$moduleId}" *}
            {else}{* $module_nobox eq 'y' *}
            {* Module controls when no module box *}
            {if $user and $prefs.user_assigned_modules == 'y' and $prefs.feature_modulecontrols eq 'y' && ($module_position === 'left' || $module_position === 'right')}
                <form action="{$current_location|escape}" method="post" class="modcontrols">
                    <input type="hidden" name="redirect" value="1">
                    <div>
                        <button
                            type="submit"
                            name="mc_up"
                            value="{$moduleId}"
                            class="tips btn btn-link"
                            title=":{tr}Move up{/tr}"
                        >
                            {icon name="up"}
                        </button>
                        <button
                            type="submit"
                            name="mc_down"
                            value="{$moduleId}"
                            class="tips btn btn-link"
                            title=":{tr}Move down{/tr}"
                        >
                            {icon name="down"}
                        </button>
                        <button
                            type="submit"
                            name="mc_move"
                            value="{$moduleId}"
                            class="tips btn btn-link"
                            title=":{tr}Move to opposite side{/tr}"
                        >
                            {icon name="move"}
                        </button>
                        <button
                            type="submit"
                            name="mc_unassign"
                            value="{$moduleId}"
                            class="tips btn btn-link"
                            title=":{tr}Unassign{/tr}"
                        >
                            {icon name="remove"}
                        </button>
                    </div>
                </form>
            {/if}
        </div>{* close div id="mod-{$smarty.capture.name}" *}
</div>{* close div id="module_{$moduleId}" *}
{/if}{* close $module_nobox *}

{if !empty($module_params.topclass)}</div>{/if}