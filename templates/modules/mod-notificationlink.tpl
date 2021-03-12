
{if $user}
{tikimodule error=$module_params.error title=$tpl_module_title name="notification_link" flip=$module_params.flip decorations=$module_params.decorations nobox=$module_params.nobox notitle=$module_params.notitle}
{notification_link}
{/tikimodule}
{/if}