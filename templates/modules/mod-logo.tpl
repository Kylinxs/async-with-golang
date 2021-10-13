{* $Id$ *}
{strip}
    {tikimodule error=$module_params.error title=$tpl_module_title name="logo" flip=$module_params.flip decorations=$module_params.decorations nobox=$module_params.nobox notitle=$module_params.notitle}
        <div class="sitelogo">
        <a class="navbar-brand d-flex w-100 {$module_params.class_image|escape}" href="{$module_params.link}" title="{$module_params.title_attr|escape}">
            {if !empty($module_params.src)}
                <img class="{$module_params.class_image|escape} sitelogo-img img-fluid me-3" src="{$module_params.src}" alt="{$module_params.alt_attr|escape}" {if $prefs.site_layout eq 'social' && $prefs.theme_navbar_fixed_topbar_offset ne ''} style="height: calc({$prefs.theme_navbar_fixed_topbar_offset}px - ( 2 * var(--bs-navbar-padding-y)) - var(--tiki-fixed-top-border-top-width) - var(--tiki-fixed-top-border-bottom-width) ); width: auto;{* margin-top: var(--bs-navbar-padding-y); margin-bottom: var(--bs-navbar-padding-y)*}"{/if}>
            {/if}
            {if !empty($module_params.sitetitle) or !empty($module_params.sitesubtitle)}
                {if $prefs.site_layout neq 'social'}
                    <div class="{$module_params.class_titles|escape}"><div class="d-flex">
                {/if}
                {if !empty($module_params.sitetitle)}
                    <div class="sitetitle">{tr}{$module_params.sitetitle|escape}{/tr}</div>
                {/if}
                {if !empty($module_params.sitesubtitle)}
                    <div class="sitesubtitle">{tr}{$module_params.sitesubtitle|escape}{/tr}</div>
                {/if}
                {if $prefs.site_layout neq 'social'}</div></div>{/if}
            {/if}
        </a>
            {if $tiki_p_admin eq 'y' and $prefs.sitelogo_upload_icon eq 'y'}
                <a class="btn btn-link bottom mb-2 position-absolute tips"
                   href="tiki-admin.php?page=look&cookietab=2&highlight=sitelogo_src#feature_sitelogo_childcontainer"
                   style="left: 0; bottom: 0; opacity: .8"
                   title="{tr}Change the logo:{/tr} {tr}Click to change or upload new logo{/tr}"
                >
                    {icon name="upload"}
                </a>
            {/if}
       </div>
    {/tikimodule}
{/strip}
