
{title help="FeaturedLinksAdmin"}{tr}Featured Links{/tr}{/title}

{remarksbox type="tip" title="{tr}Tip{/tr}"}{tr}To use these links, you must assign the featured_links <a class="alert-link" href="tiki-admin_modules.php">module</a>.{/tr}{/remarksbox}

<div class="t_navbar">
    {button href="tiki-admin_links.php?generate=1" _icon_name="ranking" _text="{tr}Generate positions by hits{/tr}"}
</div>

<h2>{tr}List of featured links{/tr}</h2>
<div class="{if $js}table-responsive{/if}"> {* table-responsive class cuts off css drop-down menus *}
    <table class="table table-striped table-hover">
        <tr>
            <th>{tr}URL{/tr}</th>
            <th>{tr}Title{/tr}</th>
            <th>{tr}Hits{/tr}</th>
            <th>{tr}Position{/tr}</th>
            <th>{tr}Type{/tr}</th>
            <th></th>
        </tr>

        {section name=user loop=$links}
            <tr>
                <td class="text">{$links[user].url}</td>
                <td class="text">{$links[user].title|escape}</td>
                <td class="integer">{$links[user].hits}</td>
                <td class="id">{$links[user].position}</td>
                <td class="text">{$links[user].type}</td>
                <td class="action">
                    {actions}
                        {strip}
                            <action>
                                <a href="tiki-admin_links.php?editurl={$links[user].url|escape:"url"}">
                                    {icon name='edit' _menu_text='y' _menu_icon='y' alt="{tr}Edit{/tr}"}
                                </a>
                            </action>
                            <action>
                                <a href="tiki-admin_links.php?remove={$links[user].url|escape:"url"}" onclick="confirmPopup('{tr}Remove featured link?{/tr}', '{ticket mode=get}')">
                                    {icon name='remove' _menu_text='y' _menu_icon='y' alt="{tr}Remove{/tr}"}
                                </a>
                            </action>
                        {/strip}
                    {/actions}
                </td>
            </tr>
        {sectionelse}
            {norecords _colspan=6}
        {/section}
    </table>
</div>

{if $editurl eq 'n'}
    <h2>{tr}Add Featured Link{/tr}</h2>
{else}
    <h2>{tr}Edit this Featured Link:{/tr} {$title}</h2>
    <a href="tiki-admin_links.php">{tr}Create new Featured Link{/tr}</a>
{/if}
<form action="tiki-admin_links.php" method="post">
    {ticket}
    <div class="mb-3 row">
        <label class="col-sm-3 col-form-label">URL</label>
        <div class="col-sm-7 offset-sm-1 mb-3">
            {if $editurl eq 'n'}
                <input type="text" name="url" class="form-control">
            {else}
                {$editurl}
                <input type="hidden" name="url" value="{$editurl|escape}">
                <input type="hidden" name="editurl" value="{$editurl|escape}">
            {/if}
        </div>
    </div>
    <div class="mb-3 row">
        <label class="col-sm-3 col-form-label">{tr}Title{/tr}</label>
        <div class="col-sm-7 offset-sm-1 mb-3">
            <input type="text" name="title" value="{$title|escape}" class="form-control">
        </div>
    </div>
    <div class="mb-3 row">
        <label class="col-sm-3 col-form-label">{tr}Position{/tr}</label>
        <div class="col-sm-7 offset-sm-1 mb-3">
            <input type="text" size="3" name="position" value="{$position|escape}" class="form-control">
            <div class="small-hint">
                (0 {tr}disables the link{/tr})
            </div>
        </div>
    </div>
    <div class="mb-3 row">
        <label class="col-sm-3 col-form-label">{tr}Link type{/tr}</label>
        <div class="col-sm-7 offset-sm-1 mb-3">
            <select name="type" class="form-control">
                <option value="r" {if $type eq 'r'}selected="selected"{/if}>{tr}replace current page{/tr}</option>
                <option value="f" {if $type eq 'f'}selected="selected"{/if}>{tr}framed{/tr}</option>
                <option value="n" {if $type eq 'n'}selected="selected"{/if}>{tr}open new window{/tr}</option>
            </select>
        </div>
    </div>
    <div class="mb-3 row">
        <label class="col-sm-3 col-form-label"></label>
        <div class="col-sm-7 offset-sm-1 mb-3">
            <input type="submit" class="btn btn-primary btn-sm" name="add" value="{tr}Save{/tr}">
        </div>
    </div>
</form>