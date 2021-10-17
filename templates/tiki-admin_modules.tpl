
{* $Id$ *}

{strip}
{title help="Modules" admpage="module"}{tr}Admin Modules{/tr}{/title}

<div class="t_navbar mb-4">
    <form action="tiki-admin_modules.php" method="post" style="display: inline">
        {ticket}
        <button type="submit" name="clear_cache" value="1" class="btn btn-primary">
            {icon name="trash"} {tr}Clear Cache{/tr}
        </button>
    </form>
    {if empty($smarty.request.show_hidden_modules)}
        {button show_hidden_modules="y" _icon_name="ok" _text="{tr}Show hidden modules{/tr}"}
    {else}
        {button show_hidden_modules="" _icon_name="disable" _text="{tr}Hide hidden modules{/tr}"}
    {/if}
    {button _text="{tr}Save{/tr}" _type="primary" _icon_name="floppy" _id="save_modules" _ajax="n"}
    {if $tiki_p_edit_menu eq 'y'}
        {button href="tiki-admin_menus.php" _icon_name="menu" _type="link" _text="{tr}Admin Menus{/tr}"}
    {/if}
    {button href="./" _icon_name="disable" _type="link" _text="{tr}Exit Modules{/tr}"}
</div>

{if !empty($missing_params)}
    {remarksbox type="warning" title="{tr}Modules Parameters{/tr}"}
        {tr}The following required parameters are missing:{/tr}<br/>
        {section name=ix loop=$missing_params}
            {$missing_params[ix]}
            {if !$smarty.section.ix.last},&nbsp;{/if}
        {/section}
    {/remarksbox}
{/if}

{remarksbox type="note" title="{tr}Modules{/tr}" icon="star"}
    <ul>
        <li>{tr}Drag the modules around to re-order then click save when ready{/tr}</li>
        <li>{tr}Double click them to edit{/tr}</li>
        <li>{tr}Modules with "position: absolute" in their style can be dragged in to position{/tr}</li>
        <li>{tr}New modules can be dragged from the "All Modules" tab{/tr}</li>
    </ul>
    <p>
        <strong>{tr}Note:{/tr}</strong> {tr}Links and buttons in modules, apart from the Application Menu, have been deliberately disabled on this page to make drag and drop more reliable. Click here to return <a href="./" class="alert-link">HOME</a>{/tr}<br>
        <strong><em>{tr}More info here{/tr}</em></strong> {icon name="help" href="http://dev.tiki.org/Modules+Revamp" class="alert-link"}
    </p>

{/remarksbox}

{tabset}

    {tab name="{tr}Assigned modules{/tr}"}
        {if $prefs.feature_tabs neq 'y'}
            <legend class="heading">
                <span>
                    {tr}Assign/Edit modules{/tr}
                </span>
            </legend>
        {/if}
        <h2>{tr}Assigned Modules{/tr}</h2>
        <div class="mb-4">
            {button edit_assign=0 cookietab=2 _auto_args="edit_assign,cookietab" _text="{tr}Add module{/tr}"}
        </div>

        <div id="assigned_modules">
            {if $userHasAssignedModules}
                {remarksbox type="warning" title="{tr}Warning{/tr}"}
                    {tr}You will need to go{/tr} <a href="tiki-user_assigned_modules.php">{tr}here{/tr}</a> {tr}to reorder or move modules in the left or right columns since you have created a custom order for these.{/tr} {tr}Use the table below to assign previously unassigned modules, or reorder and move modules where there is no custom order created by the user.{/tr}
                {/remarksbox}
            {/if}
            {tabset}
                {foreach $module_zone_list as $zone_initial => $zone_info}
                    {tab name=$zone_info.name|capitalize}
                        <div id="{$zone_info.id}_modules" class="{if $js}table-responsive{/if}"> {* table-responsive class cuts off css drop-down menus *}
                            <div>
                                <table class="table table-striped table-hover" id="assigned_zone_{$zone_initial}">
                                    <tr>
                                        <th>{tr}Name{/tr}</th>
                                        <th>{tr}Order{/tr}</th>
                                        <th>{tr}Cache{/tr}</th>
                                        <th>{tr}Rows{/tr}</th>
                                        <th>{tr}Parameters{/tr}</th>
                                        <th>{tr}Groups{/tr}</th>
                                        <th></th>
                                    </tr>

                                    {foreach $assigned_modules[$zone_initial] as $module}
                                        <tr>
                                            <td>{$module.name|escape}</td>
                                            <td>{$module.ord}</td>
                                            <td>{$module.cache_time}</td>
                                            <td>{$module.rows}</td>
                                            <td class="small">{$module.params_presentable}</td>
                                            <td class="small">{$module.module_groups}</td>
                                            <td>
                                                {actions}
                                                    {strip}
                                                        <action>
                                                            <form href="tiki-admin_modules.php" method="post">
                                                                {ticket}
                                                                <button
                                                                    type="submit"
                                                                    name="modup"
                                                                    value="{$module.moduleId}"
                                                                    class="btn btn-link link-list"
                                                                    {if $module@first} disabled="disabled"{/if}
                                                                >
                                                                    {icon name="up"} {tr}Move up{/tr}
                                                                </button>
                                                            </form>
                                                        </action>
                                                        <action>
                                                            <form href="tiki-admin_modules.php" method="post">
                                                                {ticket}
                                                                <button
                                                                    type="submit"
                                                                    name="moddown"
                                                                    value="{$module.moduleId}"
                                                                    class="btn btn-link link-list"
                                                                    {if $module@last} disabled="disabled" class="disabled"{/if}
                                                                >
                                                                    {icon name="down"} {tr}Move down{/tr}
                                                                </button>
                                                            </form>
                                                        </action>
                                                        <action>
                                                            <a href="tiki-admin_modules.php?edit_assign={$module.moduleId}&cookietab=2#content_admin_modules1-2">
                                                                {icon name='edit' _menu_text='y' _menu_icon='y' alt="{tr}Edit{/tr}"}
                                                            </a>
                                                        </action>
                                                        <action>
                                                            <form href="tiki-admin_modules.php" method="post">
                                                                {ticket}
                                                                <button
                                                                    type="submit"
                                                                    name="unassign"
                                                                    value="{$module.moduleId}"
                                                                    class="btn btn-link link-list"
                                                                >
                                                                    {icon name="remove"} {tr}Unassign{/tr}
                                                                </button>
                                                            </form>
                                                        </action>
                                                    {/strip}
                                                {/actions}
                                            </td>
                                        </tr>
                                    {foreachelse}
                                        {norecords _colspan=7}
                                    {/foreach}
                                </table>
                            </div>
                        </div>
                    {/tab}
                {/foreach}
            {/tabset}
        </div>
        <form method="post" action="#">
            {ticket}
            <input id="module-order" type="hidden" name="module-order" value="">
        </form>
    {/tab}
    {if isset($smarty.request.edit_assign) or $preview eq "y"}
        {tab name="{tr}Edit module{/tr}"}
            <a id="assign"></a>
            {if $assign_name eq ''}
                <h2>{tr}Assign new module{/tr}</h2>
            {else}
                <h2>{tr}Edit this assigned module:{/tr} {$assign_name}</h2>
            {/if}

            {if $preview eq 'y'}
                <h3>{tr}Preview{/tr}</h3>
                {$preview_data}
            {/if}
            <form method="post" action="tiki-admin_modules.php{if empty($assign_name)}?cookietab=2#assign{/if}">
                {* on the initial selection of a new module, reload the page to the #assign anchor *}
                {if !empty($info.moduleId)}
                    <input type="hidden" name="moduleId" value="{$info.moduleId}">
                {elseif !empty($moduleId)}
                    <input type="hidden" name="moduleId" value="{$moduleId}">
                {/if}
                <fieldset>
                        {* because changing the module name will auto-submit the form, no reason to display these fields until a module is selected *}
                        {include file='admin_modules_form.tpl'}
                    {if empty($assign_name)}
                        <div class="input_submit_container">
                            <input type="submit" class="btn btn-primary btn-sm" name="preview" value="{tr}Module Options{/tr}">
                        </div>
                    {else}
                        {jq}$("#module_params").tabs();{/jq}
                    {/if}
                </fieldset>
            </form>
        {/tab}
    {/if}

    {tab name="{tr}Custom Modules{/tr}"}
        {if $prefs.feature_tabs neq 'y'}
            <legend class="heading">
                <a href="#usertheme" name="usertheme"><span>{tr}Custom Modules{/tr}</span></a>
            </legend>
        {/if}
        <h2>{tr}Custom Modules{/tr}</h2>
        <div class="{if $js}table-responsive{/if}">
            <table class="table">
                <tr>
                    <th>{tr}Name{/tr}</th>
                    <th>{tr}Title{/tr}</th>
                    <th></th>
                </tr>

                {section name=user loop=$user_modules}
                    <tr>
                        <td class="text"><a class="tips" href="tiki-admin_modules.php?um_edit={$user_modules[user].name|escape:'url'}&amp;cookietab=2#editcreate" title="{tr}Edit{/tr}">{$user_modules[user].name|escape}</a></td>
                        <td class="text">{$user_modules[user].title|escape}</td>
                        <td class="action">
                            {actions}
                                {strip}
                                    <action>
                                        <a href="tiki-admin_modules.php?um_edit={$user_modules[user].name|escape:'url'}&amp;cookietab=2#editcreate">
                                            {icon name='edit' _menu_text='y' _menu_icon='y' alt="{tr}Edit{/tr}"}
                                        </a>
                                    </action>
                                    <action>
                                        <a href="tiki-admin_modules.php?edit_assign={$user_modules[user].name|escape:'url'}&amp;cookietab=2#assign">
                                            {icon name='ok' _menu_text='y' _menu_icon='y' alt="{tr}Assign{/tr}"}
                                        </a>
                                    </action>
                                    <action>
                                        <form action="tiki-admin_modules.php?cookietab=2" method="post">
                                            {ticket}
                                            <button
                                                type="submit"
                                                name="um_remove"
                                                value="{$user_modules[user].name|escape}"
                                                class="btn btn-link link-list"
                                                onclick="confirmPopup('{tr}Delete custom module?{/tr}')"
                                            >
                                                {icon name='remove'} {tr}Delete{/tr}
                                            </button>
                                        </form>
                                    </action>
                                {/strip}
                            {/actions}
                        </td>
                    </tr>
                {sectionelse}
                {norecords _colspan=3}
                {/section}
            </table>
        </div>
        <br>
        {pagination_links cant=$maximum step=$maxRecords offset=$offset}{/pagination_links}
        {if $um_name eq ''}
            <h2>{tr}Create new custom module{/tr}</h2>
        {else}
            <h2>{tr}Edit this custom module{/tr} {$um_name}</h2>
        {/if}
        <div class="col-sm-10 offset-sm-2">
            {remarksbox type="tip" title="{tr}Tip{/tr}"}
                {tr}Create your new custom module below. Make sure to preview first and make sure all is OK before <a href="#assign" class="alert-link">assigning it</a>. Using HTML, you will be fine. However, if you improperly use wiki syntax or Smarty code, you could lock yourself out of the site.{/tr}
            {/remarksbox}
        </div>

        <form name='editusr' method="post" action="tiki-admin_modules.php">
            {ticket}
            <input type="hidden" name="um_tgt_module" value="{$um_tgt_module|escape}" hidden="hidden" />
            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label">{tr}Name{/tr}</label>
                <div class="col-sm-10">
                    <input type="text" id="um_name" name="um_name" value="{$um_name|escape}" class="form-control">
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label">{tr}Title{/tr}</label>
                <div class="col-sm-10">
                    <input type="text" id="um_title" name="um_title" value="{$um_title|escape}" class="form-control">
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label">{tr}Parse using{/tr}</label>
                <div class="col-sm-10">
                    <select name="um_parse" id="um_parse" class="form-control mb-3">
                        <option value=""{if $um_parse eq ""} selected="selected"{/if}>{tr}None{/tr}</option>
                        <option value="y"{if $um_parse eq "y"} selected="selected"{/if}>{tr}Wiki Markup{/tr}</option>
                    </select>
                </div>
            </div>
            <h3>{tr}Objects that can be included{/tr}</h3>
            {pagination_links cant=$maximum step=$maxRecords offset=$offset}{/pagination_links}
            {if $prefs.feature_polls eq "y"}
                <div class="mb-3 row">
                    <label class="col-sm-2 col-form-label">{tr}Polls{/tr}</label>
                    <div class="col-sm-9">
                        <select name="polls" id='list_polls' class="form-control">
                            <option value="{literal}{{/literal}poll{literal}}{/literal}">--{tr}Random active poll{/tr}--</option>
                            <option value="{literal}{{/literal}poll id=current{literal}}{/literal}">--{tr}Random current poll{/tr}--</option>
                            {section name=ix loop=$polls}
                                <option value="{literal}{{/literal}poll pollId={$polls[ix].pollId}{literal}}{/literal}">{$polls[ix].title|escape}</option>
                            {/section}
                        </select>
                    </div>
                    <div class="col-sm-1 pt-1">
                        <a class="tips" href="javascript:setUserModuleFromCombo('list_polls', 'um_data');" title=":{tr}Use poll{/tr}">{icon name='add' alt="{tr}Use{/tr}"}</a>
                        <a title="{tr}Help{/tr}" {popup text="Params: id=" width=100 center=true}>{icon name='help'}</a>
                    </div>
                </div>
            {/if}
            {if $contents}
                <div class="mb-3 row">
                    <label class="col-sm-2 col-form-label">{tr}Dynamic content blocks{/tr}</label>
                    <div class="col-sm-9">
                        <select name="contents" id='list_contents' class="form-control">
                            {section name=ix loop=$contents}
                                <option value="{literal}{{/literal}content id={$contents[ix].contentId}{literal}}{/literal}">{$contents[ix].description|truncate:20:"...":true}</option>
                            {/section}
                        </select>
                    </div>
                    <div class="col-sm-1 pt-1">
                        <a class="tips" href="javascript:setUserModuleFromCombo('list_contents', 'um_data');" title=":{tr}Use dynamic content{/tr}">{icon name='add' alt="{tr}Use{/tr}"}</a>>
                        <a title="{tr}Help{/tr}" {popup text="Params: id=" width=100 center=true}>{icon name='help'}</a>
                    </div>
                </div>
            {/if}
            {if $rsss}
                <div class="mb-3 row">
                    <label class="col-sm-2 col-form-label">{tr}Feeds{/tr}</label>
                    <div class="col-sm-9">
                        <select name="rsss" id='list_rsss' class="form-control">
                            {section name=ix loop=$rsss}
                                <option value="{literal}{{/literal}rss id={$rsss[ix].rssId}{literal}}{/literal}">{$rsss[ix].name|escape}</option>
                            {/section}
                        </select>
                    </div>
                    <div class="col-sm-1 pt-1">
                        <a class="tips" href="javascript:setUserModuleFromCombo('list_rsss', 'um_data');" title=":{tr}Use RSS module{/tr}">{icon name='add' alt="{tr}Use{/tr}"}</a>
                        <a title="{tr}Help{/tr}" {popup text="Params: id= max= skip=x,y " width=100 center=true}>{icon name='help'}</a>
                    </div>
                </div>
            {/if}
            {if $banners}
                <div class="mb-3 row">
                    <label class="col-sm-2 col-form-label">{tr}Banners{/tr}</label>
                    <div class="col-sm-9">
                        <select name="banners" id='list_banners' class="form-control">
                            {section name=ix loop=$banners}
                                <option value="{literal}{{/literal}banner zone={$banners[ix].zone}{literal}}{/literal}">{$banners[ix].zone}</option>
                            {/section}
                        </select>
                    </div>
                    <div class="col-sm-1 pt-1">
                        <a class="tips" href="javascript:setUserModuleFromCombo('list_banners', 'um_data');" title=":{tr}Use banner zone{/tr}">{icon name='add' alt="{tr}Use{/tr}"}</a>
                        <a title="{tr}Help{/tr}" {popup text="Params: zone= target=_blank|_self|" width=100 center=true}>{icon name='help'}</a>
                    </div>
                </div>
            {/if}
            {if $wikistructures}
                <div class="mb-3 row">
                    <label class="col-sm-2 col-form-label">{tr}Structures{/tr}</label>
                    <div class="col-sm-9">
                        <select name="structures" id='list_wikistructures' class="form-control">
                            {section name=ix loop=$wikistructures}
                                <option value="{literal}{{/literal}wikistructure id={$wikistructures[ix].page_ref_id}{literal}}{/literal}">{$wikistructures[ix].pageName|escape}</option>
                            {/section}
                        </select>
                    </div>
                    <div class="col-sm-1 pt-1">
                        <a class="tips" href="javascript:setUserModuleFromCombo('list_wikistructures', 'um_data');" title=":{tr}Use wiki structure{/tr}">{icon name='add' alt="{tr}Use{/tr}"}</a>
                        <a title="{tr}Help{/tr}" {popup text="Params: id=" width=100 center=true}>{icon name='help'}</a>
                    </div>
                </div>
            {/if}
            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label">{tr}Contents{/tr}</label>
                <div class="col-sm-10">
                    <a id="editcreate"></a>
                    {textarea name='um_data' id='um_data' _class='form-control' _toolbars='y' _previewConfirmExit='n' _simple="y" syntax='smarty'}{$um_data}{/textarea}
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                    <input type="submit" class="btn btn-primary" name="um_update" value="{if empty($um_name)}{tr}Create{/tr}{else}{tr}Save{/tr}{/if}" onclick="$(window).off('beforeunload');return true;">
                </div>
            </div>
        </form>
    {/tab}

    {tab name="{tr}All Modules{/tr}"}
        <h2>{tr}All Modules{/tr}</h2>
        <form method="post" action="tiki-admin_modules.php" class="">
            <div style="height:400px;overflow:auto;">
                <div class="was-navbar">
                    {listfilter selectors='#module_list li'}
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" name="module_list_show_all" id="module_list_show_all"{if $module_list_show_all} checked="checked"{/if}>
                        <label for="module_list_show_all" class="form-check-lable">{tr}Show all modules{/tr}</label>
                    </div>
                </div>
                <ul id="module_list">
                    {foreach key=name item=info from=$all_modules_info}
                        <li class="{if !empty($info.enabled)}enabled{else}disabled{/if} clearfix">
                            <input type="hidden" value="{$name}">
                            <div class="q1 tips"
                                    title="{$info.name} &lt;em&gt;({$name})&lt;/em&gt;|{$info.description}
                                    {if not $info.enabled}&lt;br /&gt;&lt;small&gt;&lt;em&gt;({tr}Requires{/tr} {' &amp; '|implode:$info.prefs})&lt;/em&gt;&lt;/small&gt;{/if}">
                                {icon name="module"} <strong>{$info.name}</strong> <em>{$name}</em>
                            </div>
                            <div class="description q23">
                                {$info.description}
                            </div>
                        </li>
                    {/foreach}
                </ul>
            </div>
        </form>
        {jq}
$("#module_list_show_all").click(function(){
    $("#module_list li.disabled").toggle($(this).prop("checked"));
});
        {/jq}
    {/tab}

{/tabset}
{/strip}