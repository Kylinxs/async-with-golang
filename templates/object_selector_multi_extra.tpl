{* $Id$ *}
<div class="object-selector-multi">
<textarea
    id="{$object_selector_multi.id|escape}"
    {if !empty($object_selector_multi.name)}name="{$object_selector_multi.name|escape}"{/if}
    {if !empty($object_selector_multi.class)}class="{$object_selector_multi.class|escape}"{/if}
    {if !empty($object_selector_multi.title)}data-label="{$object_selector_multi.title|escape}"{/if}
    {if !empty($object_selector_multi.parent)}data-parent="{$object_selector_multi.parent|escape}"{/if}
    {if !empty($object_selector_multi.parentkey)}data-parentkey="{$object_selector_multi.parentkey|escape}"{/if}
    {if !empty($object_selector_multi.format)}data-format="{$object_selector_multi.format|escape}"{/if}
    {if !empty($object_selector_multi.sort)}data-sort="{$object_selector_multi.sort|escape}"{/if}
    data-filters="{$object_selector_multi.filter|escape}"
    data-threshold="{$object_selector_multi.threshold|default:$prefs.tiki_object_selector_threshold|escape}"
    data-extratype='{$object_selector_multi.extra_type|json_encode}'
>{"\n"|implode:$object_selector_multi.current_selection}</textarea>
    <div class="basic-selector d-none">
        <select
            id="{$object_selector_multi.simpleid|escape}"
            name="{$object_selector_multi.simplename|escape}"
            data-current-value="{$object_selector_multi.simplevalue|escape}"
            class="form-control"
            multiple
        >
        </select>
    </div>

    <div class="card d-none">
        <div class="card-header">
            <div class="input-group">
                <div class="input-group-text">
                    {icon name=search}
                </div>
                <input type="text" placeholder="{$object_selector_multi.placeholder|escape}..." value="" class="filter form-control" autocomplete="off">
                <input type="button" class="btn btn-info search" value="{tr}Find{/tr}">
            </div>
        </div>
        <div class="card-body">
            <p class="too-many">{tr}Search and select what you are looking for from the options that appear.{/tr}</p>
            <div class="results">
                {foreach from=$object_selector_multi.current_selection item=object name=ix}
                    <div class="form-check">
                        <input id="{$object_selector_multi.id|escape}_selected_{$smarty.foreach.ix.index}" class="form-check-input" type="checkbox" value="{$object|escape}" checked>
                        <label class="form-check-label" for="{$object_selector_multi.id|escape}_selected_{$smarty.foreach.ix.index}">{$object.title|escape}</label>
                    </div>
                {/foreach}
            </div>
            <p class="no-results d-none">
                {tr}No matching results.{/tr}
            </p>
        </div>
    </div>
</div>

{jq}
$('#{{$object_selector_multi.id|escape}}')
    .object_selector_multi();
{/jq}
