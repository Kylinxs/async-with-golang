
<div class="mb-3 row mx-0">
    {if $field_type eq 'c'}
        <div class="form-check">
            <label class="form-check-label">
                {$field_input} {tr}{$field_name}{/tr} {$mandatory_sym}
            </label>
        </div>
    {else}
        <label class="col-form-label field_{$permname}" for="ins_{$field_id}">{tr}{$field_name}{/tr} {$mandatory_sym}</label>
        {$field_input}
    {/if}
    {tr}{$description}{/tr}
</div>