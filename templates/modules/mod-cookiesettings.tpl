{tikimodule error=$module_params.error title=$tpl_module_title name="cookiesettings" flip=$module_params.flip decorations=$module_params.decorations nobox=$module_params.nobox notitle=$module_params.notitle}{strip}
{*    <pre>{$module_params|var_dump}</pre>*}
    <a href="{$base_uri}{if $base_uri|strpos:'?' === false}?{else}&{/if}cookie_consent" title="|{$module_params.text}" class="tips {$module_params.textclass}">
        {if $module_params.mode eq 'both' or $module_params.mode eq 'icon'}{icon name=$module_params.icon size=$module_params.iconsize}{/if}
        {if $module_params.mode eq 'both' or $module_params.mode eq 'text'}{$module_params.text}{/if}
    </a>
{/strip}{/tikimodule}
