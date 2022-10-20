{* $Id$ *}
{remarksbox type="tip" title="{tr}Tip{/tr}"}{tr}Allows a copyright to be determined for various objects{/tr}.{/remarksbox}
<form role="form" action="tiki-admin.php?page=copyright" method="post" class="admin">
    {ticket}
    <div class="t_navbar mb-4 clearfix">
        {include file='admin/include_apply_top.tpl'}
    </div>
    <fieldset>
        <legend>{tr}Activate the feature{/tr}</legend>
        {preference name=feature_copyright visible="always"}
    </fieldset>
    <div class="adminoptionboxchild" id="feature_copyright_childcontainer">
        <fieldset>
            <legend>{tr}Features{/tr}</legend>
            {preference name=wikiLicensePage}
            {preference name=wikiSubmitNotice}
            {preference name=wiki_feature_copyrights}
            {preference name=article_feature_copyrights}
            {preference name=blog_feature_copyrights}
            {preference name=faq_feature_copyrights}
        </fieldset>
    </div>
    {include file='admin/include_apply_bottom.tpl'}
</form>
