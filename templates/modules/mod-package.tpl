{tikimodule error=$module_params.error title=$tpl_module_title name="package" flip=$module_params.flip decorations=$module_params.decorations nobox=$module_params.nobox notitle=$module_params.notitle}
{if !empty($error)}
    {$error|escape}
{/if}
{if !empty($folder) && !empty($view)}
    {include file="{$folder|escape}/templates/modules/{$view|escape}.tpl"}
{/if}
{/tikimodule}
