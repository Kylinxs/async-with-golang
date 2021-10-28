
{title help="Feeds"}{tr}External Feeds{/tr}{/title}

{remarksbox type="tip" title="{tr}Tips{/tr}"}
    {tr}This page is to configure settings of external feeds read/imported by Tiki. To generate/export feeds, look for "Feeds" on the admin panel, or{/tr}
    <a class="alert-link" href="tiki-admin.php?page=rss">{tr}Click Here{/tr}</a>.
    <hr/>
    {tr}To use feeds in a text area (Wiki page, etc), a <a class="alert-link" href="tiki-admin_modules.php">module</a> or a template, use {literal}{rss id=x}{/literal}, where x is the ID of the feed.{/tr}
    {tr}To use them to generate articles, use the <a class="alert-link" href="https://doc.tiki.org/Article+generator" target="_blank">Article generator</a> for that specific feed{/tr}.
{/remarksbox}

{if $preview eq 'y'}
    {remarksbox type="info" title="{tr}Content for the feed{/tr}"}
        {if $feedtitle ne ''}
            <h3>{$feedtitle.title|escape}</h3>
        {/if}
        <ul>
            {section name=ix loop=$items}
                <li><a href="{$items[ix].url|escape}" class="link">{$items[ix].title|escape}</a>{if $items[ix].pubDate ne ""}<br><span class="rssdate">({$items[ix].pubDate|escape})</span>{/if}</li>
            {/section}
        </ul>
    {/remarksbox}
{/if}

{tabset name="admin_rssmodules"}

    {tab name="{tr}External Feeds{/tr}"}
        {if isset($channels) && $channels|count > 0}
            <form action="tiki-admin_rssmodules.php" method="post">
                <input type="hidden" name="offset" value="{$offset|escape}">
                <input type="hidden" name="sort_mode" value="{$sort_mode|escape}">
                {ticket}
                <button
                        type="submit"
                        name="refresh_all"
                        value="y"
                        class="btn btn-secondary float-sm-end tips"
                        title=":{tr}Please be patient, this may take a while{/tr}"
                >
                    {icon name="refresh" _menu_text='y' _menu_icon='y' alt="{tr}Refresh all feeds{/tr}"}
                </button>
            </form>
        {/if}
        <h2>{tr}External Feeds{/tr}</h2>
        <div align="center">
            {if $channels or ($find ne '')}
                {include file='find.tpl'}
            {/if}
            <table class="table table-striped table-hover">
                <tr>
                    <th>{self_link _sort_arg='sort_mode' _sort_field='rssId'}{tr}ID{/tr}{/self_link}</th>
                    <th>{self_link _sort_arg='sort_mode' _sort_field='name'}{tr}Name{/tr}{/self_link}</th>
                    <th>{self_link _sort_arg='sort_mode' _sort_field='lastUpdated'}{tr}Last update{/tr}{/self_link}</th>
                    <th>{self_link _sort_arg='sort_mode' _sort_field='showTitle'}{tr}Show Title{/tr}{/self_link}</th>
                    <th>{self_link _sort_arg='sort_mode' _sort_field='showPubDate'}{tr}Show Date{/tr}{/self_link}</th>
                    <th></th>
                </tr>
                {section name=chan loop=$channels}
                    <tr>
                        <td class="id">{$channels[chan].rssId|escape}</td>
                        <td class="text">
                            {$channels[chan].name|escape}
                            <span class="form-text">
                                {if $channels[chan].description}{$channels[chan].description|escape|nl2br}<br>{/if}
                                {tr}Site:{/tr} <a href="{$channels[chan].siteurl|escape}">{$channels[chan].sitetitle|escape}</a><br>
                                {tr}Feed:{/tr} <a class="link" href="{$channels[chan].url|escape}">{$channels[chan].url|truncate:50:"...":true}</a>
                            </span>
                        </td>
                        <td class="text">
                            {if $channels[chan].lastUpdated eq '1000000'}{tr}Never{/tr}{else}{$channels[chan].lastUpdated|tiki_short_datetime}{/if}
                            <span class="form-text">{tr}Refresh rate:{/tr} {$channels[chan].refresh|duration}</span>
                        </td>
                        <td class="text">{$channels[chan].showTitle|escape}</td>
                        <td class="text">{$channels[chan].showPubDate|escape}</td>
                        <td class="action">
                            {actions}
                                {strip}
                                    <action>
                                        <a href="tiki-admin_rssmodules.php?offset={$offset|escape}&amp;sort_mode={$sort_mode|escape}&amp;view={$channels[chan].rssId|escape}">
                                            {icon name="rss" _menu_text='y' _menu_icon='y' alt="{tr}View{/tr}"}
                                        </a>
                                    </action>
                                    <action>
                                        <form action="tiki-admin_rssmodules.php" method="post">
                                            <input type="hidden" name="offset" value="{$offset|escape}">
                                            <input type="hidden" name="sort_mode" value="{$sort_mode|escape}">
                                            {ticket}
                                            <button
                                                type="submit"
                                                name="refresh"
                                                value="{$channels[chan].rssId|escape}"
                                                class="btn btn-link link-list"
                                            >
                                                {icon name="refresh"} {tr}Refresh{/tr}
                                            </button>
                                        </form>
                                    </action>
                                    <action>
                                        <a href="tiki-admin_rssmodules.php?offset={$offset|escape}&amp;sort_mode={$sort_mode|escape}&amp;rssId={$channels[chan].rssId|escape}">
                                            {icon name='edit' _menu_text='y' _menu_icon='y' alt="{tr}Edit{/tr}"}
                                        </a>
                                    </action>
                                    {if $prefs.feature_articles eq 'y'}
                                        <action>
                                            <a href="tiki-admin_rssmodules.php?offset={$offset|escape}&amp;sort_mode={$sort_mode|escape}&amp;article={$channels[chan].rssId|escape}">
                                                {icon name='textfile' _menu_text='y' _menu_icon='y' alt="{tr}Article generator{/tr}"}
                                            </a>
                                        </action>
                                    {/if}
                                    <action>
                                        <form action="tiki-admin_rssmodules.php" method="post">
                                            <input type="hidden" name="offset" value="{$offset|escape}">
                                            <input type="hidden" name="sort_mode" value="{$sort_mode|escape}">
                                            {ticket}
                                            <button
                                                    type="submit"
                                                    name="clear"
                                                    value="{$channels[chan].rssId|escape}"
                                                    class="btn btn-link link-list"
                                            >
                                                {icon name="trash"} {tr}Clear cache{/tr}
                                            </button>
                                        </form>
                                    </action>
                                    <action>
                                        <a href="tiki-admin_rssmodules.php?offset={$offset|escape}&amp;sort_mode={$sort_mode|escape}&amp;remove={$channels[chan].rssId|escape}"
                                           onclick="confirmPopup('{tr}Remove external feed?{/tr}', '{ticket mode="get"}')"
                                        >
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

            {pagination_links cant=$cant step=$maxRecords offset=$offset}{/pagination_links}

        </div>
    {/tab}

    {if $rssId > 0}
        {assign var="feedEditLabel" value="{tr}Edit Feed{/tr}"}
    {else}
        {assign var="feedEditLabel" value="{tr}Create Feed{/tr}"}
    {/if}
    {tab name=$feedEditLabel}
        <h2>{$feedEditLabel}
        {if $rssId > 0}
            {$name|escape}</h2>
            {button href="tiki-admin_rssmodules.php" cookietab="2" _keepall="y" _icon_name="create" _text="{tr}Create new external feed{/tr}"}
        {else}
            </h2>
        {/if}
        <form action="tiki-admin_rssmodules.php" method="post">
            {ticket}
            <input type="hidden" name="rssId" value="{$rssId|escape}">
            <div class="mb-3 row">
                <label for="name" class="col-form-label col-sm-3">{tr}Name{/tr}</label>
                <div class="col-sm-9">
                    <input type="text" name="name" value="{$name|escape}" class="form-control">
                </div>
            </div>
            <div class="mb-3 row">
                <label for="url" class="col-form-label col-sm-3">{tr}URL{/tr}</label>
                <div class="col-sm-9">
                    <input type="url" name="url" value="{$url|escape}" class="form-control">
                </div>
            </div>
            <div class="mb-3 row">
                <label for="description" class="col-form-label col-sm-3">{tr}Description{/tr}</label>
                <div class="col-sm-9">
                    <textarea name="description" rows="4" class="form-control">{$description|escape}</textarea>
                </div>
            </div>
            <div class="mb-3 row">
                <label for="refreshMinutes" class="col-form-label col-sm-3">{tr}Refresh rate{/tr}</label>
                <div class="col-sm-9">
                    <select class="form-select" name="refreshMinutes">
                        {foreach [1, 5, 10, 15, 20, 30, 45, 60, 90, 120, 360, 720, 1440] as $min}
                            <option value="{$min|escape}" {if $refreshSeconds eq ($min*60)}selected="selected"{/if}>{($min*60)|duration}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="mb-3 row">
                <div class="col-sm-9 offset-sm-3">
                    <div class="form-check">
                        <label class="form-label form-check-label">
                            <input type="checkbox" class="form-check-input" name="showTitle" {if $showTitle eq 'y'}checked="checked"{/if}>
                            {tr}Show feed title{/tr}
                        </label>
                    </div>
                </div>
            </div>
            <div class="mb-3 row">
                <div class="col-sm-9 offset-sm-3">
                    <div class="form-check">
                        <label class="form-label form-check-label">
                            <input type="checkbox" class="form-check-input" name="showPubDate" {if $showPubDate eq 'y'}checked="checked"{/if}>
                            {tr}Show publish date{/tr}
                        </label>
                    </div>
                </div>
            </div>
            <div class="mb-3 row">
                <div class="col-sm-9 offset-sm-3">
                    <input type="submit" class="btn btn-primary" name="save" value="{tr}Save{/tr}">
                </div>
            </div>
        </form>
    {/tab}

    {if $articleConfig}
        {tab name="{tr}Article Generator{/tr}"}
            <h2>{tr _0='"'|cat:$articleConfig.feed_name|cat:'"'|escape}Article Generator for %0{/tr}</h2>
            {remarksbox type="tip" title="{tr}Tips{/tr}"}
                    {tr}Once you have defined the settings below, each new item in this rss feed will generate a new article{/tr}.
                    <a target="tikihelp" href="https://doc.tiki.org/Article+generator" class="tikihelp alert-link" style="float:none" title="{tr}Article Generator:{/tr}
                        {tr}Documentation{/tr}">
                        {icon name="help"}
                    </a>
                    <hr>
                    {tr}You can enable <strong>Show source</strong> for the <a href="tiki-article_types.php" class="alert-link" target="_blank">article type</a> (hidden by default), to allow users to read the full content{/tr}.
            {/remarksbox}

            <form method="post" action="">
                {ticket}
                <div class="mb-3 row">
                    <label for="article_active" class="col-form-label col-sm-3">{tr}Enable{/tr}</label>
                    <div class="col-sm-9">
                        <div class="form-check">
                            <input
                                id="article_active"
                                type="checkbox"
                                name="enable"
                                class="form-check-input"
                                value="1"
                                {if !empty($articleConfig.active)} checked="checked"{/if}
                            >
                        </div>
                    </div>
                </div>
                {if $prefs.feature_submissions eq 'y'}
                    <div class="mb-3 row">
                        <label for="article_submission" class="col-form-label col-sm-3">{tr}Use article submission system{/tr}</label>
                        <div class="col-sm-9">
                            <div class="form-check">
                                <input
                                    id="article_submission"
                                    type="checkbox"
                                    name="submission"
                                    class="form-check-input"
                                    value="1"
                                    {if !empty($articleConfig.active)} checked="checked"{/if}
                                >
                            </div>
                        </div>
                    </div>
                {/if}
                <div class="mb-3 row">
                    <label for="article_expiry" class="col-form-label col-sm-3">{tr}Expiration{/tr}</label>
                    <div class="col-sm-9">
                        <div class="input-group">
                            <input
                                id="article_expiry"
                                type="text"
                                name="expiry"
                                class="form-control"
                                value="{$articleConfig.expiry|escape}"
                            >
                            <span class="input-group-text">{tr}days{/tr}</span>
                        </div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="article_future_publish" class="col-form-label col-sm-3">{tr}Publish in the future{/tr}</label>
                    <div class="col-sm-9">
                        <div class="input-group">
                            <input
                                id="article_future_publish"
                                type="text"
                                name="future_publish"
                                class="form-control"
                                value="{$articleConfig.future_publish|escape}"
                            >
                            <span class="input-group-text">{tr}minutes{/tr}</span>
                        </div>
                        <div class="form-text">
                            {tr}Enter -1 to use original publishing date from the feed{/tr}
                        </div>
                    </div>
                </div>
                <fieldset>
                    <legend>{tr}Default Settings{/tr}</legend>
                    <div class="mb-3 row">
                        <label for="article_type" class="col-form-label col-sm-3">{tr}Type{/tr}</label>
                        <div class="col-sm-9">
                            <select id="article_type" name="type" class="form-control">
                                {foreach from=$types item=t}
                                    <option value="{$t.type|escape}"{if $t.type eq $articleConfig.atype} selected="selected"{/if}>{$t.type|escape}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="article_topic" class="col-form-label col-sm-3">{tr}Topic{/tr}</label>
                        <div class="col-sm-9">
                            <select id="article_topic" name="topic" class="form-control">
                                <option value="0">{tr}None{/tr}</option>
                                {foreach from=$topics item=t}
                                    <option value="{$t.topicId|escape}"{if $t.topicId eq $articleConfig.topic} selected="selected"{/if}>{$t.name|escape}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="article_rating" class="col-form-label col-sm-3">{tr}Rating{/tr}</label>
                        <div class="col-sm-9">
                            <select id="article_rating" name="rating" class="form-control">
                                {foreach from=$ratingOptions item=v}
                                    <option{if $v eq $articleConfig.rating} selected="selected"{/if}>{$v|escape}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    {if $prefs.feature_multilingual eq 'y'}
                        <div class="mb-3 row">
                            <label for="article_language" class="col-form-label col-sm-3">{tr}Language{/tr}</label>
                            <div class="col-sm-9">
                                <select id="article_language" name="a_lang" class="form-control">
                                    <option value="">{tr}Not set{/tr}</option>
                                    {section name=ix loop=$languages}
                                        <option value="{$languages[ix].value|escape}"{if $articleConfig.a_lang|escape eq $languages[ix].value} selected="selected"{/if}>{$languages[ix].name}</option>
                                    {/section}
                                </select>
                            </div>
                        </div>
                    {/if}
                </fieldset>
                <fieldset>
                    <legend>
                        {tr}Custom Settings for Source Categories{/tr}
                    </legend>
                    {if !$sourcecats}
                        <p class="font-italic">{tr}No source categories detected for this feed{/tr}</p>
                    {else}
                        <table>
                            <tr>
                                <th>{tr}Source Category{/tr}
                                <th>{tr}Type{/tr}</th>
                                <th>{tr}Topic{/tr}</th>
                                <th>{tr}Rating{/tr}</th>
                                <th>{tr}Priority (10 is highest){/tr}</th>
                            </tr>
                            {foreach $sourcecats as $sourcecat => $settings}
                                <tr>
                                    <td>
                                        {$sourcecat|escape}
                                    </td>
                                    <td>
                                        <select name="custom_atype[{$sourcecat|escape}]">
                                            <option value="">{tr}Default{/tr}</option>
                                            {foreach from=$types item=t}
                                                <option value="{$t.type|escape}"{if $t.type eq $article_custom_info[$sourcecat].atype} selected="selected"{/if}>{$t.type|escape}</option>
                                            {/foreach}
                                        </select>
                                    </td>
                                    <td>
                                        <select name="custom_topic[{$sourcecat|escape}]">
                                            <option value="">{tr}Default{/tr}</option>
                                            <option value="0" {if $article_custom_info[$sourcecat].topic === "0"} selected="selected"{/if}>{tr}None{/tr}</option>
                                            {foreach from=$topics item=t}
                                                <option value="{$t.topicId|escape}"{if $t.topicId eq $article_custom_info[$sourcecat].topic} selected="selected"{/if}>{$t.name|escape}</option>
                                            {/foreach}
                                        </select>
                                    </td>
                                    <td>
                                        <select name="custom_rating[{$sourcecat|escape}]">
                                            <option value="">{tr}Default{/tr}</option>
                                            {foreach from=$ratingOptions item=v}
                                                <option value="{$v|escape}"{if $v === $article_custom_info[$sourcecat].rating} selected="selected"{/if}>{$v|escape}</option>
                                            {/foreach}
                                        </select>
                                    </td>
                                    <td>
                                        <select name="custom_priority[{$sourcecat|escape}]">
                                            {foreach from=$ratingOptions item=v}
                                                <option value="{$v|escape}"{if $v === $article_custom_info[$sourcecat].priority} selected="selected"{/if}>{$v|escape}</option>
                                            {/foreach}
                                        </select>
                                    </td>
                                </tr>
                            {/foreach}
                        </table>
                    {/if}
                </fieldset>
                <fieldset>
                    <legend>{tr}Categorize Created Articles{/tr}</legend>
                    <div class="mb-3 row">
                        <div class="col-sm-9 offset-sm-3">
                            {include file='categorize.tpl'}
                        </div>
                    </div>
                </fieldset>
                <fieldset>
                    <div class="mb-3 row">
                        <input type="submit" class="btn btn-primary" value="{tr}Save{/tr}">
                    </div>
                </fieldset>
            </form>
        {/tab}
    {/if}
{/tabset}