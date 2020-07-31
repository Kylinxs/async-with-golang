{* $Id$ *}
<div class="d-flex">
    <div class="me-4">
        <span class="fa-stack fa-lg" style="width: 100px;" title="Configuration Wizard">
            <i class="fas fa-cog fa-stack-2x"></i>
            <i class="fas fa-flip-horizontal fa-magic fa-stack-1x ms-4 mt-4"></i>
        </span>
    </div>
    <div class="flex-grow-1 ms-3">
        {icon name="admin_category" size=3 iclass="float-sm-end"}
        <h4 class="mt-0 mb-4">{tr}Global content category system</h4>
        Items of different types (wiki pages, articles, tracker items, etc) can be added to one or more categories. Permissions set for a category will apply to all items in that category, allowing access to be restricted to certain groups, users, etc{/tr}.
        <fieldset>
            <legend>{tr}Categories{/tr}</legend>
            {tr}Categories are set up in the admin categories panel. Please see the Categories item in the Admin menu{/tr}.<br>
            <br>
            {tr}or{/tr} <a href="tiki-admin_categories.php" target="_blank">{tr}Set up categories here{/tr}</a><br>
            <br>
            {if $prefs['flaggedrev_approval'] eq 'y' && empty($prefs['flaggedrev_approval_categories'])}
                {remarksbox type="info" title="{tr}Info{/tr}"}
                    {tr}You have the feature '<strong>Revision Approval</strong>' enabled, but you haven't defined yet which content categories require revision approval for their wiki pages{/tr}.
                    {tr}Once you have <a href="tiki-admin_categories.php" class="alert-link" target="_blank">some categories defined</a>, go back to the Configuration Wizard step '<strong>Set up Wiki environment</strong>' and define them there{/tr}.
                {/remarksbox}
            {/if}
            <br>
            <em>{tr}See also{/tr} <a href="http://doc.tiki.org/category" target="_blank">{tr}Categories{/tr} @ doc.tiki.org</a></em>
        </fieldset>
        <br>
    </div>
</div>
