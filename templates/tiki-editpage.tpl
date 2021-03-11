
{* $Id$ *}
{extends 'layout_edit.tpl'}
{block name=title}
    {if $translation_mode eq 'n'}
        <h1 class="pagetitle">
            {capture name="pageDescription"}
                {$page|escape}{if $pageAlias ne ''} ({$pageAlias|escape}){/if}
            {/capture}
            {if isset($hdr) && $prefs.wiki_edit_section eq 'y'}
                {tr}Edit Section:{/tr} {$smarty.capture.pageDescription}
            {else}
                {tr _0='<a class="edit_pagetitle" href="'|cat:($page|sefurl)|cat:'">'|cat:$smarty.capture.pageDescription|cat:'</a>'}Edit %0{/tr}
            {/if}
        </h1>
    {else}
        {title}{tr}Update '{$page}'{/tr}{/title}
    {/if}
{/block}
{block name=content}
    {if $page|lower neq 'sandbox' and $prefs.feature_contribution eq 'y' and $prefs.feature_contribution_mandatory eq 'y'}
        {remarksbox type='tip' title="{tr}Tip{/tr}"}
            <strong class='mandatory_note'>{tr}Fields marked with an * are mandatory.{/tr}</strong>
        {/remarksbox}
    {/if}
    {if isset($customTip)}
        {remarksbox type='tip' title=$customTipTitle}
            {tr}{$customTip|escape}{/tr}
        {/remarksbox}
    {/if}
    {if isset($wikiHeaderTpl)}
        {$wikiHeaderTpl}
    {/if}
    {if $prefs.ajax_autosave eq "y"}
        <div class="float-end">
            {self_link _icon_name="view" _class="previewBtn tips" _ajax="n" _title=":{tr}Preview your changes{/tr}"}
            {/self_link}
        </div>
        {jq} $(".previewBtn").click(function(){
            auto_save('editwiki', autoSaveId);
            if ($('#autosave_preview:visible').length === 0) {
                if (!ajaxPreviewWindow) {
                    setCookie("preview_diff_style", "", "preview", "session");
                    $("#preview_diff_style").val("").trigger("change.select2");
                    $('#autosave_preview').slideDown('slow', function(){ ajax_preview( 'editwiki', autoSaveId, true );});
                }
            } else {
                ajax_preview( 'editwiki', autoSaveId, true );
            }
            return false;
        });{/jq}
    {else}
        <input type="hidden" value="{$alert_content}" name="alert_content">
        <div id="tiki_autosave_alert"></div>
    {/if}
    {if isset($data.draft)}
        {tr}Draft written on{/tr} {$data.draft.lastModif|tiki_long_time}<br/>
        {if $data.draft.lastModif < $data.lastModif}
            <b>{tr}Warning: new versions of this page have been made after this draft{/tr}</b>
        {/if}
    {/if}
    {if $page|lower eq 'sandbox'}
        {remarksbox type='tip' title="{tr}Tip{/tr}"}
            {tr}The Sandbox is a page where you can practice editing, etc. and use the preview feature to check the appearance of the page. No versions are stored for this page.{/tr}
        {/remarksbox}
    {/if}
        {if isset($included_by)}
                {remarksbox type='Warning' title="{tr}Warning{/tr}"}
                        {tr}The following item(s) include this one with Plugin Include and might be affected by these changes. Renaming sections can break related item(s).{/tr}
                {include file='tiki-edit-page-included_by.tpl'}
                {/remarksbox}
        {/if}
    {if $recursive_include eq 'y'}
        {remarksbox type='warning' title="{tr}Warning{/tr}"}
            {tr}You can not include a page within itself{/tr}
        {/remarksbox}
    {/if}
    {if $category_needed eq 'y'}
        {remarksbox type='Warning' title="{tr}Warning{/tr}"}
            <div class="alert alert-warning alert-dismissible"><em class=''>{tr}A category is mandatory{/tr}</em></div>
        {/remarksbox}
    {/if}
    {if $contribution_needed eq 'y'}
        {remarksbox type='Warning' title="{tr}Warning{/tr}"}
            <div class="alert alert-warning alert-dismissible"><em class=''>{tr}A contribution is mandatory {/tr}</em></div>
        {/remarksbox}
    {/if}
    {if isset($summary_needed) && $summary_needed eq 'y'}
        {remarksbox type='Warning' title="{tr}Warning{/tr}"}
            <div class="alert alert-warning alert-dismissible"><em class=''>{tr}An edit summary is mandatory {/tr}</em></div>
        {/remarksbox}
    {/if}
    {if $likepages}
        <div>
            {tr}Perhaps you are looking for:{/tr}
            {if $likepages|@count < 0}
                <ul>
                    {section name=back loop=$likepages}
                        <li>
                            <a href="{$likepages[back]|sefurl}" class="wiki">{$likepages[back]|escape}</a>
                        </li>
                    {/section}
                </ul>
            {else}
                <div class="table-responsive">
                    <table class="table">
                        <tr>
                            {cycle name=table values=',,,,</tr><tr>' print=false advance=false}
                            {section name=back loop=$likepages}
                                <td><a href="{$likepages[back]|sefurl}" class="wiki">{$likepages[back]|escape}</a></td>
                                {cycle name=table}
                            {/section}
                        </tr>
                    </table>
                </div>
            {/if}
        </div>
    {/if}
    {if $preview or $prefs.ajax_autosave eq "y"}
        {include file='tiki-preview.tpl'}
    {/if}
    {if isset($diff_style)}
        <div id="diff_outer">
            {if $translation_mode == 'y'}
                <div class="translation_message">
                    <h2>{icon name="language"} {tr}Translate from:{/tr} {$source_page|escape}</h2>
                    {tr}Changes that need to be translated are highlighted below.{/tr}
                </div>
            {/if}
            <div id="diff_history">
                {include file='pagehistory.tpl' cant=0}
                {if $diff_summaries}
                    <div class="wikitext" id="diff_versions">
                        <ul>
                            {foreach item=diff from=$diff_summaries}
                                <li>
                                    {tr}Version:{/tr} {$diff.version|escape} - {$diff.comment|escape|default:"<em>{tr}No comment{/tr}</em>"}
                                    {if count($diff_summaries) gt 1}
                                        {assign var=diff_version value=$diff.version}
                                        {icon name="next" onclick="\$('input[name=oldver]').val($diff_version);\$('#editpageform').submit();return false;" title=":{tr}View{/tr}" style="cursor: pointer"}
                                    {/if}
                                </li>
                            {/foreach}
                            {button _onclick="\$('input[name=oldver]').val(1);\$('#editpageform').submit();return false;" _text="{tr}All Versions{/tr}" _ajax="n"}
                        </ul>
                    </div>
                {/if}
            </div>
        </div>
    {/if}
    {if $prompt_for_edit_or_translate == 'y'}
        {include file='tiki-edit-page-include-prompt_for_edit_or_translate.tpl'}
    {/if}
    <form enctype="multipart/form-data" method="post" action="tiki-editpage.php?page={$page|escape:'url'}" {*class="form-horizontal"*} id='editpageform' name='editpageform'>
        <input type="hidden" name="no_bl" value="y">
        {if !empty($smarty.request.returnto)}<input type="hidden" name="returnto" value="{$smarty.request.returnto}">{/if}
        {if isset($diff_style)}
            <select name="diff_style" class="wikiaction"title="{tr}Edit wiki page{/tr}|{tr}Select the style used to display differences to be translated.{/tr}">
                <option value="htmldiff"{if isset($diff_style) && $diff_style eq "htmldiff"} selected="selected"{/if}>{tr}html{/tr}</option>
                <option value="inlinediff"{if isset($diff_style) && $diff_style eq "inlinediff"} selected="selected"{/if} >{tr}text{/tr}</option>
                <option value="inlinediff-full"{if isset($diff_style) && $diff_style eq "inlinediff-full"} selected="selected"{/if} >{tr}text full{/tr}</option>
            </select>
            <input type="submit" class="wikiaction tips btn btn-primary" title="{tr}Edit wiki page{/tr}|{tr}Change the style used to display differences to be translated.{/tr}" name="preview" value="{tr}Change diff styles{/tr}" onclick="needToConfirm=false;">
        {/if}
        {if $page_ref_id}<input type="hidden" name="page_ref_id" value="{$page_ref_id}">{/if}
        {if isset($hdr)}<input type="hidden" name="hdr" value="{$hdr}">{/if}
        {if isset($cell)}<input type="hidden" name="cell" value="{$cell}">{/if}
        {if isset($pos)}<input type="hidden" name="pos" value="{$pos}">{/if}
        {if $current_page_id}<input type="hidden" name="current_page_id" value="{$current_page_id}">{/if}
        {if $add_child}<input type="hidden" name="add_child" value="true">{/if}
        {if $preview or $prefs.wiki_actions_bar eq 'top' or $prefs.wiki_actions_bar eq 'both'}
            <div class='top_actions'>
                {include file='wiki_edit_actions.tpl' wysiwyg=$wysiwyg}
            </div>
        {/if}
        <div class="mb-3 mx-0">
            {if isset($page_badchars_display)}
                {if $prefs.wiki_badchar_prevent eq 'y'}
                    {remarksbox type=errors title="{tr}Invalid page name{/tr}"}
                        {tr _0=$page_badchars_display|escape}The page name specified contains unallowed characters. It will not be possible to save the page until those are removed: <strong>%0</strong>{/tr}
                    {/remarksbox}
                {else}
                    {remarksbox type=tip title="{tr}Tip{/tr}"}
                        {tr _0=$page_badchars_display|escape}The page name specified contains characters that may render the page hard to access. You may want to consider removing those: <strong>%0</strong>{/tr}
                    {/remarksbox}
                {/if}
                    <p>{tr}Page name:{/tr} <input type="text" name="page" value="{$page|escape}">
                        <input type="submit" class="btn btn-primary btn-sm" name="rename" value="{tr}Rename{/tr}">
                    </p>
            {else}
                <input type="hidden" name="page" value="{$page|escape}">
                {* the above hidden field is needed for auto-save to work *}
            {/if}
            {tabset name='tabs_editpage' cookietab=1}
                {tab name="{tr}Edit page{/tr}"}
                    {if $translation_mode == 'y'}
                        <div class="translation_message">
                            <h2>{tr}Translate to:{/tr} {$target_page|escape}</h2>
                            {tr}Reproduce the changes highlighted on the left using the editor below{/tr}.
                        </div>
                    {/if}
                    {textarea codemirror='true'}{$pagedata}{/textarea}
                        {if $prefs.wiki_freetags_edit_position eq 'edit'}
                            {if $prefs.feature_freetags eq 'y' and $tiki_p_freetags_tag eq 'y'}
                                <fieldset>
                                    <legend>{tr}Tags{/tr}</legend>
                                    <table>
                                        {include file='freetag.tpl'}
                                    </table>
                                </fieldset>
                            {/if}
                    {/if}
                    {if $page|lower neq 'sandbox'}
                        <fieldset class="edit-zone-footer my-3">
                            <label for="comment">{tr}Describe the change you made{/tr} {help url='Editing-Wiki-Pages' desc="{tr}Edit comment: Enter some text to describe the changes you are currently making{/tr}"}</label>
                            <input class="form-control wikiedit" type="text" id="comment" name="comment" value="{$commentdata|escape}" maxlength="255">
                            {if isset($show_watch) && $show_watch eq 'y'}
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="watch" name="watch" value="1"{if $watch_checked eq 'y'} checked="checked"{/if}>
                                    <label class="form-check-label" for="watch">{tr}Monitor this page{/tr}</label>
                                </div>
                            {/if}
                        </fieldset>
                        {if $prefs.feature_contribution eq 'y'}
                            <fieldset>
                                <legend>{tr}Contributions{/tr}</legend>
                                {include file='contribution.tpl'}
                            </fieldset>
                        {/if}
                        {if (!isset($wysiwyg) || $wysiwyg neq 'y') and $prefs.feature_wiki_pictures eq 'y' and $tiki_p_upload_picture eq 'y' and $prefs.feature_filegals_manager neq 'y'}
                            <fieldset>
                                <legend>{tr}Upload picture{/tr}</legend>
                                <input type="hidden" name="MAX_FILE_SIZE" value="1000000000">
                                <input type="hidden" name="hasAlreadyInserted" value="">
                                <input type="hidden" name="prefix" value="/img/wiki_up/{if $tikidomain}{$tikidomain}/{/if}">
                                <input name="picfile1" type="file" class="form-control" onchange="javascript:insertImgFile('editwiki','picfile1','hasAlreadyInserted','img')">
                                <div id="new_img_form"></div>
                                <a href="javascript:addImgForm()" onclick="needToConfirm = false;">{tr}Add another image{/tr}</a>
                            </fieldset>
                        {/if}
                    {/if}
                {/tab}
                {if $prefs.feature_categories eq 'y' and $tiki_p_modify_object_categories eq 'y' and count($categories) gt 0}
                    {tab name="{tr}Categories{/tr}"}
                        {if $categIds}
                            {remarksbox type="note" title="{tr}Note:{/tr}"}
                                <strong>{tr}Categorization has been preset for this edit{/tr}</strong>
                            {/remarksbox}
                            {section name=o loop=$categIds}
                                <input type="hidden" name="cat_categories[]" value="{$categIds[o]}">
                            {/section}
                            <input type="hidden" name="cat_categorize" value="on">
                            {if $prefs.feature_wiki_categorize_structure eq 'y'}
                                {tr}Categories will be inherited from the structure top page{/tr}
                            {/if}
                        {else}
                            {if $page|lower ne 'sandbox'}
                                {include file='categorize.tpl' notable='y'}
                            {/if}{* sandbox *}
                        {/if}
                    {/tab}
                {/if}
                {if $prefs.wiki_freetags_edit_position eq 'freetagstab'}
                    {if $prefs.feature_freetags eq 'y' and $tiki_p_freetags_tag eq 'y'}
                        {tab name="{tr}Tags{/tr}"}
                            {include file='freetag.tpl'}
                        {/tab}
                    {/if}
                {/if}
                {if !empty($showPropertiesTab)}
                    {tab name="{tr}Properties{/tr}"}
                        <div class="t_navbar mb-4 clearfix">
                            {if $tiki_p_admin_wiki eq "y"}
                                <a href="tiki-admin.php?page=wiki" class="btn btn-link" title="{tr}Leave page and go to....{/tr}">
                                    {icon name="cog"} {tr}Wiki Preferences{/tr}
                                </a>
                            {/if}
                            {if $tiki_p_admin_wiki eq 'y' or $tiki_p_assign_perm_wiki_page eq 'y'}
                                {permission_link mode=link type="wiki page" id=$page permType=wiki title=$page}
                            {/if}
                            {if isset($tiki_p_edit_content_templates) and tiki_p_edit_content_templates eq 'y'}
                                <a href="tiki-admin_content_templates.php" class="btn btn-link"  title="{tr}Leave page and go to....{/tr}" onclick="needToConfirm = true;">
                                    {icon name="content-template"} {tr}Content Templates{/tr}
                                </a>
                            {/if}
                            {if isset($tiki_p_edit_structures) and $tiki_p_edit_structures eq 'y'}
                                <a href="tiki-admin_structures.php" class="btn btn-link" title="{tr}Leave page and go to....{/tr}">
                                    {icon name="structure"} {tr}Structures{/tr}
                                </a>
                            {/if}
                            {if $prefs.feature_copyright eq 'y' and $prefs.wiki_feature_copyrights eq 'y'}
                                {if !empty($copyrights)}
                                    <a href="copyrights.php?page={$page|escape}" class="btn btn-link" title="{tr}Leave page and go to....{/tr}">{icon name="copyright"} {tr}Copyright notices{/tr}</a>
                                {/if}
                                <a href="{$prefs.wikiLicensePage|sefurl}" class="btn btn-link" title="{tr}Leave page and go to....{/tr}">{icon name="wiki"} {tr}Copyright page{/tr}</a>
                            {/if}
                        </div>
                        {if $prefs.feature_wiki_templates eq 'y' and $tiki_p_use_content_templates eq 'y'}
                            <div class="mb-3 row">
                                <label for="templateId" class="col-md-4 col-form-label">{tr}Apply content template{/tr}</label>
                                <div class="col-md-8">
                                    <select class="form-select" id="templateId" name="templateId" onchange="needToConfirm=false;$('#editpageform').submit();">
                                        <option value="0">{tr}none{/tr}</option>
                                            {section name=ix loop=$templates}
                                                <option value="{$templates[ix].templateId|escape}" {if $templateId eq $templates[ix].templateId}selected="selected"{/if}>{tr}{$templates[ix].name|escape}{/tr}</option>
                                            {/section}
                                    </select>
                                </div>
                            </div>
                        {/if}
                        {if $prefs.feature_wiki_usrlock eq 'y' && ($tiki_p_lock eq 'y' || $tiki_p_admin_wiki eq 'y')}
                            <div class="mb-3 row">
                                <label for="lock_it" class="col-md-4 col-form-label">{tr}Lock this page{/tr}</label>
                                <div class="col-md-8">
                                    <input type="checkbox" id="lock_it" name="lock_it" {if $lock_it eq 'y'}checked="checked"{/if}>
                                </div>
                            </div>
                        {/if}
                        {if $prefs.wiki_comments_allow_per_page neq 'n'}
                            <div class="mb-3 row">
                                <label for="comments_enabled" class="col-md-4 col-form-label">{tr}Allow comments on this page{/tr}</label>
                                <div class="col-md-8">
                                    <input type="checkbox" id="comments_enabled" name="comments_enabled" {if $comments_enabled eq 'y'}checked="checked"{/if}>
                                </div>
                            </div>
                        {/if}
                        {if $prefs.feature_wiki_allowhtml eq 'y' and $tiki_p_use_HTML eq 'y' and ($wysiwyg neq 'y' or $prefs.wysiwyg_htmltowiki eq 'y')}
                            <div class="mb-3 row">
                                <label class="col-md-4 col-form-label" for="allowhtml">{tr}Allow HTML{/tr}</label>
                                <div class="col-md-8">
                                    <input type="checkbox" name="allowhtml" {if $allowhtml eq 'y'}checked="checked"{/if}>
                                    <span class="form-text">
                                        {tr}HTML tags are used to create elements of the wiki page, instead of being displayed as code.{/tr}
                                    </span>
                                </div>
                            </div>
                            {if $prefs.ajax_autosave eq "y"}
                                {jq}
    $("input[name=allowhtml]").change(function() {
    auto_save( "editwiki", autoSaveId );
    });
                                {/jq}
                            {/if}
                        {else}
                            <input type="hidden" name="allowhtml" value="{if $allowhtml eq 'y'}on{/if}">
                        {/if}
                        {if $prefs.feature_wiki_import_html eq 'y'}
                            <div class="mb-3 row">
                                <label for="suck_url" class="col-md-4 col-form-label">{tr}Import HTML{/tr}</label>
                                <div class="col-md-8 d-flex flex-row flex-wrap align-items-center">
                                    <input class="form-control wikiedit mr-2" type="text" id="suck_url" name="suck_url" value="{$suck_url|escape}">
                                    <input type="submit" class="wikiaction btn btn-primary mr-2" name="do_suck" value="{tr}Import{/tr}" onclick="needToConfirm=false;">
                                    <label><input type="checkbox" name="parsehtml" {if $parsehtml eq 'y'}checked="checked"{/if}>&nbsp;
                                    {tr}Try to convert HTML to wiki{/tr}</label>
                                </div>
                            </div>
                        {/if}
                        {if $prefs.feature_wiki_import_page eq 'y'}
                            <div class="mb-3 row clearfix">
                                <label for="userfile1" class="col-md-4 col-form-label">{tr}Import page{/tr}</label>
                                <div class="col-md-8 d-flex flex-row flex-wrap align-items-center">
                                    <input type="hidden" name="MAX_FILE_SIZE" value="1000000000">
                                    <input class="form-control mr-2" id="userfile1" name="userfile1" type="file">
                                    <input type="submit" class="wikiaction btn btn-primary" name="attach" value="{tr}Import{/tr}" onclick="javascript:needToConfirm=false;insertImgFile('editwiki','userfile2','hasAlreadyInserted2','file', 'page2', 'attach_comment'); return true;">
                                </div>
                            </div>
                        {/if}
                        {if $prefs.feature_wiki_export eq 'y' and $tiki_p_export_wiki eq 'y'}
                            <div class="mb-3 row">
                                <label for="" class="col-md-4 col-form-label">{tr}Export page{/tr}</label>
                                <div class="col-md-8">
                                    <a href="tiki-export_wiki_pages.php?page={$page|escape:"url"}&amp;all=1" class="btn btn-primary">{icon name="export"} {tr}Export all versions{/tr}</a>
                                </div>
                            </div>
                        {/if}
                        {if !isset($wysiwyg) || $wysiwyg neq 'y'}
                            {if $prefs.feature_wiki_attachments == 'y' and ($tiki_p_wiki_attach_files eq 'y' or $tiki_p_wiki_admin_attachments eq 'y')}
                                <input type="hidden" name="MAX_FILE_SIZE" value="1000000000">
                                <input type="hidden" name="hasAlreadyInserted2" value="">
                                <input type="hidden" id="page2" name="page2" value="{$page}">
                                <div class="mb-3 row align-items-left">
                                    <label for="attach-upload" class="col-sm-4 col-form-label">{tr}Attach file{/tr}</label>
                                    <div class="col-sm-8 mb-2">
                                        <input name="userfile2" type="file" id="attach-upload" class="form-control">
                                    </div>
                                    <div class="col-sm-8 offset-sm-4 d-flex flex-row flex-wrap align-items-center">
                                        <input type="text" name="attach_comment" class="col-sm-10 form-control mr-2" maxlength="250" id="attach-comment" placeholder="{tr}Comment{/tr}">
                                        <input type="submit" class="wikiaction btn btn-primary" name="attach" value="{tr}Attach{/tr}" onclick="javascript:needToConfirm=false;insertImgFile('editwiki','userfile2','hasAlreadyInserted2','file', 'page2', 'attach_comment'); return true;">
                                    </div>
                                </div>
                            {/if}
                        {/if}
                        {* merged tool and property tabs for tiki 6 *}
                        {if $page|lower neq 'sandbox'}
                            {if $prefs.wikiplugin_addreference eq 'y' && $showBiblioSection}
                                <div class="mb-3 row">
                                    <label for="" class="col-md-4 col-form-label">{tr}Bibliography{/tr}</label>
                                    <div class="col-md-8">
                                        {include file='addreference.tpl'}
                                    </div>
                                </div>
                            {/if}
                            {if $prefs.wiki_freetags_edit_position eq 'properties' or $prefs.wiki_freetags_edit_position eq ''}
                                {if $prefs.feature_freetags eq 'y' and $tiki_p_freetags_tag eq 'y'}
                                    {include file='freetag.tpl'}
                                {/if}
                            {/if}
                            {if $prefs.feature_wiki_icache eq 'y'}
                                <div class="mb-3 row">
                                    <label for="wiki_cache" class="col-form-label col-md-4">{tr}Cache{/tr}</label>
                                    <div class="col-md-8">
                                        <select id="wiki_cache" name="wiki_cache" class="form-control">
                                            <option value="0" {if $prefs.wiki_cache eq 0}selected="selected"{/if}>0 ({tr}no cache{/tr})</option>
                                            <option value="60" {if $prefs.wiki_cache eq 60}selected="selected"{/if}>1 {tr}minute{/tr}</option>
                                            <option value="300" {if $prefs.wiki_cache eq 300}selected="selected"{/if}>5 {tr}minutes{/tr}</option>
                                            <option value="600" {if $prefs.wiki_cache eq 600}selected="selected"{/if}>10 {tr}minute{/tr}</option>
                                            <option value="900" {if $prefs.wiki_cache eq 900}selected="selected"{/if}>15 {tr}minutes{/tr}</option>
                                            <option value="1800" {if $prefs.wiki_cache eq 1800}selected="selected"{/if}>30 {tr}minute{/tr}</option>
                                            <option value="3600" {if $prefs.wiki_cache eq 3600}selected="selected"{/if}>1 {tr}hour{/tr}</option>
                                            <option value="7200" {if $prefs.wiki_cache eq 7200}selected="selected"{/if}>2 {tr}hours{/tr}</option>
                                        </select>
                                        {if $prefs.wiki_cache == 0}
                                            {remarksbox type="warning" title="{tr}Warning{/tr}" close="n"}{tr}Only cache a page if it should look the same to all groups authorized to see it.{/tr}{/remarksbox}
                                        {/if}
                                    </div>
                                </div>
                            {/if}
                            {if $prefs.feature_wiki_structure eq 'y' && $showstructs|@count gt 0}
                                <div class="mb-3 row">
                                    <label class="col-md-4 col-form-label">{tr}Structures{/tr}</label>
                                    <div class="col-md-8" id="showstructs">
                                        <ul>
                                            {foreach from=$showstructs item=page_info}
                                                <li>{$page_info.pageName}{if !empty(${$page_info.outputType}.page_alias)}({$page_info.page_alias}){/if}</li>
                                            {/foreach}
                                        </ul>
                                    </div>
                                </div>
                            {/if}
                            {if $prefs.feature_copyright eq 'y' and $prefs.wiki_feature_copyrights eq 'y'}
                                <div class="mb-3 row clearfix">
                                    <label for="" class="col-md-4 col-form-label">{tr}Copyright{/tr}</label>
                                    <div class="col-md-8">
                                        <div class="mb-3 row">
                                            <label class="col-md-4 col-form-label" for="copyrightTitle">{tr}Title{/tr}</label>
                                            <div class="col-md-8">
                                                <input class="form-control wikiedit" type="text" id="copyrightTitle" name="copyrightTitle" value="{$copyrightTitle|escape}">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-md-4 col-form-label" for="copyrightYear">{tr}Year{/tr}</label>
                                            <div class="col-md-8">
                                                <input size="4" class="form-control wikiedit" type="text" id="copyrightYear" name="copyrightYear" value="{$copyrightYear|escape}">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-md-4 col-form-label" for="copyrightAuthors">{tr}Authors{/tr}</label>
                                            <div class="col-md-8">
                                                <input class="form-control wikiedit" id="copyrightAuthors" name="copyrightAuthors" type="text" value="{$copyrightAuthors|escape}">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-md-4 col-form-label" for="copyrightHolder">{tr}Copyright Holder{/tr}</label>
                                            <div class="col-md-8">
                                                <input class="form-control wikiedit" id="copyrightHolder" name="copyrightHolder" type="text" value="{$copyrightHolder|escape}">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            {if $prefs.wikiSubmitNotice neq ""}
                                                {remarksbox type="note" title="{tr}Important:{/tr}"}
                                                    {tr}{$prefs.wikiSubmitNotice}{/tr}
                                                {/remarksbox}
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                            {/if}
                            {if $tiki_p_admin_wiki eq 'y' && $prefs.wiki_authors_style_by_page eq 'y'}
                                <div class="mb-3 row">
                                    <label class="col-md-4 col-form-label">{tr}Authors{/tr}</label>
                                    <div class="col-md-8">
                                        <select name="wiki_authors_style" id="wiki_authors_style" class="form-control">
                                            <option value="" style="font-style:italic;border-bottom:1px dashed #666;"{if $wiki_authors_style eq ''} selected="selected"{/if}>{tr}Site default{/tr}</option>
                                            <option value="classic"{if $wiki_authors_style eq 'classic'} selected="selected"{/if}>{tr}as Creator &amp; Last Editor{/tr}</option>
                                            <option value="business"{if $wiki_authors_style eq 'business'} selected="selected"{/if}>{tr}Business style{/tr}</option>
                                            <option value="collaborative"{if $wiki_authors_style eq 'collaborative'} selected="selected"{/if}>{tr}Collaborative style{/tr}</option>
                                            <option value="lastmodif"{if $wiki_authors_style eq 'lastmodif'} selected="selected"{/if}>{tr}Page last modified on{/tr}</option>
                                            <option value="none"{if $wiki_authors_style eq 'none'} selected="selected"{/if}>{tr}no (disabled){/tr}</option>
                                        </select>
                                    </div>
                                </div>
                            {/if}
                        {/if}{*end if sandbox *}
                        {if $prefs.feature_wiki_description eq 'y' or $prefs.metatag_pagedesc eq 'y'}
                            <div class="mb-3 row">
                                {if $prefs.metatag_pagedesc eq 'y'}
                                    <label for="" class="col-md-4 col-form-label">{tr}Description (used for metatags){/tr}</label>
                                {else}
                                    <label for="" class="col-md-4 col-form-label">{tr}Description{/tr}</label>
                                {/if}
                                <div class="col-md-8">
                                    <input class="form-control" type="text" id="description" name="description" maxlength="200" value="{$description|escape}">
                                    {jq}
                                        $("#description").keyup(function () {
                                            var length = $(this).val().length;
                                            if(length == 200) {
                                                alert("You have reached the number of characters allowed (200 max) for the description field");
                                            }
                                        });
                                    {/jq}
                                </div>
                            </div>
                        {/if}
                        {if $prefs.feature_wiki_footnotes eq 'y'}
                            {if $user}
                                <div class="mb-3 row">
                                    <label for="footnote" class="col-md-4 col-form-label">{tr}My Footnotes{/tr}</label>
                                    <div class="col-md-8">
                                        <textarea id="footnote" name="footnote" class="form-control" rows="8">{$footnote|escape}</textarea>
                                    </div>
                                </div>
                            {/if}
                        {/if}
                        {if $prefs.feature_wiki_ratings eq 'y' and $tiki_p_wiki_admin_ratings eq 'y'}
                            <div class="mb-3 row">
                                <label for="" class="col-md-4 col-form-label">{tr}Rating{/tr}</label>
                                <div class="col-md-8">
                                    {foreach from=$poll_rated item=rating}
                                        <div>
                                            <a href="tiki-admin_poll_options.php?pollId={$rating.info.pollId}">{$rating.info.title}</a>
                                            {assign var=thispage value=$page|escape:"url"}
                                            {assign var=thispoll_rated value=$rating.info.pollId}
                                            {button href="?page=$thispage&amp;removepoll=$thispoll_rated" _text="{tr}Disable{/tr}"}
                                        </div>
                                    {/foreach}
                                    {if $tiki_p_admin_poll eq 'y'}
                                        {button href="tiki-admin_polls.php" _text="{tr}Admin Polls{/tr}"}
                                    {/if}
                                    {if $poll_rated|@count <= 1 or $prefs.poll_multiple_per_object eq 'y'}
                                        <div class="row mb-0">
                                            {if count($polls_templates)}
                                                <label class="col-sm-2 col-form-label">{tr}Type{/tr}</label>
                                                <div class="col-sm-10 mb-2">
                                                    <select name="poll_template" class="form-control">
                                                        <option value="0">{tr}none{/tr}</option>
                                                        {foreach item=template from=$polls_templates}
                                                            <option value="{$template.pollId|escape}"{if $template.pollId eq $poll_template} selected="selected"{/if}>{tr}{$template.title|escape}{/tr}</option>
                                                        {/foreach}
                                                    </select>
                                                </div>
                                                <label class="col-sm-2 col-form-label">{tr}Title{/tr}</label>
                                                <div class="col-sm-10 mb-2">
                                                    <input type="text" name="poll_title" class="form-control">
                                                </div>
                                            {else}
                                                <div class="col-sm-12">
                                                {remarksbox type="info" title="{tr}Information{/tr}" close="n"}
                                                    {tr}There is no available poll template.{/tr}
                                                    {if $tiki_p_admin_polls ne 'y'}
                                                        {tr}Please ask an administrator to create one.{/tr}
                                                    {/if}
                                                {/remarksbox}
                                                </div>
                                            {/if}
                                        </div>
                                    {/if}
                                </div>
                            </div>
                        {/if}
                        {if $prefs.feature_multilingual eq 'y'}
                            <fieldset>
                                <div class="mb-3 row clearfix">
                                    <label for="" class="col-md-4 col-form-label">{tr}Language{/tr}</label>
                                    <div class="col-md-8">
                                        <select name="lang" id="lang" class="form-control mb-3">
                                            <option value=""{if empty($lang)} selected="selected"{/if}>{tr}Unknown{/tr}</option>
                                            {section name=ix loop=$languages}
                                                <option value="{$languages[ix].value|escape}"{if $lang eq $languages[ix].value} selected="selected"{/if}>{$languages[ix].name}</option>
                                            {/section}
                                        </select>
                                        <span class="form-text">
                                            {tr _0="tiki-edit_translation.php?no_bl=y&amp;page={$page|escape:url}"}To translate, do not change the language and the content. Instead, <a class="alert-link" href="%0">create a new translation</a> in the new language.{/tr}
                                            {if $translationOf}
                                                <input type="hidden" name="translationOf" value="{$translationOf|escape}">
                                            {/if}
                                        </span>
                                    </div>
                                </div>
                            </fieldset>
                            {if $trads|@count > 1 and $urgent_allowed}
                                <div class="mb-3 row">
                                    <label for="" class="col-md-4 col-form-label">{tr}Translation{/tr}</label>
                                    <div class="col-md-8">
                                        <fieldset {if $prefs.feature_urgent_translation neq 'y' or $diff_style} style="display:none;"{/if}>
                                            <legend>{tr}Translation request:{/tr}</legend>
                                            <input type="hidden" name="lang" value="{$lang|escape}">
                                            <input type="checkbox" id="translation_critical" name="translation_critical" id="translation_critical"{if $translation_critical} checked="checked"{/if}>
                                            <label for="translation_critical">{tr}Send urgent translation request.{/tr}</label>
                                            {if $diff_style}
                                                <input type="hidden" name="oldver" value="{$diff_oldver|escape}">
                                                <input type="hidden" name="newver" value="{$diff_newver|escape}">
                                            {/if}
                                        </fieldset>
                                    </div>
                                </div>
                            {/if}
                        {/if}
                        {if $prefs.geo_locate_wiki eq 'y'}
                            {$headerlib->add_map()}
                            <div class="mb-3 row">
                                <label for="" class="col-md-4 col-form-label">{tr}Geolocation{/tr}</label>
                                <div class="col-md-8">
                                    <div class="map-container form-control" data-geo-center="{defaultmapcenter}" data-target-field="geolocation" style="height: 250px;"></div>
                                    <input type="hidden" name="geolocation" value="{$geolocation_string}">
                                </div>
                            </div>
                        {/if}
                        {* check if wiki_auto_toc is set - but don't understand why wiki_page_hide_title is checked - also the logic made into an 'and' since the previous 'or' made no sense *}
                        {if $prefs.wiki_auto_toc eq 'y' and $prefs.wiki_page_hide_title eq 'y'}
                            <div class="mb-3 row clearfix">
                                <label for="pageAutoToc" class="col-md-4 col-form-label">{tr}Show automatic table of contents{/tr}</label>
                                <div class="col-md-8">
                                    <select name="pageAutoToc" class="form-control">
                                        <option value="0" {if $pageAutoToc == 0}selected{/if}>{tr}Default{/tr} ({if $prefs.wiki_toc_default eq 'off'}{tr}No{/tr}{else}{tr}Yes{/tr}{/if})</option>
                                        <option value="1" {if $pageAutoToc == 1}selected{/if}>{tr}Yes{/tr}</option>
                                        <option value="-1" {if $pageAutoToc == -1}selected{/if}>{tr}No{/tr}</option>
                                    </select>
                                </div>
                            </div>
                        {/if}
                        {if $prefs.wiki_page_hide_title eq 'y' && ($prefs.wiki_page_name_above eq 'y' or $prefs.feature_page_title eq 'y' or $prefs.wiki_page_name_inside eq 'y')}
                            <div class="mb-3 row clearfix">
                                <label for="page_hide_title" class="col-md-4 col-form-label">{tr}Show page title{/tr}</label>
                                <div class="col-md-8">
                                    <select name="page_hide_title" class="form-control">
                                        <option value="0" {if $page_hide_title == 0}selected{/if}>{tr}Default{/tr}</option>
                                        {*<option value="1" {if $page_hide_title == 1}selected{/if}>{tr}On_C(on-off){/tr}</option>*}
                                        <option value="-1" {if $page_hide_title == -1}selected{/if}>{tr}Off_C(on-off){/tr}</option>
                                    </select>
                                </div>
                            </div>
                        {/if}
                        {if $prefs.namespace_enabled eq 'y'}
                            <div class="mb-3 row clearfix">
                                <label for="explicit_namespace" class="col-md-4 col-form-label">{tr}Namespace{/tr}</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" name="explicit_namespace" value="{$explicit_namespace|escape}" placeholder="{tr}Explicit Namespace{/tr}">
                                    <span class="form-text">
                                        {tr}The namespace for a page is guessed automatically from the page name. However, some exceptions may arise. This option allows to override the namespace.{/tr}
                                    </span>
                                </div>
                            </div>
                        {/if}
                        {if $prefs.site_layout_per_object eq 'y'}
                            <fieldset>
                                <div class="mb-3 row">
                                    <label for="object_layout" class="col-md-4 col-form-label">{tr}Page layout{/tr}</label>
                                    <div class="col-md-8">
                                        <select name="object_layout" class="form-control">
                                            <option value="">{tr}Site Default{/tr}</option>
                                            {foreach $object_layout.available as $key => $label}
                                                <option value="{$key|escape}"{if $object_layout.current eq $key} selected{/if}>{$label|escape}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                            </fieldset>
                        {/if}
                        {if $prefs.object_maintainers_enable eq 'y'}
                            <fieldset>
                                <div class="mb-3 row">
                                    <label for="object_layout" class="col-md-4 col-form-label">{tr}Page Maintainers{/tr}</label>
                                    <div class="col-sm-8 mb-2">
                                        {object_selector_multi _simplename=maintainers _simplevalue=$object_maintainers type=user _threshold=-1 _class="user-selector" _separator=";" _placeholder="{tr}Name{/tr}"}
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="object_layout" class="col-md-4 col-form-label">{tr}Update Frequency{/tr}</label>
                                    <div class="col-sm-8 mb-2">
                                        <div class="input-group">
                                            <input type="text" name="update_frequency" class="col-sm-10 form-control mr-2" maxlength="4" id="update_frequency" value="{$update_frequency}" placeholder="{tr}Days{/tr}">
                                            <span class="input-group-text">{tr}days{/tr}</span>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        {/if}
                    {/tab}{* end properties tab *}
                {else}
                    {if $wysiwyg eq 'y'}{* include hidden allowhtml for wysiwyg if the properties tab isn't needed *}
                        <input type="hidden" name="allowhtml" value="{if $allowhtml eq 'y'}on{/if}">
                    {/if}
                {/if}
            {/tabset}
        </div>
        <div class="row d-flex justify-content-center">
            {if $page|lower ne 'sandbox'}
                {if $prefs.feature_antibot eq 'y' && (isset($anon_user) && $anon_user eq 'y')}
                    {include file='antibot.tpl' tr_style="formcolor"}
                {/if}
            {/if}{* sandbox *}
            {if $prefs.wiki_actions_bar neq 'top'}
                {include file='wiki_edit_actions.tpl' wysiwyg=$wysiwyg page_info=$page_info}
            {/if}
        </div>
    </form>
    {include file='tiki-page_bar.tpl'}
{/block}