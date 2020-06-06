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
        <form method="post" action="{service controller=manager action=apply }" id="tiki-manager-apply-profile">
            <input required class="form-control" id="instanceId" value="{$instanceId}" type="hidden" name="instanceId">
            {include file="manager/apply_fields.tpl"}
            <div class="form-group row mb-3">
                <label class="col-form-label col-sm-3"></label>
                <div class="col-sm-9">
                    <input class="btn btn-primary" type="submit" name="apply" value="{tr}Apply profile{/tr}">
                </div>
            </div>
        </form>
    {/if}
{/block}
