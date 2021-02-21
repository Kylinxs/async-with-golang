
{* $Id$ *}
{title url=$trackerId|sefurl:'tracker' adm="trackers"}{tr}{$tracker_info.name}{/tr}{/title}
{if !empty($tracker_info.description)}
    {if $tracker_info.descriptionIsParsed eq 'y'}
        <div class="description form-text mb-2">{wiki}{$tracker_info.description}{/wiki}</div>
    {else}
        <div class="description form-text mb-2">{$tracker_info.description|escape|nl2br}</div>
    {/if}
{/if}
<div class="t_navbar mb-4">
    {if $tiki_p_create_tracker_items eq 'y' && $prefs.tracker_legacy_insert neq 'y'}
        {if $fields_count gt 0}
            <a class="btn btn-primary" href="{bootstrap_modal controller=tracker action=insert_item trackerId=$trackerId size='modal-lg'}">
                {icon name="create"} {tr}Create Item{/tr}
            </a>
        {else}
            <a class="btn btn-primary disabled" aria-disabled="true" href="{bootstrap_modal controller=tracker action=insert_item trackerId=$trackerId size='modal-lg'}">
                {icon name="create"} {tr}Create Item{/tr}
            </a>
        {/if}
    {/if}
    {include file="tracker_actions.tpl" showitems="n"}
    <div class="btn-group float-sm-end">
        {if ! $js}<ul><li>{/if}
                {if $prefs.feature_group_watches eq 'y' or $prefs.feature_user_watches eq 'y' or $prefs.feed_tracker eq 'y' or $tiki_p_admin_trackers eq 'y' or $tiki_p_export_tracker eq 'y' or $prefs.sefurl_short_url eq 'y'}
                    <a class="btn btn-info btn-sm dropdown-toggle" data-bs-toggle="dropdown" data-hover="dropdown" href="#" title="{tr}Tracker actions{/tr}">
                        {icon name="menu-extra"}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-header">
                            {tr}Tracker actions{/tr}
                        </li>
                        <li class="dropdown-divider"></li>
                        {if $prefs.feature_group_watches eq 'y' and ( $tiki_p_admin_users eq 'y' or $tiki_p_admin eq 'y' )}
                            <li class="dropdown-item">
                                <a href="tiki-object_watches.php?objectId={$trackerId|escape:"url"}&amp;watch_event=tracker_modified&amp;objectType=tracker&amp;objectName={$tracker_info.name|escape:"url"}&amp;objectHref={'tiki-view_tracker.php?trackerId='|cat:$trackerId|escape:"url"}">
                                    {icon name="watch-group"} {tr}Group Monitoring{/tr}
                                </a>
                            </li>
                        {/if}
                        {if $prefs.feature_user_watches eq 'y' and $tiki_p_watch_trackers eq 'y' and $user}
                            <li class="dropdown-item">
                                {if $user_watching_tracker ne 'y'}
                                    <form action="tiki-view_tracker.php" method="post">
                                        <input type="hidden" name="trackerId" value="{$trackerId|escape}">
                                        {ticket}
                                        <button
                                                type="submit"
                                                name="watch"
                                                value="add"
                                                class="btn btn-link link-list"
                                        >
                                            {icon name="watch"} {tr}Monitor{/tr}
                                        </button>
                                    </form>
                                {else}
                                    <form action="tiki-view_tracker.php" method="post">
                                        <input type="hidden" name="trackerId" value="{$trackerId|escape}">
                                        {ticket}
                                        <button
                                                type="submit"
                                                name="watch"
                                                value="stop"
                                                class="btn btn-link link-list"
                                        >
                                            {icon name="stop-watching"} {tr}Stop monitoring{/tr}
                                        </button>
                                    </form>
                                {/if}
                            </li>
                        {/if}
                        {if $prefs.feed_tracker eq "y"}
                            <li class="dropdown-item">
                                <a href="tiki-tracker_rss.php?trackerId={$trackerId}">
                                    {icon name="rss"} {tr}RSS{/tr}
                                </a>
                            </li>
                        {/if}
                        {if $tiki_p_admin_trackers eq "y"}
                            <li class="dropdown-item">
                                <a class="import dialog" href="{service controller=tracker action=import_items trackerId=$trackerId}">
                                    {icon name="import"} {tr}Import{/tr}
                                </a>
                            </li>
                            {jq}
                                $('.import.dialog').click(function () {
                                    var link = this;
                                    $(this).serviceDialog({
                                        title: '{tr}Import{/tr}',
                                        data: {
                                            controller: 'tracker',
                                            action: 'import_items',
                                            trackerId: {{$trackerId}}
                                        }
                                    });
                                    return false;
                                });
                            {/jq}
                        {/if}
                        {if $tiki_p_export_tracker eq "y"}
                            <li class="dropdown-item">
                                <a class="export dialog" href="{service controller=tracker action=export trackerId=$trackerId filterfield=$filterfield filtervalue=$filtervalue}">
                                    {icon name="export"} {tr}Export{/tr}
                                </a>
                            </li>
                            {jq}
                                $('.export.dialog').click(function () {
                                    var link = this;
                                    $(this).serviceDialog({
                                        title: '{tr}Export{/tr}',
                                        data: {
                                            controller: 'tracker',
                                            action: 'export',
                                            trackerId: {{$trackerId}},
                                            filterfield: '{{$filterfield}}',
                                            filtervalue: {{$filtervalue|json_encode}}
                                        }
                                    });
                                    return false;
                                });
                            {/jq}
                        {/if}
                        {if $tiki_p_admin_trackers eq "y"}
                            <li class="dropdown-item">
                                {permission_link mode=text type=tracker id=$trackerId permType=trackers}
                            </li>
                        {/if}
                        {if $prefs.sefurl_short_url eq 'y'}
                            <li class="dropdown-item">
                                <a id="short_url_link" href="#" onclick="(function() { $(document.activeElement).attr('href', 'tiki-short_url.php?url=' + encodeURIComponent(window.location.href) + '&title=' + encodeURIComponent(document.title)); })();">
                                    {icon name="link"} {tr}Get a short URL{/tr}
                                    {assign var="hasPageAction" value="1"}
                                </a>
                            </li>
                        {/if}
                    </ul>
                {/if}
        {if ! $js}</li></ul>{/if}
    </div>
</div>

{if $user and $prefs.feature_user_watches eq 'y' and $category_watched eq 'y'}
    <div class="categbar">
        {tr}Watched by categories:{/tr}
        {section name=i loop=$watching_categories}
            <a href="tiki-browse_categories.php?parentId={$watching_categories[i].categId}">{$watching_categories[i].name|escape}</a>&nbsp;
        {/section}
    </div>
{/if}

{if !empty($mail_msg)}
    <div class="wikitext">{$mail_msg}</div>
{/if}

{include file='tracker_error.tpl'}

{tabset name='tabs_view_tracker' skipsingle=1}

    {if $tiki_p_view_trackers eq 'y' or (($tracker_info.writerCanModify eq 'y' or $tracker_info.userCanSeeOwn eq 'y' or $tracker_info.groupCanSeeOwn eq 'y' or $tracker_info.writerGroupCanModify eq 'y') and $user)}
        {tab name="{tr}Tracker Items{/tr}"}
            <h2>{tr}Items{/tr} <span class="badge bg-secondary" style="vertical-align: middle">{$item_count}</span></h2>
            {* -------------------------------------------------- tab with list --- *}

            {if (($tracker_info.showStatus eq 'y' and $tracker_info.showStatusAdminOnly ne 'y') or $tiki_p_admin_trackers eq 'y') or $show_filters eq 'y'}
                {include file='tracker_filter.tpl'}
            {/if}

            {if (isset($cant_pages) && $cant_pages > 1) or $initial}{initials_filter_links}{/if}

            {if $items|@count ge '1'}
                {* ------- list headings --- *}
                <form name="checkform" method="post">
                    <div class="{if $js}table-responsive{/if}"> {*the table-responsive class cuts off dropdown menus *}
                        <table class="table table-striped table-hover">
                            <tr>
                                {if $tracker_info.showStatus eq 'y' or ($tracker_info.showStatusAdminOnly eq 'y' and $tiki_p_admin_trackers eq 'y')}
                                    <th class="auto" style="width:20px;"></th>
                                {/if}

                                {if $tiki_p_admin_trackers eq 'y'}
                                    <th width="15">
                                        {select_all checkbox_names='action[]'}
                                    </th>
                                {/if}

                                {foreach from=$listfields key=ix item=field_value}
                                    {if $field_value.isTblVisible eq 'y' and ( $field_value.type ne 'x' and $field_value.type ne 'h') and ($field_value.type ne 'p' or $field_value.options_array[0] ne 'password') and $field_value.visibleInViewMode eq 'y'}
                                        <th class="auto">
                                            {self_link _sort_arg='sort_mode' _sort_field='f_'|cat:$field_value.fieldId}{$field_value.name|tra|truncate:255:"..."|escape|default:"&nbsp;"}{/self_link}
                                        </th>
                                    {/if}
                                {/foreach}

                                {if $tracker_info.showCreated eq 'y'}
                                    <th class="auto">
                                        {self_link _sort_arg='sort_mode' _sort_field='created'}{tr}Created{/tr}{/self_link}
                                    </th>
                                {/if}
                                {if $tracker_info.showLastModif eq 'y'}
                                    <th class="auto">
                                        {self_link _sort_arg='sort_mode' _sort_field='lastModif'}{tr}Last modified{/tr}{/self_link}
                                    </th>
                                {/if}
                                {if $tracker_info.showLastModifBy eq 'y'}
                                    <th class="auto">
                                        {self_link _sort_arg='sort_mode' _sort_field='lastModifby'}{tr}Last modifier{/tr}{/self_link}
                                    </th>
                                {/if}
                                {if $tracker_info.useComments eq 'y' and ($tracker_info.showComments eq 'y' || $tracker_info.showLastComment eq 'y') and $tiki_p_tracker_view_comments ne 'n'}
                                    <th{if $tracker_info.showLastComment ne 'y'} style="width:5%"{/if}>{tr}Comments{/tr}</th>
                                {/if}
                                {if ($tiki_p_tracker_view_attachments eq 'y' or $tiki_p_admin_trackers eq 'y') and $tracker_info.useAttachments eq 'y' and $tracker_info.showAttachments eq 'y'}
                                    <th style="width:5%">{tr}atts{/tr}</th>
                                    {if $tiki_p_admin_trackers eq 'y'}<th style="width:5%">{tr}dls{/tr}</th>{/if}
                                {/if}
                                {if $tiki_p_admin_trackers eq 'y' or $tiki_p_remove_tracker_items eq 'y' or $tiki_p_remove_tracker_items_pending eq 'y' or $tiki_p_remove_tracker_items_closed eq 'y'}
                                    <th style="width:20px"></th>
                                {/if}
                            </tr>

                            {* ------- Items loop --- *}
                            {assign var=itemoff value=0}

                            {section name=user loop=$items}
                                <tr>
                                    {if $tracker_info.showStatus eq 'y' or ($tracker_info.showStatusAdminOnly eq 'y' and $tiki_p_admin_trackers eq 'y')}
                                        <td class="icon">
                                            {assign var=ustatus value=$items[user].status|default:"c"}
                                            {icon name=$status_types.$ustatus.iconname iclass='tips' ititle=":{$status_types.$ustatus.label}"}
                                        </td>
                                    {/if}
                                    {if $tiki_p_admin_trackers eq 'y'}
                                        <td class="checkbox-cell">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="action[]" value='{$items[user].itemId}'>
                                            </div>
                                        </td>
                                    {/if}

                                    {* ------- list values --- *}
                                    {$ajaxedit = $prefs.ajax_inline_edit_trackerlist eq 'y' and
                                            ($tiki_p_modify_tracker_items eq 'y' and $items[user].status ne 'p' and $items[user].status ne 'c') or
                                            ($tiki_p_modify_tracker_items_pending eq 'y' and $items[user].status eq 'p') or
                                            ($tiki_p_modify_tracker_items_closed eq 'y' and $items[user].status eq 'c')
                                    }
                                    {foreach from=$items[user].field_values key=ix item=field_value}
                                        {if $field_value.isTblVisible eq 'y' and $field_value.type ne 'x' and $field_value.type ne 'h' and ($field_value.type ne 'p' or $field_value.options_array[0] ne 'password') and $field_value.visibleInViewMode eq 'y'}
                                            <td class={if $field_value.type eq 'n' or $field_value.type eq 'q' or $field_value.type eq 'b'}"numeric"{else}"auto"{/if}>
                                                {trackeroutput field=$field_value showlinks=y showpopup="y" item=$items[user] list_mode=y inTable=formcolor reloff=$itemoff editable=($ajaxedit and $listfields[$field_value.fieldId].editable) ? 'block' : ''}
                                            </td>
                                        {/if}
                                    {/foreach}

                                    {if $tracker_info.showCreated eq 'y'}
                                        <td class="date">{if !empty($tracker_info.showCreatedFormat)}{$items[user].created|tiki_date_format:$tracker_info.showCreatedFormat}{else}{$items[user].created|tiki_short_datetime}{/if}</td>
                                    {/if}
                                    {if $tracker_info.showLastModif eq 'y'}
                                        <td class="date">{if !empty($tracker_info.showLastModifFormat)}{$items[user].lastModif|tiki_date_format:$tracker_info.showLastModifFormat}{else}{$items[user].lastModif|tiki_short_datetime}{/if}</td>
                                    {/if}
                                    {if $tracker_info.showLastModifBy eq 'y'}
                                        <td class="date">
                                            {if empty($items[user].lastModifBy)}Unknown{else}{$items[user].lastModifBy|username}{/if}
                                        </td>
                                    {/if}
                                    {if $tracker_info.useComments eq 'y' and ($tracker_info.showComments eq 'y' or $tracker_info.showLastComment eq 'y') and $tiki_p_tracker_view_comments ne 'n'}
                                        <td style="text-align:center;">{if $tracker_info.showComments eq 'y'}{$items[user].comments}{/if}{if $tracker_info.showComments eq 'y' and $tracker_info.showLastComment eq 'y'}<br>{/if}{if $tracker_info.showLastComment eq 'y' and !empty($items[user].lastComment)}{$items[user].lastComment.userName|escape}-{$items[user].lastComment.commentDate|tiki_short_date}{/if}</td>
                                    {/if}
                                    {if ($tiki_p_tracker_view_attachments eq 'y' or $tiki_p_admin_trackers eq 'y') and $tracker_info.useAttachments eq 'y' and $tracker_info.showAttachments eq 'y'}
                                        <td class="icon"><a href="tiki-view_tracker_item.php?itemId={$items[user].itemId}&amp;show=att{if $offset}&amp;offset={$offset}{/if}{foreach key=urlkey item=urlval from=$urlquery}{if $urlval}&amp;{$urlkey}={$urlval|escape:"url"}{/if}{/foreach}"
                                        link="{tr}List Attachments{/tr}">{icon name="attach"}</a> {$items[user].attachments}</td>
                                        {if $tiki_p_admin_trackers eq 'y'}<td style="text-align:center;">{$items[user].hits}</td>{/if}
                                    {/if}
                                    {if $tiki_p_admin_trackers eq 'y' or ($tiki_p_remove_tracker_items eq 'y' and $items[user].status ne 'p' and $items[user].status ne 'c') or ($tiki_p_remove_tracker_items_pending eq 'y' and $items[user].status eq 'p') or ($tiki_p_remove_tracker_items_closed eq 'y' and $items[user].status eq 'c')}
                                        <td class="action">
                                            {actions}
                                                {strip}
                                                    {if $prefs.tracker_legacy_insert neq 'y'}
                                                        <action>
                                                            <a href="{bootstrap_modal controller=tracker action=update_item trackerId=$trackerId itemId=$items[user].itemId size='modal-lg'}"
                                                                onclick="$('[data-bs-toggle=popover]').popover('hide');"
                                                            >
                                                                {icon name="edit" _menu_text='y' _menu_icon='y' alt="{tr}Edit{/tr}"}
                                                            </a>
                                                        </action>
                                                    {else}
                                                        <action>
                                                            <a href="tiki-view_tracker_item.php?itemId={$items[user].itemId}&amp;show=mod"
                                                                onclick="$('[data-bs-toggle=popover]').popover('hide');"
                                                            >
                                                                {icon name="post" _menu_text='y' _menu_icon='y' alt="{tr}View/Edit{/tr}"}
                                                            </a>
                                                        </action>
                                                    {/if}
                                                    {if $tiki_p_create_tracker_items eq 'y' and $prefs.tracker_clone_item eq 'y'}
                                                        <action>
                                                            <a href="{bootstrap_modal controller=tracker action=clone_item trackerId=$trackerId itemId=$items[user].itemId size='modal-lg'}"
                                                                onclick="$('[data-bs-toggle=popover]').popover('hide');"
                                                            >
                                                                {icon name="copy" _menu_text='y' _menu_icon='y' alt="{tr}Duplicate{/tr}"}
                                                            </a>
                                                        </action>
                                                    {/if}
                                                    <action>
                                                        <a href="{bootstrap_modal controller=tracker action=remove_item trackerId=$trackerId itemId=$items[user].itemId}"
                                                            onclick="$('[data-bs-toggle=popover]').popover('hide');"
                                                        >
                                                            {icon name="delete" _menu_text='y' _menu_icon='y' alt="{tr}Delete{/tr}"}
                                                        </a>
                                                    </action>
                                                    {if $tiki_p_admin_trackers eq 'y'}
                                                        <action>
                                                            {permission_link mode=text type=trackeritem id=$items[user].itemId permType=trackers parentId=$trackerId}
                                                        </action>
                                                        <action>
                                                            <a href="tiki-tracker_view_history.php?itemId={$items[user].itemId}"
                                                                onclick="$('[data-bs-toggle=popover]').popover('hide');"
                                                            >
                                                                {icon name="history" _menu_text='y' _menu_icon='y' alt="{tr}History{/tr}"}
                                                            </a>
                                                        </action>
                                                    {/if}
                                                {/strip}
                                            {/actions}
                                        </td>
                                    {/if}
                                </tr>
                                {assign var=itemoff value=$itemoff+1}
                            {/section}
                        </table>
                    </div>

                    {if $tiki_p_admin_trackers eq 'y'}
                        <div class="mb-3 row">
                            <div class="input-group min-width-customized">
                                <select name="batchaction" class="form-select">
                                    <option value="" selected="selected">
                                        {tr}Select the action to be performed with checked{/tr}...
                                    </option>
                                    <option value="delete">{tr}Delete Selected{/tr}</option>
                                    {if $tracker_info.showStatus eq 'y' or ($tracker_info.showStatusAdminOnly eq 'y' and $tiki_p_admin_trackers eq 'y')}
                                        <option value="c">{tr}Close{/tr}</option>
                                        <option value="o">{tr}Open{/tr}</option>
                                        <option value="p">{tr}Pending{/tr}</option>
                                    {/if}
                                </select>
                                {ticket}
                                <input type="hidden" name="trackerId" value="{$trackerId}">
                                <input type="submit" class="btn btn-primary" name="act" value="{tr}OK{/tr}">
                            </div>
                        </div>
                    {/if}
                </form>
                {pagination_links cant=$item_count step=$maxRecords offset=$offset}{/pagination_links}
            {/if}
        {/tab}
    {/if}

    {if $tiki_p_create_tracker_items eq 'y' && $prefs.tracker_legacy_insert eq 'y'}
        {* --------------------------------------------------------------------------------- tab with edit --- *}
        {tab name="{tr}Insert New Item{/tr}"}
            <h2>{tr}Insert New Item{/tr}</h2>
            {if isset($validationjs)}
                {jq}
                    $("#newItemForm").validate({
                        {{$validationjs}},
                        ignore: '.ignore',
                        submitHandler: function(form, event){return process_submit(form, event);}
                    });
                {/jq}
            {/if}
            <form enctype="multipart/form-data" action="tiki-view_tracker.php" id="newItemForm" method="post">
                <input type="hidden" name="trackerId" value="{$trackerId|escape}">

                {remarksbox type="note"}<strong class='mandatory_note'>{tr}Fields marked with an * are mandatory.{/tr}</strong>{/remarksbox}
                <div>

                    {if $tracker_info.showStatus eq 'y' and ($tracker_info.showStatusAdminOnly ne 'y' or $tiki_p_admin_trackers eq 'y')}
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{tr}Status{/tr}</label>
                            <div class="col-sm-8">
                                {include file='tracker_status_input.tpl' tracker=$tracker_info form_status=status}
                            </div>
                        </div>
                    {/if}
                    {foreach from=$ins_fields key=ix item=field_value}
                        {if $field_value.type ne 'x' and $field_value.type ne 'l' and $field_value.type ne 'q' and
                                ($field_value.type ne 'A' or $tiki_p_attach_trackers eq 'y') and $field_value.type ne 'N' and $field_value.type ne '*' and
                                !($field_value.type eq 's' and $field_value.name eq 'Rating')
                        }
                            <div class="mb-3 row">
                                {if empty($field_value.options_map.labelasplaceholder)}
                                    <label class="col-sm-3 {if $field_value.type eq 'h'}h{$field_value.options_map.level}{else}col-form-label{/if}">
                                            {if $field_value.isMandatory eq 'y'}
                                                {$field_value.name|tra} <strong class='mandatory_star text-danger tips' title=":{tr}This field is mandatory{/tr}">*</strong>
                                            {else}
                                                {$field_value.name|tra}
                                            {/if}
                                    </label>
                                {/if}
                                <div class="col-sm-{if empty($field_value.options_map.labelasplaceholder)}8{else}12{/if}">
                                    {trackerinput field=$field_value inTable=formcolor showDescription=y}
                                </div>
                            </div>
                        {/if}
                    {/foreach}

                    {* -------------------- antibot code -------------------- *}
                    {if $prefs.feature_antibot eq 'y' && $user eq ''}
                        {include file='antibot.tpl' tr_style="formcolor" showmandatory=y}
                    {/if}

                    {if !isset($groupforalert) || $groupforalert ne ''}
                        <div class="mb-3 row">
                            {if $showeachuser eq 'y'}

                                <label class="col-sm-3 col-form-label">{tr}Choose users to alert{/tr}</label>

                            {/if}
                            {section name=idx loop=$listusertoalert}
                                <div class="col-sm-8 form-check-inline">
                                    {if $showeachuser eq 'n'}
                                        <input type="hidden" name="listtoalert[]" value="{$listusertoalert[idx].user}">
                                    {else}
                                        <input type="checkbox" class="form-check-input" name="listtoalert[]" value="{$listusertoalert[idx].user}"> {$listusertoalert[idx].user}
                                    {/if}
                                </div>
                            {/section}

                        </div>
                    {/if}

                    <div class="mb-3 row">
                        <div class="col-sm-8 offset-sm-3">
                            <div class="d-flex flex-row flex-wrap align-items-center">
                                <div class="col-sm-2"><input type="submit" class="btn btn-primary btn-sm" name="save" value="{tr}Save{/tr}" onclick="needToConfirm = false;"></div>
                                <div class="col-sm-5"><input type="radio" name="viewitem" value="view" class="form-check-input" /> {tr}View inserted item{/tr}</div>
                                {* --------------------------- to continue inserting items after saving --------- *}
                                <div class="col-sm-5"><input type="radio" name="viewitem" value="new" class="form-check-input" checked="checked"> {tr}Insert new item{/tr}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        {/tab}
    {/if}

    {if $tracker_sync}
        {tab name="{tr}Synchronization{/tr}"}
            <h2>{tr}Synchronization{/tr}</h2>
            <p>
                {tr _0=$tracker_sync.provider|cat:'/tracker'|cat:$tracker_sync.source}This tracker is a remote copy of <a href="%0">%0</a>.{/tr}
                {if !empty($tracker_sync.last)}
                    {tr _0=$tracker_sync.last|tiki_short_date}It was last updated on %0.{/tr}
                {/if}
            </p>
            {permission name=tiki_p_admin_trackers}
                <form class="sync-refresh" method="post" action="{service controller=tracker_sync action=sync_meta trackerId=$trackerId}">
                    <p><input type="submit" class="btn btn-primary btn-sm" value="{tr}Reload field definitions{/tr}"></p>
                </form>
                <form class="sync-refresh" method="post" action="{service controller=tracker_sync action=sync_new trackerId=$trackerId}">
                    <p>{tr}Items added locally{/tr}</p>
                    <ul class="load-items items">
                    </ul>
                    <p><input type="submit" class="btn btn-primary btn-sm" value="{tr}Push new items{/tr}"></p>
                </form>
                <form class="sync-refresh" method="post" action="{service controller=tracker_sync action=sync_edit trackerId=$trackerId}">
                    <div class="item-block">
                        <p>{tr}Safe modifications (no remote conflict){/tr}</p>
                        <ul class="load-items automatic">
                        </ul>
                    </div>
                    <div class="item-block">
                        <p>{tr}Dangerous modifications (remote conflict){/tr}</p>
                        <ul class="load-items manual">
                        </ul>
                    </div>
                    <p>{tr}On push, local items will be removed until data reload.{/tr}</p>
                    <p><input type="submit" class="btn btn-primary btn-sm" value="{tr}Push local changes{/tr}"></p>
                </form>
                <form class="sync-refresh" method="post" action="{service controller=tracker_sync action=sync_refresh trackerId=$trackerId}">
                    {if !empty($tracker_sync.modified)}
                        {remarksbox type=warning title="{tr}Local changes will be lost{/tr}"}
                            <p>{tr}When reloading the data from the source, all local changes will be lost.{/tr}</p>
                            <ul>
                                <li>{tr}New items that must be preserved should be pushed using the above controls.{/tr}</li>
                                <li>
                                    {tr}Modifications that must be preserved should be replicated.{/tr}
                                    <ul>
                                        <li>{tr}Without conflicts: Using the above controls{/tr}</li>
                                        <li>{tr}With conflicts: Manually on the source.{/tr} <em>{tr}Using the above controls will cause information loss.{/tr}</em></li>
                                    </ul>
                                </li>
                            </ul>
                        {/remarksbox}
                    {/if}
                    <div class="submit">
                        <input type="hidden" name="confirm" value="1">
                        <input type="submit" class="btn btn-primary btn-sm" name="submit" value="{tr}Reload data from source{/tr}">
                    </div>
                </form>
                {jq}
                    $('.sync-refresh').submit(function () {
                        var form = this;
                        $.ajax({
                            type: 'post',
                            url: $(form).attr('action'),
                            dataType: 'json',
                            data: $(form).serialize(),
                            error: function (jqxhr) {
                                $(':submit', form).showError(jqxhr);
                            },
                            success: function () {
                                document.location.reload();
                            }
                        });
                        return false;
                    });
                    $('.load-items').closest('form').each(function () {
                        var form = this;
                        $(form).hide();
                        $.getJSON($(this).attr('action'), function (data) {
                            $.each(data.sets, function (k, name) {
                                var list = $(form).find('.load-items.' + name)[0];

                                $.each(data[name], function (k, info) {
                                    var li = $('<li/>');
                                    li.append($('<label/>')
                                        .text(info.title)
                                        .prepend($('<input type="checkbox" class="form-check-input" name="' + name + '[]">').attr('value', info.itemId))
                                    );

                                    $.each({localUrl: "{tr}Local{/tr}", remoteUrl: "{tr}Remote{/tr}"}, function (key, label) {
                                        if (info[key]) {
                                            li
                                                .append(' ')
                                                .append($('<a/>')
                                                    .attr('href', info[key])
                                                    .text(label));
                                        }
                                    });

                                    $(list).append(li);
                                });

                                if (data[name].length === 0) {
                                    $(list).closest('.item-block').hide();
                                } else {
                                    $(form).show();
                                }
                            });
                        });
                    });
                {/jq}
            {/permission}
        {/tab}
    {/if}
{/tabset}