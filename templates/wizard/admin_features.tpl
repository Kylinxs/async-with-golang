{* $Id$ *}

<div class="d-flex">
    <div class="flex-shrink-0">
        <span class="fa-stack fa-lg" style="width: 100px;" title="Configuration Wizard">
            <i class="fas fa-cog fa-stack-2x"></i>
            <i class="fas fa-flip-horizontal fa-magic fa-stack-1x ms-4 mt-4"></i>
        </span>
    </div>
    <div class="flex-grow-1 ms-3">
        {icon name="admin_features" size=3 iclass="float-sm-end"}
        <h4 class="mt-0 mb-4">{tr}Set up the main Tiki features. The wiki and file gallery features are always enabled.{/tr}</h4>
        <fieldset>
            <legend>{tr}Main Tiki features{/tr}</legend>
            {* preference name=feature_wiki *}
            {* preference name=feature_file_galleries *}
            <div class="admin clearfix featurelist">
                {preference name=feature_blogs}
                {preference name=feature_articles}
                {preference name=feature_forums}
                {preference name=feature_trackers}
                {preference name=feature_polls}
                {preference name=feature_sheet}
                {preference name=feature_calendar}
                {preference name=feature_newsletters}
                {preference name=feature_banners}
                {preference name=feature_freetags}
            </div>
            <br>
            <em>{tr}Tiki has many more features{/tr}.
            {tr}See also{/tr} <a href="http://doc.tiki.org/Global+Features" target="_blank">{tr}Global Features{/tr} @ doc.tiki.org</a></em>
        </fieldset>
        <fieldset>
            <legend>{tr}Watches{/tr}</legend>
            {icon name="envelope-o" size=2 iclass="float-sm-end"}
            {tr}Enable email notifications to users when changes in the content of specific items (pages, posts, trackers, etc.) are made{/tr}.
            <div class="admin clearfix featurelist">
                {preference name=feature_user_watches}
                {preference name=feature_group_watches}
                {preference name=feature_daily_report_watches}
                <div class="adminoptionboxchild" id="feature_daily_report_watches_childcontainer">
                    {preference name=dailyreports_enabled_for_new_users}
                </div>
                {if $isMultiLanguage eq true}
                    {preference name=feature_user_watches_translations}
                    {preference name=feature_user_watches_languages}
                {/if}
            </div>
        </fieldset>
    </div>
</div>
