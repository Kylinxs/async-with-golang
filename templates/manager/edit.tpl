
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
        <form method="post" action="{service controller=manager action=edit}" id="tiki-manager-edit-instance">
            <input required value="{$inputValues['instance']}" class="form-control" id="instance" type="hidden" name="instance">
            <div class="form-group row mb-3">
                <label class="col-form-label col-sm-3">
                    {tr}Instance name{/tr}
                    <a class="tikihelp text-info" title="{tr}Description:{/tr} {tr}{$help.name}{/tr}">
                        {icon name=information}
                    </a>
                    </label>
                <div class="col-sm-9">
                    <input required value="{$inputValues['name']}" class="form-control" id="name" type="text" name="name">
                </div>
            </div>
            <div class="form-group row mb-3">
                <label class="col-form-label col-sm-3">
                    {tr}Instance URL{/tr}
                    <a class="tikihelp text-info" title="{tr}Description:{/tr} {tr}{$help.url}{/tr}">
                        {icon name=information}
                    </a>
                    </label>
                <div class="col-sm-9">
                    <input required value="{$inputValues['url']}" class="form-control" id="url" type="url" name="url" placeholder="example.org">
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
                    <input required value="{$inputValues['email']}" class="form-control" id="email" type="email" name="email" placeholder="johndoe@example.org">
                </div>
            </div>
            <div class="form-group row mb-3">
                <label class="col-form-label col-sm-3">
                    {tr}Instance Webroot{/tr}
                    <a class="tikihelp text-info" title="{tr}Description:{/tr} {tr}{$help.webroot}{/tr}">
                        {icon name=information}
                    </a>
                    </label>
                <div class="col-sm-9">
                    <input required value="{$inputValues['webroot']}" class="form-control" id="webroot" type="text" name="webroot" placeholder="/var/www/html">
                </div>
            </div>
            <div class="form-group row mb-3">
                <label class="col-form-label col-sm-3">
                    {tr}Working directory{/tr}
                    <a class="tikihelp text-info" title="{tr}Description:{/tr} {tr}{$help.tempdir}{/tr}">
                        {icon name=information}
                    </a>
                    </label>
                <div class="col-sm-9">
                    <input required value="{$inputValues['temp_dir']}" class="form-control" id="tempdir" type="text" name="tempdir">
                </div>
            </div>
            <div class="form-group row mb-3">
                <label class="col-form-label col-sm-3">
                    {tr}Backup User{/tr}
                    <a class="tikihelp text-info" title="{tr}Description:{/tr} {tr}{$help['backup-user']}{/tr}">
                        {icon name=information}
                    </a>
                    </label>
                <div class="col-sm-9">
                    <input required value="{$inputValues['backup_user']}" class="form-control" id="backup_user" type="text" name="backup_user">
                </div>
            </div>
            <div class="form-group row mb-3">
                <label class="col-form-label col-sm-3">
                    {tr}Backup Group{/tr}
                    <a class="tikihelp text-info" title="{tr}Description:{/tr} {tr}{$help['backup-group']}{/tr}">
                        {icon name=information}
                    </a>
                </label>
                <div class="col-sm-9">
                    <input required value="{$inputValues['backup_group']}" class="form-control" id="backup_group" type="text" name="backup_group">
                </div>
            </div>
            <div class="form-group row mb-3">
                <label class="col-form-label col-sm-3">
                    {tr}Backup Permission{/tr}
                    <a class="tikihelp text-info" title="{tr}Description:{/tr} {tr}{$help['backup-permission']}{/tr}">
                        {icon name=information}
                    </a>
                </label>
                <div class="col-sm-9">
                    <input required value="{$inputValues['backup_permission']}" placeholder="777" class="form-control" id="backup_permission" type="text" name="backup_permission">
                </div>
            </div>
            <div class="form-group row mb-3">
                <label class="col-form-label col-sm-3"></label>
                <div class="col-sm-9">
                    <input class="btn btn-primary" type="submit" name="edit" value="{tr}Edit instance{/tr}">
                </div>
            </div>
        </form>
    {/if}
{/block}