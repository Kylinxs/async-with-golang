
{* $Id$ *}

{if ! $composer_available}
    {remarksbox type="warning" title="{tr}Composer not found{/tr}"}
    {tr}Composer could not be executed, so the automated check on the packages cannot be performed.{/tr}
    <br/>
    {tr _0='<a href="javascript:void(0)" class="install-composer alert-link">' _1='</a>'}In %0Diagnose%1 tab you can install composer.{/tr}
    {/remarksbox}
{/if}

{if isset($composer_output)}
    {remarksbox type="note" title="{tr}Note{/tr}"}

    {tr}Result of executing the changes to the packages:{/tr}<br />
        <pre>{$composer_output}</pre>
    {/remarksbox}
{/if}

{if isset($composer_installed_errors)}
    {remarksbox type="warning" title="{tr}Composer errors{/tr}"}

    {tr}Composer returned some errors:{/tr}<br />
        <pre>{$composer_installed_errors}</pre>
    {/remarksbox}
{/if}

{if not empty($composer_environment_warning)}
    {remarksbox type="warning" title="{tr}Issues with composer environment{/tr}"}

        <p>{tr}Issues with composer environment:{/tr}</p>
        <ul>
            {foreach item=entry from=$composer_environment_warning}
                <li>{$entry}</li>
            {/foreach}
        </ul>
        <p>{tr _0='<code>sh setup.sh fix</code>' _1='<a href="https://doc.tiki.org/Packages" class="alert-link">doc.tiki.org/Packages</a>'}Run %0 from a shell to try to fix this, see %1 for more info.{/tr}</p>
    {/remarksbox}
{/if}

{if isset($extensions_output)}
    {if $extensions_status}
        {$type = 'success'}
        {$title = "{tr}Success{/tr}"}
    {else}
        {$type = 'error'}
        {$title = "{tr}Error{/tr}"}
    {/if}
    {remarksbox type=$type title=$title close="y"}
    <pre>{$extensions_output}</pre>
    {/remarksbox}
{/if}

{tabset name='tabs_admin-packages'}
    {tab name="{tr}Packages{/tr}"}
        {if ! $composer_phar_exists}
            <div class="clearfix"></div>
            {remarksbox type="note" title="{tr}Note{/tr}"}
            {tr}Packages check could not be performed at the moment due to missing{/tr} <code>temp/composer.phar</code><br />
            {tr}You can try to install composer from the <a href="javascript:void(0)" class="install-composer alert-link">Diagnose</a> tab or look at the manual instructions bellow.{/tr}
            {/remarksbox}
        {/if}
        {if count($composer_packages_installed)}
        <br />
        <h4>{tr}Installed Packages{/tr} <small>{tr}Status of the packages registered in the composer.json file{/tr}</small></h4>
        <table class="table">
            <tr>
                <th>{tr}Package Name{/tr}</th>
                <th>{tr}Version Required{/tr}</th>
                <th>{tr}Status{/tr}
                <th>{tr}Version Installed{/tr}</th>
                <th>{tr}Action{/tr}</th>
            </tr>
            {foreach item=entry from=$composer_packages_installed}
                <tr>
                    <td>{$entry.name}</td>
                    <td>{$entry.required} {if !empty($entry.upgradeVersion)}<span class="label label-warning">{tr}Update:{/tr} {$entry.requiredVersion}</span>{/if}</td>
                    <td>
                        {if $composer_phar_exists}
                            {if $entry.status == 'installed'}
                                {icon name='success' iclass='tips' ititle="{tr}Status:{/tr}{tr}Installed{/tr}"}
                            {elseif $entry.status == 'missing'}
                                {icon name='warning' iclass='tips' ititle="{tr}Status:{/tr}{tr}Missing{/tr}"}
                            {else}
                                &nbsp;
                            {/if}
                        {/if}
                    </td>
                    <td>
                        {$entry.installed|default:'&nbsp;'}
                        {if $entry.state == 'replaced'}
                            <a class="tips text-warning"
                               href="#"
                               style="text-decoration: none;"
                               data-bs-toggle="popover"
                               data-bs-container="body"
                               data-bs-trigger="hover"
                               data-bs-content="<strong>{tr}Licence{/tr}</strong>: <a href='{$entry.licenceUrl}'>{$entry.licence}</a><br/><strong>{tr}Replaced by{/tr}</strong>:<br/>{'<br/>'|implode:$entry.replacedBy}"
                               data-bs-original-title="{tr}Replaced{/tr}"
                            >
                                {icon name='exclamation-triangle'}
                            </a>
                        {elseif $entry.state == 'deprecated'}
                            <a class="tips text-warning"
                               href="#"
                               style="text-decoration: none;"
                               data-bs-toggle="popover"
                               data-bs-container="body"
                               data-bs-trigger="hover"
                               data-bs-content="<strong>{tr}Licence{/tr}</strong>: <a href='{$entry.licenceUrl}'>{$entry.licence}</a>"
                               data-bs-original-title="{tr}Deprecated{/tr}"
                            >
                                {icon name='exclamation-triangle'}
                            </a>
                        {else}
                            <a class="tips"
                               href="#"
                               style="text-decoration: none;"
                               data-bs-toggle="popover"
                               data-bs-container="body"
                               data-bs-trigger="hover"
                               data-bs-content="<strong>{tr}Licence{/tr}</strong>: <a href='{$entry.licenceUrl}'>{$entry.licence}</a><br/><strong>{tr}Required by{/tr}</strong>:<br/>{'<br/>'|implode:$entry.requiredBy}"
                               data-bs-original-title="{tr}Info{/tr}"
                            >
                                {icon name='info-circle'}
                            </a>

                        {/if}

                    </td>
                    <td>
                        {if !empty($entry.extension)}
                            <form action="tiki-admin.php?page=packages&cookietab=1" method="post">
                                <input type="hidden" name="redirect" value="0">
                                <input type="hidden" name="enabled" value="{$entry.enabled}">
                                {ticket}
                                {if !$entry.extensionEnabled}
                                    <button class="btn btn-primary" name="enable-extension-package" value="{$entry.name}">{tr}Enable extension{/tr}</button>
                                {elseif $entry.extensionUpdate}
                                    <button class="btn btn-secondary" name="enable-extension-package" value="{$entry.name}">{tr}Update extension{/tr}</button>
                                {else}
                                    <button class="btn btn-danger" name="disable-extension-package" value="{$entry.name}">{tr}Disable extension{/tr}</button>
                                {/if}
                            </form>
                        {/if}
                        {if !empty($entry.key)}
                            {if $composer_phar_exists}
                                <form action="tiki-admin.php?page=packages&cookietab=1" method="post">
                                    <input type="hidden" name="redirect" value="0">
                                    <input type="hidden" name="installed" value="{$entry.installed}">
                                    {ticket}
                                    {if $entry.installed && $entry.upgradeVersion}
                                        <button class="btn btn-primary" name="auto-update-package" value="{$entry.key}">{tr}Update{/tr}</button>
                                    {/if}
                                    <button class="btn btn-danger" name="auto-remove-package" value="{$entry.key}">{tr}Remove{/tr}</button>
                                </form>
                            {/if}
                        {else}
                            {tr}Removal not available{/tr}
                            {icon name='info' iclass='tips' ititle="{tr}Removal not available:{/tr}{tr}This package is not managed by Tiki and likely was installed manually.{/tr}"}
                        {/if}
                    </td>
                </tr>
            {/foreach}
            {if $composer_packages_missing}
                <tr>
                    <td colspan="5">
                        {if $composer_available}
                            <h4>{tr}One or more packages appear to be missing{/tr}</h4>
                            {tr}In the list above, some packages could not be found. They are defined in the composer.json file, but do not seem to be installed.{/tr}
                        {else}
                            <h4>{tr}Could not validate the status of one or more packages{/tr}</h4>
                            {tr}In the list above, some packages status could not be validated, since composer was not found, you can try to install composer and try again or install the packages manually.{/tr}
                        {/if}
                        <br><br>
                        {if $composer_available}
                            <h4>{tr}Install packages from the administrator interface{/tr}</h4>
                            <p>
                            {tr}The administrator interface can be used to install the packages marked as missing in the list above.{/tr}
                            {tr}Click the "Fix Missing Packages" button below, and Tiki will try to install them:{/tr}
                            </p>
                            <form action="tiki-admin.php?page=packages&cookietab=1" method="post">
                                <input type="hidden" name="redirect" value="0">
                                {ticket}
                                <button class="btn btn-primary" name="auto-fix-missing-packages" value="auto-fix-missing-packages">{tr}Fix Missing Packages{/tr}</button>
                            </form>
                            <br />
                            <p>
                            The results of the execution of the commands will be displayed after the process finishes.
                            </p>
                        {/if}
                            <h4>{tr}Install packages manually{/tr}</h4>
                        {if ! $composer_available}
                            <p><strong>{tr _0="<code>composer</code>"}Make sure %0 is installed.{/tr}</strong></p>
                            <p>
                                {tr}Composer can be installed manually, in the host machine, by following the instructions from the{/tr}
                                <a href="https://doc.tiki.org/Composer">Composer</a> {tr}website.{/tr}
                            </p>
                            <p>
                                {tr _0="<code>setup.sh</code>" _1="<code>temp/composer.phar</code>"}The script %0 that is included in the Tiki distribution can be run to make sure composer is installed and the file permissions are correctly applied. In this case, composer will be installed as %1.{/tr}
                                {tr}Below is an example of how to do this in a Linux-like operating system:{/tr}<br>
                                <code>bash ./setup.sh composer</code>
                            </p>
                            <p><strong>{tr}Install the missing packages.{/tr}</strong></p>
                        {/if}
                        <p>{tr}You can install the missing packages by issuing the command{/tr}
                        <code>composer --no-dev --prefer-dist update</code>.
                        {tr}Below is an example of how to do this in a Linux-like operating system:{/tr} <br>
                        <code>php temp/composer.phar --no-dev --prefer-dist update</code></p>
                    </td>
                </tr>
            {/if}
        </table>
        {/if}
        {if count($composer_packages_available)}
        <br />
        <h4>{tr}Available Packages{/tr} <small>{tr}These packages have been identified as required by one or more features.{/tr}</small></h4>
        <table class="table" id="packagesInfo">
            <tr>
                <th>{tr}Package Name{/tr}</th>
                <th>{tr}Version{/tr}</th>
                <th>{tr}Licence{/tr}</th>
                <th>{tr}Required by{/tr}</th>
                <th width="200">{tr}Action{/tr}</th>
            </tr>
            {foreach item=entry from=$composer_packages_available}
                <tr>
                    <td>
                    {$entry.name}
                    {if $entry.state == 'replaced'}
                            <a class="tips text-warning"
                               href="#"
                               style="text-decoration: none;"
                               data-bs-toggle="popover"
                               data-bs-container="body"
                               data-bs-trigger="hover"
                               data-bs-content="<strong>{tr}Licence{/tr}</strong>: <a href='{$entry.licenceUrl}'>{$entry.licence}</a><br/><strong>{tr}Replaced by{/tr}</strong>:<br/>{'<br/>'|implode:$entry.replacedBy}"
                               data-bs-original-title="{tr}Replaced{/tr}"
                            >
                                {icon name='exclamation-triangle'}
                            </a>
                    {/if}
                    </td>
                    <td>{$entry.requiredVersion}</td>
                    <td><a href="{$entry.licenceUrl}">{if empty($entry.licence)}{tr}Not Available{/tr}{else}{$entry.licence}{/if}</a></td>
                    <td>{', '|implode:$entry.requiredBy}</td>
                    <td class="d-flex">
                        {if $composer_phar_exists}
                        <form action="tiki-admin.php?page=packages&cookietab=1" method="post">
                            <input type="hidden" name="redirect" value="0">
                            {ticket}
                            <button class="btn btn-primary" name="auto-install-package" value="{$entry.key}">{tr}Install{/tr}</button>
                        </form>
                        {/if}
                        <button type="button" data-bs-toggle="collapse" data-package-name="{$entry.name}" data-collapse-key="collapse-{$entry.key}" href="#collapse-{$entry.key}" role="button" aria-expanded="false" aria-controls="collapse-{$entry.name}" class="btn btn-light collapse-package-detail">{tr}Info{/tr}</button>
                    </td>
                </tr>
                <tr class="collapse border bg-light" id="collapse-{$entry.key}">
                    <td colspan="5">
                        <h6 class="text-info"><b>{$entry.name}</b></h6>
                        <div class="detail-package" data-package-name="{$entry.name}"></div>
                    </td>
                </tr>
            {/foreach}
                <tr>
                    <td colspan="5">
                        <h4>{tr}There appear to be some optional packages that can be installed{/tr}</h4>
                        {tr}In the list above, there are optional packages that may be installed in order to use the Tiki features that require the package.{/tr}

                        <br><br>

                        {if $composer_available}
                            <h4>{tr}Install packages from the administrator interface{/tr}</h4>
                            <p>
                                {tr}The administrator interface can be used to install the optional packages in the list above.{/tr}
                                {tr}Click the "Install Package" button, and Tiki will try to install them.{/tr}
                            </p>
                        {/if}

                        <h4>{tr}Install packages manually{/tr}</h4>
                        {if ! $composer_available}
                            <p><strong>{tr _0="<code>composer</code>"}Make sure %0 is installed.{/tr}</strong></p>
                            <p>
                                {tr}Composer can be installed manually, in the host machine, by following the instructions from the{/tr}
                                <a href="https://doc.tiki.org/Composer">Composer</a> {tr}website.{/tr}
                            </p>
                            <p>
                                {tr _0="<code>setup.sh</code>" _1="<code>temp/composer.phar</code>"}The script %0 that is included in the Tiki distribution can be run to make sure composer is installed and the file permissions are correctly applied. In this case, composer will be installed as %1.{/tr}
                                {tr}Below is an example of how to do this in a Linux-like operating system:{/tr}<br>
                                <code>bash ./setup.sh composer</code>
                            </p>
                        {/if}
                        <p><strong>{tr _0="<code>composer.json</code>"}Make sure there is a %0 file in the root of the website.{/tr}</strong></p>
                        <p>
                            {tr _0="<code>composer.json</code>"}If there is not already a %0 file, then create one.{/tr}
                            {tr}The sample <code>composer.json.dist</code> that comes with Tiki can be used as a starting point.{/tr}
                            {tr}Below is an example of how to do this in a Linux-like operating system:{/tr} <br>
                            <code>cp composer.json.dist composer.json</code>
                        </p>
                        <p><strong>{tr}Install the package.{/tr}</strong></p>
                        <p>
                            {tr}Packages can be installed by issuing a command{/tr}
                            <code>composer require package:version</code> {tr}for each package that is to be installed.{/tr}
                            {tr}Below is an example of how to do this in a Linux-like operating system:{/tr} <br>
                            <code>php temp/composer.phar require --update-no-dev --prefer-dist psr/log:^1.0</code>
                        </p>
                    </td>
                </tr>
        </table>
        {/if}
    {/tab}
    {tab name="{tr}Packages Custom{/tr}"}
        {if ! $composer_custom_packages_installed}
            {remarksbox type="info" title="{tr}There are no manual managed packages installed in the folder vendor_custom{/tr}"}
            {tr}Please check <a href="https://packages.tiki.org/">Tiki Packages</a> website for information on how to download and use these manual packages.{/tr} <br/>
            {tr}Note that this in place for when you can not use composer to manage your packages, example when there is restricted access to internet and composer will not be able to download the packages, for most users the most convenient way is to install directly from the "Packages" tab.{/tr}
            {/remarksbox}
        {/if}
        {if $composer_custom_packages_installed}
            {if ! $composer_phar_exists}
                {remarksbox type="info" title="{tr}Note{/tr}"}
                {tr}Packages check could not be performed at the moment due to missing{/tr} <code>temp/composer.phar</code><br />
                {tr}You can try to install composer from the <a href="javascript:void(0)" class="install-composer alert-link">Diagnose</a> tab or look at the manual instructions bellow.{/tr}
                {/remarksbox}
            {/if}
            <br>
            <h4>{tr}Composer Packages Custom <small>Status of the packages registered in the vendor_custom folder</small>{/tr}</h4>
            {remarksbox type="info" title="{tr}For information only{/tr}"}
            {tr}These packages are managed manually and displayed here for informational purposes.{/tr}
            {/remarksbox}
            <table class="table">
                <tr>
                    <th>{tr}Package Name{/tr}</th>
                    <th>{tr}Version Required{/tr}</th>
                    <th>{tr}Status{/tr}
                    <th>{tr}Version Installed{/tr}</th>
                    <th>Actions</th>
                </tr>
            {foreach item=packages key=folderName from=$composer_custom_packages_installed}
                <tr><td colspan="5" style="font-weight: bold;">{$folderName}</td></tr>
                {foreach item=package from=$packages}
                    <tr>
                        <td width="45%">
                            {$package.name}
                        </td>
                        <td width="15%">{$package.required}</td>
                        <td width="10%">
                            {if $package.status == 'installed'}
                                {icon name='success' iclass='tips' ititle="{tr}Status:{/tr}{tr}Installed{/tr}"}
                            {elseif $package.status == 'missing'}
                                {icon name='warning' iclass='tips' ititle="{tr}Status:{/tr}{tr}Missing{/tr}"}
                            {/if}
                        </td>
                        <td width="15%">{$package.installed|default:'&nbsp;'}</td>
                        <td width="15%">
                            {if !empty($package.extension)}
                                <form action="tiki-admin.php?page=packages&cookietab=2" method="post" style="display:inline">
                                    <input type="hidden" name="redirect" value="0">
                                    <input type="hidden" name="enabled" value="{$package.enabled}">
                                    {ticket}
                                    {if !$package.extensionEnabled}
                                        <button class="btn btn-primary btn-sm" name="enable-extension-package" value="{$package.name}">{tr}Enable extension{/tr}</button>
                                    {elseif $package.extensionUpdate}
                                        <button class="btn btn-danger btn-sm" name="enable-extension-package" value="{$package.name}">{tr}Update extension{/tr}</button>
                                    {else}
                                        <button class="btn btn-danger btn-sm" name="disable-extension-package" value="{$package.name}">{tr}Disable extension{/tr}</button>
                                    {/if}
                                </form>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
            {/foreach}
            </table>
        {/if}
    {/tab}
    {tab name="{tr}Packages Bundled{/tr}"}
        {if $composer_available}
            {remarksbox type="info" title="{tr}For information only{/tr}"}
            {tr}These packages are bundled with Tiki, and displayed here for informational purposes.{/tr}
            {/remarksbox}
        {/if}
        {if ! $composer_phar_exists}
            {remarksbox type="info" title="{tr}Note{/tr}"}
            {tr}Packages check could not be performed at the moment due to missing{/tr} <code>temp/composer.phar</code><br />
            {tr}You can try to install composer from the <a href="javascript:void(0)" class="install-composer alert-link">Diagnose</a> tab or look at the manual instructions bellow.{/tr}
            {/remarksbox}
        {/if}
        <br>
        <h4>{tr}Composer Packages Bundled{/tr} <small>{tr}status of the packages registered in the vendor_bundled/composer.json file{/tr}</small></h4>
        <table class="table">
            <tr>
                <th>{tr}Package Name{/tr}</th>
                <th>{tr}Version Required{/tr}</th>
                <th>{tr}Status{/tr}
                <th>{tr}Version Installed{/tr}</th>
            </tr>
            {foreach item=entry from=$composer_bundled_packages_installed}
                <tr>
                    <td>{$entry.name}</td>
                    <td>{$entry.required}</td>
                    <td>
                        {if $composer_phar_exists}
                            {if $entry.status == 'installed'}
                                {icon name='success' iclass='tips' ititle="{tr}Status:{/tr}{tr}Installed{/tr}"}
                            {elseif $entry.status == 'missing'}
                                {icon name='warning' iclass='tips' ititle="{tr}Status:{/tr}{tr}Missing{/tr}"}
                            {else}
                                &nbsp;
                            {/if}
                        {/if}
                    </td>
                    <td>{$entry.installed|default:'&nbsp;'}</td>
                </tr>
            {/foreach}
        </table>
    {/tab}
    {tab name="{tr}Diagnose{/tr}"}
        <br>
        <h4>{tr}Diagnose Composer Installation{/tr} <small>{tr}Use the button below to test your composer installation.{/tr}</small></h4>
        <form action="tiki-admin.php?page=packages&cookietab=4" method="post">
            <input type="hidden" name="redirect" value="0">
            {ticket}
            <button class="btn btn-primary" name="auto-run-diagnostics" value="run">{tr}Diagnose Composer{/tr}</button>
        </form>
        <br />
        <h4>{tr}Composer management{/tr}</h4>
        <form action="tiki-admin.php?page=packages&cookietab=4" method="post">
            <input type="hidden" name="redirect" value="0">
            {ticket}
            {if ! $composer_available}
                <button class="btn btn-primary" name="install-composer" value="run">{tr}Install Composer{/tr}</button>
            {else}
                <button class="btn btn-primary" name="update-composer" value="run">{tr}Update Composer{/tr}</button>
            {/if}
            <button class="btn btn-primary" name="remove-composer-locker" value="run">{tr}Remove composer.lock{/tr}</button>
            <button class="btn btn-primary" name="clean-vendor-folder" value="run">{tr}Clean vendor folder{/tr}</button>
        </form>
        <br />

        {if isset($diagnostic_composer_location) || $diagnostic_composer_output || $composer_management_success || $composer_management_error}
            <br />
            <h4>{tr}Results{/tr}</h4>
            {if isset($diagnostic_composer_location) }
                <p><strong>Composer:</strong> {if $diagnostic_composer_location}{tr}{$diagnostic_composer_location}{/tr}{else}{tr}Composer not found{/tr}{/if}</p>
            {/if}
            {if $diagnostic_composer_output}
                <p><strong>Composer diagnose output</strong></p>
                <pre>{$diagnostic_composer_output}</pre>
            {/if}

            {if $composer_management_success}
                {remarksbox type="success" title="{tr}Success{/tr}" close="n"}
                {$composer_management_success}
                {/remarksbox}
            {/if}

            {if $composer_management_error}
                {remarksbox type="error" title="{tr}Error{/tr}" close="n"}
                {$composer_management_error}
                {/remarksbox}
            {/if}
            <br>
        {/if}
    {/tab}
    {if count($packageprefs)}
        {tab name="{tr}Extensions Preferences{/tr}"}
            <form action="tiki-admin.php?page=packages&cookietab=5" method="post">
                {ticket}
                <input type="hidden" name="redirect" value="0">
                <fieldset>
                    <legend>{tr}Activate Extension Package{/tr}</legend>
                    {foreach $packageprefs as $package}
                        {preference name="{$package|escape}"}
                    {/foreach}
                </fieldset>
                <button class="btn btn-primary" name="apply-packages-preferences" value="run">{tr}Apply{/tr}</button>
            </form>
            <br>
        {/tab}
    {/if}
{/tabset}

{jq}
    $(document).ready(function(){
        $(".install-composer").click(function(){
            $('.nav-tabs a[href="#contenttabs_admin_packages-4"]').tab('show');
        });

        $(".collapse-package-detail").click(function() {
            var packageName = $(this).attr("data-package-name");
            var callapseKey = $(this).attr("data-collapse-key");
            var collapsible = document.getElementById(callapseKey);

            //Close the previously opened tab when clicking on a tab
            var packagesInfo = $('#packagesInfo');
            packagesInfo.on('show.bs.collapse','.collapse', function() {
                packagesInfo.find('.collapse.show').collapse('hide');
            });

            collapsible.addEventListener('shown.bs.collapse', function () {
                $('.detail-package[data-package-name="'+packageName+'"]').html('<img width="20" height="20" src="img/spinner.gif" title="'+tr("Looking for package details...")+'" alt="'+tr("Looking for package details...")+'"/>');
                $.getJSON("tiki-admin.php?page=packages&package_name="+packageName, function(json) {
                    var data = json['packages'];
                    var html = '<p>'+data[packageName][0]['description']+'</p>';
                    html += '<table>';

                    if (data[packageName][0]['keywords'] !== '') {
                        html += '<tr><td><b class="pe-3">'+tr("Keywords")+': </b></td><td>'+data[packageName][0]['keywords'].join(", ")+'</td></tr>';
                    }

                    if (data[packageName][0]['homepage'] !== '') {
                        html += '<tr><td><b class="pe-3">'+tr("Home page")+': </b></td><td><a target="blank" href="'+data[packageName][0]['homepage']+'">'+data[packageName][0]['homepage']+'</a></td></tr>';
                    }

                    if (data[packageName][0]['license'] !== '') {
                        html += '<tr><td><b class="pe-3">'+tr("License")+': </b></td><td>'+data[packageName][0]['license'].join(", ")+'</td></tr>';
                    }

                    if (data[packageName][0]['authors'] !== '') {
                        html += '<tr><td><b class="pe-3">'+tr("Authors")+': </b></td><td>';
                        var authors = [];

                        data[packageName][0]['authors'].forEach(author => {
                            authors.push(author["name"]);
                        })

                        html += '<span>'+authors.join(", ")+'</span>';
                        html += '</td></tr>';
                    }

                    $('.detail-package[data-package-name="'+packageName+'"]').html(html);
                }).fail(function(jqXMLHttpRequest,textStatus,errorThrown) {
                    $('.detail-package[data-package-name="'+packageName+'"]').html('<p class="text-error">'+tr("No description found")+'</p>');
                });
            })
        })
    });
{/jq}