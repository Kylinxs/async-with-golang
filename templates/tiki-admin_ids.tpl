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
                       