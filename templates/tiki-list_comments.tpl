
{* $Id$ *}
{title help="Comments" admpage="comments"}{$title}{/title}

{if $comments or ($find ne '') or count($show_types) gt 0 or isset($smarty.request.findfilter_approved)}
    {include file='find.tpl' types=$show_types find_type=$selected_types types_tag='checkbox' filters=$filters filter_names=$filter_names filter_values=$filter_values}
{/if}

{if $comments}
    <form name="checkboxes_on" method="post" action="tiki-list_comments.php">
    {ticket}
    {query _type='form_input'}
{/if}

{assign var=numbercol value=2}

        <div class="{if $js}table-responsive{/if} comment-table"> {*the table-responsive class cuts off dropdown menus *}
<table class="table table-striped table-hover">
    <tr>
        {if $comments}
            <th>
                {select_all checkbox_names='checked[]'}
                {assign var=numbercol value=$numbercol+1}
            </th>
        {/if}
        <th></th>

        {foreach key=headerKey item=headerName from=$headers}
            <th>
                {assign var=numbercol value=$numbercol+1}
                {self_link _sort_arg="sort_mode" _sort_field=$headerKey}{tr}{$headerName}{/tr}{/self_link}
            </th>
        {/foreach}

        {if $tiki_p_admin_comments eq 'y' and $prefs.feature_comments_moderation eq 'y'}
            <th>
                {assign var=numbercol value=$numbercol+1}
                {self_link _sort_arg="sort_mode" _sort_field='approved'}{tr}Approval{/tr}{/self_link}
            </th>
        {/if}
        <th></th>
    </tr>

    {section name=ix loop=$comments}{assign var=id value=$comments[ix].threadId}
        <tr class="{cycle}{if $prefs.feature_comments_moderation eq 'y'} post-approved-{$comments[ix].approved}{/if}">
            <td class="checkbox-cell"><div class="form-check"><input type="checkbox" class="form-check-input" name="checked[]" value="{$id}" {if isset($rejected[$id]) }checked="checked"{/if}></div></td>
            <td class="action">
                {actions}
                    {strip}
                        <action>
                            <a href="{$comments[ix].object|sefurl:$comments[ix].objectType}#?comzone=show#threadId{$comments[ix].threadId}">
                                {icon name='view' _menu_text='y' _menu_icon='y' alt="{tr}View{/tr}"}
                            </a>
                        </action>
                        <action>
                            <a href="{$comments[ix].href|cat:"&amp;comments_threadId=$id&amp;edit_reply=1#form"}">
                                {icon name='edit' _menu_text='y' _menu_icon='y' alt="{tr}Edit{/tr}"}
                            </a>
                        </action>
                        {if $tiki_p_admin_comments eq 'y' and $prefs.comments_archive eq 'y'}
                            {if $comments[ix].archived eq 'y'}
                                <action>
                                    <form action="tiki-list_comments.php" method="post">
                                        {ticket}
                                        <input type="hidden" name="checked" value="{$id|escape}">
                                        <button
                                            type="submit"
                                            name="action"
                                            value="unarchive"
                                            class="btn btn-link link-list"
                                        >
                                            {icon name='file-archive-open'} {tr}Unarchive{/tr}
                                        </button>
                                    </form>
                                </action>
                            {else}
                                <action>
                                    <form action="tiki-list_comments.php" method="post">
                                        {ticket}
                                        <input type="hidden" name="checked" value="{$id|escape}">
                                        <button
                                            type="submit"
                                            name="action"
                                            value="archive"
                                            class="btn btn-link link-list"
                                        >
                                            {icon name='file-archive'} {tr}Archive{/tr}
                                        </button>
                                    </form>
                                </action>
                            {/if}
                        {/if}
                        <action>
                            <a href="tiki-list_comments.php?checked={$id|escape:'url'}&amp;action=remove" onclick="confirmPopup('{tr}Delete comment?{/tr}', '{ticket mode=get}')">
                                {icon name='remove' _menu_text='y' _menu_icon='y' alt="{tr}Delete{/tr}"}
                            </a>
                        </action>
                    {/strip}
                {/actions}
            </td>

            {foreach key=headerKey item=headerName from=$headers}{assign var=val value=$comments[ix].$headerKey}
                <td {if $headerKey eq 'data'}{popup caption=$comments[ix].title|escape:"javascript"|escape:"html" text=$comments[ix].parsed}{/if}>
                    <span> {* span is used for some themes CSS opacity on some cells content *}
                        {if $headerKey eq 'title'}
                            <a href="{$comments[ix].object|sefurl:$comments[ix].objectType}#?comzone=show#threadId{$comments[ix].threadId}" title="{$val|escape}">
                                {if !empty($val)}
                                    {$val|truncate:50:"...":true|escape}
                                {else}
                                    {tr}(no title){/tr}
                                {/if}
                            </a>
                        {elseif $headerKey eq 'objectType'}
                            {tr}{$val|ucwords}{/tr}
                        {elseif $headerKey eq 'object'}
                            {$val|truncate:50:"...":true|escape}
                        {elseif $headerKey eq 'data'}
                            {$val|truncate:90:"...":true|escape}
                        {elseif $headerKey eq 'commentDate'}
                            {$val|tiki_short_datetime}
                        {elseif $headerKey eq 'userName'}
                            {$val|userlink}
                        {else}
                            {$val}
                        {/if}
                    </span>
                </td>
            {/foreach}

            {if $tiki_p_admin_comments eq 'y' and $prefs.feature_comments_moderation eq 'y'}
                <td class="approval">
                    {if $comments[ix].approved eq 'n'}
                        <a href="#" data-action="approve" data-checked="{$id}" class="tips moderation-post text-success" title=":{tr}Approve{/tr}">{icon name='ok'}</a>
                        <a href="#" data-action="reject" data-checked="{$id}" class="tips moderation-post text-danger" title=":{tr}Reject{/tr}">{icon name='delete'}</a>
                    {elseif $comments[ix].approved eq 'y'}
                        &nbsp;{tr}Approved{/tr}&nbsp;
                    {elseif $comments[ix].approved eq 'r'}
                        <span>&nbsp;{tr}Rejected{/tr}&nbsp;</span>
                    {/if}
                </td>
                {jq}$(".moderation-post").click(function () {
    let $this = $(this), $form = $this.parents("form");
    $form.find("select[name=action]").val($this.data("action"));
    $this.parents("tr:first").tikiModal(tr("Saving...")).find("input[type=checkbox]").prop("checked", true);
    $form.submit();
    return false;
});{/jq}
            {/if}

            <td>
                {actions title="{tr}More Information{/tr}" icon="information"}
                    {strip}
                        {foreach from=$more_info_headers key=headerKey item=headerName}
                            {if (isset($comments[ix].$headerKey))}
                                {assign var=val value=$comments[ix].$headerKey}
                                <action>
                                    <b>{tr}{$headerName}{/tr}</b>: {$val}<br />
                                </action>
                            {/if}
                        {/foreach}
                    {/strip}
                {/actions}
            </td>
        </tr>
    {sectionelse}
        {norecords _colspan=$numbercol}
    {/section}
</table>
</div>

{if $comments}
    <div class="input-group col-sm-8">
        <select class="form-select" name="action">
            <option value="no_action" selected="selected">
                {tr}Select action to perform with checked{/tr}...
            </option>
            <option value="remove" class="confirm-popup" data-confirm-text="{tr}Delete selected comments?{/tr}">
                {tr}Delete{/tr}
            </option>
            {if $tiki_p_admin_comments eq 'y' and $prefs.feature_banning eq 'y'}
                <option value="ban">
                    {tr}Ban{/tr}
                </option>
                <option
                    value="ban_remove"
                    class="confirm-popup"
                    data-confirm-text="{tr}Delete and ban selected comments?{/tr}"
                >
                    {tr}Delete and ban{/tr}
                </option>
            {/if}
            {if $tiki_p_admin_comments eq 'y' and $prefs.feature_comments_moderation eq 'y'}
                <option value="approve">
                    {tr}Approve{/tr}
                </option>
                <option value="reject">
                    {tr}Reject{/tr}
                </option>
            {/if}
            {if $tiki_p_admin_comments eq 'y' and $prefs.comments_archive eq 'y'}
                <option value="archive">
                    {tr}Archive{/tr}
                </option>
                <option value="unarchive">
                    {tr}Unarchive{/tr}
                </option>
            {/if}
        </select>
        <button type="submit" class="btn btn-primary" onclick="confirmPopup()">
            {tr}OK{/tr}
        </button>
    </div>
    </form>
{/if}

{pagination_links cant=$cant step=$maxRecords offset=$offset}{/pagination_links}