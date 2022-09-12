<div class="adminoptionbox preference clearfix {$p.tagstring|escape}{if isset($smarty.request.highlight) and $smarty.request.highlight eq $p.preference} highlight{/if}">
    <div class="adminoption mb-3 row">
        <label class="col-sm-4">
            {$p.name|escape}
        </label>
        <div class="col-sm-8">
            <div class="form-check">
                <input id="{$p.id|escape}" class="form-check-input" type="checkbox" name="{$p.preference|escape}" {if $p.value eq 'y'}checked="checked" {/if}
                    {if ! $p.available}disabled="disabled"{/if} {$p.params}
                    data-tiki-admin-child-block="#{$p.preference|escape}_childcontainer"
                    data-tiki-admin-child-mode="{$mode|escape}"
                >
                {include file="prefs/shared.tpl"}
            </div>

        </div>
    </div>
</div>
