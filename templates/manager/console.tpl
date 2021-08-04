
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
        <form method="post" action="{service controller=manager action=console }">
            <input required class="form-control" id="instanceId" value="{$instanceId}" type="hidden" name="instanceId">
            <div class="form-group row">
                <label class="col-form-label col-sm-3">{tr}Write command to execute{/tr}</label>
                <div class="col-sm-9">
                    <input required placeholder="e.g. manager:instance:list" class="form-control" id="command" type="text" name="command">
                </div>
            </div>
            <div class="form-group row">
                <label class="col-form-label col-sm-3"></label>
                <div class="col-sm-9">
                    <input class="btn btn-primary" type="submit" name="run" value="{tr}Run Command{/tr}">
                </div>
            </div>
        </form>
    {/if}
{/block}