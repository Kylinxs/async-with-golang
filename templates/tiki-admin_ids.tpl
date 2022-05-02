{* $Id$ *}
{title admpage="security" url="tiki-admin_ids.php"}{tr}IDS Rules{/tr}{/title}
<div class="t_navbar mb-4">
    {if isset($ruleinfo.id)}
        {button href="?add=1" class="btn btn-primary" _text="{tr}Add a new Rule{/tr}"}
    {/if}

</div>
{tabset name='tabs_admin_ids'}

    {* ---------------------- tab with list -------------------- *}
{if $ids_rules|count > 0}
    {tab name="{tr}IDS Rules{/tr}"}
        <form {*class="form-horizontal"*} name="checkform" id="checkform" method="post">
            <div id="admin_ids-div">
                <div class="{if $js}table-responsive {/if}ts-wrapperdiv">
                    {* Use css menus as fallback for item dropdown action menu if javascript is not being used *}
                    <table id="admin_ids" class="table normal table-striped table-hover" data-count="{$ids_rules|count}">
                        <thead>
                        <tr>
                            <th>
                                {tr}Rule ID{/tr}
                            </th>
                            <th>
                                {tr}Description{/tr}
                            </th>
                            <th>
                                {tr}Tags{/tr}
                            </th>
                            <th>
                                {tr}Impact{/tr}
                            </th>
                            <th id="actions"></th>
                        </tr>
                        </thead>

                        <tbody>
                        {section name=rule loop=$ids_rules}
                            {$rule_id = $ids_rules[rule].id|escape}
                            <tr>
                                <td class="rule_name">
                                    <a class="link tips"
                                        href="tiki-admin_ids.php?rule={$ids_rules[rule].id}{if $prefs.feature_tabs ne 'y'}#2{/if}"
                                        title="{$rule_id}:{tr}Edit rule settings{/tr}"
                                    >
                                        {$rule_id}
                                    </a>
                                </td>
                                <td class="rule_description">
                                    {$ids_rules[rule].description|escape}
                                </td>
                                <td class="rule_tags">
                                    {$ids_rules[rule].tags|escape}
                                </td>
                                <td class="rule_impact">
                                    {$ids_rules[rule].impact|escape}
                                </td>

                                <td class="action">
                                    {actions}
                                        {strip}
                                            <action>
                                                <a href="{query _type='relative' rule=$ids_rules[rule].id}">
                                                    {icon name="edit" _menu_text='y' _menu_icon='y' alt="{tr}Edit{/tr}"}
                                                </a>
                                            </action>
                                            <action>
                                                <a href="{bootstrap_modal controller=ids action=remove ruleId=$ids_rules[rule].id}">
                                                    {icon name="remove" _menu_text='y' _menu_icon='y' alt="{tr}Delete{/tr}"}
                                                </a>
                                            </action>
                                        {/strip}
                                    {/actions}
                            </tr>
                        {/section}
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    {/tab}
{/if}
    {* ---------------------- tab with form -------------------- *}
    <a id="tab2"></a>
{if isset($ruleinfo.id) && $ruleinfo.id && !$ruleinfo.error}
    {$add_edit_rule_tablabel = "{tr}Edit Rule{/tr}"}
    {$rulename = "<i>{$ruleinfo.name|escape}</i>"}
{else}
    {$add_edit_rule_tablabel = "{tr}Add a new rule{/tr}"}
    {$rulename = ""}
{/if}

{tab name="{$add_edit_rule_tablabel} {$rulename}"}
    <br><br>
    <form class="form{*}form-horizontal*}" action="tiki-admin_ids.php" method="post"
            enctype="multipart/form-data" name="RegForm" autocomplete="off">
        {ticket}
        <div class="mb-3 row">
            <label class="col-sm-3 col-md-2 col-form-label" for="rule_id">{tr}Rule Id{/tr}</label>
            <div class="col-sm-7 col-md-6">
                <input type="text" id='rule_id' class="form-control" name='rule_id'
                    value="{$ruleinfo.id|escape}" {if $ruleinfo.id && !$ruleinfo.error}readonly{/if}>
                <span class="form-text">{tr}Rule Id must be numeric{/tr}</span>
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-sm-3 col-md-2 col-form-label" for="rule_regex">{tr}Rule Regex{/tr}</label>
            <div class="col-sm-7 col-md-6">
                <input type="text" id='rule_regex' class="form-control" name='rule_regex'
                       value="{$ruleinfo.regex|escape}">
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-sm-3 col-md-2 col-form-label" for="rule_description">{tr}Description{/tr}</label>
            <div class="col-sm-7 col-md-6">
                <input type="text" id='rule_description' class="form-control" name='rule_description'
                    value="{$ruleinfo.description|escape}">
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-md-2 col-form-label" for="rule_tags">{tr}Tags{/tr}</label>
            <div class="col-sm-7 col-md-6">
                <input type="text" id='rule_tags' class="form-control" name='rule_tags'
                    value="{$ruleinfo.tags|escape}">
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-md-2 col-form-label" for="rule_impact">{tr}Impact{/tr}</label>
            <div class="col-sm-7 col-md-6">
                <input type="text" id='rule_impact' class="form-control" name='rule_impact'
                       value="{$ruleinfo.impact|escape}">
            </div>
        </div>

        <div class="mb-3 row">
            <div class="col-sm-7 col-md-6 offset-sm-3 offset-md-2">
                {if isset($ruleinfo.id) && $ruleinfo.id && !$ruleinfo.error}
                    <input type="hidden" name="rule" value="{$ruleinfo.id|escape}">
                    <input type="hidden" name="editrule" value="1">
                    <input type="submit" class="btn btn-primary" name="save" value="{tr}Save{/tr}">
                {else}
                    <input type="submit" class="btn btn-secondary" name="new_rule" value="{tr}Add{/tr}">
                {/if}
            </div>
        </div>

    </form>
{/tab}
{/tabset}
