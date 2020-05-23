{if isset($items) && is_array($items)}
    {foreach $items as $itemKey => $itemValue}
        <input type='hidden' name="items[{$itemKey|escape}]" value="{$itemValue|escape}">
    {/foreach}
{/if}
{if isset($extra) && is_array($extra)}
    {foreach $extra as $extraKey => $extraValue}
        {if ! is_array($extraValue) && $extraKey != 'warning'}
            <input type='hidden' name='{$extraKey|escape}' value="{$extraValue|escape}">
        {elseif is_array($extraValue)}
            {foreach $extraValue as $valueKey => $valueValue}
                {if $extraKey != 'fields'}
                    <input type='hidden' name="{$extraKey|escape}[{$valueKey|escape}]" value="{$valueValue|escape}">
                {/if}
            {/foreach}
        {/if}
    {/foreach}
{/if}
{if isset($toList) && is_array($toList)}
    {foreach $toList as $toKey => $toValue}
        <input type='hidden' name="toList[{$toKey|escape}]" value="{$toValue|escape}">
    {/foreach}
{/if}
{ticket mode="confirm"}
