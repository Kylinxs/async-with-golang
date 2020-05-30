<div{if $field.options_map.prepend or $field.options_map.append or $field.options_map.labelasplaceholder} class="input-group"{/if}>
    {if !empty($field.options_map.prepend)}
        <span class="input-group-text">{$field.options_map.prepend}&nbsp</span>
    {/if}

    <input type="number" class="numeric form-control {if !empty($field.options_map.labelasplaceholder)}labelasplaceholder{/if}" name="{$field.ins_id|escape}"
            {if $field.options_array[1]}size="{$field.options_array[1]|escape}" maxlength="{$field.options_array[1]|escape}"{/if}
            value="{$field.value|escape}" id="{$field.ins_id}"
            {if !empty($field.options_map.labelasplaceholder)}placeholder="{$field.name}"{/if}
    >

    {if !empty($field.options_map.append)}
        <span class="input-group-text">{$field.options_map.append}</span>
    {/if}
</div>
