{* $Id$ *}
<form action="tiki-admin.php?page=category" method="post" class="admin">
    {ticket}
    <div class="row">
        <div class="mb-3 col-lg-12 clearfix">
            <a role="link" class="btn btn-link tips" href="tiki-browse_categories.php" title=":{tr}Categories listing{/tr}">
                {icon name="list"} {tr}Browse Categories{/tr}
            </a>
            <a role="link" class="btn btn-link tips" href="tiki-admin_categories.php" title=":{tr}Administration{/tr}">
                {icon name="cog"} {tr}Administer Categories{/tr}
            </a>
            <a role="link" class="btn btn-link tips" href="tiki-edit_categories.php" title=":{tr}Organize objects{/tr}">
                {icon name="sort"} {tr}Organize Objects{/tr}
            </a>
            {include file='admin/include_apply_top.tpl'}
        </div>
    </div>

    <fieldset id="Categories">
        <legend>{tr}Activate the feature{/tr}</legend>
        {preference name=feature_categories visible="always"}
    </fieldset>

    <fieldset class="mb-3 w-100">
        <legend>{tr}Plugins{/tr} {help url="Plugins"}</legend>
        {preference name=wikiplugin_category}
        {preference name=wikiplugin_catpath}
        {preference name=wikiplugin_catorphans}
    </fieldset>

    <fieldset class="mb-3 w-100">
        <legend>{tr}Inline plugins{/tr}</legend>
        {preference name=wikiplugininline_category}
        {preference name=wikiplugininline_categorytransition}
        {preference name=wikiplugininline_catorphans}
        {preference name=wikiplugininline_catpath}
    </fieldset>

    <fieldset>
        <legend>
            {tr}Features{/tr}
        </legend>
        {preference name=feature_categorypath}
        <div class="adminoptionboxchild" id="feature_categorypath_childcontainer">
            {preference name=categorypath_excluded}
            {preference name=categorypath_format}
        </div>

        {preference name=category_sort_ascii}
        <fieldset>
            <legend>
                {tr}Category objects{/tr}
            </legend>
            {preference name=feature_categoryobjects}
            {preference name=flaggedrev_approval_categories}
            {preference name=category_morelikethis_algorithm}
            {preference name=category_morelikethis_mincommon}
            {preference name=category_morelikethis_mincommon_orless}
            {preference name=category_morelikethis_mincommon_max}
        </fieldset>

        {preference name=feature_category_transition}
        <div class="adminoptionboxchild" id="feature_category_transition_childcontainer">
            {preference name=wikiplugin_categorytransition}
        </div>

        {preference name=categories_used_in_tpl}
        {preference name=categories_add_class_to_body_tag}
        {preference name=unified_excluded_categories}
        {preference name=categories_cache_refresh_on_object_cat}
        {preference name=category_custom_facets}

        <div class="adminoptionboxchild" id="categories_used_in_tpl_childcontainer">
            {preference name=feature_areas}
            {preference name=areas_root}
        </div>
        {preference name=search_show_category_filter}
        {preference name=category_jail}
        {preference name=category_jail_root}
        {preference name=category_defaults}
        <div class="mb-3 row">
            <div class="col-sm-8 offset-sm-4">
                {if !empty($prefs.category_defaults)}
                    <button type="submit" class="btn btn-primary" name="assignWikiCategories" value="y">
                        {tr}Re-apply last saved category defaults to wiki pages{/tr}
                    </button>
                {/if}
            </div>
        </div>

        {preference name=category_autogeocode_within}
        <div class="adminoptionboxchild" id="category_autogeocode_within_childcontainer">
            {preference name=category_autogeocode_replace}
            {preference name=category_autogeocode_fudge}
        </div>

        {preference name=category_i18n_sync}
        <div class="adminoptionboxchild category_i18n_sync_childcontainer blacklist whitelist required">
            {preference name=category_i18n_synced}
        </div>

        {preference name=workspace_root_category}
        {preference name=multidomain_default_not_categorized}
        {preference name=feature_wiki_mandatory_category}
        {preference name=feature_blog_mandatory_category}
        {preference name=unified_add_to_categ_search}

    </fieldset>

    <fieldset>
        <legend>{tr}Theme{/tr}</legend>
        {preference name=feature_theme_control_autocategorize}
        {preference name=feature_theme_control_parentcategory}
        {preference name=feature_theme_control_savesession}
    </fieldset>

    <fieldset>
        <legend>{tr}Tracker{/tr}</legend>
        {preference name=trackerfield_category}
        {preference name=unified_trackeritem_category_names}
    </fieldset>

    <fieldset>
        <legend>{tr}Forum{/tr}</legend>
        {preference name=forum_category_selector_in_list}
        {preference name=forum_available_categories}
    </fieldset>

    <fieldset>
        <legend>{tr}Wiki{/tr}</legend>
        {preference name=wiki_list_categories}
        {preference name=wiki_list_categories_path}
    </fieldset>

    <fieldset>
        <legend>{tr}Performance{/tr}</legend>
        {preference name=feature_search_show_forbidden_cat}
        {preference name=category_browse_count_objects}
    </fieldset>

    <fieldset>
        <legend>{tr}Poll{/tr}</legend>
            {preference name=poll_list_categories}
    </fieldset>

    <fieldset>
        <legend>{tr}Structure{/tr}</legend>
            {preference name=feature_wiki_categorize_structure}
    </fieldset>

    {include file='admin/include_apply_bottom.tpl'}
</form>
