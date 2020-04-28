
<div class="adminoptionbox preference clearfix multicheckbox mb-3 row text-start {$p.tagstring|escape}{if isset($smarty.request.highlight) and $smarty.request.highlight eq $p.preference} highlight{/if}">
    <label for="{$p.id|escape}" class="col-form-label col-sm-4">{$p.name|escape}</label>
    <div class="col-sm-8">
        {foreach from=$p.options key=value item=label}
            <div class="form-check form-check-inline">
                <label class="col-form-label me-3">
                    <input class="form-check-inline" type="checkbox" name="{$p.preference|escape}[]" value="{$value|escape}"{if is_array($p.value) and in_array($value, $p.value)} checked="checked"{/if} {$p.params}>
                    {$label|escape}
                </label>
            </div>
        {/foreach}

        <div>
            {include file="prefs/shared.tpl"}
        </div>
    </div>
</div>