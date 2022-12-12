
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
        {tr}Main new and improved features and settings in Tiki 17{/tr}.
        <a href="http://doc.tiki.org/Tiki17" target="tikihelp" class="tikihelp" title="{tr}Tiki17:{/tr}
            {tr}Tiki17 is a standard non-LTS version{/tr}.
            {tr}It will be supported until 18.1 is released{/tr}.
            {tr}Many libraries have been upgraded{/tr}.
            <br/><br/>
            {tr}Click to read more{/tr}
        ">
            {icon name="help" size=1}
        </a>
        <fieldset>
            <legend>{tr}H5P{/tr}</legend>
            {preference name='h5p_enabled'}
            <div class="adminoptionboxchild" id="h5p_enabled_childcontainer">
                {preference name='h5p_filegal_id'}
                {preference name='h5p_whitelist'}
                {preference name='h5p_dev_mode'}
                {preference name='h5p_track_user'}
                {preference name='h5p_save_content_state'}
                <div class="adminoptionboxchild" id="h5p_save_content_state_childcontainer">
                    {preference name='h5p_save_content_frequency'}
                </div>
                {preference name='h5p_export'}
            </div>
        </fieldset>
        <fieldset class="mb-3 w-100 clearfix featurelist">
            <legend>{tr}New Wiki Plugins{/tr}</legend>
            {preference name=wikiplugin_benchmark}
            {preference name=wikiplugin_casperjs}
            {preference name=wikiplugin_fluidgrid}
            {preference name=wikiplugin_h5p}
            {preference name=wikiplugin_metatag}
            {preference name=wikiplugin_pdf}
            {preference name=wikiplugin_pdfpagebreak}
            {preference name=wikiplugin_shorten}
        </fieldset>
        <fieldset class="mb-3 w-100 clearfix featurelist">
            <legend>{tr}Improved Plugins{/tr}</legend>
            {preference name=wikiplugin_html}
            {preference name=wikiplugin_list}
            {preference name=wikiplugin_list_gui}
            {preference name=wikiplugin_listexecute}
        </fieldset>
        <fieldset class="mb-3 w-100 clearfix featurelist">
            <legend>{tr}HTTP Header Options{/tr}</legend>
            {preference name=http_header_frame_options}
            <div class="adminoptionboxchild" id="http_header_frame_options_childcontainer">
                {preference name=http_header_frame_options_value}
            </div>

            {preference name=http_header_xss_protection}
            <div class="adminoptionboxchild" id="http_header_xss_protection_childcontainer">
                {preference name=http_header_xss_protection_value}
            </div>

            {preference name=http_header_content_type_options}

            {preference name=http_header_content_security_policy}
            <div class="adminoptionboxchild" id="http_header_content_security_policy_childcontainer">
                {preference name=http_header_content_security_policy_value}
            </div>

            {preference name=http_header_strict_transport_security}
            <div class="adminoptionboxchild" id="http_header_strict_transport_security_childcontainer">
                {preference name=http_header_strict_transport_security_value}
            </div>

            {preference name=http_header_public_key_pins}
            <div class="adminoptionboxchild" id="http_header_public_key_pins_childcontainer">
                {preference name=http_header_public_key_pins_value}
            </div>

        </fieldset>
        <fieldset class="mb-3 w-100 clearfix featurelist">
            <legend>{tr}Intrusion Detection System (IDS) with Expose{/tr}</legend>
            {preference name=ids_enabled}
            <div class="adminoptionboxchild" id="ids_enabled_childcontainer">
                <div class="mb-3 adminoptionbox clearfix">
                    <div class="offset-sm-4 col-sm-8">
                        <a href="tiki-admin_ids.php">{tr}Admin IDS custom rules{/tr}</a>
                    </div>
                </div>
                {preference name=ids_log_to_file}
                {*{preference name=ids_log_to_database}*}
            </div>
        </fieldset>
        <fieldset>
            <legend>{tr}Web Cron{/tr}</legend>
            <div class="adminoptionbox">
                {preference name=webcron_enabled}
                <div class="adminoptionboxchild" id="webcron_enabled_childcontainer">
                    {preference name=webcron_type}
                    {preference name=webcron_run_interval}
                    {preference name=webcron_token}
                </div>
            </div>
        </fieldset>
        <fieldset class="mb-3 w-100 clearfix featurelist">
            <legend>{tr}Other new or Extended Features{/tr}</legend>
            {*preference name=feature_scheduler*}
            {preference name=xmpp_feature}
            <div class="adminoptionboxchild" id="xmpp_feature_childcontainer">
                {preference name=xmpp_server_host}
                {preference name=xmpp_server_http_bind}
                {preference name=xmpp_openfire_use_token}
            </div>
            {preference name=feature_inline_comments}
            {preference name=site_favicon_enable}
            {preference name=pass_blacklist}
            <div class="col-sm-2">
                <b>{tr}Console{/tr}</b>:
            </div>
            <div class="col-sm-10">
                {tr}Preferences can be set{/tr}.
                <a href="https://doc.tiki.org/Console">{tr}More Information{/tr}...</a><br/>
            </div>
            <div class="col-sm-2">
                <b>{tr}Scheduler{/tr}</b>:
            </div>
            <div class="col-sm-10">
                {tr}One system cron job can trigger all required actions at their specific scheduled times{/tr}.
                <a href="https://doc.tiki.org/Scheduler">{tr}More Information{/tr}...</a><br/>
            </div>
            <div class="col-sm-2">
                <b>{tr}Search{/tr}</b>:
            </div>
            <div class="description offset-sm-1">
                {tr}Elasticsearch only{/tr}
            </div>
            <div class="offset-sm-1 col-sm-11">
                {preference name="unified_elastic_possessive_stemmer"}
            </div>
        </fieldset>
        <i>{tr}See the full list of changes{/tr}.</i>
        <a href="https://doc.tiki.org/Tiki17" target="tikihelp" class="tikihelp" title="{tr}Tiki17:{/tr}
            {tr}Click to read more{/tr}
        ">
            {icon name="help" size=1}
        </a>
    </div>
</div>