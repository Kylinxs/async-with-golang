{* $Id$ *}

<div class="d-flex">
    <div class="me-4">
            <span class="float-start fa-stack fa-lg margin-right-18em" alt="{tr}Changes Wizard{/tr}" title="Changes Wizard">
            <i class="fas fa-arrow-circle-up fa-stack-2x"></i>
            <i class="fas fa-flip-horizontal fa-magic fa-stack-1x ms-4 mt-4"></i>
            </span>
    </div>
    <br/><br/><br/>
    <div class="flex-grow-1 ms-3">
        {tr}Main new and improved features and settings in Tiki 22.{/tr}
        <a href="https://doc.tiki.org/Tiki22" target="tikihelp" class="tikihelp text-info" title="{tr}Tiki22:{/tr}
            {tr}It is a Standard Term Support (STS) version.{/tr}
            {tr}It will be supported until Tiki 23.1 is released.{/tr}
            {tr}Some internal libraries and optional external packages have been upgraded or replaced by more updated ones.{/tr}
            <br/><br/>
            {tr}Click to read more{/tr}
        ">
            {icon name="help" size=1}
        </a>
        <fieldset class="mb-3 w-100 clearfix featurelist">
            <legend>{tr}New Features{/tr}</legend>
            {preference name='feature_system_suggestions'}
            {preference name='zend_mail_redirect'}
            <div class="adminoption mb-3 row">
                <label class="col-sm-3 col-form-label"><b>{tr}Security{/tr}</b>:</label>
                <div class="offset-sm-1 col-sm-11">
                    {tr}Shamir's Secret Sharing.{/tr}
                    <a href="https://doc.tiki.org/Shared-Secret">{tr}More Information{/tr}...</a><br/><br/>
                </div>
            </div>
            <fieldset class="mb-3 w-100 clearfix featurelist">
                <legend>{tr}New Wiki Plugins{/tr}</legend>
                {preference name=wikiplugin_totp}
            </fieldset>
        </fieldset>
        <fieldset class="mb-3 w-100 clearfix featurelist">
            <legend>{tr}Improved Plugins{/tr}</legend>
            {preference name=wikiplugin_list}
            {preference name=wikiplugin_listexecute}
            {preference name=wikiplugin_pivottable}
        </fieldset>
        <fieldset class="mb-3 w-100 clearfix featurelist">
            <legend>{tr}Other Extended Features{/tr}</legend>
            <div class="adminoption mb-3 row">
                <label class="col-sm-3 col-form-label"><b>{tr}MailIn{/tr}</b>:</label>
                <div class="offset-sm-1 col-sm-11">
                    {tr}MailIn to Files.{/tr}
                    <a href="https://doc.tiki.org/Tiki22#Mail-in_to_files">{tr}More Information{/tr}...</a><br/><br/>
                </div>
                <div class="offset-sm-1 col-sm-11">
                    {tr}MailIn to Trackers.{/tr}
                    <a href="https://doc.tiki.org/Tiki22#Mail-in_to_trackers">{tr}More Information{/tr}...</a><br/><br/>
                </div>
                <label class="col-sm-3 col-form-label"><b>{tr}Webmail{/tr}</b>:</label>
                <div class="offset-sm-1 col-sm-11">
                    {tr}Webmail contacts can be read from or stored to the user Contacts list.{/tr}
                    <a href="http://doc.tiki.org/Tiki22#Webmail_contacts">{tr}More Information{/tr}...</a><br/><br/>
                </div>
            </div>
            {preference name=feature_trackers}
            <div class="adminoptionboxchild" id="feature_trackers_childcontainer">
                <legend>{tr}General{/tr}</legend>
                <div class="col-sm-12">
                    {tr}Orphaned field names can be found more easily and non-searchable tracker fields can be excluded from the indexing{/tr}
                    <a href="http://doc.tiki.org/Tiki22#Search_orphaned_field_names">{tr}More Information{/tr}...</a><br/><br/>
                </div>
                <div class="col-sm-12">
                    {tr}Group of users can see their own items{/tr}
                    <a href="http://doc.tiki.org/Tiki22#Group_can_see_their_own_items">{tr}More Information{/tr}...</a><br/><br/>
                </div>
                <legend>{tr}New Fields{/tr}</legend>
                {preference name=trackerfield_duration}<br/>
                <legend>{tr}Other options{/tr}</legend>
                {preference name=unified_numeric_field_scroll}
            </div>
        </fieldset>
        <i>{tr}And many more improvements{/tr}.
            {tr}See the full list of changes.{/tr}</i>
        <a href="https://doc.tiki.org/Tiki22" target="tikihelp" class="tikihelp" title="{tr}Tiki22:{/tr}
            {tr}Click to read more{/tr}
        ">
            {icon name="help" size=1}
        </a>
    </div>
</div>
