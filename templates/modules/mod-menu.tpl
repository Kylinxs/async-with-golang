{* $Id$ *}

{tikimodule error=$module_error title=$tpl_module_title name=$tpl_module_name flip=$module_params.flip|default:null decorations=$module_params.decorations|default:null nobox=$module_params.nobox|default:null notitle=$module_params.notitle|default:null type=$module_type}
    {if $module_params.bootstrap|default:null neq 'n'}
        <nav class="{if $prefs.jquery_smartmenus_enable eq 'y'} navbar navbar-expand-lg {elseif !empty($module_params.navbar_class)}{$module_params.navbar_class}{else}navbar {if $module_params.type == 'horiz'}navbar-expand-lg{/if}{/if} {if $module_params.type == 'horiz'}navbar-{$navbar_color_variant} bg-{$navbar_color_variant}{/if}" role="navigation"> {* Only horizontal navbars get Bootstrap navbar color and background.  *}
            {if $module_params.navbar_brand neq ''}
                <a class="navbar-brand" href="index.php">
                    <img id="logo-header" src="{$module_params.navbar_brand}" alt="Logo">
                </a>
            {/if}
            {if empty($module_params.navbar_toggle)}
                {if empty($module_params.type) or $module_params.type eq 'vert'}
                    {$module_params.navbar_toggle = 'n'}
                {else}
                    {$module_params.navbar_toggle = 'y'}
                {/if}
            {/if}
            {if $module_params.navbar_toggle eq 'y'}
                <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#mod-menu{$module_position}{$module_ord} .navbar-collapse" aria-controls="mod-menu{$module_position}{$module_ord}" aria-expanded="false" aria-label="{tr}Toggle navigation{/tr}">
                    <span class="navbar-toggler-icon"></span>
                </button>
            {/if}
            <div class="collapse navbar-collapse {if $module_params.navbar_toggle eq 'n'}show{/if} {if $module_params.megamenu eq 'y' and $module_params.megamenu_static eq 'y' }mega-menu-static{/if}">
                {if $prefs.menus_edit_icon eq 'y' AND $tiki_p_admin eq 'y' AND $module_params.id neq '42'}
                    <div class="edit-menu">
                        <a href="tiki-admin_menu_options.php?menuId={$module_params.id}" title="{tr}Edit this menu{/tr}">{icon name="edit"}</a>
                    </div>
                {/if}
                {menu params=$module_params bootstrap=navbar}
            </div>
        </nav>
    {else}{* non bootstrap legacy menus *}
        <div class="clearfix {if !empty($module_params.menu_class)}{$module_params.menu_class}{/if}"{if !empty($module_params.menu_id)} id="{$module_params.menu_id}"{/if}>
            {menu params=$module_params}
        </div>
    {/if}
{/tikimodule}
