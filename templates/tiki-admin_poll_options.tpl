
{title url="tiki-admin_poll_options.php?pollId=$pollId"}{tr}Admin Polls:{/tr} {$menu_info.title}{/title}

<div class="t_navbar btn-group mb-3">
    {button href="tiki-admin_polls.php" class="btn btn-info" _class="me-2" _icon_name="list" _text="{tr}List{/tr}"}
    {button href="tiki-admin_polls.php?pollId=$pollId" class="btn btn-primary" _icon_name="edit" _text="{tr}Edit{/tr}"}
</div>

<h2>{tr}Preview poll{/tr}</h2>
<div class="row">
    <div class="col-sm-4 offset-sm-4">
        <div class="card">
            <div class="card-header">{$menu_info.name}</div>
            <div class="card-body">
                {include file='tiki-poll.tpl'}
            </div>
        </div>
    </div>
</div>

<h2>{if $optionId eq ''}{tr}Add poll option{/tr}{else}{tr}Edit poll option{/tr}{/if}</h2>
<form action="tiki-admin_poll_options.php" method="post">
    {ticket}
    <input type="hidden" name="optionId" value="{$optionId|escape}">
    <input type="hidden" name="pollId" value="{$pollId|escape}">

    <div class="mb-3 row">
        <label class="col-sm-3 col-form-label">{tr}Option{/tr}</label>
        <div class="col-sm-7">
            <input type="text" name="title" value="{$title|escape}" maxlength="40" class="form-control">
        </div>
    </div>
    <div class="mb-3 row">
        <label class="col-sm-3 col-form-label">{tr}Position{/tr}</label>
        <div class="col-sm-7">
            <input type="text" name="position" value="{$position|escape}" maxlength="4" class="form-control">
        </div>
    </div>
    <div class="mb-3 row">
        <label class="col-sm-3 col-form-label"></label>
        <div class="col-sm-7">
            <input type="submit" class="btn btn-primary" name="save" value="{tr}Save{/tr}">
        </div>
    </div>
</form>
<br>
<h2>{tr}Poll options{/tr}</h2>
<div align="center">
    <table class="table table-striped table-hover">
        <tr>
            <th>{tr}Position{/tr}</th>
            <th>{tr}Title{/tr}</th>
            <th>{tr}Votes{/tr}</th>
            <th></th>
        </tr>

        {section name=user loop=$channels}
            <tr>
                <td class="id">{$channels[user].position}</td>
                <td class="text">{$channels[user].title|escape}</td>
                <td class="integer">{$channels[user].votes}</td>
                <td class="action">
                    {actions}
                        {strip}
                            <action>
                                <a href="tiki-admin_poll_options.php?pollId={$pollId}&amp;optionId={$channels[user].optionId}">
                                    {icon name='edit' _menu_text='y' _menu_icon='y' alt="{tr}Edit{/tr}"}
                                </a>
                            </action>
                            <action>
                                <form action="tiki-admin_poll_options.php" method="post">
                                    {ticket}
                                    <input type="hidden" name="pollId" value="{$pollId|escape:'attr'}">
                                    <input type="hidden" name="remove" value="{$channels[user].optionId|escape:'attr'}">
                                    <button type="submit" class="btn btn-link link-list" onclick="confirmPopup('{tr}Remove poll option?{/tr}')">
                                        {icon name="remove"} {tr}Remove{/tr}
                                    </button>
                                </form>
                            </action>
                        {/strip}
                    {/actions}
                </td>
            </tr>
        {sectionelse}
            {norecords _colspan=4}
        {/section}
    </table>
</div>