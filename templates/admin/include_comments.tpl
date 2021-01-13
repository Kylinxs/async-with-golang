
{* $Id$ *}
<form action="tiki-admin.php?page=comments" method="post" class="admin">
    {ticket}
    <div class="row">
        <div class="mb-3 col-lg-12 clearfix">
            <a role="link" href="tiki-list_comments.php" class="btn btn-link tips" title=":{tr}Comments listing{/tr}">{icon name="list"} {tr}Comments{/tr}</a>
            {permission_link mode=link addclass="btn btn-link tips" _icon_name="key" textFilter=comment showDisabled=y}
            {include file='admin/include_apply_top.tpl'}
        </div>
    </div>
    <fieldset id="Comments">
        <legend>{tr}Site-wide features{/tr}</legend>
        <div class="admin featurelist">
            {preference name=feature_comments_moderation}
            {preference name=feature_comments_locking}
            {preference name=feature_comments_send_author_name}
            {preference name=feature_comments_post_as_anonymous}
            {preference name=comments_vote}
            {preference name=comments_archive}
            {preference name=comments_allow_correction}
            <div class="adminoptionboxchild" id="comments_allow_correction_childcontainer">
                {preference name=comments_correction_timeout}
            </div>
            {preference name=comments_akismet_filter}
            {preference name=tracker_show_comments_below}
            <div class="adminoptionboxchild" id="comments_akismet_filter_childcontainer">
                {preference name=comments_akismet_apikey}
                {preference name=comments_akismet_check_users}
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend>{tr}Display options{/tr}</legend>
        <div class="admin featurelist">
            {preference name=comments_per_page}
            {preference name=comments_sort_mode}
            {preference name=comments_threshold_indent}
            {preference name=comments_notitle}
            {preference name=comments_heading_links}
            {preference name=section_comments_parse}
            {preference name=comments_field_email}
            {preference name=comments_field_website}
            {preference name=default_rows_textarea_comment}
        </div>
    </fieldset>
    <fieldset>
        <legend>{tr}Annotations{/tr}</legend>
        {preference name=feature_inline_comments}
        <div class="adminoptionboxchild" id="feature_inline_comments_childcontainer">
            {preference name=comments_inline_annotator}
        </div>
    </fieldset>
    <fieldset>
        <legend>{tr}Using comments in various features{/tr}</legend>
        {preference name=feature_article_comments}
        {preference name=feature_wiki_comments}
        <div class="adminoptionboxchild" id="feature_wiki_comments_childcontainer">
            {preference name=wiki_comments_displayed_default}
            {preference name=wiki_comments_form_displayed_default}
            {preference name=wiki_comments_per_page}
            {preference name=wiki_comments_default_ordering}
            {preference name=wiki_comments_allow_per_page}
            {preference name=wiki_watch_comments}
            {preference name=wiki_comments_print}
        </div>
        {preference name=feature_blogposts_comments}
        {preference name=feature_file_galleries_comments}
        <div class="adminoptionboxchild" id="feature_file_galleries_comments_childcontainer">
            {preference name='file_galleries_comments_per_page'}
            {preference name='file_galleries_comments_default_ordering'}
        </div>
        {preference name=feature_poll_comments}
        {preference name=feature_faq_comments}
        {preference name=wikiplugin_trackercomments}
    </fieldset>
    {include file='admin/include_apply_bottom.tpl'}
</form>