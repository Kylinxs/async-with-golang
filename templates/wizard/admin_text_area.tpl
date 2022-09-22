
{* $Id$ *}

<div class="d-flex">
    <div class="flex-shrink-0">
        <span class="fa-stack fa-lg" style="width: 100px;" title="Configuration Wizard">
            <i class="fas fa-cog fa-stack-2x"></i>
            <i class="fas fa-flip-horizontal fa-magic fa-stack-1x ms-4 mt-4"></i>
        </span>
    </div>
    <div class="flex-grow-1 ms-3">
        {icon name="admin_textarea" size=3 iclass="adminWizardIconright"}
        <h4 class="mt-0 mb-4">{tr}Set up the text area environment (Editing and Plugins){/tr}</h4>
        <fieldset>
            <legend>{tr}General settings{/tr}</legend>
            <div class="admin clearfix featurelist">
                {preference name=feature_fullscreen}
                {preference name=wiki_edit_plugin}
                {preference name=wiki_edit_icons_toggle}
                {preference name=wikipluginprefs_pending_notification}
                {if $isRTL eq false and $isHtmlMode neq true}        {* Disable Codemirror for RTL languages. It doesn't work. *}
                    {preference name=feature_syntax_highlighter}
                    {preference name=feature_syntax_highlighter_theme}
                {/if}
            </div>
            <br>
            <em>{tr}See also{/tr} <a href="tiki-admin.php?page=textarea&amp;alt=Editing+and+Plugins#content1" target="_blank">{tr}Editing and plugins admin panel{/tr}</a></em>
        </fieldset>
    </div>
</div>