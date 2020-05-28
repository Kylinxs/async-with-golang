
{* $Id$ *}
{if !$ts.ajax}
    {block name=title}
        {title help="forums" admpage="forums" url=$forum_info.forumId|sefurl:'forum'}{$forum_info.name}{/title}
    {/block}

    {if $forum_info.show_description eq 'y'}
        <div class="description form-text">{wiki}{$forum_info.description}{/wiki}</div>
    {/if}

    <div class="t_navbar mb-4">
        {assign var=thisforum_info value=$forum_info.forumId}
        {if ($tiki_p_forum_post_topic eq 'y' and ($prefs.feature_wiki_discuss ne 'y' or $prefs.$forumId ne $prefs.wiki_forum_id)) or $tiki_p_admin_forum eq 'y'}
            {if !isset($comments_threadId) or $comments_threadId eq 0}
                {button href="tiki-view_forum.php?openpost=1&amp;forumId=$thisforum_info&amp;comments_threadId=0&amp;comments_threshold=$comments_threshold&amp;comments_offset=$comments_offset&amp;thread_sort_mode=$thread_sort_mode&amp;comments_per_page=$comments_per_page" _onclick='$("#forumpost").show();return false;' _icon_name="create" _type="primary" class="btn btn-primary" _text="{tr}New Topic{/tr}"}
            {else}
                {button href="tiki-view_forum.php?openpost=1&amp;forumId=$thisforum_info&amp;comments_threadId=0&amp;comments_threshold=$comments_threshold&amp;comments_offset=$comments_offset&amp;thread_sort_mode=$thread_sort_mode&amp;comments_per_page=$comments_per_page" _onclick='$("#forumpost").show();return false;' _icon_name="create" _type="link" class="btn btn-link" _text="{tr}New Topic{/tr}"}
            {/if}
        {/if}
        {if $tiki_p_admin_forum eq 'y'}
            {button href="tiki-admin_forums.php?parentId=$thisforum_info&amp;cookietab=2#content_admin_forums1-2" _icon_name="create" _type="link" class="btn btn-link" _text="{tr}Add Sub Forum{/tr}"}
            {button href="tiki-admin_forums.php?forumId=$thisforum_info&amp;cookietab=2#content_admin_forums1-2" _icon_name="edit" _type="link" class="btn btn-link" _text="{tr}Edit Forum{/tr}"}
            {button href="tiki-admin_forums.php?parentId=$thisforum_info" _icon_name="cog" _type="link" class="btn btn-link" _text="{tr}Admin{/tr}"}
        {/if}
        {if $tiki_p_admin_forum eq 'y' or !isset($all_forums) or $all_forums|@count > 1}
            {* No need for users to go to forum list if they are already looking at the only forum BUT note that all_forums only defined with quickjump feature *}
            {button href="tiki-forums.php" _icon_name="list" _type="link" class="btn btn-link" _text="{tr}Forum List{/tr}"}
        {/if}

        <div class="btn-group float-sm-end">
            {if ! $js}<ul><li>{/if}
            <a class="btn btn-info btn-sm dropdown-toggle" data-bs-toggle="dropdown" data-hover="dropdown" href="#" title="{tr}Forum actions{/tr}">
                {icon name="menu-extra"}
            </a>
            <div class="dropdown-menu dropdown-menu-end">
                <h6 class="dropdown-header">
                    {tr}Forum actions{/tr}
                </h6>
                <div class="dropdown-divider"></div>
                {if $user and $prefs.feature_user_watches eq 'y'}
                    {if $user_watching_forum eq 'n'}
                            <a class="dropdown-item" href="tiki-view_forum.php?forumId={$forumId}&amp;watch_event=forum_post_topic&amp;watch_object={$forumId}&amp;watch_action=add">
                                {icon name="watch"} {tr}Monitor topics{/tr}
                            </a>
                        {else}
                            <a class="dropdown-item" href="tiki-view_forum.php?forumId={$forumId}&amp;watch_event=forum_post_topic&amp;watch_object={$forumId}&amp;watch_action=remove">
                                {icon name="stop-watching"} {tr}Stop monitoring topics{/tr}
                            </a>
                        {/if}
                {/if}
                {if $user and $prefs.feature_user_watches eq 'y'}
                        {if $user_watching_forum_topic_and_thread eq 'n'}
                            <a class="dropdown-item" href="tiki-view_forum.php?forumId={$forumId}&amp;watch_event=forum_post_topic_and_thread&amp;watch_object={$forumId}&amp;watch_action=add">
                                {icon name="watch"} {tr}Monitor topics and threads{/tr}
                            </a>
                        {else}
                            <a class="float-sm-end tips" href="tiki-view_forum.php?forumId={$forumId}&amp;watch_event=forum_post_topic_and_thread&amp;watch_object={$forumId}&amp;watch_action=remove">
                                {icon name="stop-watching"} {tr}Stop monitoring topics and threads{/tr}
                            </a>
                        {/if}
                {/if}
                {if $prefs.feature_group_watches eq 'y' and ( $tiki_p_admin_users eq 'y' or $tiki_p_admin eq 'y' )}
                    <a class="dropdown-item" href="tiki-object_watches.php?objectId={$forumId|escape:"url"}&amp;watch_event=forum_post_topic&amp;objectType=forum&amp;objectName={$forum_info.name|escape:"url"}&amp;objectHref={'tiki-view_forum.php?forumId='|cat:$forumId|escape:"url"}">
                            {icon name="watch-group"} {tr}Group monitor topics{/tr}
                        </a>
                    <a class="dropdown-item" href="tiki-object_watches.php?objectId={$forumId|escape:"url"}&amp;watch_event=forum_post_topic_and_thread&amp;objectType=forum&amp;objectName={$forum_info.name|escape:"url"}&amp;objectHref={'tiki-view_forum.php?forumId='|cat:$forumId|escape:"url"}">
                            {icon name="watch-group"} {tr}Group monitor topics and threads{/tr}
                        </a>
                {/if}
                {if !empty($tiki_p_forum_lock) and $tiki_p_forum_lock eq 'y'}
                    {if $forum_info.is_locked eq 'y'}
                            {self_link lock='n' _icon_name='unlock' _class='dropdown-item' _menu_text='y' _menu_icon='y'}
                                {tr}Unlock{/tr}
                            {/self_link}
                        {else}
                            {self_link lock='y' _icon_name='lock' _class='dropdown-item' _menu_text='y' _menu_icon='y'}
                                {tr}Lock{/tr}
                            {/self_link}
                        {/if}
                {/if}
                {if $prefs.feed_forum eq 'y'}
                        <a class="dropdown-item" href="tiki-forum_rss.php?forumId={$forumId}">
                            {icon name="rss"} {tr}RSS feed{/tr}
                        </a>
                {/if}
                {if $prefs.sefurl_short_url eq 'y'}
                    <a class="dropdown-item" id="short_url_link" href="#" onclick="(function() { $(document.activeElement).attr('href', 'tiki-short_url.php?url=' + encodeURIComponent(window.location.href) + '&title=' + encodeURIComponent(document.title)); })();">
                            {icon name="link"} {tr}Get a short URL{/tr}
                            {assign var="hasPageAction" value="1"}
                        </a>
                {/if}
            </div>
            {if ! $js}</li></ul>{/if}
        </div>
        {if $user and $prefs.feature_user_watches eq 'y' and isset($category_watched) and $category_watched eq 'y'}
            <div class="categbar">
                {tr}Watched by categories:{/tr}
                {section name=i loop=$watching_categories}
                    <a href="tiki-browse_categories.php?parentId={$watching_categories[i].categId}">{$watching_categories[i].name|escape}</a>
                    &nbsp;
                {/section}
            </div>
        {/if}
    </div>
    <div class="breadcrumb">
        <a class="link" href="{if $prefs.feature_sefurl eq 'y'}forums{else}tiki-forums.php{/if}">{tr}Forums{/tr}</a>
        {$prefs.site_crumb_seper}
        {foreach from=$parents item=parent}
            {if isset($parent.name)}
                <a class="link" href="{$parent.forumId|sefurl:'forum'}">{$parent.name|escape}</a>
                {$prefs.site_crumb_seper}
            {/if}
        {/foreach}
        <a class="link" href="{$forumId|sefurl:'forum'}">{$forum_info.name|escape}</a>
    </div>

    {if $tiki_p_forum_post_topic eq 'y'}
        {if $comment_preview eq 'y'}
            <br><br>
            <b>{tr}Preview{/tr}</b>
            <div class="commentscomment">
                <div class="commentheader">
                    <table>
                        <tr>
                            <td>
                                <div class="commentheader">
                                    <span class="commentstitle">{$comments_preview_title|escape}</span>
                                    <br>
                                    {tr}by{/tr} {$user|userlink}
                                </div>
                            </td>
                            <td valign="top" align="right">
                                <div class="commentheader">
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="commenttext">
                    {$comments_preview_data}
                    <br>
                </div>
            </div>
        {/if}


        <div id="forumpost" style="display:{if $comments_threadId > 0 or $openpost eq 'y' or $warning eq 'y' or !empty($comment_title) or !empty($smarty.request.comments_previewComment)}block{else}none{/if};">
            {if $comments_threadId > 0}
                {tr}Editing:{/tr} {$comment_title|escape} (<a class="forumbutlink" href="tiki-view_forum.php?openpost=1&amp;forumId={$forum_info.forumId}&amp;comments_threadId=0&amp;comments_threshold={$comments_threshold}&amp;comments_offset={$comments_offset}&amp;thread_sort_mode={$thread_sort_mode}&amp;comments_per_page={$comments_per_page}">{tr}Post New{/tr}</a>)
            {/if}
            <form method="post" enctype="multipart/form-data" action="tiki-view_forum.php" id="editpageform">
                <input type="hidden" name="comments_offset" value="{$comments_offset|escape}">
                <input type="hidden" name="comments_threadId" value="{$comments_threadId|escape}">
                <input type="hidden" name="comments_threshold" value="{$comments_threshold|escape}">
                <input type="hidden" name="thread_sort_mode" value="{$thread_sort_mode|escape}">
                <input type="hidden" name="forumId" value="{$forumId|escape}">
                <input type="hidden" name="openpost" value="{$openpost|escape}">

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label" for="comments_title">{tr}Title{/tr}</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="comments_title" id="comments_title" value="{$comment_title|escape}">
                        </div>
                    </div>
                    {if $forum_info.forum_use_password ne 'n'}
                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label" for="comment_password">{tr}Password{/tr}</label>
                            <div class="col-sm-10">
                                <input type="password" name="comment_password" id="comment_password" class="form-control">
                            </div>
                        </div>
                    {/if}
                    {if $tiki_p_admin_forum eq 'y'}
                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label" for="comments_topictype">{tr}Type{/tr}</label>
                            <div class="col-sm-10">
                                <select name="comment_topictype" id="comment_topictype" class="form-select comment_topictype">
                                    <option value="n" {if $comment_topictype eq 'n'}selected="selected"{/if}>{tr}Normal{/tr}</option>
                                    <option value="a" {if $comment_topictype eq 'a'}selected="selected"{/if}>{tr}Announce{/tr}</option>
                                    <option value="h" {if $comment_topictype eq 'h'}selected="selected"{/if}>{tr}Hot{/tr}</option>
                                    <option value="s" {if $comment_topictype eq 's'}selected="selected"{/if}>{tr}Sticky{/tr}</option>
                                    <option value="d" {if $comment_topictype eq 'd'}selected="selected"{/if}>{tr}Deliberation{/tr}</option>
                                </select>
                            </div>
                        </div>
                    {/if}
                    {if $forum_info.topic_smileys eq 'y'}
                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label" for="comments_topictype">{tr}Smiley{/tr}</label>
                            <div class="col-sm-10">
                                <select name="comment_topicsmiley" class="form-select comment_topicsmiley">
                                    <option value="" {if $comment_topicsmiley eq ''}selected="selected"{/if}>{tr}no feeling{/tr}</option>
                                    <option value="icon_frown.gif" {if $comment_topicsmiley eq 'icon_frown.gif'}selected="selected"{/if}>{tr}frown{/tr}</option>
                                    <option value="icon_exclaim.gif" {if $comment_topicsmiley eq 'icon_exclaim.gif'}selected="selected"{/if}>{tr}exclaim{/tr}</option>
                                    <option value="icon_idea.gif" {if $comment_topicsmiley eq 'icon_idea.gif'}selected="selected"{/if}>{tr}idea{/tr}</option>
                                    <option value="icon_mad.gif" {if $comment_topicsmiley eq 'icon_mad.gif'}selected="selected"{/if}>{tr}mad{/tr}</option>
                                    <option value="icon_neutral.gif" {if $comment_topicsmiley eq 'icon_neutral.gif'}selected="selected"{/if}>{tr}neutral{/tr}</option>
                                    <option value="icon_question.gif" {if $comment_topicsmiley eq 'icon_question.gif'}selected="selected"{/if}>{tr}question{/tr}</option>
                                    <option value="icon_sad.gif" {if $comment_topicsmiley eq 'icon_sad.gif'}selected="selected"{/if}>{tr}sad{/tr}</option>
                                    <option value="icon_smile.gif" {if $comment_topicsmiley eq 'icon_smile.gif'}selected="selected"{/if}>{tr}happy{/tr}</option>
                                    <option value="icon_wink.gif" {if $comment_topicsmiley eq 'icon_wink.gif'}selected="selected"{/if}>{tr}wink{/tr}</option>
                                </select>
                            </div>
                        </div>
                    {/if}
                    {if $forum_info.topic_summary eq 'y'}
                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">{tr}Summary{/tr}</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="comment_topicsummary" id="comment_topicsummary" value="{$comment_topicsummary|escape}" maxlength="240">
                            </div>
                        </div>
                    {/if}
                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label" for="editpost">{tr}Message{/tr}</label>
                        <div class="col-sm-10">
                            {if $prefs.feature_wysiwyg eq 'y' and $prefs.wysiwyg_htmltowiki eq 'y' and $prefs.feature_forum_parse eq 'y' and ($prefs.wysiwyg_default eq 'y' and not isset($smarty.request.mode_wysiwyg) or $smarty.request.mode_wysiwyg eq 'y')}
                                {$forum_wysiwyg = 'y'}
                            {else}
                                {$forum_wysiwyg = 'n'}
                            {/if}
                            {textarea id="editpost" class="form-control" name="comments_data" codemirror="y" _toolbars=$prefs.feature_forum_parse _wysiwyg=$forum_wysiwyg _preview=$prefs.ajax_edit_previews}{$comment_data}{/textarea}
                        </div>
                    </div>
                    {if ($forum_info.att eq 'att_all') or ($forum_info.att eq 'att_admin' and $tiki_p_admin_forum eq 'y') or ($forum_info.att eq 'att_perm' and $tiki_p_forum_attach eq 'y')}
                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label" for="userfile1">{tr}Attach a file{/tr}</label>
                            <div class="col-sm-10">
                                <input type="hidden" name="MAX_FILE_SIZE" value="{$forum_info.att_max_size|escape}">
                                <input name="userfile1" id="userfile1" class="form-control" type="file">{tr}Maximum size:{/tr} {$forum_info.att_max_size|kbsize}
                            </div>
                        </div>
                    {/if}

                    {if $prefs.feature_contribution eq 'y'}
                        {include file='contribution.tpl'}
                    {/if}
                    <script>
                        function showDeliberationItemRating(me, btn, input, ratings) {
                            btn.find('.deliberationConfigureItemRating').remove();
                            btn.append(me.find('div.deliberationConfigureItemRating[data-val="' + input.val() + '"]').clone());
                        }

                        function configureDeliberationItemRatings(me) {
                            me = $(me);
                            var btn = me.find('.deliberationConfigureItemRatings'),
                                input = btn.next('input.deliberatioRatingOverrideSelector'),
                                dialog = btn.prev('div.deliberationItemRatings').clone(),
                                ratings = dialog.find('.deliberationConfigureItemRating');

                            showDeliberationItemRating(me, btn, input, ratings);

                            btn.click(function() {


                                ratings
                                    .hover(function() {
                                        $(this).addClass('ui-statue-hover');
                                    },function() {
                                        $(this).removeClass('ui-statue-hover');
                                    })
                                    .click(function() {
                                        ratings.removeClass('ui-state-highlight');
                                            $(this).addClass('ui-state-highlight');
                                    });

                                ratings.filter('[data-val="' + input.val() + '"]').addClass('ui-state-highlight');

                                var btns = {};
                                btns[tr('Ok')] = function() {
                                    input.val(dialog.find('div.deliberationConfigureItemRating.ui-state-highlight').data('val'));
                                    showDeliberationItemRating(me, btn, input, ratings);
                                    dialog.dialog('close');
                                };

                                btns[tr('Cancel')] = function() {
                                    dialog.dialog('close');
                                };

                                dialog.dialog({
                                    modal: true,
                                    title: tr('Configure Deliberation Item Ratings'),
                                    buttons: btns
                                });

                                return false;
                            });
                        }
                    </script>
                    {jq}
                        $('select.comment_topictype')
                            .change(function() {
                                if ($('select.comment_topictype').val() == 'd') {
                                    $('tr.forum_deliberation').show();
                                } else {
                                    $('tr.forum_deliberation').hide();
                                }
                            })
                            .change();

                        var itemMaster;
                        $('.forum_deliberation_add_item').click(function() {
                            var thisItem;
                            if (!itemMaster) {
                                $.tikiModal(tr('Loading...'));
                                $.get('tiki-ajax_services', {controller: 'comment', action: "deliberation_item"}, function(itemInput) {
                                    itemMaster = itemInput;
                                    thisItem = $(itemMaster).insertBefore('div.forum_deliberation_items_toolbar');
                                    configureDeliberationItemRatings(thisItem);
                                    $.tikiModal();
                                });
                            } else {
                                thisItem = $(itemMaster).insertBefore('div.forum_deliberation_items_toolbar');
                                configureDeliberationItemRatings(thisItem);
                            }

                            return false;
                        });
                    {/jq}
                    <div class="mb-3 forum_deliberation" style="display: none;">
                        <label class="col-sm-2 col-form-label">{tr}Deliberation{/tr}</label>
                        <div class="col-sm-10 forum_deliberation_items">
                            <div class="forum_deliberation_items_toolbar">
                                {button href="#" _class="forum_deliberation_add_item" _text="{tr}Add Deliberation Item{/tr}"}
                            </div>
                        </div>
                    </div>

                    {if $prefs.feature_antibot eq 'y'}
                        {include file='antibot.tpl' tr_style="formcolor"}
                    {/if}

                    {if $prefs.feature_freetags eq 'y' and $tiki_p_freetags_tag eq 'y'}
                        {include file='freetag.tpl' labelColClass='col-sm-2' inputColClass='col-sm-10'}
                    {/if}

                    {if $user and $prefs.feature_user_watches eq 'y' and (!isset($comments_threadId) or $comments_threadId eq 0)}
                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">{tr}Watch for replies{/tr}</label>
                            <div class="col-sm-10">
                                <input type="radio" name="set_thread_watch" value="y" id="thread_watch_yes" checked="checked">
                                <label for="thread_watch_yes">{tr}Send me an email when someone replies to my topic{/tr}</label>
                                <br>
                                <input type="radio" name="set_thread_watch" value="n" id="thread_watch_no">
                                <label for="thread_watch_no">{tr}Don't send me any emails{/tr}</label>
                            </div>
                        </div>
                    {/if}
                    {if empty($user) && $prefs.feature_user_watches eq 'y'}
                        <div class="mb-3 row">
                            <label for="anonymous_email" class="col-sm-2 col-form-label">{tr}If you would like to be notified when someone replies to this topic<br>please tell us your e-mail address:{/tr}</label></td>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="anonymous_email" name="anonymous_email">
                            </div>
                        </div>
                    {/if}

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label" for="anonymous_name">{tr}Post{/tr}</label>
                        <div class="col-sm-10">
                            {if empty($user)}
                                {tr}Enter your name:{/tr}&nbsp;<input type="text" maxlength="50" id="anonymous_name" name="anonymous_name">
                            {/if}
                            <input type="submit" class="btn btn-primary btn-sm" name="comments_postComment" value="{tr}Post{/tr}"
                                    {if empty($user)}
                                        onclick="setCookie('anonymous_name',document.getElementById('anonymous_name').value);needToConfirm=false;"
                                    {else}
                                        onclick="needToConfirm=false;"
                                    {/if}
                            >
                            {if $prefs.ajax_edit_previews eq 'n'}
                                <input type="submit" class="btn btn-secondary btn-sm" name="comments_previewComment" value="{tr}Preview{/tr}" {if empty($user)}onclick="setCookie('anonymous_name',document.getElementById('anonymous_name').value);needToConfirm=false;"{/if}>
                            {/if}
                            <input type="submit" class="btn btn-link btn-sm" name="comments_postCancel" value="{tr}Cancel{/tr}" {if $comment_preview neq 'y'}onclick="hide('forumpost');window.location='#header';return false;"{/if}>
                        </div>
                    </div>

            </form>
            {remarksbox title="{tr}Editing posts{/tr}"}
                {tr}Use wiki syntax when editing the content of posts - HTML is not allowed. Please click on the following link for documentation on wiki syntax:{/tr} {wiki}[http://doc.tiki.org/Wiki-syntax]{/wiki}
            {/remarksbox}
        </div> {* end forumpost *}
    {/if}
    {if $prefs.feature_forum_content_search eq 'y' and $prefs.feature_search eq 'y'}
        <div class="row mb-4 mx-0">
        <div class="col-md-5 offset-md-7">
            <form id="search-form" class="form" role="form" method="get" action="tiki-search{if $prefs.feature_forum_local_tiki_search eq 'y'}index{else}results{/if}.php">
                <div class="mb-3 row">
                    <div class="input-group">
                        <span class="input-group-text">
                            {icon name="search"}
                        </span>
                        <input name="highlight" id="findinforums" type="text" class="form-control" placeholder="{tr}Find{/tr}...">
                        <input type="hidden" name="where" value="forums">
                        <input type="hidden" name="forumId" value="{$forum_info.forumId}">
                        <input type="submit" class="wikiaction btn btn-primary" name="search" value="{tr}Find{/tr}">
                    </div>
                </div>
            </form>
        </div>
        </div>
    {/if}
{/if}
{if count($channels) > 0}
<div id="{$ts.tableid}-div" class="{if $js}table-responsive{/if} ts-wrapperdiv" {if !empty($ts.enabled)}style="visibility:hidden;"{/if}> {*the table-responsive class cuts off dropdown menus *}
    <div class="card card-primary">
        <div class="card-header">
            {tr}Sub Forums{/tr}
        </div>
    </div>
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
                                <action>
                                    <a href="{bootstrap_modal controller=forum action=delete_forum checked={$channels[user].forumId}}">
                                        {icon name='remove' _menu_text='y' _menu_icon='y' alt="{tr}Delete{/tr}"}
                                    </a>
                                </action>
                            {/if}
                        {/strip}
                        {/actions}
                    </td>
                </tr>
            {/block}
            {sectionelse}
            {if !$ts.enabled || ($ts.enabled && $ts.ajax)}
                {norecords _colspan=$numbercol _text="{tr}No Sub forums found{/tr}"}
            {else}
                {norecords _colspan=$numbercol _text="{tr}Loading{/tr}..."}
            {/if}
        {/section}
        </tbody>
    </table>
</div>
{/if}
<form id="view_forum" method="post">
    {if $tiki_p_admin_forum eq 'y' && ($comments_coms|@count > 0 || $queued > 0 || $reported > 0)}
        <div class="card card-primary">
            <div class="card-header">
                {tr}Moderator actions on selected topics{/tr}
            </div>
            <div class="card-body">
                <div class="float-start">
                    {if $comments_coms|@count > 1}
                        <button
                            type="submit"
                            formaction="{bootstrap_modal controller=forum action=merge_topic}"
                            title=":{tr}Merge{/tr}"
                            class="btn btn-primary btn-sm tips"
                            onclick="confirmPopup()"
                        >
                            {icon name="merge"}
                        </button>
                    {/if}
                    {if $all_forums|@count > 1 && $comments_coms|@count > 0}
                        <button
                            type="submit"
                            formaction="{bootstrap_modal controller=forum action=move_topic}"
                            title=":{tr}Move{/tr}"
                            class="btn btn-primary btn-sm tips"
                            onclick="confirmPopup()"
                        >
                            {icon name="move"}
                        </button>
                    {/if}
                    {if $comments_coms|@count > 0}
                        <button
                            type="submit"
                            formaction="{bootstrap_modal controller=forum action=lock_topic}"
                            title=":{tr}Lock{/tr}"
                            class="btn btn-primary btn-sm tips"
                            onclick="confirmPopup()"
                        >
                            {icon name="lock"}
                        </button>
                        <button
                            type="submit"
                            formaction="{bootstrap_modal controller=forum action=unlock_topic}"
                            title=":{tr}Unlock{/tr}"
                            class="btn btn-primary btn-sm tips"
                            onclick="confirmPopup()"
                        >
                            {icon name="unlock"}
                        </button>
                        <button
                            type="submit"
                            formaction="{bootstrap_modal controller=forum action=delete_topic}"
                            title=":{tr}Delete{/tr}"
                            class="btn btn-primary btn-sm tips"
                            onclick="confirmPopup()"
                        >
                            {icon name="remove"}
                        </button>
                    {/if}
                </div>
                <div class="float-sm-end">
                    {if $reported > 0}
                        <a class="btn btn-primary btn-sm tips" href="tiki-forums_reported.php?forumId={$forumId}" title=":{tr}Reported messages{/tr}">{tr}Reported{/tr} <span class="badge bg-secondary">{$reported}<span></a>
                    {/if}
                    {if $queued > 0}
                        <a class="btn btn-primary btn-sm tips" href="tiki-forum_queue.php?forumId={$forumId}" title=":{tr}Queued messages{/tr}">{tr}Queued{/tr} <span class="badge bg-secondary">{$queued}</span></a>
                    {/if}
                </div>
            </div>
        </div>
    {/if}
    <input type="hidden" name="comments_offset" value="{$comments_offset|escape}">
    <input type="hidden" name="comments_threadId" value="{$comments_threadId|escape}">
    <input type="hidden" name="comments_threshold" value="{$comments_threshold|escape}">
    <input type="hidden" name="thread_sort_mode" value="{$thread_sort_mode|escape}">
    <input type="hidden" name="forumId" value="{$forumId|escape}">
    {* Use css menus as fallback for item dropdown action menu if javascript is not being used *}
    <div id="{$ts.tableid}-div" class="{if $js}table-responsive{/if} ts-wrapperdiv" {if !empty($ts.enabled)}style="visibility:hidden;"{/if}>
        <table id="{$ts.tableid}" class="table normal table-striped table-hover table-forum" data-count="{$comments_cant|escape}">
            {block name=forumheader}
            <thead>
                <tr>
                    {$cntcol = 0}
                    {if $tiki_p_admin_forum eq 'y'}
                        <th id="checkbox">
                            {select_all checkbox_names='forumtopic[]' tablesorter="{$ts.enabled}"}
                        </th>
                        {$cntcol = $cntcol + 1}
                    {/if}
                    <th id="type">{self_link _sort_arg='thread_sort_mode' _sort_field='type'}{tr}Type{/tr}{/self_link}</th>
                    {$cntcol = $cntcol + 1}
                    {if $forum_info.topic_smileys eq 'y'}
                        <th id="smiley">{self_link _sort_arg='thread_sort_mode' _sort_field='smiley'}{tr}Emot{/tr}{/self_link}</th>
                        {$cntcol = $cntcol + 1}
                    {/if}
                    <th id="title">{self_link _sort_arg='thread_sort_mode' _sort_field='title'}{tr}Title{/tr}{/self_link}</th>
                    {$cntcol = $cntcol + 1}
                    {if $forum_info.topics_list_replies eq 'y'}
                        <th id="replies">{self_link _sort_arg='thread_sort_mode' _sort_field='replies'}{tr}Replies{/tr}{/self_link}</th>
                        {$cntcol = $cntcol + 1}
                    {/if}
                    {if $forum_info.topics_list_reads eq 'y'}
                        <th id="hits">{self_link _sort_arg='thread_sort_mode' _sort_field='hits'}{tr}Reads{/tr}{/self_link}</th>
                        {$cntcol = $cntcol + 1}
                    {/if}
                    {if $forum_info.vote_threads eq 'y' and ($tiki_p_ratings_view_results eq 'y' or $tiki_p_admin eq 'y')}
                        <th id="rating">{tr}Rating <br/>(avg/max){/tr}</th>
                        {$cntcol = $cntcol + 1}
                        {if $prefs.rating_results_detailed eq 'y' and $prefs.rating_results_detailed_percent neq 'y'}
                            <th id="rating2">{tr}Detailed results <br/>(counts){/tr}</th>
                            {$cntcol = $cntcol + 1}
                        {elseif $prefs.rating_results_detailed eq 'y' and $prefs.rating_results_detailed_percent eq 'y'}
                            <th id="rating3">{tr}Detailed results <br/>(counts/%){/tr}</th>
                            {$cntcol = $cntcol + 1}
                        {/if}
                    {/if}
                    {if $forum_info.topics_list_pts eq 'y'}
                        <th id="average">{self_link _sort_arg='thread_sort_mode' _sort_field='average'}{tr}pts{/tr}{/self_link}</th>
                        {$cntcol = $cntcol + 1}
                    {/if}
                    {if $forum_info.topics_list_lastpost eq 'y' or $forum_info.topics_list_lastpost_avatar eq 'y'}
                        <th id="lastpost">{self_link _sort_arg='thread_sort_mode' _sort_field='lastPost'}{tr}Last Post{/tr}{/self_link}</th>
                        {$cntcol = $cntcol + 1}
                    {/if}
                    {if $forum_info.topics_list_author eq 'y' or $forum_info.topics_list_author_avatar eq 'y'}
                        <th id="poster">{self_link _sort_arg='thread_sort_mode' _sort_field='userName'}{tr}Author{/tr}{/self_link}</th>
                        {$cntcol = $cntcol + 1}
                    {/if}
                    {if $forum_info.att_list_nb eq 'y'}
                        <th id="atts">{tr}Files{/tr}</th>
                        {$cntcol = $cntcol + 1}
                    {/if}
                    {if $prefs.feature_multilingual eq 'y'}
                        <th id="lang">{tr}Language{/tr}</th>
                        {$cntcol = $cntcol + 1}
                    {/if}
                    {if $prefs.forum_category_selector_in_list eq 'y'}
                        <th id="category">{tr}Category{/tr}</th>
                        {$cntcol = $cntcol + 1}
                    {/if}
                    <th id="actions"></th>
                    {$cntcol = $cntcol + 1}
                </tr>
            </thead>
            {/block}
            <tbody>
                {section name=ix loop=$comments_coms}
                    {if $userinfo && $comments_coms[ix].lastPost > $userinfo.lastLogin}
                        {assign var="newtopic" value="_new"}
                    {else}
                        {assign var="newtopic" value=""}
                    {/if}
                    {block name=forumrow}
                    <tr>
                        {if $tiki_p_admin_forum eq 'y'}
                            <td class="checkbox-cell">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="forumtopic[]" value="{$comments_coms[ix].threadId|escape}" {if isset($smarty.request.forumtopic) and in_array($comments_coms[ix].threadId,$smarty.request.forumtopic)}checked="checked"{/if}>
                                </div>
                            </td>
                        {/if}
                        <td class="icon">
                            {if $newtopic neq ''}
                                {assign var=nticon value=$newtopic}
                                {assign var=ntalt value="-{tr}New{/tr}"}
                            {/if}
                            {if $comments_coms[ix].type eq 'n'}
                                {tr}Normal{/tr}
                            {elseif $comments_coms[ix].type eq 'a'}
                                {tr}Announce{/tr}
                            {elseif $comments_coms[ix].type eq 'h'}
                                {tr}Hot{/tr}
                            {elseif $comments_coms[ix].type eq 's'}
                                {tr}Sticky{/tr}
                            {elseif $comments_coms[ix].type eq 'l'}
                                {tr}Locked{/tr}
                            {elseif $comments_coms[ix].type eq 'd'}
                                {tr}Deliberation{/tr}
                            {/if}

                            {if $comments_coms[ix].locked eq 'y'}
                                {icon name="lock" title=":{tr}Topic locked{/tr}" class="tips"}
                            {elseif $forum_info.is_locked eq 'y'}
                                {icon name="lock" title=":{tr}Forum locked{/tr}" class="tips"}
                            {/if}
                        </td>
                        {if $forum_info.topic_smileys eq 'y'}
                            <td class="icon">
                                {if strlen($comments_coms[ix].smiley) > 0}
                                    <img src='img/smiles/{$comments_coms[ix].smiley}'>
                                {else}
                                    &nbsp;{$comments_coms[ix].smiley}
                                {/if}
                            </td>
                        {/if}

                        <td class="text">
                            {if $prefs.feature_sefurl === 'y'}{$sep = '?'}{else}{$sep = '&amp;'}{/if}
                            <a {if $comments_coms[ix].is_marked}class="forumnameread"{else}class="forumname"{/if} href="{$comments_coms[ix].threadId|sefurl:'forumthread'}{$sep}topics_offset={math equation="x + y" x=$comments_offset y=$smarty.section.ix.index}{if $comments_threshold}&amp;topics_threshold={$comments_threshold}{/if}{if $thread_sort_mode ne $forum_info.topicOrdering}&amp;topics_sort_mode={$thread_sort_mode}{/if}{if isset($topics_find) and $topics_find}&amp;topics_find={$comments_find}{/if}">
                                {$comments_coms[ix].title|escape}
                            </a>
                            {if $forum_info.topic_summary eq 'y'}
                                <div class="subcomment">
                                    {if $comments_coms[ix].summary|count_characters > 0}
                                        {$comments_coms[ix].summary|truncate:240:"...":true|escape}
                                    {else}
                                        {$comments_coms[ix].data|truncate:100:"...":true|escape}
                                    {/if}
                                </div>
                            {/if}
                        </td>
                        {if $forum_info.topics_list_replies eq 'y'}
                            <td class="integer"><span class="badge bg-secondary">{$comments_coms[ix].replies}</span></td>
                        {/if}
                        {if $forum_info.topics_list_reads eq 'y'}
                            <td class="integer"><span class="badge bg-secondary">{$comments_coms[ix].hits}</span></td>
                        {/if}
                        {if $forum_info.vote_threads eq 'y' and ($tiki_p_ratings_view_results eq 'y' or $tiki_p_admin eq 'y')}
                            <td class="integer">{rating_result_avg type=comment id=$comments_coms[ix].threadId }&nbsp;&nbsp;&nbsp;</td>
                            {if $prefs.rating_results_detailed eq 'y'}
                                <td class="text">{rating_result type=comment id=$comments_coms[ix].threadId }</td>
                            {/if}
                        {/if}
                        {if $forum_info.topics_list_pts eq 'y'}
                            <td class="integer"><span class="badge bg-secondary">{$comments_coms[ix].average|string_format:"%.2f"}</span></td>
                        {/if}
                        {if $forum_info.topics_list_lastpost eq 'y'}
                            <td class="text">
                                {if $forum_info.topics_list_lastpost_avatar eq 'y' and $prefs.feature_userPreferences eq 'y'}
                                    <div style="float:left;padding-right:2px">{$comments_coms[ix].lastPostData.userName|avatarize}</div>
                                {/if}
                                <div style="float:left;">
                                    {$comments_coms[ix].lastPost|tiki_short_datetime} {* date_format:"%b %d [%H:%M]" *}
                                    {if $comments_coms[ix].replies}
                                        <br>
                                        <small>{if $forum_info.topics_list_lastpost_title eq 'y'}<i>{$comments_coms[ix].lastPostData.title|escape}</i> {/if}{tr}by{/tr} {$comments_coms[ix].lastPostData.userName|userlink}</small>
                                    {/if}
                                </div>
                            </td>
                        {elseif $forum_info.topics_list_lastpost_avatar eq 'y' and $prefs.feature_userPreferences eq 'y'}
                            <td class="text">
                                {$comments_coms[ix].lastPostData.userName|avatarize}
                            </td>
                        {/if}
                        {if $forum_info.topics_list_author eq 'y'}
                            <td class="text">
                                {if $forum_info.topics_list_author_avatar eq 'y' and $prefs.feature_userPreferences eq 'y'}
                                    <div style="float:left;padding-right:2px">
                                        {$comments_coms[ix].userName|avatarize}
                                    </div>
                                {/if}
                                <div style="float:left">
                                    {$comments_coms[ix].userName|userlink}</td>
                                </div>
                        {elseif $forum_info.topics_list_author_avatar eq 'y' and $prefs.feature_userPreferences eq 'y'}
                            <td class="text">
                                {$comments_coms[ix].userName|avatarize}
                            </td>
                        {/if}

                        {if $forum_info.att_list_nb eq 'y'}
                            <td style="text-align:center;">
                                {if !empty($comments_coms[ix].nb_attachments)}<a href="tiki-view_forum_thread.php?comments_parentId={$comments_coms[ix].threadId}&amp;view_atts=y#attachments" title="{tr}Attachments{/tr}">{/if}
                                <span>
                                    {$comments_coms[ix].nb_attachments}
                                </span>
                                {if !empty($comments_coms[ix].nb_attachments)}</a>{/if}
                            </td>
                        {/if}

                        {if $prefs.feature_multilingual eq 'y'}
                            <td>
                                {$forum_info.forumLanguage}
                            </td>
                        {/if}

                        {if $prefs.forum_category_selector_in_list eq 'y'}
                            <td>{categoryselector type="forum post" object=$comments_coms[ix].threadId categories=$prefs.forum_available_categories}</td>
                        {/if}

                        <td class="text" nowrap="nowrap">
                            {actions}
                                {strip}
                                    {if ( $tiki_p_admin_forum eq 'y' or ($comments_coms[ix].userName == $user && $tiki_p_forum_post eq 'y') ) and $forum_info.is_locked neq 'y' and $comments_coms[ix].locked neq 'y'}
                                        <action>
                                            <a href="tiki-view_forum.php?openpost=1&amp;comments_threadId={$comments_coms[ix].threadId}&amp;forumId={$forum_info.forumId}&amp;comments_threshold={$comments_threshold}&amp;comments_offset={$comments_offset}&amp;thread_sort_mode={$thread_sort_mode}&amp;comments_per_page={$comments_per_page}">
                                                {icon name='edit' _menu_text='y' _menu_icon='y' alt="{tr}Edit{/tr}"}
                                            </a>
                                        </action>
                                    {/if}
                                    {if $prefs.feature_forum_topics_archiving eq 'y' && $tiki_p_admin_forum eq 'y'}
                                        {if $comments_coms[ix].archived eq 'y'}
                                            <action>
                                                <a href="{bootstrap_modal controller=forum action=unarchive_topic forumId={$forum_info.forumId} comments_parentId={$comments_coms[ix].threadId} comments_offset={$comments_offset} thread_sort_mode={$thread_sort_mode} comments_per_page={$comments_per_page}}">
                                                    {icon name='file-archive-open' _menu_text='y' _menu_icon='y' alt="{tr}Unarchive{/tr}"}
                                                </a>
                                            </action>
                                        {else}
                                            <action>
                                                <a href="{bootstrap_modal controller=forum action=archive_topic forumId={$forum_info.forumId} comments_parentId={$comments_coms[ix].threadId} comments_offset={$comments_offset} thread_sort_mode={$thread_sort_mode} comments_per_page={$comments_per_page}}">
                                                    {icon name='file-archive' _menu_text='y' _menu_icon='y' alt="{tr}Archive{/tr}"}
                                                </a>
                                            </action>
                                        {/if}
                                    {/if}
                                    {if $tiki_p_admin_forum eq 'y'}
                                        <action>
                                            <a href="{bootstrap_modal controller=forum action=delete_topic forumId={$forum_info.forumId} forumtopic={$comments_coms[ix].threadId} comments_offset={$comments_offset} thread_sort_mode={$thread_sort_mode} comments_per_page={$comments_per_page}}">
                                                {icon name='remove' _menu_text='y' _menu_icon='y' alt="{tr}Delete{/tr}"}
                                            </a>
                                        </action>
                                        <action>
                                            {permission_link mode=text type="thread" permType="forums" id=$comments_coms[ix].threadId title=$comments_coms[ix].title}
                                        </action>
                                    {/if}
                                {/strip}
                            {/actions}
                        </td>
                    </tr>
                    {/block}
                {sectionelse}
                    {norecords _colspan=$cntcol _text="{tr}No topics found{/tr}"}
                {/section}
            </tbody>
        </table>
    </div>
</form>
{if !$ts.ajax}
    {if !$ts.enabled}
        {pagination_links cant=$comments_cant step=$comments_per_page offset=$comments_offset offset_arg='comments_offset'}{/pagination_links}
    {/if}
    {if $forum_info.forum_last_n > 0 && count($last_comments)}
        {* Last n titles *}
        <div class="table-responsive">
        <table class="table">
            <tr>
                <th>{tr}Last{/tr} {$forum_info.forum_last_n} {tr}posts in this forum{/tr}</th>
            </tr>
            {section name=ix loop=$last_comments}
                <tr>
                    <td>
                        {if $last_comments[ix].parentId eq 0}
                            {assign var="idt" value=$last_comments[ix].threadId}
                        {else}
                            {assign var="idt" value=$last_comments[ix].parentId}
                        {/if}
                        <a class="forumname" href="tiki-view_forum_thread.php?comments_parentId={$idt}&amp;topics_threshold={$comments_threshold}&amp;topics_offset={math equation="x + y" x=$comments_offset y=$smarty.section.ix.index}&amp;topics_sort_mode={$thread_sort_mode}&amp;topics_find={$comments_find}&amp;forumId={$forum_info.forumId}">{$last_comments[ix].title|escape}</a>
                    </td>
                </tr>
            {/section}
        </table>
        </div>
        <br>
    {/if}

    {if !$ts.enabled}
        <div class="col-md-8" styles="padding-top:15px">
            <div class="card" id="filter-panel">
                <div class="card-header filter-card-header">
                    <h4 class="card-title">
                        <a data-bs-toggle="collapse" href="#filterCollapse" class="collapsed">
                            {tr}Filter Posts{/tr} {icon name="angle-down"}
                        </a>
                    </h4>
                </div>
                <div id="filterCollapse" class="card-collapse collapse">
                    <div class="card-body">
                        <form id='time_control' method="post" action="tiki-view_forum.php">
                            {if $comments_offset neq 0}
                                <input type="hidden" name="comments_offset" value="0">{*reset the offset when starting a new filtered search*}
                            {/if}
                            {if $comments_threadId neq 0}
                                <input type="hidden" name="comments_threadId" value="{$comments_threadId|escape}">
                            {/if}
                            {if $comments_threshold neq 0}
                                <input type="hidden" name="comments_threshold" value="{$comments_threshold|escape}">
                            {/if}
                            <input type="hidden" name="thread_sort_mode" value="{$thread_sort_mode|escape}">
                            <input type="hidden" name="forumId" value="{$forumId|escape}">
                            <div class="mb-3 row mx-0">
                                <label class="col-md-4 col-form-label form-control-sm" for="filter_time">{tr}Last post date{/tr}</label>
                                <div class="col-md-8">
                                    <select id="filter_time" name="time_control" class="form-control form-control-sm">
                                        <option value="" {if $smarty.request.time_control eq ''}selected="selected"{/if}>{tr}All posts{/tr}</option>
                                        <option value="3600" {if $smarty.request.time_control eq 3600}selected="selected"{/if}>{tr}Last hour{/tr}</option>
                                        <option value="86400" {if $smarty.request.time_control eq 86400}selected="selected"{/if}>{tr}Last 24 hours{/tr}</option>
                                        <option value="172800" {if $smarty.request.time_control eq 172800}selected="selected"{/if}>{tr}Last 48 hours{/tr}</option>
                                    </select>
                                </div>
                            </div>
                            {if $prefs.feature_forum_topics_archiving eq 'y'}
                                <div class="mb-3 row mx-0">
                                    <label class="col-md-4 col-form-label form-control-sm" for="show_archived">{tr}Show archived posts{/tr}</label>
                                    <div class="col-md-8">
                                        <input type="checkbox" class="form-check-input" id="show_archived" name="show_archived" {if $show_archived eq 'y'}checked="checked"{/if}>
                                    </div>
                                </div>
                            {/if}
                            {if $user}
                                <div class="mb-3 row mx-0">
                                    <label class="col-md-4 col-form-label form-control-sm" for="filter_poster">{tr}Containing posts by{/tr}</label>
                                    <div class="col-md-8">
                                        <select id="filter_poster" class="form-control form-control-sm" name="poster">
                                            <option value=""{if empty($smarty.request.poster)} selected="selected"{/if}>
                                                {tr}All posts{/tr}
                                            </option>
                                            <option value="_me" {if isset($smarty.request.poster) and $smarty.request.poster eq '_me'} selected="selected"{/if}>
                                                {tr}Me{/tr}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            {/if}
                            <div class="mb-3 row mx-0">
                                <label class="col-md-4 col-form-label form-control-sm" for="filter_type">{tr}Type{/tr}</label>
                                <div class="col-md-8">
                                    <select id="filter_type" name="filter_type" class="form-control form-control-sm">
                                        <option value=""{if empty($smarty.request.filter_type)}selected="selected"{/if}>
                                            {tr}All posts{/tr}
                                        </option>
                                        <option value="n" {if isset($smarty.request.filter_type) and $smarty.request.filter_type eq 'n'} selected="selected"{/if}>
                                            {tr}normal{/tr}
                                        </option>
                                        <option value="a" {if isset($smarty.request.filter_type) and $smarty.request.filter_type eq 'a'} selected="selected"{/if}>
                                            {tr}announce{/tr}
                                        </option>
                                        <option value="h"{if isset($smarty.request.filter_type) and $smarty.request.filter_type eq 'h'} selected="selected"{/if}>
                                            {tr}hot{/tr}
                                        </option>
                                        <option value="s"{if isset($smarty.request.filter_type) and $smarty.request.filter_type eq 's'} selected="selected"{/if}>
                                            {tr}sticky{/tr}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3 row mx-0">
                                <label class="col-md-4 col-form-label form-control-sm" for="filter_replies">{tr}Replies{/tr}</label>
                                <div class="col-md-8">
                                    <select id="filter_replies" name="reply_state" class="form-control form-control-sm">
                                        <option value=""{if empty($smarty.request.reply_state)} selected="selected"{/if}>
                                            {tr}All posts{/tr}
                                        </option>
                                        <option value="none"{if isset($smarty.request.reply_state) and $smarty.request.reply_state eq 'none'} selected="selected"{/if}>
                                            {tr}Posts with no replies{/tr}
                                        </option>
                                    </select>
                                </div>
                            </div>
                                <div class="d-flex justify-content-around">
                                    <input type="submit" class="btn btn-primary btn-sm" id="filter_submit" value="{tr}Filter{/tr}">
                                </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    {/if}

    {if empty($user) and $prefs.javascript_enabled eq "y"}
        {jq}
            var js_anonymous_name = getCookie('anonymous_name');
            if (js_anonymous_name) document.getElementById('anonymous_name').value = js_anonymous_name;
        {/jq}
    {/if}
    {jq}
        var $forum = $("#editpageform");

        if (jqueryTiki.validate) {
            $forum.validate({
                rules: {        // make sure required fields are entered
                    comments_title: "required",
                    comments_data: "required",
                },
                messages: {
                    comments_title: "{tr}Topic title is required {/tr}",
                    comments_data: "{tr}Topic message is required {/tr}",
                },
            });
        }

        $forum.submit(function() {
            if (jqueryTiki.validate && ! $(this).valid()) {
                return false;
            }
            // prevent double submission
            if (!$forum.data("sub")) {
                $forum.tikiModal('Save in Progress...');
                $forum.data("sub", true);
                $forum.submit();
            }
        });
    {/jq}
{/if}