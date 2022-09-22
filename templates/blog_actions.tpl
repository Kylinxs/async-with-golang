{* $Id$ *}
<div class="blogactions">
    <div class="btn-group">
        {if ! $js}<ul><li>{/if}
        <a class="btn btn-info btn-sm dropdown-toggle" data-bs-toggle="dropdown" data-hover="dropdown" href="#"title="{tr}Blog actions{/tr}">
            {icon name="menu-extra"}
        </a>
        <div class="dropdown-menu">
            <h6 class="dropdown-header">
                {tr}Blog Actions{/tr}
            </h6>
            <div class="dropdown-divider"></div>
            {if $tiki_p_blog_post eq "y"}
                {if $ownsblog eq "y" or $tiki_p_blog_admin eq "y" or $public eq "y"}
                    <a class="dropdown-item" href="tiki-blog_post.php?blogId={$blogId}">
                            {icon name='post'} {tr}Post{/tr}
                        </a>
                {/if}
            {/if}
            {if $ownsblog eq "y" or $tiki_p_blog_admin eq "y"}
                <a class="dropdown-item" href="tiki-edit_blog.php?blogId={$blogId}">
                        {icon name='edit'} {tr}Edit{/tr}
                </a>
                {if $allow_comments eq 'y'}
                    <a class="dropdown-item" href='tiki-list_comments.php?types_section=blogs&amp;blogId={$blogId}'>
                            {icon name='comments'} {tr}Comments{/tr}
                    </a>
                {/if}
            {/if}
            {if $user and $prefs.feature_user_watches eq 'y'}
                {if $user_watching_blog eq 'n'}
                    <a class="dropdown-item" href="tiki-view_blog.php?blogId={$blogId}&amp;watch_event=blog_post&amp;watch_object={$blogId}&amp;watch_action=add" onclick="confirmPopup('{tr}Monitor?{/tr}', '{ticket mode=get}')">
                        {icon name='watch'} {tr}Monitor{/tr}
                    </a>
                {else}
                    <a class="dropdown-item" href="tiki-view_blog.php?blogId={$blogId}&amp;watch_event=blog_post&amp;watch_object={$blogId}&amp;watch_action=remove" onclick="confirmPopup('{tr}Stop monitoring?{/tr}', '{ticket mode=get}')">
                        {icon name='stop_watching'} {tr}Stop monitoring{/tr}
                    </a>
                {/if}
            {/if}
            {if $prefs.feature_group_watches eq 'y' and ( $tiki_p_admin_users eq 'y' or $tiki_p_admin eq 'y' )}
                <a class="dropdown-item" href="tiki-object_watches.php?objectId={$blogId|escape:"url"}&amp;watch_event=blog_post&amp;objectType=blog&amp;objectName={$title|escape:"url"}&amp;objectHref={'tiki-view_blog.php?blogId='|cat:$blogId|escape:"url"}">
                    {icon name="watch-group"} {tr}Group Monitor{/tr}
                </a>
            {/if}
            {if $prefs.feed_blog eq "y"}
                <a class="dropdown-item"href="tiki-blog_rss.php?blogId={$blogId}">
                    {icon name='rss'} {tr}RSS{/tr}
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
        {if $user and $prefs.feature_user_watches eq 'y'}
            {if $category_watched eq 'y'}
                <div>
                    {tr}Watched by categories:{/tr}
                    {section name=i loop=$watching_categories}
                        <a href="tiki-browse_categories.php?parentId={$watching_categories[i].categId}" class="btn btn-primary btn-small">{$watching_categories[i].name|escape}</a>&nbsp;
                    {/section}
                </div>
            {/if}
        {/if}
    </div>
</div>
