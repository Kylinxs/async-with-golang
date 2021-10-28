
{* $Id$ *}
{if !$ts.ajax}
    {block name=title}
        {title help="Forums" admpage="forums"}{tr}Forums{/tr}{/title}
    {/block}
    <div class="t_navbar mb-4">
        {if $tiki_p_admin_forum eq 'y'}
            {button href="tiki-admin_forums.php" _type="link" class="btn btn-link" _icon_name="cog" _text="{tr}Admin{/tr}"}
        {/if}
        {if $tiki_p_forum_read eq 'y' and $prefs.feature_forum_rankings eq 'y'}
            {button href="tiki-forum_rankings.php" _type="link" class="btn btn-link" _icon_name="ranking" _text="{tr}Rankings{/tr}"}
        {/if}
    </div>
    {if !$ts.enabled}
        {if $channels or ($find ne '')}
            {if $prefs.feature_forums_search eq 'y' or $prefs.feature_forums_name_search eq 'y'}
                {if $prefs.feature_forums_name_search eq 'y'}
                    <form method="get" class="form" role="form" action="tiki-forums.php">
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text">
                                    {icon name="search"}
                                </span>
                                <input type="text" name="find" class="form-control" value="{$find|escape}" placeholder="{tr}Find{/tr}">
                                <input type="hidden" name="sort_mode" value="{$sort_mode|escape}">
                                <input type="submit" class="btn btn-info" value="{tr}Search by name{/tr}" name="search">
                            </div>
                        </div>
                    </form>
                {/if}
                {if $prefs.feature_forums_search eq 'y' and $prefs.feature_search eq 'y'}
                    <div class="row mb-4 mx-0">
                        <div class="col-md-5 offset-md-7">
                            <form class="form mb-3" method="get" role="form" action="{if $prefs.feature_search_fulltext neq 'y'}tiki-searchindex.php{else}tiki-searchresults.php{/if}">
                                <div class="input-group">
                                    <span class="input-group-text">
                                         {icon name="search"}
                                     </span>
                                    <input name="highlight" type="text" class="form-control" placeholder="{tr}Find{/tr}">
                                    <input type="hidden" name="where" value="forums">
                                    <input type="hidden" name="filter~type" value="forum post">
                                    <input type="submit" class="wikiaction btn btn-info" name="search" value="{tr}Search in content{/tr}">
                                </div>
                            </form>
                        </div>
                    </div>
                {/if}
            {/if}
        {/if}
    {elseif $prefs.feature_forums_search eq 'y' and $prefs.feature_search eq 'y'}{* and $ts.enabled *}
        <div class="row mb-4 mx-0">
            <div class="col-12">
                <form class="form" method="get" role="form" action="{if $prefs.feature_search_fulltext neq 'y'}tiki-searchindex.php{else}tiki-searchresults.php{/if}">

                        <div class="input-group">
                            <span class="input-group-text">
                                {icon name="search"}
                            </span>
                            <input name="filter~content" type="text" class="form-control" aria-label="{tr}Find{/tr}" placeholder="{tr}Find{/tr}">
                            <input type="hidden" name="where" value="forums">
                            <input type="hidden" name="filter~type" value="forum post">
                            <input type="button" class="wikiaction btn btn-info" name="search" value="{tr}Search in content{/tr}">
                        </div>

                </form>
            </div>
        </div>
    {/if}
{/if}
<div id="{$ts.tableid}-div" class="{if $js}table-responsive{/if} ts-wrapperdiv" {if !empty($ts.enabled)}style="visibility:hidden;"{/if}> {*the table-responsive class cuts off dropdown menus *}
    <table id="{$ts.tableid}" class="table table-striped table-hover table-forum normal" data-count="{$cant|escape}">
        {block name=forumheader}
        <thead>
            <tr>
                {$numbercol = 1}
                <th id="name">{self_link _sort_arg='sort_mode' _sort_field='name'}{tr}Name{/tr}{/self_link}</th>

                {if $prefs.forum_list_topics eq 'y'}
                    {$numbercol = $numbercol + 1}
                    <th id="threads" class="text-end">{self_link _sort_arg='sort_mode' _sort_field='threads'}{tr}Topics{/tr}{/self_link}</th>
                {/if}

                {if $prefs.forum_list_posts eq 'y'}
                    {$numbercol = $numbercol + 1}
                    <th id="comments" class="text-end">{self_link _sort_arg='sort_mode' _sort_field='comments'}{tr}Posts{/tr}{/self_link}</th>
                {/if}

                {if $prefs.forum_list_ppd eq 'y'}
                    {$numbercol = $numbercol + 1}
                    <th id="ppd">{tr}PPD{/tr}</th>
                {/if}

                {if $prefs.forum_list_lastpost eq 'y'}
                    {$numbercol = $numbercol + 1}
                    <th id="lastPost">{self_link _sort_arg='sort_mode' _sort_field='lastPost'}{tr}Last Post{/tr}{/self_link}</th>
                {/if}

                {if $prefs.forum_list_visits eq 'y'}
                    {$numbercol = $numbercol + 1}
                    <th id="hits" class="text-end">{self_link _sort_arg='sort_mode' _sort_field='hits'}{tr}Visits{/tr}{/self_link}</th>
                {/if}
                {$numbercol = $numbercol + 1}
                <th id="actions"></th>
            </tr>
        </thead>
        {/block}
        <tbody>
            {assign var=section_old value=""}
            {section name=user loop=$channels}
                {assign var=section value=$channels[user].section}
                {if $section ne $section_old}
                    {assign var=section_old value=$section}
                    <td class="third info" colspan="{$numbercol}">{tr}{$section|escape}{/tr}</td>
                {/if}
                {block name=forumrow}
                <tr>
                    <td class="text">
                        {if (isset($channels[user].individual) and $channels[user].individual eq 'n')
                            or ($tiki_p_admin eq 'y') or ($channels[user].individual_tiki_p_forum_read eq 'y')}
                            <a class="forumname" href="{$channels[user].forumId|sefurl:'forum'}">{$channels[user].name|escape}</a>
                        {else}
                            {$channels[user].name|escape}
                        {/if}
                        {if $prefs.forum_list_desc eq 'y'}
                            <div class="form-text">
                                {capture name="parsedDesc"}{wiki}{$channels[user].description}{/wiki}{/capture}
                                {if strlen($smarty.capture.parsedDesc) < $prefs.forum_list_description_len}
                                    {$smarty.capture.parsedDesc}
                                {else}
                                    {$smarty.capture.parsedDesc|strip_tags|truncate:$prefs.forum_list_description_len:"...":true}
                                {/if}
                            </div>
                        {/if}
                        <div class="t_navbar mb-4">
                            {if count($channels[user].sub_forums) > 0}
                                <b>Sub Forums</b>:
                                {foreach from=$channels[user].sub_forums item=forum}
                                    <i>{button href="tiki-view_forum.php?forumId={$forum.forumId}" _onclick='$("#forumpost").show();return false;' _icon_name="users" _type="link" class="btn btn-link" _text="{tr}{$forum.name}{/tr}"}</i>
                                {/foreach}
                            {/if}
                        </div>
                    </td>
                    {if $prefs.forum_list_topics eq 'y'}
                        <td class="integer">{$channels[user].threads}</td>
                    {/if}
                    {if $prefs.forum_list_posts eq 'y'}
                        <td class="integer">{$channels[user].comments}</td>
                    {/if}
                    {if $prefs.forum_list_ppd eq 'y'}
                        <td class="integer">{$channels[user].posts_per_day|string_format:"%.2f"}</td>
                    {/if}
                    {if $prefs.forum_list_lastpost eq 'y'}
                        <td class="text">
                            {if isset($channels[user].lastPost)}
                                {$channels[user].lastPost|tiki_short_datetime}<br>
                                {if $prefs.forum_reply_notitle neq 'y'}<small><i>{$channels[user].lastPostData.title|escape}</i>{/if}
                                {tr}by{/tr} {$channels[user].lastPostData.userName|username}</small>
                            {/if}
                        </td>
                    {/if}
                    {if $prefs.forum_list_visits eq 'y'}
                        <td class="integer">{$channels[user].hits}</td>
                    {/if}
                    <td class="action">
                        {actions}
                            {strip}
                                <action>
                                    <a href="{$channels[user].forumId|sefurl:'forum'}">
                                        {icon name="view" _menu_text='y' _menu_icon='y' alt="{tr}View{/tr}"}
                                    </a>
                                </action>
                                {if ($tiki_p_admin eq 'y') or (($channels[user].individual eq 'n') and ($tiki_p_admin_forum eq 'y')) or ($channels[user].individual_tiki_p_admin_forum eq 'y')}
                                    <action>
                                        <a href="tiki-admin_forums.php?forumId={$channels[user].forumId}&amp;cookietab=2#content_admin_forums1-2">
                                            {icon name="edit" _menu_text='y' _menu_icon='y' alt="{tr}Edit{/tr}"}
                                        </a>
                                    </action>
                                    <action>
                                        {permission_link mode=text type="forum" permType="forums" id=$channels[user].forumId}
                                    </action>
                                {/if}
                            {/strip}
                        {/actions}
                    </td>
                </tr>
                {/block}
            {sectionelse}
                {if !$ts.enabled || ($ts.enabled && $ts.ajax)}
                    {norecords _colspan=$numbercol _text="{tr}No forums found{/tr}"}
                {else}
                    {norecords _colspan=$numbercol _text="{tr}Loading{/tr}..."}
                {/if}
            {/section}
        </tbody>
    </table>
</div>
{if !$ts.enabled}
    {pagination_links cant=$cant step=$prefs.maxRecords offset=$offset}{/pagination_links}
{/if}