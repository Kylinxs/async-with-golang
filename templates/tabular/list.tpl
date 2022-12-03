
{extends 'layout_view.tpl'}

{block name="title"}
    {title}{$title|escape}{/title}
{/block}

{block name="navigation"}
    {include file='templates/tabular/include_tabular_navbar.tpl' mode='list'}
{/block}

{block name="content"}
{if !empty($filters.primary.usable)}
    <form method="get" action="{service controller=tabular action=list}">
        {foreach $filters.primary.controls as $filter}
            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label" for="{$filter.id|escape}">{$filter.label|escape}</label>
                <div class="col-sm-9">
                    {$filter.control}
                </div>
            </div>
        {/foreach}
        <div class="submit mb-3 row">
            <div class="hidden">
                <input type="hidden" name="tabularId" value="{$tabularId|escape}">
                {* Include default filters to preserve them *}
                {* Exclude side filters to reset them, as they are secondary *}
                {foreach $filters.default.controls as $filter}
                    {$filter.control}
                {/foreach}
            </div>
            <div class="col-sm-9 offset-sm-3">
                <input class="btn btn-secondary" type="submit" value="{tr}Search{/tr}">
            </div>
        </div>
    </form>
{/if}
{if !empty($filters.default.selected)}
    <h4>{tr}Applied filters{/tr}</h4>
    <dl class="row mx-0">
        {foreach $filters.default.controls as $filter}
            {if !empty($filter.selected)}
                <dt class="col-sm-3">{$filter.label|escape}</dt><dd class="col-sm-9">{$filter.description|escape}</dd>
            {/if}
        {/foreach}
    </dl>
{/if}
<div class="table-responsive">
{if !empty($filters.side.usable)}
    <div class="row">
        <div class="col-sm-9">
            <table class="table">
                <tr>
                    {foreach $columns as $column}
                        <th class="text-{$column->getDisplayAlign()|escape}">{$column->getLabel()}</th>
                    {/foreach}
                </tr>
                {foreach $data as $row}
                    <tr>
                        {foreach $row as $i => $col}
                            <td class="text-{$columns[$i]->getDisplayAlign()|escape}">{$col}</td>
                        {/foreach}
                    </tr>
                {/foreach}
            </table>
            {pagination_links resultset=$resultset}{service controller=tabular action=list tabularId=$tabularId _params=$baseArguments}{/pagination_links}
        </div>
        <div class="col-sm-3">
            <form method="get" action="{service controller=tabular action=list}">
                {foreach $filters.side.controls as $filter}
                    <div class="mb-3 row">
                        <label class="col-form-label" for="{$filter.id|escape}">{$filter.label|escape}</label>
                        {$filter.control}
                    </div>
                {/foreach}
                <div class="mb-3 submit">
                    <div class="hidden">
                        <input type="hidden" name="tabularId" value="{$tabularId|escape}">

                        {* Include default filters to preserve them *}
                        {* Include primary filters to preserve them, as they are higher *}
                        {foreach $filters.default.controls as $filter}
                            {$filter.control}
                        {/foreach}
                        {foreach $filters.primary.controls as $filter}
                            {$filter.control}
                        {/foreach}
                    </div>
                    <input class="btn btn-primary" type="submit" value="{tr}Filter{/tr}">
                </div>
            </form>
        </div>
    </div>
{else}
    <table class="table">
        <tr>
            {foreach $columns as $column}
                <th class="text-{$column->getDisplayAlign()|escape}">{$column->getLabel()}</th>
            {/foreach}
        </tr>
        {foreach $data as $row}
            <tr>
                {foreach $row as $i => $col}
                    <td class="text-{$columns[$i]->getDisplayAlign()|escape}">{$col}</td>
                {/foreach}
            </tr>
        {/foreach}
    </table>
    {pagination_links resultset=$resultset}{service controller=tabular action=list tabularId=$tabularId _params=$baseArguments}{/pagination_links}
{/if}
</div>{* .table-responsive END *}
{/block}