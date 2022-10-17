
{extends "layout_view.tpl"}

{block name="title"}
    {title}{$title}{/title}
{/block}

{block name="navigation"}
    {include file='manager/nav.tpl'}
{/block}

{block name="content"}
    {if not empty($info)}
        <div class="rounded bg-dark text-light p-3">{$info|nl2br}</div>
    {else}
        <form method="post" action="{service controller=manager action=upgrade}" id="tiki-manager-upgrade-instance">
            <div class="form-group row mb-3">
                <label class="col-form-label col-sm-3">
                    {tr}Instances{/tr}
                    <a class="tikihelp text-info"
                        title="{tr}Description:{/tr} {tr}Instances to update. You can choose one or multiple instances.{/tr}">
                        {icon name=information}
                    </a>
                </label>
                <div class="col-sm-9">
                    <select class="form-control" id="instances" name="instances[]" multiple required>
                        {foreach item=instance from=$instances}
                        <option value="{$instance->id|escape}" {if $instance->id eq $selectedInstanceId}selected{/if}>{$instance->name|escape}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="form-group row mb-3">
                <label class="col-form-label col-sm-3">
                    {tr}Tiki Version{/tr}
                    <a class="tikihelp text-info"
                        title="{tr}Description:{/tr} {tr}The version you want to upgrade to. You should make a backup before you upgrade so you can return to this version if issues arise.{/tr}">
                        {icon name=information}
                    </a>
                </label>
                <div class="col-sm-9">
                    <select class="form-control" id="branch" name="branch" required>
                        <option value="" disabled selected hidden>{tr}Choose the version you want to upgrade to{/tr}</option>
                        {foreach item=branch from=$branches}
                            <option value="{$branch|escape}">{$branch|escape}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="form-group row mb-3">
                <label class="col-form-label col-sm-3">
                    {tr}Check{/tr}
                    <a class="tikihelp text-info"
                        title="{tr}Description:{/tr} {tr}{$help.check}{/tr}">
                        {icon name=information}
                    </a>
                </label>
                <div class="col-sm-9">
                    <select class="form-control" id="check" name="check">
                        {$boolOptions}
                    </select>
                </div>
            </div>
            <div class="form-group row mb-3">
                <label class="col-form-label col-sm-3">
                    {tr}Skip Reindex{/tr}
                    <a class="tikihelp text-info"
                        title="{tr}Description:{/tr} {tr}{$help['skip-reindex']}{/tr}">
                        {icon name=information}
                    </a>
                </label>
                <div class="col-sm-9">
                    <select class="form-control" id="skipReindex" name="skipReindex">
                        {$boolOptions}
                    </select>
                </div>
            </div>
            <div class="form-group row mb-3">
                <label class="col-form-label col-sm-3">
                    {tr}Skip cache warmup{/tr}
                    <a class="tikihelp text-info"
                        title="{tr}Description:{/tr} {tr}{$help['skip-cache-warmup']}{/tr}">
                        {icon name=information}
                    </a>
                </label>
                <div class="col-sm-9">
                    <select class="form-control" id="skipCacheWarmup" name="skipCacheWarmup">
                        {$boolOptions}
                    </select>
                </div>
            </div>
            <div class="form-group row mb-3">
                <label class="col-form-label col-sm-3">
                    {tr}Live reindex{/tr}
                    <a class="tikihelp text-info"
                        title="{tr}Description:{/tr} {tr}{$help['live-reindex']}{/tr}">
                        {icon name=information}
                    </a>
                </label>
                <div class="col-sm-9">
                    <select class="form-control" id="liveReindex" name="liveReindex">
                        {$boolOptions}
                    </select>
                </div>
            </div>
            <div class="form-group row mb-3">
                <label class="col-form-label col-sm-3">
                    {tr}Lag{/tr}
                    <a class="tikihelp text-info" title="{tr}Description:{/tr} {tr}{$help.lag}{/tr}">
                        {icon name=information}
                    </a>
                </label>
                <div class="col-sm-9">
                    <input value="0" class="form-control" type="number" id="lag" name="lag" min="0" max="30">
                </div>
            </div>
            <div class="form-group row mb-3">
                <label class="col-form-label col-sm-3">
                    {tr}Stash{/tr}
                    <a class="tikihelp text-info"
                        title="{tr}Description:{/tr} {tr}{$help.stash}{/tr}">
                        {icon name=information}
                    </a>
                </label>
                <div class="col-sm-9">
                    <select class="form-control" id="stash" name="stash">
                        {$boolOptions}
                    </select>
                </div>
            </div>
            <div class="form-group row mb-3">
                <label class="col-form-label col-sm-3">
                    {tr}Ignore requirements{/tr}
                    <a class="tikihelp text-info"
                        title="{tr}Description:{/tr} {tr}{$help['ignore-requirements']}{/tr}">
                        {icon name=information}
                    </a>
                </label>
                <div class="col-sm-9">
                    <select class="form-control" id="ignoreRequirements" name="ignoreRequirements">
                        {$boolOptions}
                    </select>
                </div>
            </div>
            <div class="form-group row mb-3">
                <label class="col-form-label col-sm-3"></label>
                <div class="col-sm-9">
                    <input class="btn btn-primary" type="submit" name="upgrade" value="{tr}Upgrade{/tr}">
                </div>
            </div>
        </form>
    {/if}
{/block}

{jq}
    $('#instances').change(function () {
        var instancesIds = $('#instances').val();
        if(instancesIds.length > 0) {
            $.ajax("tiki-ajax_services.php", {
                type: "GET",
                dataType: 'json',
                data: {
                    controller: 'manager',
                    action: 'get_instances_upper_versions',
                    instancesIds
                },
                success: function (data) {
                    var upperVersions = Object.values(data.upperVersions);

                    $("#branch").empty();
                    if(upperVersions.length > 0) {
                        $("#branch").append('<option  value="" disabled selected hidden>' + tr("Choose the version you want to upgrade to") + '</option>');
                        upperVersions.forEach((version) => {
                            $("#branch").append(`<option value="${version}">${version}</option>`);
                        });
                    }
                },
                error: function () {
                    feedback(
                        tr('An error occured while loading Tiki upgrade versions. Please try again and contact the site admin if the error persists.'),
                        "error",
                        false,
                        tr("Loading Tiki upgrade versions failed")
                    );
                }
            });
        } else {
            $("#branch").empty();
        }
    });
{/jq}