{remarksbox type="tip" title="{tr}Tip{/tr}"}
    {tr}See also{/tr} <a class="alert-link" href="tiki-admin.php?page=sefurl">{tr}Search Engine Friendly URLs{/tr}</a>. {tr}Also{/tr} <a target="_blank" href="http://en.wikipedia.org/wiki/Geotagging#HTML_pages">{tr}here{/tr}</a> {tr}for more information on geo tagging.{/tr}
{/remarksbox}

<form action="tiki-admin.php?page=metatags" method="post" class="admin">
    {ticket}

    <div class="row">
        <div class="mb-3 col-lg-12 clearfix">
            {include file='admin/include_apply_top.tpl'}
        </div>
    </div>

    {tabset name="admin_metatags"}
        {tab name="{tr}Meta tags{/tr}"}
            <fieldset>
                <legend>{tr}General{/tr}</legend>
                    <div class="adminoptionbox">
                        {preference name=metatag_keywords}
                        {preference name=metatag_freetags}
                        {preference name=metatag_threadtitle}
                        {preference name=metatag_description}
                        {preference name=metatag_pagedesc}
                        {preference name=metatag_author}
                    </div>
            </fieldset>

            <fieldset>
                <legend>{tr}Twitter{/tr}</legend>
                <br>
                <div class="adminoptionbox">
                    {preference name=socialnetworks_twitter_site_name}
                    {preference name=socialnetworks_twitter_site_image}
                </div>
            </fieldset>
            <fieldset>
                <legend>{tr}Facebook{/tr}</legend>
                <br>
                <div class="adminoptionbox">
                    {preference name=socialnetworks_facebook_site_name}
                    {preference name=socialnetworks_facebook_site_image}
                </div>
            </fieldset>
        {/tab}

        {tab name="{tr}Geo meta tags{/tr}"}
            <br>

            {preference name=metatag_geoposition}
            {preference name=metatag_georegion}
            {preference name=metatag_geoplacename}
        {/tab}
        {tab name="{tr}Robots{/tr}"}
            <br>
            {* Need to show site_metatag_robots as real metatags are overridden at runtime *}

            {preference name=metatag_robots}
            {preference name=metatag_revisitafter}
        {/tab}
    {/tabset}
    {include file='admin/include_apply_bottom.tpl'}
</form>
