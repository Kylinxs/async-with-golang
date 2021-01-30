{* $Id$ *}
{title}{if $parentId ne 0}{tr}Category:{/tr} {$p_info.name}{else}{tr}Categories{/tr}{/if}{/title}

{if $parentId and $p_info.description}
    <div class="description form-text">{$p_info.description|escape|nl2br}</div>
{/if}
<div class="mb-3 mx-0 t_navbar">
    {button href="tiki-edit_categories.php" _type="link" _text="{tr}Organize Objects{/tr}" _icon_name="structure" _title="{tr}Organize Objects{/tr}"}
    {if $tiki_p_admin_categories eq 'y'}
        {button href="tiki-admin_categories.php?parentId=$parentId" _type="link" _icon_name="settings" _text="{tr}Admin Categories{/tr}" _title="{tr}Admin the Category System{/tr}"}
    {/if}
</div>

<div class="t_navbar mb-4">
    {tr}Browse in:{/tr}
    <div class="btn-group">
        <a class="btn btn-info" {if $type eq ''} id="highlight"{/if} href="tiki-browse_categories.php?find={$find|escape:"url"}&amp;deep={$deep}&amp;parentId={$parentId|escape:"url"}&amp;sort_mode={$sort_mode|escape:"url"}">{tr}All{/tr}</a>
        <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown">
            {tr}or in{/tr}
            <span class="sr-only">Toggle dropdown</span>
        </button>
        <div class="dropdown-menu" role="menu">
            {if $prefs.feature_wiki eq 'y'}
                <a class="dropdown-item" {if $type eq 'wiki page'} id="highlight"{/if} href="tiki-browse_categories.php?find={$find|escape:"url"}&amp;deep={$deep|escape:"url"}&amp;type=wiki+page&amp;parentId={$parentId|escape:"url"}&amp;sort_mode={$sort_mode|escape:"url"}">
                    {tr}Wiki pages{/tr}
                </a>
            {/if}
            {if $prefs.feature_file_galleries eq 'y'}
                <a class="dropdown-item" {if $type eq 'file gallery'}id="highlight"{/if} href="tiki-browse_categories.php?find={$find|escape:"url"}&amp;deep={$deep|escape:"url"}&amp;type=file+gallery&amp;parentId={$parentId|escape:"url"}&amp;sort_mode={$sort_mode|escape:"url"}">
                    {tr}File Galleries{/tr}
                </a>
                <a class="dropdown-item" {if $type eq 'file'}id="highlight"{/if} href="tiki-browse_categories.php?find={$find|escape:"url"}&amp;deep={$deep|escape:"url"}&amp;type=file&amp;parentId={$parentId|escape:"url"}&amp;sort_mode={$sort_mode|escape:"url"}">
                    {tr}Files{/tr}
                </a>
            {/if}
            {if $prefs.feature_blogs eq 'y'}
                <a class="dropdown-item" {if $type eq 'blog'}id="highlight"{/if} href="tiki-browse_categories.php?find={$find|escape:"url"}&amp;deep={$deep|escape:"url"}&amp;type=blog&amp;parentId={$parentId|escape:"url"}&amp;sort_mode={$sort_mode|escape:"url"}">
                    {tr}Blogs{/tr}
                </a>
            {/if}
            {if $prefs.feature_trackers eq 'y'}
                <a class="dropdown-item" {if $type eq 'tracker'}id="highlight"{/if} href="tiki-browse_categories.php?find={$find|escape:"url"}&amp;deep={$deep|escape:"url"}&amp;type=tracker&amp;parentId={$parentId|escape:"url"}&amp;sort_mode={$sort_mode|escape:"url"}">
                    {tr}Trackers{/tr}
                </a>
                <a class="dropdown-item" {if $type eq 'trackeritem'}id="highlight"{/if} href="tiki-browse_categories.php?find={$find|escape:"url"}&amp;deep={$deep|escape:"url"}&amp;type=trackeritem&amp;parentId={$parentId|escape:"url"}&amp;sort_mode={$sort_mode|escape:"url"}">
                    {tr}Trackers Items{/tr}
                </a>
            {/if}
            {if $prefs.feature_quizzes eq 'y'}
                <a class="dropdown-item" {if $type eq 'quiz'}id="highlight"{/if} href="tiki-browse_categories.php?find={$find|escape:"url"}&amp;deep={$deep|escape:"url"}&amp;type=quiz&amp;parentId={$parentId|escape:"url"}&amp;sort_mode={$sort_mode|escape:"url"}">
                    {tr}Quizzes{/tr}
                </a>
            {/if}
            {if $prefs.feature_polls eq 'y'}
                <a class="dropdown-item" {if $type eq 'poll'}id="highlight"{/if} href="tiki-browse_categories.php?find={$find|escape:"url"}&amp;deep={$deep|escape:"url"}&amp;type=poll&amp;parentId={$parentId|escape:"url"}&amp;sort_mode={$sort_mode|escape:"url"}">
                    {tr}Polls{/tr}
                </a>
            {/if}
            {if $prefs.feature_surveys eq 'y'}
                <a class="dropdown-item" {if $type eq 'survey'}id="highlight"{/if} href="tiki-browse_categories.php?find={$find|escape:"url"}&amp;deep={$deep|escape:"url"}&amp;type=survey&amp;parentId={$parentId|escape:"url"}&amp;sort_mode={$sort_mode|escape:"url"}">
                    {tr}Surveys{/tr}
                </a>
            {/if}
            {if $prefs.feature_directory eq 'y'}
                <a class="dropdown-item" {if $type eq 'directory'}id="highlight"{/if} href="tiki-browse_categories.php?find={$find|escape:"url"}&amp;deep={$deep|escape:"url"}&amp;type=directory&amp;parentId={$parentId|escape:"url"}&amp;sort_mode={$sort_mode|escape:"url"}">
                    {tr}Directory{/tr}
                </a>
            {/if}
            {if $prefs.feature_faqs eq 'y'}
                <a class="dropdown-item" {if $type eq 'faq'}id="highlight"{/if} href="tiki-browse_categories.php?find={$find|escape:"url"}&amp;deep={$deep|escape:"url"}&amp;type=faq&amp;parentId={$parentId|escape:"url"}&amp;sort_mode={$sort_mode|escape:"url"}">
                    {tr}FAQs{/tr}
                </a>
            {/if}
            {if $prefs.feature_sheet eq 'y'}
                <a class="dropdown-item" {if $type eq 'sheet'}id="highlight"{/if} href="tiki-browse_categories.php?find={$find|escape:"url"}&amp;deep={$deep|escape:"url"}&amp;type=sheet&amp;parentId={$parentId|escape:"url"}&amp;sort_mode={$sort_mode|escape:"url"}">
                    {tr}Sheets{/tr}
                </a>
            {/if}
            {if $prefs.feature_articles eq 'y'}
                <a class="dropdown-item" {if $type eq 'article'}id="highlight"{/if} href="tiki-browse_categories.php?find={$find|escape:"url"}&amp;deep={$deep|escape:"url"}&amp;type=article&amp;parentId={$parentId|escape:"url"}&amp;sort_mode={$sort_mode|escape:"url"}">
                    {tr}Articles{/tr}
                </a>
            {/if}
            {if $prefs.feature_forums eq 'y'}
                <a class="dropdown-item" {if $type eq 'forum'}id="highlight"{/if} href="tiki-browse_categories.php?find={$find|escape:"url"}&amp;deep={$deep|escape:"url"}&amp;type=forum&amp;parentId={$parentId|escape:"url"}&amp;sort_mode={$sort_mode|escape:"url"}">
                    {tr}Forums{/tr}
                </a>
            {/if}
        </div>
    </div>
</div>

<form method="post" action="tiki-browse_categories.php" class="d-flex flex-row flex-wrap align-items-center" role="form">
    <div class="mb-3 row mx-0">
        <label class="col-form-label sr-only" for="find">{tr}Find{/tr}</label>
        <div class="input-group">
            <span class="input-group-text">
                {icon name="search"} {if $parentId ne 0}{$p_info.name|escape} {/if}
            </span>
            <input class="form-control input-sm" type="text" name="find" id="find" value="{$find|escape}">
            <input type="submit" class="btn btn-info" value="{tr}Find{/tr}" name="search">
        </div>
        <span class="form-text" style="display:inline-block; margin: 0;">{help url="#" desc="{tr}Find in:{/tr} <ul><li>{tr}Name{/tr}</li><li>{tr}Description{/tr}</li></ul>"}</span>
    </div>
    <div class="mb-3 row mx-0">
        <div class="form-check">
            <label class="form-check-label"><input type="checkbox" class="form-check-input" name="deep" {if $deep eq 'on'}checked="checked"{/if}>{tr} in the current category and its subcategories{/tr}</label>
        </div>
    </div>
    <input type="hidden" name="parentId" value="{$parentId|escape}">
    <input type="hidden" name="type" value="{$type|escape}">
    <input type="hidden" name="sort_mode" value="{$sort_mode|escape}">
</form>

{if $deep eq 'on'}
    <a class="link" href="tiki-browse_categories.php?find={$find|escape:"url"}&amp;type={$type|escape:"url"}&amp;parentId={$parentId|escape:"url"}&amp;sort_mode={$sort_mode|escape:"url"}">
        {tr}Hide subcategories objects{/tr}
    </a>
{else}
    <a class="link" href="tiki-browse_categories.php?find={$find|escape:"url"}&amp;type={$type|escape:"url"}&amp;deep=on&amp;parentId={$parentId|escape:"url"}&amp;sort_mode={$sort_mode|escape:"url"}">{tr}Show subcategories objects{/tr}</a>
{/if}

<br><br>

{if isset($p_info)}
    <div class="breadcrumb treetitle">{tr}Current category:{/tr}
        <a href="tiki-browse_categories.php?parentId=0&amp;deep={$deep|escape:"url"}&amp;type={$type|escape:"url"}" class="categpath">{tr}Top{/tr}</a>
        {foreach $p_info.tepath as $id=>$name}
            &nbsp;{$prefs.site_crumb_seper}&nbsp;
            <a class="categpath" href="tiki-browse_categories.php?parentId={$id}&amp;deep={$deep|escape:"url"}&amp;type={$type|escape:"url"}">{$name|escape}</a>
        {/foreach}
        {$eyes_curr}
    </div>
{/if}

{if $cant_pages > 0}
    {tabset name='browse-categories'}
        {tab name="{tr}Categories{/tr}"}
            {if $parentId ne '0'}
                <div class="ps-3">
                    <a class="catname tips" href="tiki-browse_categories.php?parentId={$father|escape:"url"}&amp;deep={$deep|escape:"url"}&amp;type={$type|escape:"url"}" title=":{tr}Up one level{/tr}">
                        {icon name='level-up'}
                    </a>
                </div>
            {/if}
            <div class="cattree">{$tree}</div>
        {/tab}
        {tab name="{tr}Objects{/tr}"}
            <div class="catobj">
                <div class="table-responsive">
                    <table class="table">
                        <tr>
                            <th>
                                {tr}Name{/tr}
                            </th>
                            <th>
                                {tr}Type{/tr}
                            </th>
                            {if $deep eq 'on'}
                                <th>
                                    {tr}Category{/tr}
                                </th>
                            {/if}
                        </tr>

                        {section name=ix loop=$objects}
                            <tr>
                                <td class="text">
                                    <a class="catname" href="{if empty($objects[ix].sefurl)}{$objects[ix].href}{else}{$objects[ix].sefurl}{/if}">
                                        {$objects[ix].name|escape|default:'&nbsp;'}
                                    </a>
                                    {if $objects[ix].type ne 'blog post'}<div class="subcomment">{$objects[ix].description|escape|nl2br}</div>{/if}
                                </td>
                                <td class="text">
                                    {tr}{$objects[ix].type|replace:"wiki page":"wiki"|replace:"trackeritem":"tracker item"}{/tr}
                                </td>
                                {if $deep eq 'on'}
                                    <td class="text">
                                        {$objects[ix].categName|tr_if|escape}
                                    </td>
                                {/if}
                            </tr>
                        {sectionelse}
                            {if $deep eq 'on'}
                                {norecords _colspan=3}
                            {else}
                                {norecords _colspan=2}
                            {/if}
                        {/section}
                    </table>
                </div>
            </div>

            {pagination_links cant=$cant_pages step=$maxRecords offset=$offset}{/pagination_links}
        {/tab}
    {/tabset}
{else}
    <div class="cattree">{$tree}</div>
{/if}
