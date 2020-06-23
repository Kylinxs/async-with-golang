{extends 'layout_view.tpl'}

{block name="title"}
    {title}{$title|escape}{/title}
{/block}

{block name="content"}
<form method="post" action="{service controller=tracker action=add_field}">
    <div class="mb-3 row mx-0">
        <label for="name" class="col-form-label">{tr}Name{/tr}</label>
        <input type="text" name="name" id="name" value="{$name|escape}" required="required" class="form-control">
    </div>
    <div class="mb-3 row mx-0" style="display: none;">
        <label for="permName" class="col-form-label">{tr}Permanent name{/tr}</label>
        <input type="text" name="permName" id="permName" value="{$permName|escape}" pattern="[a-zA-Z0-9_]+" class="form-control">
        <input type="hidden" id="fieldPrefix" value="{$fieldPrefix|escape}">
    </div>
    <div class="mb-3 row mx-0">
        <label for="type" class="col-form-label">{tr}Type{/tr}</label>
        <select name="type" id="type" class="form-select">
            {foreach from=$types key=k item=info}
                <option value="{$k|escape}"
                    {if $type eq $k}selected="selected"{/if}>
            