
{extends 'layout_view.tpl'}

{block name="title"}
    {title}{$title|escape}{/title}
{/block}

{block name="content"}
    {remarksbox type="warning" title="{tr}Deprecated{/tr}"}
        {tr}To export tracker or tracker items please{/tr} <a href="tiki-admin.php?page=trackers&highlight=tracker_tabular_enabled" class="alert-link" target="_blank" title="{tr}enable{/tr}">{tr}enable{/tr}</a> {tr}and use{/tr} <a href="tiki-tabular-manage" class="alert-link" target="_blank" title="{tr}Tracker Import-Export{/tr}">{tr}Tracker Import-Export{/tr}</a>. {tr}It is easy to use, optimised and far more powerful.{/tr}
        {tr}For complete documentation, please visit{/tr} <a href="https://doc.tiki.org/Tracker-Tabular" class="alert-link" target="_blank" title="{tr}Tracker Import-Export{/tr}"> {tr}Tracker Import-Export{/tr} {icon name="documentation"}</a>
    {/remarksbox}
{accordion}
    {accordion_group title="{tr}Export Tracker Items{/tr}"}
    <form class="simple no-ajax" action="{service controller=tracker action=export_items trackerId=$trackerId filterfield=$filterfield filtervalue=$filtervalue}" method="post">
        <div class="mb-3 row mx-0">
            <label>{tr}Filename{/tr}</label>
            <input type="text" value="Tracker_{$trackerId|escape}.csv" disabled="disabled" class="form-control">
        </div>
        <div class="mb-3 row mx-0">
            <label for="encoding mx-0">{tr}Charset encoding{/tr}</label>
            <select name="encoding" class="form-select">
                <option value="UTF-8" selected="selected">{tr}UTF-8{/tr}</option>
                <option value="ISO-8859-1">{tr}ISO-8859-1 Latin{/tr}</option>
            </select>
        </div>
        <div class="mb-3 row mx-0">
            <label for="separator">{tr}Separator{/tr}</label>
            <input type="text" name="separator" value="," size="2" class="form-control">
        </div>
        <div class="mb-3 row mx-0">
            <label for="delimitorL">{tr}Delimitor (left){/tr}</label>
            <input type="text" name="delimitorL" value="&quot;" size="2" class="form-control">
        </div>
        <div class="mb-3 row mx-0">
            <label for="delimitorR">{tr}Delimitor (right){/tr}</label>
            <input type="text" name="delimitorR" value="&quot;" size="2" class="form-control">
        </div>
        <div class="mb-3 row mx-0">
            <label for="CR">{tr}Carriage return inside field value{/tr}</label>
            <input type="text" name="CR" value="%%%" size="4" class="form-control">
        </div>

        <div class="form-check">
            <label>
                <input type="checkbox" class="form-check-input" name="dateFormatUnixTimestamp" value="1">
                {tr}Export dates as UNIX Timestamps to facilitate importing{/tr}
            </label>
        </div>
        <div class="form-check">
            <label>
                <input type="checkbox" class="form-check-input" name="keepItemlinkId" value="1">
                {tr}Export ItemLink type fields as the itemId of the linked item (to facilitate importing){/tr}
            </label>
        </div>
        <div class="form-check">
            <label>
                <input type="checkbox" class="form-check-input" name="keepCountryId" value="1" >
                {tr}Export country type fields as the system name of the country (to facilitate importing){/tr}
            </label>
        </div>
        <div class="form-check mb-4">
            <label>
                <input type="checkbox" class="form-check-input" name="parse" value="1">
                {tr}Parse as wiki text{/tr}
            </label>
        </div>

        <fieldset>
            <legend>{tr}Generic information{/tr}</legend>
            <div class="form-check mt-0">
                <label>
                    <input type="checkbox" class="form-check-input" name="showItemId" value="1" checked="checked">
                    {tr}Item ID{/tr}
                </label>
            </div>
            <div class="form-check">
                <label>
                    <input type="checkbox" class="form-check-input" name="showStatus" value="1" checked="checked">
                    {tr}Status{/tr}
                </label>
            </div>
            <div class="form-check">
                <label>
                    <input type="checkbox" class="form-check-input" name="showCreated" value="1" checked="checked">
                    {tr}Creation date{/tr}
                </label>
            </div>
            <div class="form-check mb-4">
                <label>
                    <input type="checkbox" class="form-check-input" name="showLastModif" value="1" checked="checked">
                    {tr}Last modification date{/tr}
                </label>
            </div>
        </fieldset>

        <fieldset>
            <legend>{tr}Fields{/tr}</legend>
            {foreach from=$fields item=field}
                <div class="form-check mt-0">
                    <label>
                        <input type="checkbox" class="form-check-input" name="listfields[]" value="{$field.fieldId|escape}" checked="checked">
                        {$field.name|escape}
                    </label>
                </div>
            {/foreach}
        </fieldset>
        <div class="mb-3 row mx-0">
            <label for="recordsMax">{tr}Number of records{/tr}</label>
            <input type="number" name="recordsMax" value="{$recordsMax|escape}" class="form-control">
        </div>
        <div class="mb-3 row mx-0">
            <label for="recordsOffset">{tr}First record{/tr}</label>
            <input type="number" name="recordsOffset" value="1" class="form-control">
        </div>
        <div>
            <input type="submit" class="btn btn-primary" value="{tr}Export{/tr}">
        </div>
    </form>
    {/accordion_group}
{accordion_group title="{tr}Quick Export{/tr}"}
    <form method="post" class="simple no-ajax" action="{service controller=tracker action=dump_items trackerId=$trackerId}">
        <p>{tr}Produce a CSV with basic formatting.{/tr}</p>
        {remarksbox type="info" title="{tr}Note{/tr}" icon="bricks"}
            <p>{tr}If you use field types such as 'User Preference', 'Relations' or 'Items list/Item link', please export your items through the next section below 'Export Tracker Items'{/tr}</p>
        {/remarksbox}
        <div>
            <input type="submit" class="btn btn-primary" value="{tr}Export{/tr}">
        </div>
    </form>
{/accordion_group}
    {if isset($export)}
    {accordion_group title="{tr}Structure{/tr}"}
    <form class="simple" action="" method="post">
        <div class="mb-3 row mx-0">
            <label for="export">{tr}Tracker Export{/tr}</label>
            <textarea name="export" id="export" class="form-control" rows="20">{$export|escape}</textarea>
        </div>
        <div class="description">
            {tr}Copy the definition text above and paste into the Import Structure box for a new tracker.{/tr}
        </div>
    </form>
    {service_inline controller='tracker' action='export_fields' trackerId=$trackerId}
    {/accordion_group}
    {accordion_group title="{tr}Profile Export{/tr}"}
    <form method="post" class="simple no-ajax" action="{service controller=tracker action=export_profile trackerId=$trackerId}">
        <p>{tr}Produce YAML for a profile.{/tr}</p>
        {remarksbox type="info" title="{tr}New Feature{/tr}" icon="bricks"}
            <p><em>{tr}Please note: Experimental - work in progress{/tr}</em></p>
            <p>{tr}Linked tracker and field IDs (such as those referenced in ItemLink, ItemsList field options, for instance) are not currently converted to profile object references, so will need manual replacement.{/tr}</p>
            <p>{tr}For example: $profileobject:field_ref${/tr}</p>
        {/remarksbox}
        <div>
            <input type="submit" class="btn btn-primary" value="{tr}Export Profile{/tr}">
        </div>
    </form>
    {/accordion_group}
    {/if}
{/accordion}
{/block}