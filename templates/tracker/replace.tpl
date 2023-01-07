
{extends 'layout_view.tpl'}

{block name="title"}
    {title}{$title|escape}{/title}
{/block}

{block name="content"}

<form method="post" action="{service controller=tracker action=replace}">
    {accordion}
        {accordion_group title="{tr}General{/tr}"}
            <div class="mb-3 row mx-0">
                <label for="name">{tr}Name{/tr}</label>
                <input class="form-control" type="text" name="name" id="name" value="{$info.name|escape}" required="required">
            </div>
            <div class="mb-3 row mx-0">
                <label for="description">{tr}Description{/tr}</label>
                <textarea class="form-control" name="description" id="description" cols="40">{$info.description|escape}</textarea>
            </div>
            <div class="form-check">
                <label>
                    <input type="checkbox" class="form-check-input" name="descriptionIsParsed" {if $info.descriptionIsParsed eq 'y'}checked="checked"{/if} value="1">
                    {tr}Description is wiki-parsed{/tr}
                </label>
            </div>
            <div class="mb-3 row mx-0">
                <label for="fieldPrefix">{tr}Field Prefix{/tr}</label>
                <input class="form-control" type="text" name="fieldPrefix" id="fieldPrefix" value="{$info.fieldPrefix|escape}">
                {tr}Short string prepended by default to all fields in this tracker.{/tr}
            </div>
            <div class="mb-3 row mx-0">
                <label for="permName">{tr}Permanent Name{/tr}</label>
                <input class="form-control" type="text" name="permName" id="permName" value="{$info.permName|escape}">
                {tr}Required for Advanced Shopping Cart and some other tracker features, do not change this unless you are sure.{/tr}
            </div>
            {jq}$("#name").change(function() {
    if ($("#name").val()) {
        if (! $("#fieldPrefix").val()) {
            $("#fieldPrefix").val($("#name").val().replace(/s$/, "").replace(/\W/g, "").toLowerCase());
        }
        if (! $("#permName").val()) {
            $("#permName").val($("#name").val().replace(/s$/, "").replace(/\W/g, "").toLowerCase());
        }
    }
});{/jq}
        {/accordion_group}
        {accordion_group title="{tr}Features{/tr}"}
            <div class="form-check">
                <label>
                    <input type="checkbox" class="form-check-input" name="useRatings" value="1"
                        {if $info.useRatings eq 'y'} checked="checked"{/if}>
                    {tr}Allow ratings (deprecated, use rating field){/tr}
                </label>
            </div>
            <div class="mb-3 row mx-0 depends" data-on="useRatings">
                <label for="ratingOptions">{tr}Rating options{/tr}</label>
                <input class="form-controls" type="text" name="ratingOptions" id="ratingOptions" value="{$info.ratingOptions|default:'-2,-1,0,1,2'|escape}">
            </div>
            <div class="form-check depends" data-on="useRatings">
                <label>
                    <input type="checkbox" class="form-check-input" name="showRatings" value="1"
                        {if $info.showRatings eq 'y'} checked="checked"{/if}>
                    {tr}Show ratings in listing{/tr}
                </label>
            </div>
            <div class="form-check">
                <label>
                    <input type="checkbox" class="form-check-input" name="useComments" value="1"
                        {if $info.useComments eq 'y'} checked="checked"{/if}>
                    {tr}Allow comments{/tr}
                </label>
            </div>
            <div class="form-check depends" data-on="useComments">
                <label>
                    <input type="checkbox" class="form-check-input" name="showComments" value="1"
                        {if $info.showComments eq 'y'} checked="checked"{/if}>
                    {tr}Show comments in listing{/tr}
                </label>
            </div>
            <div class="form-check depends" data-on="useComments">
                <label>
                    <input type="checkbox" class="form-check-input" name="showLastComment" value="1"
                        {if $info.showLastComment eq 'y'} checked="checked"{/if}>
                    {tr}Display last comment author and date{/tr}
                </label>
            </div>
            <div class="form-check depends" data-on="useComments">
                <label>
                    <input type="checkbox" class="form-check-input" name="saveAndComment" value="1"
                            {if $info.saveAndComment eq 'y'} checked="checked"{/if}>
                    {tr}Save and Comment{/tr}
                </label>
            </div>
            <div class="form-check">
                <label>
                    <input type="checkbox" class="form-check-input" name="useAttachments" value="1"
                        {if $info.useAttachments eq 'y'} checked="checked"{/if}>
                    {tr}Allow attachments (deprecated, use files field){/tr}
                </label>
            </div>
            <div class="form-check depends" data-on="useAttachments">
                <label>
                    <input type="checkbox" class="form-check-input" name="showAttachments" value="1"
                        {if $info.showAttachments eq 'y'} checked="checked"{/if}>
                    {tr}Display attachments in listing{/tr}
                </label>
            </div>
            <fieldset class="depends sortable" data-on="useAttachments" data-selector="div.checkbox">
                <legend>{tr}Attachment attributes (sortable){/tr}</legend>
                {foreach from=$attachmentAttributes key=name item=att}
                    <div class="form-check">
                        <label>
                            <input type="checkbox" class="form-check-input" name="orderAttachments[]" value="{$name|escape}" {if !empty($att.selected)} checked="checked"{/if}>
                            {$att.label|escape}
                        </label>
                    </div>
                {/foreach}
            </fieldset>
        {/accordion_group}
        {accordion_group title="{tr}Display{/tr}"}
            <div class="mb-3 row mx-0">
                <label class="col-form-label" for="logo">{tr}Logo{/tr}</label>
                <input class="form-control" type="text" name="logo" id="logo" value="{$info.logo|escape}">
                <div class="form-text">
                    {tr}Recommended size: 64x64px.{/tr}
                </div>
            </div>
            <div class="form-check">
                <label>
                    <input type="checkbox" class="form-check-input" name="showStatus" value="1"
                        {if $info.showStatus eq 'y'} checked="checked"{/if}>
                    {tr}Show status{/tr}
                </label>
            </div>
            <div class="form-check depends" data-on="showStatus">
                <label>
                    <input type="checkbox" class="form-check-input" name="showStatusAdminOnly" value="1"
                        {if $info.showStatusAdminOnly eq 'y'} checked="checked"{/if}>
                    {tr}Show status to tracker administrator only{/tr}
                </label>
            </div>
            <div class="form-check">
                <label>
                    <input type="checkbox" class="form-check-input" name="showCreated" value="1"
                        {if $info.showCreated eq 'y'} checked="checked"{/if}>
                    {tr}Show creation date when listing items{/tr}
                </label>
            </div>
            <div class="mb-3 row mx-0 depends" data-on="showCreated">
                <label for="showCreatedFormat">{tr}Creation date format{/tr}</label>
                <input type="text" name="showCreatedFormat" id="showCreatedFormat" value="{$info.showCreatedFormat|escape}">
                <div class="form-text">
                    <a rel="external" class="link" target="_blank" href="https://doc.tiki.org/Date-and-Time-Features">{tr}Date and Time Format Help{/tr}</a>
                </div>
            </div>
            <div class="form-check depends" data-on="showCreated">
                <label>
                    <input type="checkbox" class="form-check-input" name="showCreatedBy" value="1"
                        {if $info.showCreatedBy eq 'y'} checked="checked"{/if}>
                    {tr}Show item creator{/tr}
                </label>
            </div>
            <div class="form-check">
                <label>
                    <input type="checkbox" class="form-check-input" name="showCreatedView" value="1"
                        {if $info.showCreatedView eq 'y'} checked="checked"{/if}>
                    {tr}Show creation date when viewing items{/tr}
                </label>
            </div>
            <div class="form-check">
                <label>
                    <input type="checkbox" class="form-check-input" name="showLastModif" value="1"
                        {if $info.showLastModif eq 'y'} checked="checked"{/if}>
                    {tr}Show last modification date when listing items{/tr}
                </label>
            </div>
            <div class="form-check depends" data-on="showLastModif">
                <label>
                    <input type="checkbox" class="form-check-input" name="showLastModifBy" value="1"
                        {if $info.showLastModifBy eq 'y'} checked="checked"{/if}>
                    {tr}Show item last modifier{/tr}
                </label>
            </div>
            <div class="mb-3 row mx-0 depends" data-on="showLastModif">
                <label for="showLastModifFormat">{tr}Modification date format{/tr}</label>
                <input class="form-control" type="text" name="showLastModifFormat" id="showLastModifFormat" value="{$info.showLastModifFormat|escape}">
                <div class="form-text">
                    <a class="link" target="_blank" href="https://doc.tiki.org/Date-and-Time-Features">{tr}Date and Time Format Help{/tr}</a>
                </div>
            </div>
            <div class="form-check">
                <label>
                    <input type="checkbox" class="form-check-input" name="showLastModifView" value="1"
                        {if $info.showLastModifView eq 'y'} checked="checked"{/if}>
                    {tr}Show last modification date when viewing items{/tr}
                </label>
            </div>
            <div class="mb-3 row mx-0">
                <label for="defaultOrderKey">{tr}Default sort order{/tr}</label>
                <select name="defaultOrderKey" id="defaultOrderKey" class="form-select">
                    {foreach from=$sortFields key=k item=label}
                        <option value="{$k|escape}" {if $k eq $info.defaultOrderKey} selected="selected"{/if}>{$label|truncate:42:'...'|escape}</option>
                    {/foreach}
                </select>
            </div>
            <div class="mb-3 row mx-0">
                <label for="defaultOrderDir">{tr}Default sort direction{/tr}</label>
                <select name="defaultOrderDir" id="defaultOrderDir" class="form-select">
                    <option value="asc" {if $info.defaultOrderDir eq 'asc'}selected="selected"{/if}>{tr}ascending{/tr}</option>
                    <option value="desc" {if $info.defaultOrderDir eq 'desc'}selected="selected"{/if}>{tr}descending{/tr}</option>
                </select>
            </div>
            <div class="form-check">
                <label>
                    <input type="checkbox" class="form-check-input" name="doNotShowEmptyField" value="1"
                        {if $info.doNotShowEmptyField eq 'y'} checked="checked"{/if}>
                    {tr}Hide empty fields from item view{/tr}
                </label>
            </div>
            <div class="mb-3 row mx-0" id="fieldsDetails">
                <label for="showPopup">{tr}List detail popup{/tr}</label>
                {object_selector_multi type=trackerfield tracker_id=$info.trackerId _simplevalue=$info.showPopup _separator="," _simplename="showPopup"}
            </div>
        {/accordion_group}
    {accordion_group title="{tr}Section Format{/tr}"}
                <div class="mb-3 row mx-0">
                    <label for="sectionFormat">{tr}Section format{/tr}</label>
                    <select name="sectionFormat" id="sectionFormat" class="form-select">
                        {foreach $sectionFormats as $format => $label}
                            <option value="{$format|escape}"{if $info.sectionFormat eq $format} selected="selected"{/if}>{$label|escape}</option>
                        {/foreach}
                    </select>
                    <div class="form-text">
                        <p>{tr}Determines how headers will be rendered when using header fields as form section dividers.{/tr}</p>
                        <p>{tr}Set to <em>Configured</em> to use the four following fields.{/tr}</p>
                    </div>
                </div>
                <div class="form-check">
                    <label>
                        <input type="checkbox" class="form-check-input" name="useFormClasses" value="1" {if $info.useFormClasses eq 'y'} checked="checked"{/if}>
                        {tr}Use Form Classes{/tr}
                    </label>
                </div>
                <div class="mb-3 row mx-0">
                    <label for="formClasses">{tr}Input Form Classes{/tr}</label>
                    <input class="form-control" type="text" name="formClasses" id="formClasses" value="{$info.formClasses|escape}">
                    <div class="form-text">
                        <p>{tr}Sets classes for form to be used in Tracker Plugin (e.g., col-md-9).{/tr}</p>
                    </div>
                </div>
                <div class="mb-3 row mx-0">
                    <label for="viewItemPretty">{tr}Template to display an item{/tr}</label>
                    <input class="form-control" type="text" name="viewItemPretty" id="viewItemPretty" value="{$info.viewItemPretty|escape}">
                    <div class="form-text">
                        {tr}wiki:pageName for a wiki page or tpl:tplName for a template{/tr}
                    </div>
                </div>
                <div class="mb-3 row mx-0">
                    <label for="editItemPretty">{tr}Template to edit an item{/tr}</label>
                    <input class="form-control" type="text" name="editItemPretty" id="editItemPretty" value="{$info.editItemPretty|escape}">
                    <div class="form-text">
                        {tr}wiki:pageName for a wiki page or tpl:tplName for a template{/tr}
                    </div>
                </div>
    {/accordion_group}
        {accordion_group title="{tr}Status{/tr}"}
            <div class="mb-3 row mx-0">
                <label for="newItemStatus">{tr}New item status{/tr}</label>
                <select name="newItemStatus" id="newItemStatus" class="form-select">
                    {foreach key=st item=stdata from=$statusTypes}
                        <option value="{$st|escape}"
                            {if $st eq $info.newItemStatus} selected="selected"{/if}>
                            {$stdata.label|escape}
                        </option>
                    {/foreach}
                </select>
            </div>
            <div class="mb-3 row mx-0">
                <label for="modItemStatus">{tr}Modified item status{/tr}</label>
                <select name="modItemStatus" id="modItemStatus" class="form-control">
                    <option value="">{tr}No change{/tr}</option>
                    {foreach key=st item=stdata from=$statusTypes}
                        <option value="{$st|escape}"
                            {if $st eq $info.modItemStatus} selected="selected"{/if}>
                            {$stdata.label|escape}
                        </option>
                    {/foreach}
                </select>
            </div>
            <div class="mb-3 row mx-0">
                <label>{tr}Default status displayed in list mode{/tr}</label>

                    {foreach key=st item=stdata from=$statusTypes}
                <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            {$stdata.label|escape}
                        </label>
                        <input type="checkbox" class="form-check-input" name="defaultStatus[]" value="{$st|escape}"{if in_array($st, $statusList)} checked="checked"{/if}>
                </div>
                    {/foreach}

            </div>
        {/accordion_group}
        {accordion_group title="{tr}Notifications{/tr}"}
            <div class="mb-3 row mx-0">
                <label for="outboundEmail">{tr}Copy activity to email{/tr}</label>
                <input name="outboundEmail" id="outboundEmail" value="{$info.outboundEmail|escape}" class="email_multi form-control" size="60">
                <div class="form-text">
                    {tr}You can add several email addresses by separating them with commas.{/tr}
                </div>
            </div>
            <div class="form-check">
                <label>
                    <input type="checkbox" class="form-check-input" name="simpleEmail" value="1"
                        {if $info.simpleEmail eq 'y'} checked="checked"{/if}>
                    {tr}Use simplified email format{/tr}
                </label>
                <div class="description form-text mb-4">
                    {tr}The tracker will use the text field named Subject if any as subject and will use the user email or for anonymous the email field if any as sender{/tr}
                </div>
            </div>
            <div class="mb-3 row mx-0">
                <label for="notifyOn">{tr}Copy activity to email only on{/tr}</label>
                <select name="notifyOn" id="notifyOn" class="form-select">
            <option value="both" {if $info.notifyOn eq 'both'}selected="selected"{/if}>{tr}Creation and Update (default){/tr}</option>
                    <option value="creation" {if $info.notifyOn eq 'creation'}selected="selected"{/if}>{tr}Item creation{/tr}</option>
                    <option value="update" {if $info.notifyOn eq 'update'}selected="selected"{/if}>{tr}Item update{/tr}</option>
                </select>
            </div>
            <div class="form-check">
                <label>
                    <input type="checkbox" class="form-check-input" name="publishRSS" value="1"
                        {if $prefs.feed_tracker neq 'y'}disabled="disabled"{/if}
                        {if $info.publishRSS eq 'y'}checked="checked"{/if}>
                    {tr}Publish RSS feed for this tracker{/tr}
                </label>
                <div class="description form-text">
                    {tr}Requires "RSS per tracker" to be set in Admin/RSS{/tr}
                    {if $prefs.feed_tracker eq 'y'}
                        {tr}(Currently set){/tr}
                    {else}
                        {tr}(Currently not set){/tr}
                    {/if}
                </div>
            </div>

            {if $prefs.feature_groupalert eq 'y'}
                <div class="mb-3 row mx-0">
                    <label class="col-form-label" for="groupforAlert">{tr}Group alerted on item modification{/tr}</label>
                    <select name="groupforAlert" id="groupforAlert" class="form-control">
                        <option value=""></option>
                        {foreach from=$groupList item=g}
                            <option value="{$g|escape}" {if $g eq $groupforAlert}selected="selected"{/if}>{$g|escape}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="showeachuser" id="showeachuser" value="1"
                        {if $showeachuser eq 'y'}checked="checked"{/if}>
                    <label for="showeachuser">{tr}Allow user selection for small groups{/tr}</label>
                </div>
            {/if}
        {/accordion_group}
        {accordion_group title="{tr}Permissions{/tr}"}
            <div class="form-check">
                <label>
                    <input type="checkbox" class="form-check-input" name="userCanSeeOwn" value="1"
                        {if $info.userCanSeeOwn eq 'y'}checked="checked"{/if}>
                    {tr}User can see his own items{/tr}
                </label>
                <div class="description form-text mb-4">
                    {tr}The tracker needs a user field with the item-owner activated{/tr}.
                    {tr}No extra permission is needed at the tracker permissions level to allow a user to see just his own items through Plugin TrackerList with the param view=user{/tr}
                </div>
            </div>
            <div class="form-check">
                <label>
                    <input type="checkbox" class="form-check-input" name="groupCanSeeOwn" value="1"
                        {if $info.groupCanSeeOwn eq 'y'}checked="checked"{/if}>
                    {tr}Group can see their own items{/tr}
                </label>
                <div class="description form-text mb-4">
                    {tr}The tracker needs a group field with the item-owner activated{/tr}.
                    {tr}No extra permission is needed at the tracker permissions level to allow a group of users to see just their own items through Plugin TrackerList with the param view=group{/tr}
                </div>
            </div>
            <div class="form-check">
                <label>
                    <input type="checkbox" class="form-check-input" name="writerCanModify" value="1"
                        {if $info.writerCanModify eq 'y'}checked="checked"{/if}>
                    {tr}Item creator can modify his items{/tr}
                </label>
                <div class="description form-text mb-4">
                    {tr}The tracker needs a user field with the auto-assign activated{/tr}
                </div>
            </div>
            <div class="form-check">
                <label>
                    <input type="checkbox" class="form-check-input" name="writerCanRemove" value="1"
                        {if $info.writerCanRemove eq 'y'}checked="checked"{/if}>
                    {tr}Item creator can remove his items{/tr}
                </label>
                <div class="description form-text mb-4">
                    {tr}The tracker needs a user field with the auto-assign activated{/tr}
                </div>
            </div>
            <div class="form-check">
                <label>
                    <input type="checkbox" class="form-check-input" name="userCanTakeOwnership" value="1"
                        {if $info.userCanTakeOwnership eq 'y'}checked="checked"{/if}>
                    {tr}User can take ownership of item created by anonymous{/tr}
                </label>
            </div>
            <div class="form-check">
                <label>
                    <input type="checkbox" class="form-check-input" name="oneUserItem" value="1"
                        {if $info.oneUserItem eq 'y'}checked="checked"{/if}>
                    {tr}Only one item per user or IP{/tr}
                </label>
                <div class="description form-text mb-4">
                    {tr}The tracker needs a user or IP address field with the auto-assign set to Creator{/tr}
                </div>
            </div>
            <div class="form-check">
                <label>
                    <input type="checkbox" class="form-check-input" name="writerGroupCanModify" value="1"
                        {if $info.writerGroupCanModify eq 'y'}checked="checked"{/if}>
                    {tr}Members of the creator group can modify items{/tr}
                </label>
                <div class="description form-text mb-4">
                    {tr}The tracker needs a group field with the auto-assign activated{/tr}
                </div>
            </div>
            <div class="form-check">
                <label>
                    <input type="checkbox" class="form-check-input" name="writerGroupCanRemove" value="1"
                        {if $info.writerGroupCanRemove eq 'y'}checked="checked"{/if}>
                    {tr}Members of the creator group can remove items{/tr}
                </label>
                <div class="description form-text mb-4">
                    {tr}The tracker needs a group field with the auto-assign activated{/tr}
                </div>
            </div>
            <div class="form-check">
                <label>
                    <input type="checkbox" class="form-check-input" name="adminOnlyViewEditItem" value="1"
                        {if $info.adminOnlyViewEditItem eq 'y'} checked="checked"{/if}>
                    {tr}Restrict non admins to wiki page access only{/tr}
                </label>
                <div class="description form-text mb-4">
                    {tr}Only users with admin tracker permission (tiki_p_admin_trackers) can use the built-in tracker interfaces (tiki-view_tracker.php and tiki-view_tracker_item.php). This is useful if you want the users of these trackers to only access them via wiki pages, where you can use the various tracker plugins to embed forms and reports.{/tr}
                </div>
            </div>
            <fieldset>
                <legend>{tr}Creation date constraint{/tr}</legend>
                <div class="description form-text mb-4">
                    {tr}The tracker will be <strong>open</strong> for non-admin users through wiki pages with PluginTracker <strong>only</strong> during the period 'After' the start date and/or 'Before' the end date set below{/tr}.
                </div>
                <div class="mb-3 row mx-0 depends" data-on="start">
                    <label for="startDate">{tr}Date{/tr}</label>
                    <input type="date" name="startDate" id="startDate" value="{$startDate|escape}" class="form-control">
                </div>
                <div class="mb-3 row mx-0 depends" data-on="start">
                    <label for="startTime">{tr}Time{/tr}</label>
                    <input type="time" name="startTime" id="startTime" value="{$startTime|default:'00:00'|escape}" class="form-control">
                </div>
                <div class="form-check">
                    <label>
                        <input type="checkbox" class="form-check-input" name="end" value="1"
                            {if !empty($info.end)}checked="checked"{/if}>
                        {tr}Before{/tr}
                    </label>
                </div>
                <div class="mb-3 row mx-0 depends" data-on="end">
                    <label for="endDate">{tr}Date{/tr}</label>
                    <input type="date" name="endDate" id="endDate" value="{$endDate|escape}" class="form-control">
                </div>
                <div class="mb-3 row mx-0 depends" data-on="end">
                    <label for="endTime">{tr}Time{/tr}</label>
                    <input type="time" name="endTime" id="endTime" value="{$endTime|default:'00:00'|escape}" class="form-control">
                </div>
            </fieldset>
        {/accordion_group}
        {if $prefs.feature_categories eq 'y'}
            {accordion_group title="{tr}Categories{/tr}"}
                <div class="mb-3 row mx-0">
                    {include file='categorize.tpl' notable=y auto=y}
                </div>
                <div class="form-check">
                    <label>
                        <input type="checkbox" class="form-check-input" name="autoCreateCategories" value="1"
                            {if $info.autoCreateCategories eq 'y'}checked="checked"{/if}>
                        {tr}Auto-create corresponding categories{/tr}
                    </label>
                </div>
            {/accordion_group}
        {/if}
        {if $prefs.groupTracker eq 'y'}
            {accordion_group title="{tr}Groups{/tr}"}
                <div class="form-check">
                    <label>
                        <input type="checkbox" class="form-check-input" name="autoCreateGroup" value="1"
                            {if $info.autoCreateGroup eq 'y'} checked="checked"{/if}>
                        {tr}Create a group for each item{/tr}
                    </label>
                </div>
                <div class="mb-3 row mx-0 depends" data-on="autoCreateGroup">
                    <label for="autoCreateGroupInc">{tr}Groups will include{/tr}</label>
                    <select name="autoCreateGroupInc" id="autoCreateGroupInc" class="form-control">
                        <option value=""></option>
                        {foreach from=$groupList item=g}
                            <option value="{$g|escape}" {if $g eq $info.autoCreateGroupInc}selected="selected"{/if}>{$g|escape}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="form-check depends" data-on="autoCreateGroup">
                    <label>
                        <input type="checkbox" class="form-check-input" name="autoAssignCreatorGroup" value="1"
                            {if $info.autoAssignCreatorGroup eq 'y'} checked="checked"{/if}>
                        {tr}Creator is assigned to the group{/tr}
                    </label>
                </div>
                <div class="form-check depends" data-on="autoCreateGroup">
                    <label>
                        <input type="checkbox" class="form-check-input" name="autoAssignCreatorGroupDefault" value="1"
                            {if $info.autoAssignCreatorGroupDefault eq 'y'} checked="checked"{/if}>
                        {tr}Will become the creator's default group{/tr}
                    </label>
                </div>
                <div class="form-check depends" data-on="autoCreateGroup">
                    <label>
                        <input type="checkbox" class="form-check-input" name="autoAssignGroupItem" value="1"
                            {if $info.autoAssignGroupItem eq 'y'} checked="checked"{/if}>
                        {tr}Will become the new item's group creator{/tr}
                    </label>
                </div>
                <div class="form-check depends" data-on="autoCreateGroup">
                    <label>
                        <input type="checkbox" class="form-check-input" name="autoCopyGroup" value="1"
                            {if $info.autoCopyGroup eq 'y'} checked="checked"{/if}>
                        {tr}Copy the default group in the field ID before updating the group{/tr}
                    </label>
                </div>
            {/accordion_group}
        {/if}
        {if $prefs.tracker_tabular_enabled eq 'y' and $remoteTabulars}
            {accordion_group title="{tr}Remote synchronization{/tr}"}
                <div class="mb-3 row mx-0">
                    <label for="tabularSync">{tr}Choose import-export format{/tr}</label>
                    <select name="tabularSync" id="tabularSync" class="form-control">
                        <option value="">{tr}None{/tr}</option>
                        {foreach item=tabular from=$remoteTabulars}
                            <option value="{$tabular.tabularId|escape}"
                                {if $tabular.tabularId eq $info.tabularSync} selected="selected"{/if}>
                                {$tabular.name|escape}
                            </option>
                        {/foreach}
                    </select>
                </div>
                <div class="mb-3 row depends" data-on="tabularSync">
                    <label for="tabularSyncModifiedField">
                        {tr}Last revision/modification field{/tr}
                        <a class="tikihelp text-info" title="{tr}Field selection:{/tr} {tr}Choose one of the tracker fields if remote items update its value every time a change happens. This will ensure only updated items get synchronized when importing from remote source.{/tr}">
                            {icon name=information}
                        </a>
                    </label>
                    <div style="width: 100%">
                        {object_selector type=trackerfield tracker_id=$info.trackerId _simplevalue=$info.tabularSyncModifiedField _simplename="tabularSyncModifiedField"}
                    </div>
                </div>
                <div class="mb-3 row depends" data-on="tabularSyncModifiedField">
                    <label for="tabularSyncLastImport">
                        {tr}Last import time{/tr}
                        <a class="tikihelp text-info" title="{tr}Time entry:{/tr} {tr}This tracks the last date/time when this tracker was synchronized with remote source. Subsequent import-export imports will only fetch content newer than this date. Reset to something in the past if you want to re-import.{/tr}">
                            {icon name=information}
                        </a>
                    </label>
                    <div style="width: 100%">
                        {if empty($info.tabularSyncLastImport)}{assign var="tabularSyncLastImport" value="0"}{else}{assign var="tabularSyncLastImport" value=$info.tabularSyncLastImport}{/if}
                        {jscalendar id="tabularSyncLastImport" date=$tabularSyncLastImport fieldname="tabularSyncLastImport" showtime='y' isutc=0}
                    </div>
                </div>
            {/accordion_group}
        {/if}
    {/accordion}
    <div class="mb-3 row mx-0 submit">
        <input type="hidden" name="confirm" value="1">
        <input type="hidden" name="trackerId" value="{$trackerId|escape}">
        <input type="submit" class="btn btn-primary" value="{tr}Save{/tr}">
    </div>
</form>
    {/block}