{* $Id$ *}
{title help="Blogs" admpage="blogs"}{tr}Blogs{/tr}{/title}

<div class="t_navbar mb-4">
    {if $tiki_p_create_blogs eq 'y' or $tiki_p_blog_admin eq 'y'}
        {button href="tiki-edit_blog.php" _icon_name="create" _text="{tr}Create Blog{/tr}" _type="link" class="btn btn-link"}
        {if $tiki_p_read_blog eq 'y' and $tiki_p_blog_admin eq 'y'}
            {button href="tiki-list_posts.php" _type="link" class="btn btn-link" _icon_name="list" _text="{tr}List Posts{/tr}"}
        {/if}
    {/if}
</div>

{if $listpages or ($find ne '')}
    {include file='find.tpl'}
{/if}

<div class="{if $js}table-responsive{/if}"> {*the table-responsive class cuts off dropdown menus *}
    <table class="table table-striped normal">
        {assign var=numbercol value=0}
        <tr>
            {if $prefs.blog_list_title eq 'y' or $prefs.blog_list_description eq 'y'}
                {assign var=numbercol value=$numbercol+1}
                <th><a href="tiki-list_blogs.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'title_desc'}title_asc{else}title_desc{/if}">{tr}Blog{/tr}</a></th>
            {/if}
            {if $prefs.blog_list_created eq 'y'}
                {assign var=numbercol value=$numbercol+1}
                <th><a href="tiki-list_blogs.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'created_desc'}created_asc{else}created_desc{/if}">{tr}Created{/tr}</a></th>
            {/if}
            {if $prefs.blog_list_lastmodif eq 'y'}
                {assign var=numbercol value=$numbercol+1}
                <th><a href="tiki-list_blogs.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'lastModif_desc'}lastModif_asc{else}lastModif_desc{/if}">{tr}Last post{/tr}</a></th>
            {/if}
            {if $prefs.blog_list_user ne 'disabled'}
                {assign var=numbercol value=$numbercol+1}
                <th><a href="tiki-list_blogs.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'user_desc'}user_asc{else}user_desc{/if}">{tr}User{/tr}</a></th>
            {/if}
            {if $prefs.blog_list_posts eq 'y'}
                {assign var=numbercol value=$numbercol+1}
                <th class="text-end"><a href="tiki-list_blogs.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'posts_desc'}posts_asc{else}posts_desc{/if}">{tr}Posts{/tr}</a></th>
            {/if}
            {if $prefs.blog_list_visits eq 'y'}
                {assign var=numbercol value=$numbercol+1}
                <th class="text-end"><a href="tiki-list_blogs.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'hits_desc'}hits_asc{else}hits_desc{/if}">{tr}Visits{/tr}</a></th>
            {/if}
            {if $prefs.blog_list_activity eq 'y'}
                {assign var=numberco