{* $Id$ *}

{title help="My Account"}{tr}My Account{/tr}{/title}

{include file='tiki-mytiki_bar.tpl'}
<br>

{capture name=my}
    {tabset name="mytiki"}
        {if $prefs.feature_wiki eq 'y' and $mytiki_pages eq 'y'}
            {tab name="{if $userwatch eq $user}{tr}My pages{/tr}{else}{tr}User Pages{/tr}{/if}"}
                <div id="content1" class="content clearfix mb-4">
                    <h4>{if $userwatch eq $user}{tr}My pages{/tr}{else}{tr}User Pages{/tr}{/if}</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <tr>
                                <th>
                                    <a href="tiki-my_tiki.php?sort_mode={if $sort_mode eq 'pageName_desc'}pageName_asc{else}pageName_desc{/if}">{tr}Page{/tr}</a>
                                </th>
                                <th>{tr}Creator{/tr}</th>
                                <th>{tr}Last editor{/tr}</th>
                                <th>
                                    <a href="tiki-my_tiki.php?sort_mode={if $sort_mode eq 'date_desc'}date_asc{else}date_desc{/if}">{tr}Last modification{/tr}</a>
                                </th>
                                <th></th>
                            </tr>

                            {section name=ix loop=$user_pages}
                                <tr>
                                    <td class="text">
                                        <a class="tips" title=":{tr}View{/tr}" href="tiki-index.php?page={$user_pages[ix].pageName|escape:"url"}">{$user_pages[ix].pageName|truncate:40:"(...)"}</a>
                                    </td>
                                    <td class="username">
                                        {if $userwatch eq $user_pages[ix].creator}{tr}y{/tr}{else}&nbsp;{/if}
                                    </td>
                                    <td class="username">
                                        {if $userwatch eq $user_pages[ix].lastEditor}{tr}y{/tr}{else}&nbsp;{/if}
                                    </td>
                                    <td class="date">
                                        {$user_pages[ix].date|tiki_short_datetime}
                                    </td>
                                    <td class="action">
                                        <a class="tips" href="tiki-editpage.php?page={$user_pages[ix].pageName|escape:"url"}" title=":{tr}Edit{/tr}">
                                            {icon name='edit'}
                                        </a>
                                    </td>
                                </tr>
                            {/section}
                        </table>
                    </div>
                    <ul class="nav nav-pills float-end">
                        <li><a href="#">{tr}Records{/tr} <span class="badge bg-secondary">{$user_pages|@count}</span></a></li>
                    </ul>
                </div>
            {/tab}
        {/if}

        {if $prefs.feature_articles eq 'y' and $mytiki_articles eq 'y'}
            {tab name="{if $userwatch eq $user}{tr}My Articles{/tr}{else}{tr}User Articles{/tr}{/if}"}
                <div id="content3" class="content clearfix mb-4">
                    <h4>{if $userwatch eq $user}{tr}My Articles{/tr}{else}{tr}User Articles{/tr}{/if}</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <tr>
                                <th>{tr}Article{/tr}</th>
                                <th style="width:50px"></th>
                            </tr>

                            {section name=ix loop=$user_articles}
                                <tr>
                                    <td class="text">
                                        <a class="tips" title=":{tr}Edit{/tr}" href="{$user_articles[ix].articleId|sefurl:article}">
                                            {$user_articles[ix].title}
                                        </a>
                                    </td>
                                    <td class="action">
                                        <a class="tips" href="tiki-edit_article.php?articleId={$user_articles[ix].articleId}" title=":{tr}Edit{/tr}">
                                            {icon name='edit'}
                                        </a>
                                    </td>
                                </tr>
                            {/section}
                        </table>
                    </div>
                    <ul class="nav nav-pills float-end">
                        <li><a href="#">{tr}Records{/tr} <span class="badge bg-secondary">{$user_articles|@count}</span></a></li>
                    </ul>
                </div>
            {/tab}
        {/if}

        {if $prefs.feature_trackers eq 'y' and $mytiki_user_items eq 'y'}
            {tab name="{if $userwatch eq $user}{tr}My User Items{/tr}{else}{tr}User Items{/tr}{/if}"}
                <div id="content4" class="content clearfix mb-4">
                    <h4>{if $userwatch eq $user}{tr}My User Items{/tr}{else}{tr}User Items{/tr}{/if}</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <tr>
                                <th>{tr}Item{/tr}</th>
                                <th>{tr}Tracker{/tr}</th>
                            </tr>

                            {section name=ix loop=$user_items}
                                <tr>
                                    <td class="text">
                                        <a class="tips" title=":{tr}View{/tr}" href="tiki-view_tracker_item.php?trackerId={$user_items[ix].trackerId}&amp;ite