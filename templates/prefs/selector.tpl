<div class="adminoptionbox preference clearfix mb-3 row {$p.tagstring|escape}{if isset($smarty.request.highlight) and $smarty.request.highlight eq $p.preference} highlight{/if}">
    <label class="col-form-label col-sm-4" for="{$p.id|escape}">{$p.name|escape}</label>
    <div class="col-sm-8">
        {object_selector _simplename=$p.preference _simpleid=$p.id _simplevalue=$p.value type=$p.selector_type _format=$p.format|default:null _parent=$p.parent|default:null _parentkey=$p.parentkey|default:null}
        {include file="prefs/shared.tpl"}
    </div>
</div>
