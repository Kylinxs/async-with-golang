
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
        <form method="post" action="{service controller=manager action=backup}" id="tiki-manager-backup-instance">
            <input id="instanceId" type="hidden" name="instanceId" value="{$inputValues['instanceId']}">
            <div class="form-group row mb-3 preference">
                <label class="col-form-label col-sm-3">
                    {tr}Backup Type{/tr}
                    <a class="tikihelp text-info" title="{tr}Description:{/tr} {tr}{$help.partial}{/tr}">
                        {icon name=information}
                    </a>
                </label>
                <div class="col-sm-9">
                    <div class="row">
                    {foreach item=type from=$inputValues['backup_process']}
                        <div class="col-sm-3">
                            <input type="radio" class="form-check-input" id="backup_process" name="backup_process" value="{$type|escape}" {if  $type eq 'full backup'}checked{/if}>
                            <label class="form-check-label" for="backup_process">{$type|upper}</label>
                        </div>
                    {/foreach}
                    </div>
                </div>
            </div>
            <div class="form-group row mb-3">
                <label class="col-form-label col-sm-3">
                    {tr}Email{/tr}
                    <a class="tikihelp text-info" title="{tr}Description:{/tr} {tr}{$help.email}{/tr}">
                        {icon name=information}
                    </a>
                </label>
                <div class="col-sm-9">
                    <input required value="{$inputValues['email']}" class="form-control" id="name" type="text" name="email" placeholder="johndoe@example.org">
                    <div class="form-text">{tr}You can add several email addresses by separating them with commas.{/tr}</div>
                </div>
            </div>
            <div class="form-group row mb-3">
                <label class="col-form-label col-sm-3">
                    {tr}Max number of backups to keep{/tr}
                    <a class="tikihelp text-info" title="{tr}Description:{/tr} {tr}{$help['max-backups']}{/tr}">
                        {icon name=information}
                    </a>
                </label>
                <div class="col-sm-9">
                    <input value="" class="form-control" id="number_backups_to_keep" type="text" name="number_backups_to_keep" placeholder="100">
                </div>
            </div>
            <div class="form-group row mb-3">
                <label class="col-form-label col-sm-3"></label>
                <div class="col-sm-9">
                    <input class="btn btn-primary" type="submit" name="backup" value="{tr}Backup instance{/tr}">
                </div>
            </div>
        </form>
    {/if}
{/block}