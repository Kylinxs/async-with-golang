{* $Id$ *}

{if !$ts.enabled && ($cant_pages > 1 or $initial or $find)}
    {initials_filter_links}
{/if}

{if $tiki_p_remove eq 'y' or $prefs.feature_wiki_multiprint eq 'y'}
    {if isset($checkboxes_on) and $checkboxes_on eq 'n'}
        {assign var='checkboxes_on' value='n'}
    {else}
        {assign var='checkboxes_on' value='y'}
    {/if}
{else}
    {assign var='checkboxes_on' value='n'}
{/if}

{if $find ne '' and $listpages|@count ne '0'}
    <p>{tr}Found{/tr} &quot;{$find|escape}&quot; {tr}in{/tr} {$listpages|@count} {tr}pages{/tr}.</p>
{/if}

    {* Action attribute has to be set explicitly so that plugins could get use of it *}
{if isset($checkboxes_on) and $checkboxes_on eq 'y'}
    <form name="checkboxes_on" id="checkboxes_on" method="post" action="tiki-listpages.php">
{/if}

{* Conditionally display this remark*}

{if $prefs.feature_stats eq 'n' AND $tiki_p_admin eq 'y'}
    <div class="container">
        <div class="row">
            <div class="col-12" style="padding:10px 0px; font-weight:bolder;"> {*Leave the style declaration here first but it should be included in the scss files for better maintainance soon*}
                {tr _0='<span class="icon icon-information fas fa-info-circle"></span>' _1='<a target="_blank" href="tiki-admin.php?page=stats&highlight=feature_stats">' _2='</a>' _3='<a target="_blank" href="https://doc.tiki.org/Stats"><span class="icon icon-help fas fa-question-circle"></span></a>'}%0 To see page hits, please %1 activate %2 the stats feature %3{/tr}
            </div>
        </div>
    </div>
{/if}

{assign var='pagefound' value='n'}
<div id="{$ts.tableid}-div" class="{if $js}table-responsive{/if} ts-wrapperdiv" {if !empty($ts.enabled)}style="visibility:hidden;"{/if}> {*the table-responsive class cuts off dropdown menus *}
    <table id="{$ts.tableid}" class="table normal table-striped table-hover" data-count="{$cant|escape}">
        <thead>
            <tr>
                {if isset($checkboxes_on) and $checkboxes_on eq 'y'}
                    <th id="checkbox">
                        {select_all checkbox_names='checked[]' tablesorter="{$ts.enabled}"}
                    </th>
                    {assign var='cntcol' value='1'}
                {else}
                    {assign var='cntcol' value='0'}
                {/if}

                {if $prefs.wiki_list_id eq 'y'}
                    {assign var='cntcol' value=$cntcol+1}
                    <th id="pageid">
                        {self_link _sort_arg='sort_mode' _sort_field='page_id'}{tr}Id{/tr}{/self_link}
                    </th>
                {/if}

                {if $prefs.wiki_list_name eq 'y'}
                    {assign var='cntcol' value=$cntcol+1}
                    <th id="pagename">
                        {self_link _sort_arg='sort_mode' _sort_field='pageName'}{tr}Page{/tr}{/self_link}
                    </th>
                {/if}

                {if isset($wplp_used)}
                    {foreach from=$wplp_used key=lc item=ln}
                        <th>{$ln|escape}</th>
                    {/foreach}
                {/if}
                {if $prefs.wiki_list_hits eq 'y' AND $prefs.feature_stats eq 'y'}
                    {assign var='cntcol' value=$cntcol+1}
                    <th id="hits">{self_link _sort_arg='sort_mode' _sort_field='hits'}{tr}Hits{/tr}{/self_link}</th>
                {/if}

                {if $prefs.wiki_list_lastmodif eq 'y' or $prefs.wiki_list_comment eq 'y'}
                    {assign var='cntcol' value=$cntcol+1}
                    <th id="lastmodif">
                        {assign var='lastmod_sortfield' value='lastModif'}
                        {assign var='lastmod_shorttitle' value="{tr}Last modification{/tr}"}
                        {if $prefs.wiki_list_lastmodif eq 'y' and $prefs.wiki_list_comment eq 'y'}
                            {assign var='lastmod_title' value="{tr}Last modification{/tr} / {tr}Comment{/tr}"}
                        {elseif $prefs.wiki_list_lastmodif eq 'y'}
                            {assign var='lastmod_title' value="{tr}Last modification{/tr}"}
                        {else}
                            {assign var='lastmod_title' value="{tr}Comment{/tr}"}
                            {assign var='lastmod_sortfield' value='comment'}
                            {assign var='lastmod_shorttitle' value="{tr}Comment{/tr}"}
                        {/if}
                        {self_link _sort_arg='sort_mode' _sort_field=$lastmod_sortfield _title=$lastmod_title}{$lastmod_shorttitle}{/self_link}
                    </th>
                {/if}

                {if $prefs.wiki_list_creator eq 'y'}
                    {assign var='cntcol' value=$cntcol+1}
                    <th id="creator">
                        {self_link _sort_arg='sort_mode' _sort_field='creator' _title="{tr}Page creator{/tr}"}{tr}Creator{/tr}{/self_link}
                    </th>
                {/if}

                {if $prefs.wiki_list_user eq 'y'}
                    {assign var='cntcol' value=$cntcol+1}
                    <th id="lastauthor">
                        {self_link _sort_arg='sort_mode' _sort_field='user' _title="{tr}Last author{/tr}"}{tr}Last author{/tr}{/self_link}
                    </th>
                {/if}

                {if $prefs.wiki_list_lastver eq 'y'}
                    {assign var='cntcol' value=$cntcol+1}
                    <th id="version">
                        {self_link _sort_arg='sort_mode' _sort_field='version' _title="{tr}Last version{/tr}"}{tr}Last version{/tr}{/self_link}
                    </th>
                {/if}

                {if $prefs.wiki_list_status eq 'y'}
                    {assign var='cntcol' value=$cntcol+1}
                    <th id="status" style="text-align:center;">
                        {self_link _sort_arg='sort_mode' _sort_field='flag' _icon_name='lock'}{tr}Status of the page{/tr}{/self_link}
                    </th>
                {/if}

                {if $prefs.wiki_list_versions eq 'y'}
                    {assign var='cntcol' value=$cntcol+1}
                    <th id="versions">
                        {self_link _sort_arg='sort_mode' _sort_field='versions' _title="{tr}Versions{/tr}"}{tr}Version{/tr}{/self_link}
                    </th>
                {/if}

                {if $prefs.wiki_list_links eq 'y'}
                    {assign var='cntcol' value=$cntcol+1}
                    <th id="links">
                        {self_link _sort_arg='sort_mode' _sort_field='links' _title="{tr}Links to other items in page{/tr}"}{tr}Links{/tr}{/self_link}
                    </th>
                {/if}

                {if $prefs.wiki_list_backlinks eq 'y'}
                    {assign var='cntcol' value=$cntcol+1}
                    <th id="backlinks">
                        {self_link _sort_arg='sort_mode' _sort_field='backlinks' _title="{tr}Links to this page in other pages{/tr}"}{tr}Backl.{/tr}{/self_link}
                    </th>
                {/if}

                {if $prefs.wiki_list_size eq 'y'}
                    {assign var='cntcol' value=$cntcol+1}
                    <th id="size">
                        {self_link _sort_arg='sort_mode' _sort_field='page_size' _title="{tr}Page size{/tr}"}{tr}Size{/tr}{/self_link}
                    </th>
                {/if}

                {if $prefs.wiki_list_language eq 'y'}
                    {assign var='cntcol' value=$cntcol+1}
                    <th id="language">
                        {self_link _sort_arg='sort_mode' _sort_field='lang' _title="{tr}Language{/tr}"}{tr}Lang.{/tr}{/self_link}
                    </th>
                {/if}

                {if $prefs.wiki_list_categories eq 'y'}
                    {assign var='cntcol' value=$cntcol+1}
                    <th id="categories">{tr}Categories{/tr}</th>
                {/if}

                {if $prefs.wiki_list_categories_path eq 'y'}
                    {assign var='cntcol' value=$cntcol+1}
                    <th id="catpaths">{tr}Categories{/tr}</th>
                {/if}

                {if $prefs.wiki_list_rating eq 'y' AND $prefs.feature_polls eq 'y'}
                    {assign var='cntcol' value=$cntcol+1}
                    <th id="rating">
                        {self_link _sort_arg='sort_mode' _sort_field='rating' _title="{tr}Ratings{/tr}"}{tr}Ratings{/tr}{/self_link}
                    </th>
                {/if}

                {if $show_actions eq 'y'}
                    {assign var='cntcol' value=$cntcol+1}
                    <th id="actions"></th>
                {/if}
            </tr>
        </thead>

        <tbody>

            {section name=changes loop=$listpages}

                {if isset($mapview) and $mapview}
                    <div class="listpagesmap" style="display:none;">{object_link type="wiki page" id="`$listpages[changes].pageName|escape`"}</div>
                {/if}

                {if $find eq $listpages[changes].pageName}
                    {assign var='pagefound' value='y'}
                {/if}

                <tr>

                    {if $checkboxes_on eq 'y'}
                        <td class="checkbox-cell">
                            <div class="form-check">
                                <input type="checkbox" name="checked[]" value="{$listpages[changes].pageName|escape}">
                            </div>
                        </td>
                    {/if}

                    {if $prefs.wiki_list_id eq 'y'}
                        <td class="integer">
                            <a href="{$listpages[changes].pageName|sefurl}" class="link tips" title="{$listpages[changes].pageName|escape}:{tr}View page{/tr}">
                                {$listpages[changes].page_id}
                            </a>
                        </td>
                    {/if}

                    {if $prefs.wiki_list_name eq 'y'}
                        <td class="text">
                            {*
                                The variant of the object link below adds the baseurl as received by the request to the href attribute generated.
                                I.e. "http://192.168.1.10/tiki-listpages.php?page=MyPage" instead of "tiki-listpages.php?page=MyPage"
                                This leads to trouble when using a reverse proxy that takes an external fqdn and maps it to a local address.
                                Other templates do not use this object_link but an simple <a href></a>. See i.e tiki_lastchanges.tpl so we use it here as well.
                                Same for the link generated for the page id (wiki_list_id) above.
                            *}
                            {*
                                {object_link type=wiki id=$listpages[changes].pageName url=$listpages[changes].pageName|sefurl:'wiki':'':$all_langs title=$listpages[changes].pageName|truncate:$prefs.wiki_list_name_len:"...":true}
                            *}
                            <a href="{$listpages[changes].pageName|sefurl}" class="link tips" title="{$listpages[changes].pageName|escape}:{tr}View page{/tr}">
                                {$listpages[changes].pageName|truncate:$prefs.wiki_list_name_len:"...":true|escape}
                            </a>
                            {if $prefs.wiki_list_description eq 'y' && $listpages[changes].description neq ""}
                                <div class="subcomment">
                                    {$listpages[changes].description|truncate:$prefs.wiki_list_description_len:"...":true}
                                </div>
                            {/if}
                            {if !empty($listpages[changes].snippet)}
                                <div class="subcomment">{$listpages[changes].snippet}</div>
                            {/if}
                        </td>
                    {/if}

                    {if isset($wplp_used)}
                        {foreach from=$wplp_used key=lc item=ln}
                            <td class="text">
                                {if $listpages[changes].translations[$lc]}
                                    <a href="{$listpages[changes].translations[$lc]|sefurl}" class="link" title="{tr}View page{/tr}&nbsp;{$listpages[changes].translations[$lc]|escape}">
                                        {$listpages[changes].translations[$lc]|escape}
                                    </a>
                                {/if}
                            </td>
                        {/foreach}
                    {/if}

                    {if $prefs.wiki_list_hits eq 'y' AND $prefs.feature_stats eq 'y'}
                        <td class="integer">
                            {$listpages[changes].hits}
                        </td>
                    {/if}

                    {if $prefs.wiki_list_lastmodif eq 'y' or $prefs.wiki_list_comment eq 'y'}
                        <td class="date">
                            {if $prefs.wiki_list_lastmodif eq 'y'}
                                <div>{$listpages[changes].lastModif|tiki_short_datetime}</div>
                            {/if}
                            {if $prefs.wiki_list_comment eq 'y' && $listpages[changes].comment neq ""}
                                <div>
                                    <i>{$listpages[changes].comment|truncate:$prefs.wiki_list_comment_len:"...":true|escape}</i>
                                </div>
                            {/if}
                        </td>
                    {/if}

                    {if $prefs.wiki_list_creator eq 'y'}
                        <td class="username">
                            {$listpages[changes].creator|userlink}
                        </td>
                    {/if}

                    {if $prefs.wiki_list_user eq 'y'}
                        <td class="username">
                            {$listpages[changes].user|userlink}
                        </td>
                    {/if}

                    {if $prefs.wiki_list_lastver eq 'y'}
                        <td class="integer">
                            {$listpages[changes].version}
                        </td>
                    {/if}

                    {if $prefs.wiki_list_status eq 'y'}
                        <td class="icon">
                            {if $listpages[changes].flag eq 'locked'}
                                {icon name='lock' alt="{tr}Locked{/tr}"}
                            {else}
                                {icon name='unlock' alt="{tr}unlocked{/tr}"}
                            {/if}
                        </td>
                    {/if}

                    {if $prefs.wiki_list_versions eq 'y'}
                        {if $prefs.feature_history eq 'y' and $tiki_p_wiki_view_history eq 'y'}
                            <td class="integer">
                                <a class="link" href="tiki-pagehistory.php?page={$listpages[changes].pageName|escape:"url"}">
                                    {$listpages[changes].version}
                                </a>
                            </td>
                        {else}
                            <td class="integer">
                                {$listpages[changes].version}
                            </td>
                        {/if}
                    {/if}

                    {if $prefs.wiki_list_links eq 'y'}
                        <td class="integer">
                            {$listpages[changes].links}
                        </td>
                    {/if}

                    {if $prefs.wiki_list_backlinks eq 'y'}
                        {if $prefs.feature_backlinks eq 'y'}
                            <td class="integer">
                                <a class="link" href="tiki-backlinks.php?page={$listpages[changes].pageName|escape:"url"}">
                                    {$listpages[changes].backlinks}
                                </a>
                            </td>
                        {else}
                            <td class="integer">{$listpages[changes].backlinks}</td>
                        {/if}
                    {/if}

                    {if $prefs.wiki_list_size eq 'y'}
                        <td class="integer">{$listpages[changes].len|kbsize}</td>
                    {/if}

                    {if $prefs.wiki_list_language eq 'y'}
                        <td class="text">
                            {$listpages[changes].lang}
                        </td>
                    {/if}

                    {if $prefs.wiki_list_categories eq 'y'}
                        <td class="text">
                            {foreach $listpages[changes].categname as $categ}
                                {if !$categ@first}<br>{/if}
                                {$categ|escape}
                            {/foreach}
                        </td>
                    {/if}

                    {if $prefs.wiki_list_categories_path eq 'y'}
                        <td class="text">
                            {foreach $listpages[changes].categpath as $categpath}
                                {if !$categpath@first}<br>{/if}
                                {$categpath|escape}
                            {/foreach}
                        </td>
                    {/if}

                    {if $prefs.wiki_list_rating eq 'y' AND $prefs.feature_polls eq 'y'}
                        <td class="integer">
                            {if isset($listpages[changes].rating)}{$listpages[changes].rating}{/if}
                        </td>
                    {/if}

                    {if $show_actions eq 'y'}
                        <td class="action">
                            {actions}
                                {strip}
                                    {if $listpages[changes].perms.tiki_p_edit eq 'y'}
                                        <action>
                                            <a href="tiki-editpage.ph