<div class="adminoptionbox preference clearfix mb-3 row {$p.tagstring|escape}{if isset($smarty.request.highlight) and $smarty.request.highlight eq $p.preference} highlight{/if}">
    {if !empty($p.name)}
        <label for="{$p.id|escape}" class="col-form-label col-sm-4">{$p.name|escape}</label>
    {/if}
    <div class="col-sm-8">
        {foreach from=$p.options key=value item=label name=loop}
            <div class="adminoptionlabel form-check">
                <input class="form-check-input" id="{$p.id|cat:'_'|cat:$smarty.foreach.loop.index|escape}" type="radio" name="{$p.preference|escape}"
                    value="{$value}"{if $p.value eq $value} checked="checked"{/if} {$p.params}
                    data-tiki-admin-child-block="#{$p.preference|escape}_childcontainer_{$smarty.foreach.loop.index|escape}"
                >
                <label class="form-check-label" for="{$p.id|cat:'_'|cat:$smarty.foreach.loop.index|escape}">{$label|escape}</label>
            </div>
        {/foreach}
        {include file="prefs/shared.tpl"}
    </div>
</div>
