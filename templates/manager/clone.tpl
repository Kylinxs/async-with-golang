
{extends "layout_view.tpl"}

{block name="title"}
    {title}{$title}{/title}
{/block}

{block name="navigation"}
    {include file='manager/nav.tpl'}
{/block}

{block name="content"}
    {if not empty($info)}
        <div class="rounded bg-dark text-light p-3">{$info|nl2br}</div>
    {else}
        <form method="post" action="{service controller=manager action=clone}">
            {foreach item=option from=$options}
            <div class="form-group row p-2">
                <label class="col-form-label col-sm-3">
                    {tr}{$option.label}{/tr}
                    <a class="tikihelp text-info" title="{tr}{$option.label}:{/tr} {tr}{$option.help}{/tr}">
                        {icon name=information}
                    </a>
                </label>
                <div class="col-sm-9">
                    {if $option.type eq 'select'}
                    <select class="form-control" name="options[--{$option.name}]{if !empty($option.is_array)}[]{/if}">
                        {foreach item=value key=key from=$option.values}
                            <option value="{$key|escape}" {if $option.selected eq $key}selected="selected"{/if}>{$value|escape}</option>
                        {/foreach}
                    </select>
                    {elseif $option.type eq 'checkbox'}
                    <input value="1" class="form-check-input" type="checkbox" name="options[--{$option.name}]{if !empty($option.is_array)}[]{/if}" {if $option.selected}checked="checked"{/if}>
                    {else}
                        <input value="{$option.selected}" placeholder="" class="form-control" type="text" name="options[--{$option.name}]{if !empty($option.is_array)}[]{/if}">
                    {/if}
                </div>
            </div>
            {/foreach}

            <div class="form-group row p-2">
                <label class="col-form-label col-sm-3"></label>
                <div class="col-sm-9">
                    <input class="btn btn-primary" type="submit" name="clone" value="{tr}Clone instance{/tr}">
                </div>
            </div>
        </form>
    {/if}
{/block}