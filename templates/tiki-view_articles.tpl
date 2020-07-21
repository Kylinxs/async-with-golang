
{if !isset($actions) or $actions eq "y"}
        {title help="View Articles" admpage="articles"}
            {if $prefs.art_home_title eq 'topic' and !empty($topic)}
                {tr}{$topic|escape}{/tr}
            {elseif $prefs.art_home_title eq 'type' and !empty($type)}
                {tr}{$type|escape}{/tr}
            {else}{tr}View Articles{/tr}{/if}
        {/title}
    {if $headerLinks eq "y"}
        <div class="t_navbar mb-4">
            {if $tiki_p_edit_article eq 'y' or $tiki_p_admin eq 'y' or $tiki_p_admin_cms eq 'y'}
                {button href="tiki-edit_article.php" _type="link" class="btn btn-link" _icon_name="create" _text="{tr}New Article{/tr}"}
            {/if}
            {if $prefs.feature_submissions == 'y' && $tiki_p_edit_submission == "y" && $tiki_p_edit_article neq 'y' && $tiki_p_admin neq 'y' && $tiki_p_admin_cms neq 'y'}
                {button href="tiki-edit_submission.php" _type="link" class="btn btn-link" _icon_name="create" _text="{tr}New Submission{/tr}"}
            {/if}
            {if $tiki_p_read_article eq 'y' or $tiki_p_articles_read_heading eq 'y' or $tiki_p_admin eq 'y' or $tiki_p_admin_cms eq 'y'}
                {button href="tiki-list_articles.php" _type="link" class="btn btn-link" _icon_name="list" _text="{tr}List Articles{/tr}"}
            {/if}

            {if $prefs.feature_submissions == 'y' && ($tiki_p_approve_submission == "y"
            || $tiki_p_remove_submission == "y" || $tiki_p_edit_submission == "y")}
                {button href="tiki-list_submissions.php" _type="link" class="btn btn-link" _icon_name="view" _text="{tr}View Submissions{/tr}"}
            {/if}

                <a class="btn btn-sm btn-info dropdown-toggle float-end" data-bs-toggle="dropdown" data-hover="dropdown" href="#">
                    {icon name='menu-extra'}
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <h6 class="dropdown-header">
                        {tr}Monitoring{/tr}
                    </h6>
                    <div class="dropdown-divider"></div>
                    {if $user_watching_articles eq 'n'}
                        {self_link watch_event='article_*' watch_object='*' watch_action='add' _icon_name='watch' _class='dropdown-item' _text="{tr}Monitor articles{/tr}"}
                        {/self_link}
                    {else}
                        {self_link watch_event='article_*' watch_object='*' watch_action='remove' _icon_name='stop-watching' _class='dropdown-item' _text="{tr}Stop monitoring articles{/tr}"}
                        {/self_link}
                    {/if}
                    {if $prefs.feature_group_watches eq 'y' and $tiki_p_admin_users eq 'y'}
                        <a class="dropdown-item" href="tiki-object_watches.php?watch_event=article_*&amp;objectId=*">
                            {icon name='watch-group'} {tr}Group monitor{/tr}
                        </a>
                    {/if}
                </div>

        </div>
    {/if}
{/if}

{section name=ix loop=$listpages}
    {capture name=href}{strip}
        {if empty($urlparam)}
            {if $useLinktoURL eq 'n' or empty($listpages[ix].linkto)}
                {$listpages[ix].articleId|sefurl:article}
            {else}
                {$listpages[ix].linkto}
            {/if}
        {else}
            {$listpages[ix].articleId|sefurl:article:with_next}{$urlparam}
        {/if}
    {/strip}{/capture}
    {if $listpages[ix].disp_article eq 'y'}
        <article class="clearfix article {if !empty($container_class)} {$container_class}{/if} article{$smarty.section.ix.index} mt-1 mb-5">
            {if ($listpages[ix].show_avatar eq 'y')}
                <div class="avatar float-start mr-3">
                    {$listpages[ix].author|avatarize}
                </div>
            {/if}
            {if $listpages[ix].show_topline eq 'y' and $listpages[ix].topline}
                <div class="articletopline">
                    {$listpages[ix].topline|escape}
                </div>
            {/if}

            <header class="articletitle mt-0 mx-0 mb-1">
                <h2>
                    {object_link type=article id=$listpages[ix].articleId url=$smarty.capture.href title=$listpages[ix].title}
                </h2>
                {if $listpages[ix].show_subtitle eq 'y' and $listpages[ix].subtitle}<div class="articlesubtitle">{$listpages[ix].subtitle|escape}</div>{/if}
                {if ($listpages[ix].show_author eq 'y')
                    or ($listpages[ix].show_pubdate eq 'y')
                    or ($listpages[ix].show_expdate eq 'y')
                    or ($listpages[ix].show_reads eq 'y')
                }
                    <span class="titleb">
                        {if $listpages[ix].show_author eq 'y'}
                            {if $listpages[ix].authorName}
                                <span class="author">
                                    {tr}Author:{/tr} {$listpages[ix].authorName|escape}
                                </span>
                            {else}
                                <span class="author">
                                    {tr}Author:{/tr} {$listpages[ix].author|username}
                                </span>
                            {/if}
                            {if $listpages[ix].show_pubdate eq 'y' or $listpages[ix].show_expdate eq 'y' or $listpages[ix].show_reads eq 'y'}
                                -
                            {/if}
                        {/if}
                        {if $listpages[ix].show_pubdate eq 'y'}
                            <span class="pubdate">
                                {tr}Published{/tr} {$listpages[ix].publishDate|tiki_short_datetime}
                            </span>
                            {if $listpages[ix].show_expdate eq 'y' or $listpages[ix].show_reads eq 'y'}
                                -
                            {/if}
                        {/if}
                        {if $listpages[ix].show_expdate eq 'y'}
                            <span class="expdate">
                                {tr}Expires{/tr} {$listpages[ix].expireDate|tiki_short_datetime}
                            </span>
                            {if $listpages[ix].show_reads eq 'y'}
                                -
                            {/if}
                        {/if}
                        {if $listpages[ix].show_reads eq 'y'}
                            <span class="reads">
                                ({$listpages[ix].nbreads} {tr}Reads{/tr})
                            </span>
                        {/if}
                        {if $listpages[ix].comment_can_rate_article eq 'y' && $prefs.article_user_rating eq 'y'
                            && ($listpages[ix].perms.tiki_p_ratings_view_results eq 'y' or $tiki_p_admin eq 'y')}
                            - <span class="ratingResultAvg">
                                {tr}Users rating: {/tr}
                            </span>{rating_result_avg id=$listpages[ix].articleId type=article}
                        {/if}
                    </span><br>
                {/if}
                {if $author ne $user and $listpages[ix].comment_can_rate_article eq 'y' and empty({$listpages[ix].body})
                    and !isset($preview) and $prefs.article_user_rating eq 'y' and ($listpages[ix].perms.tiki_p_rate_article eq 'y'
                    or $listpages[ix].perms.tiki_p_admin_cms eq 'y')}
                    <div class="articleheading">
                        <form method="post" action="">
                            {rating type=article id=$listpages[ix].articleId}
                        </form>
                    </div>
                {/if}
                {if $listpages[ix].comment_can_rate_article eq 'y' && $prefs.article_user_rating eq 'y'
                    && ($listpages[ix].perms.tiki_p_ratings_view_results eq 'y' or $tiki_p_admin eq 'y')}
                    <div class="articleheading">
                        {rating_result id=$listpages[ix].articleId type=article}
                    </div>
                {/if}
            </header>

            {if $listpages[ix].use_ratings eq 'y'}
                <div class="articleheading">
                    {tr}Rating:{/tr}
                    {repeat count=$listpages[ix].rating}
                        {icon name='star'}
                    {/repeat}
                    {if $listpages[ix].rating > $listpages[ix].entrating}
                        {icon name='star-half'}
                    {/if}
                    ({$listpages[ix].rating}/10)
                </div>
            {/if}

            <div class="articleheading {if $isfloat eq 'n'}d-md-flex{/if} clearfix"> {* No flex in sm screen, to prevent skinny text column *}
                <div class="{if $listpages[ix].isfloat eq 'y'}float-start{else}flex-shrink-0{/if} me-3">
                    {if $listpages[ix].show_image eq 'y'}
                        {if $listpages[ix].useImage eq 'y'}
                            {if $listpages[ix].hasImage eq 'y'}
                                <a
                                    href="{$smarty.capture.href}" {if $listpages[ix].isfloat eq 'y'} class="me-3 float-start"{/if}
                                    title="{if $listpages[ix].show_image_caption and $listpages[ix].image_caption}{$listpages[ix].image_caption|escape}{elseif $listpages[ix].topicName}{tr}{$listpages[ix].topicName}{/tr}{else}{tr}Read More{/tr}{/if}">
                                    {$style=''}
                                    <img class="img-thumbnail media-object article-image custom-image"
                                        alt="{if $listpages[ix].show_image_caption and $listpages[ix].image_caption}{$listpages[ix].image_caption|escape}{elseif $listpages[ix].topicName}{tr}{$listpages[ix].topicName}{/tr}{/if}"
                                        {strip}src="article_image.php?image_type=article&amp;id={$listpages[ix].articleId}
                                        {if $listpages[ix].list_image_x > 0 and ($largefirstimage neq 'y' or not $smarty.section.ix.first)}
                                            &amp;width={$listpages[ix].list_image_x}
                                        {elseif $listpages[ix].image_x > 0}
                                            &amp;width={$listpages[ix].image_x}
                                        {/if}
                                        &amp;cache=y"
                                        {if $listpages[ix].list_image_y > 0 and ($largefirstimage neq 'y' or not $smarty.section.ix.first)}
                                            {$style=$style|cat:"max-height:"|cat:$listpages[ix].list_image_y|cat:"px;"}
                                        {elseif $listpages[ix].image_y > 0}
                                            {$style=$style|cat:"max-height:"|cat:$listpages[ix].image_y|cat:"px;"}
                                        {/if}
                                        style="{$style}"
                                    >{/strip}
                                </a>
                            {else}
                                {* Intentionally left blank to allow user add an image from somewhere else through the img tag and no other extra image *}
                            {/if}
                        {else}
                            {if isset($topics[$listpages[ix].topicId].image_size) and $topics[$listpages[ix].topicId].image_size > 0}
                                <a href="{$smarty.capture.href}" class="thumbnail"{if $listpages[ix].isfloat eq 'y'} class="me-3 float-start"{/if}
                                        title="{if $listpages[ix].show_image_caption and $listpages[ix].image_caption}{$listpages[ix].image_caption|escape}{else}{tr}{$listpages[ix].topicName}{/tr}{/if}">
                                    <img class="media-object img-thumbnail article-image topic-image"
                                            alt="{if $listpages[ix].show_image_caption and $listpages[ix].image_caption}{$listpages[ix].image_caption|escape}{else}{tr}{$listpages[ix].topicName}{/tr}{/if}"
                                            src="article_image.php?image_type=topic&amp;id={$listpages[ix].topicId}">
                                </a>
                            {/if}
                        {/if}
                    {/if}
                </div>
                <div class="articleheadingtext{if $listpages[ix].isfloat eq 'n'} flex-grow-1 ms-3{/if}">{$listpages[ix].parsed_heading}</div>
            </div>
                    {if isset($fullbody) and $fullbody eq "y"}
            <div class="articlebody py-3">{$listpages[ix].parsed_body}</div>
                    {/if}

            <div class="articletrailer clearfix">
                {if $prefs.feature_freetags eq 'y' and $tiki_p_view_freetags eq 'y' and $listpages[ix].freetags.data|@count >0}
                    {$freetags = $listpages[ix].freetags}
                    {include file='freetag_list.tpl'}
                {/if}
                {if ($listpages[ix].size > 0) or (($prefs.feature_article_comments eq 'y') and ($listpages[ix].perms.tiki_p_read_comments eq 'y'))}
                <ul class="list-inline float-start">
                    {if ($listpages[ix].perms.tiki_p_read_article eq 'y' and $listpages[ix].heading_only ne 'y' and (!isset($fullbody) or $fullbody ne "y"))}
                        {if ($listpages[ix].size > 0 and !empty($listpages[ix].body))}

                            <li class="list-inline-item status"> {* named to be similar to for<um/blog item *}
                                <a href="{$smarty.capture.href}" class="more">{icon name="book-reader"} {tr}Read More{/tr}</a>
                            </li>
                            {if ($listpages[ix].show_size eq 'y')}
                                <li class="list-inline-item">
                                    ({$listpages[ix].size} {tr}bytes{/tr})
                                </li>
                            {/if}
                        {/if}
                    {/if}
                    {if ($prefs.feature_article_comments eq 'y') and ($listpages[ix].perms.tiki_p_read_comments eq 'y') and ($listpages[ix].allow_comments eq 'y')}
                        <li class="list-inline-item">
                            <a href="{$listpages[ix].articleId|sefurl:article:with_next}{if $prefs.feature_sefurl neq 'y'}&amp;{/if}show_comzone=y{if !empty($urlparam)}&amp;{$urlparam}{/if}#comments"{if $listpages[ix].comments_cant > 0} class="mark"{/if}>
                                {if $listpages[ix].comments_cant == 0 and $listpages[ix].perms.tiki_p_post_comments == 'y'}
                                    {if !isset($actions) or $actions eq "y"}
                                        {icon name="comment"} {tr}Add Comment{/tr}
                                    {/if}
                                {elseif $listpages[ix].perms.tiki_p_read_comments eq 'y'}
                                    {if $listpages[ix].comments_cant == 1}
                                        {icon name="comment"} {tr}1 comment{/tr}
                                    {else}
                                        {icon name="comments"} {$listpages[ix].comments_cant}&nbsp;{tr}comments{/tr}
                                    {/if}
                                {/if}
                            </a>
                        </li>
                    {/if}
                    </ul>
                {/if}
                {if !isset($actions) or $actions eq "y"}
                    <div class="btn-group actions float-end dropup">
                        {if $prefs.feature_multilingual eq 'y' and $listpages[ix].perms.tiki_p_edit_article eq 'y'}
                            {include file='translated-lang.tpl' object_type='article' trads=$listpages[ix].translations articleId=$listpages[ix].articleId}
                        {/if}
                        <div class="btn-group">
                            <a class="btn btn-info btn-sm dropdown-toggle" data-bs-toggle="dropdown" data-hover="dropdown" href="#">
                                {icon name="wrench"}
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <h6 class="dropdown-header">
                                    {tr _0="{$listpages[ix].title}"}Actions for %0{/tr}
                                </h6>
                                <div class="dropdown-divider"></div>
                                {if $listpages[ix].perms.tiki_p_edit_article eq 'y' or (!empty($user) and $listpages[ix].author eq $user
                                and $listpages[ix].creator_edit eq 'y')}
                                    <a class="dropdown-item" href="tiki-edit_article.php?articleId={$listpages[ix].articleId}">
                                        {icon name='edit'} {tr}Edit{/tr}
                                    </a>
                                {/if}
                                {if $prefs.feature_cms_print eq 'y'}
                                    <a class="dropdown-item" href="tiki-print_article.php?articleId={$listpages[ix].articleId}">
                                        {icon name='print'} {tr}Print{/tr}
                                    </a>
                                {/if}
                                {if $listpages[ix].perms.tiki_p_remove_article eq 'y'}
                                    <a class="dropdown-item" href="tiki-list_articles.php?remove={$listpages[ix].articleId}">
                                        {icon name='remove'} {tr}Remove{/tr}
                                    </a>
                                {/if}
                            </div>
                        </div>
                    </div>
                {/if}
            </div>

        </article>
    {/if}
{sectionelse}
    {if $quiet ne 'y'}
        {remarksbox type=info title="{tr}No articles yet.{/tr}" close="n"}
        {/remarksbox}
    {/if}
{/section}

{if !isset($actions) or $actions eq "y"}
    {if $tiki_p_edit_article eq 'y' or $tiki_p_admin eq 'y' or $tiki_p_admin_cms eq 'y'}
        <br/><a href="tiki-edit_article.php{if (isset($topicId) && !empty($topicId)) or (isset($type) && !empty($type))}?{/if}{if isset($topicId) && !empty($topicId) and is_numeric($topicId)}topicId={$topicId|escape}{/if}{if isset($type) && !empty($type)}&type={$type|escape}{/if}" class="alert-link">
            {icon name="create"} {tr}New Article{/tr}
        </a>
    {/if}
    {if $prefs.feature_submissions == 'y' && $tiki_p_edit_submission == "y" && $tiki_p_edit_article neq 'y' && $tiki_p_admin neq 'y' && $tiki_p_admin_cms neq 'y'}
        <br/><a href="tiki-edit_submission.php{if (isset($topicId) && !empty($topicId)) or (isset($type) && !empty($type))}?{/if}{if isset($topicId) && !empty($topicId) and is_numeric($topicId)}topicId={$topicId|escape}{/if}{if isset($type) && !empty($type)}&type={$type|escape}{/if}" class="alert-link">
            {icon name="create"} {tr}New Submission{/tr}
        </a>
    {/if}
{/if}

{if !empty($listpages) && (!isset($usePagination) or $usePagination ne 'n')}
    {pagination_links cant=$cant step=$maxArticles offset=$offset}{if isset($urlnext)}{$urlnext}{/if}{/pagination_links}
{/if}