
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
        <form method="post" action="{service controller=manager action=setup_watch}" id="tiki-manager-setup-watch">
            <div class="form-group row mb-3">
                <label class="col-form-label col-sm-3">
                    {tr}Email{/tr}
                    <a class="tikihelp text-info" title="{tr}Description:{/tr} {tr}{$help.email}{/tr}">
                        {icon name=information}
                    </a>
                    </label>
                <div class="col-sm-9">
                    <input required class="form-control" id="email" type="email" name="email" placeholder="johndoe@example.org">
                </div>
            </div>
            <div class="form-group row mb-3">
                <label class="col-form-label col-sm-3">
                    {tr}Time{/tr}
                    <a class="tikihelp text-info" title="{tr}Description:{/tr} {tr}{$help.time}{/tr}">
                        {icon name=information}
                    </a>
                    </label>
                <div class="col-sm-9">
                    <input required class="form-control" id="time" type="time" name="time">
                </div>
            </div>
            <div class="form-group row mb-3">
                <label class="col-form-label col-sm-3">
                    {tr}Instances To Exclude{/tr}
                    <a class="tikihelp text-info" title="{tr}Description:{/tr} {tr}{$help.exclude}{/tr}">
                        {icon name=information}
                    </a>
                </label>
                <div class="col-sm-9">
                    <select class="form-control" id="exclude" name="exclude[]" multiple>
                        {foreach item=instance from=$instances}
                            <option value="{$instance->id|escape}">{$instance->name|escape}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="form-group row mb-3">
                <label class="col-form-label col-sm-3"></label>
                <div class="col-sm-9">
                    <input class="btn btn-primary" type="submit" name="setup" value="{tr}Setup{/tr}">
                </div>
            </div>
        </form>
    {/if}
{/block}