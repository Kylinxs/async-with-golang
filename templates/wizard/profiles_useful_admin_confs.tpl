
{* $Id$ *}

<div class="d-flex">
    <div class="me-4">
        <span class="fa-stack fa-lg" style="width: 100px;" title="{tr}Configuration Profiles Wizard{/tr}" >
            <i class="fas fa-cubes fa-stack-2x"></i>
            <i class="fas fa-flip-horizontal fa-magic fa-stack-1x ms-4 mt-4"></i>
        </span>
    </div>
    <div class="flex-grow-1 ms-3">
        <h4 class="mt-0 mb-4">{tr}Check out some useful changes in the configuration for site administrators{/tr}.</h4>
        <h3>{tr}Profiles:{/tr}</h3>
        <div class="row">
            <div class="col-md-{*6 commented out until second column, below, is used. *}12">
                <div class="row">
                    <div class="col-md-6">
                        <img class="float-start" src="img/icons/large/profile_debug_mode48x48.png" alt="{tr}Debug Mode Enabled{/tr}" />
                        <h4>{tr}Debug Mode Enabled{/tr}</h4>
                        (<a href="tiki-admin.php?ticket={ticket mode=get}&profile=Debug_Mode_Enabled&show_details_for=Debug_Mode_Enabled&categories%5B%5D={$tikiMajorVersion}.x&repository=http%3a%2f%2fprofiles.tiki.org%2fprofiles&page=profiles&preloadlist=y&list=List#step2" target="_blank">{tr}apply profile now{/tr}</a>)<br/>
                    </div>
                    <div class="col-md-6">
                        <h4>{tr}Debug Mode Disabled{/tr}</h4>
                        (<a href="tiki-admin.php?ticket={ticket mode=get}&profile=Debug_Mode_Disabled&show_details_for=Debug_Mode_Disabled&categories%5B%5D={$tikiMajorVersion}.x&repository=http%3a%2f%2fprofiles.tiki.org%2fprofiles&page=profiles&preloadlist=y&list=List#step2" target="_blank">{tr}apply profile now{/tr}</a>)
                    </div>
                </div>
                {tr}Profile <i>Debug_Mode_Enabled</i> will help you detect potential errors and warnings which are hidden otherwise.{/tr}
                {tr}Once applied, you might like to apply the opposite profile: <i>Debug_Mode_Disabled</i>, if not changing the appropriate settings by hand.{/tr}
                <br/>
                <a href="https://dev.tiki.org/Recovery" target="tikihelp" class="tikihelp" title="{tr}Debug Mode Enabled{/tr} & {tr}Debug Mode Disabled:{/tr}
                        {tr}More details:{/tr}
                        <ul>
                            <li>{tr}Enables/Disables debugging tools{/tr}</li>
                            <li>{tr}Enables/Disables logging tools{/tr}</li>
                            <li>{tr}Disables/Enables redirections to similar pages{/tr}</li>
                            <li>{tr}Enables/Disables error and warning display to all users, not only admins{/tr} </li>
                        </ul>
                        {tr}Click to read more{/tr}"
                >
                    {icon name="help"}
                </a>
            </div>
            {* <div class="col-md-6">
                &nbsp;
            </div> *}
        </div>
        <div class="row">
            <div class="col-md-6">
                <h4>{tr}Scheduler_Presets{/tr}</h4>
                (<a href="tiki-admin.php?ticket={ticket mode=get}&profile=Scheduler_Presets_20&show_details_for=Scheduler_Presets_20&categories%5B%5D={$tikiMajorVersion}.x&repository=http%3a%2f%2fprofiles.tiki.org%2fprofiles&page=profiles&preloadlist=y&list=List#step2" target="_blank">{tr}apply profile now{/tr}</a>)
                <br/>
                {tr}This configuration profile enables the Scheduler feature and loads a preset of schedulers{/tr}
                <br/>
                <a href="https://doc.tiki.org/Scheduler" target="tikihelp" class="tikihelp" title="{tr}Scheduler_Presets:{/tr}
                        {tr}More details:{/tr}
                        <ul>
                            <li>{tr}Each scheduler can replace a former cron job action in the server{/tr}</li>
                            <li>{tr}You only need one cron job action in the server, the one that runs the tiki scheduler as frequently as you need.{/tr}</li>
                        </ul>
                        {tr}Click to read more{/tr}"
                >
                    {icon name="help"}
                </a>
                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <a href="https://profiles.tiki.org/display8" class="thumbnail internal" data-box="box" title="{tr}Click to expand{/tr}">
                            <img src="img/profiles/profile_thumb_scheduler_presets_20.png" alt="Click to expand" class="regImage pluginImg" title="{tr}Click to expand{/tr}" />
                        </a>
                        <div class="mini text-center">
                            <div class="thumbcaption text-center">{tr}Click to expand{/tr}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">

                <h4>{tr}Activity Stream{/tr}</h4>
                (<a href="tiki-admin.php?ticket={ticket mode=get}&profile=Activity_stream&show_details_for=Activity_stream&categories%5B%5D={$tikiMajorVersion}.x&repository=http%3a%2f%2fprofiles.tiki.org%2fprofiles&page=profiles&preloadlist=y&list=List#step2" target="_blank">{tr}apply profile now{/tr}</a>)
                <br/>
                {tr}This configuration profile showcases the activity stream feature in Tiki.{/tr}
                <br/>
                <a href="https://doc.tiki.org/Activity_stream" target="tikihelp" class="tikihelp" title="{tr}Activity Stream:{/tr}
                        {tr}More details:{/tr}
                        <ul>
                            <li>{tr}Activity Stream data can also be reviewed through PluginPivotTable{/tr}</li>
                            <li>{tr}It is somewhat similar to the basic Action log feature in Tiki{/tr}</li>
                        </ul>
                        {tr}Click to read more{/tr}"
                >
                    {icon name="help"}
                </a>
                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <a href="https://profiles.tiki.org/display8" class="thumbnail internal" data-box="box" title="{tr}Click to expand{/tr}">
                            <img src="img/profiles/profile_thumb_activity_stream.png" alt="Click to expand" class="regImage pluginImg" title="{tr}Click to expand{/tr}" />
                        </a>
                        <div class="mini text-center">
                            <div class="thumbcaption text-center">{tr}Click to expand{/tr}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>