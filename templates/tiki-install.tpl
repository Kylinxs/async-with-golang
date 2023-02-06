
<div id="tiki-installer" class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <div class="sidebar">
                <img alt="{tr}Tiki Wiki CMS Groupware{/tr}" class="img-fluid" src="img/tiki/Tiki_WCG_light.png">
                <div class="menus">
                    <form class="installer-menu menu" action="tiki-install.php" method="post">
                        {if $multi}<input type="hidden" name="multi" value="{$multi}">{/if}
                        {if $lang}<input type="hidden" name="lang" value="{$lang}">{/if}

                        <h4>{tr}Installation{/tr}</h4>
                        <ol class="nav flex-column {if $lang eq 'he' or $lang eq 'ar'}px-4{/if}">

                            {* step item base *}
                            {function name="printStepItem" step=0 active=false disabled=false title=""}
                            <li class="nav-item {if $active}active{/if}">
                                <button class="btn-link nav-link"
                                    name="install_step" value="{$step}"
                                    {if $disabled}disabled="disabled"{/if}>
                                    {$title}
                                </button>
                            </li>
                            {/function}

                            {* step 0 *}
                            {call name="printStepItem"
                                step="0"
                                title="{tr}Welcome{/tr}"
                                disabled=$install_step==0
                                active=$install_step==0}

                            {* step 1 *}
                            {call name="printStepItem"
                                step="1"
                                title="{tr}License{/tr}"
                                disabled=$install_step==1
                                active=$install_step==1}

                            {* step 2 *}
                            {call name="printStepItem"
                                step="2"
                                title="{tr}Review the System Requirements{/tr}"
                                disabled=$install_step <= 2 && $dbcon !='y'
                                active=$install_step==2}

                            {* step 3 *}
                            {call name="printStepItem"
                                step="3"
                                title={{$dbcon eq 'y'}|ternary:"{tr}Reset the Database Connection{/tr}":"{tr}Database Connection{/tr}"}
                                disabled=$install_step <= 3 && $dbcon !='y'
                                active=$install_step==3}

                            {* step 4 *}
                            {call name="printStepItem"
                                step="4"
                                title={$tikidb_created|ternary:"{tr}Install/Upgrade{/tr}":"{tr}Install{/tr}"}
                                disabled=$install_step <= 4 && $dbcon !='y' || !isset($smarty.post.scratch) || isset($smarty.post.update)
                                active=$install_step==4}

                            {* step 5 *}
                            {call name="printStepItem"
                                step="5"
                                title={isset($smarty.post.update)|ternary:"{tr}Review the Upgrade{/tr}":"{tr}Review the Installation{/tr}"}
                                disabled=$install_step <= 5 && !$tikidb_is20
                                active=$install_step==5}

                            {* step 6 *}
                            {call name="printStepItem"
                                step="6"
                                title="{tr}Configure the General Settings{/tr}"
                                disabled=$install_step <= 6 && !$tikidb_is20 || isset($smarty.post.update)
                                active=$install_step==6}

                            {* step 7 *}
                            {call name="printStepItem"
                                step="7"
                                title="{tr}Last Notes{/tr}"
                                disabled=$install_step <= 7 && !$tikidb_is20
                                active=$install_step==7}

                            {* step 8 *}
                            {call name="printStepItem"
                                step="8"
                                title="{tr}Enter Your Tiki{/tr}"
                                disabled=$install_step <= 8 && !$tikidb_is20
                                active=$install_step==8}
                        </ol>
                    </form>{* End of install-menu *}
                    <div class="help-menu menu">
                        <h4>{tr}Help{/tr}</h4>
                        <ul class="nav flex-column {if $lang eq 'he' or $lang eq 'ar'}px-4{/if}">
                            <li class="nav-item">
                                <a class="nav-link" href="https://tiki.org" target="_blank">
                                    <img src="themes/base_files/favicons/favicon-16x16.png" alt="{tr}Tiki Icon{/tr}">
                                    {tr}Tiki Project Web Site{/tr}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="https://doc.tiki.org" target="_blank" title="{tr}Documentation{/tr}">
                                    {icon name="documentation"}
                                    {tr}Documentation{/tr}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="https://tiki.org/forums" target="_blank" title="{tr}Forums{/tr}">
                                    {icon name="admin_forums"}
                                    {tr}Support Forums{/tr}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-9 installer-content">
            <div class="text-center">
                <h1>{tr}Tiki Installer{/tr} <small>{$tiki_version_name} <a class="text-info" title="{tr}Help{/tr}" href="https://doc.tiki.org/Installation" target="help">{icon name="help"}</a></small></h1>
            </div>
            <div class="row install-body" style="margin-left: 5%; margin-top: 10%">
                <div class="col-md-10 install-steps">
                    {feedback}
                    {if $install_step eq '0' or !$install_step}{* start of installation *}
                    <div class="install-step0">
                        <h1 class="pagetitle">{tr}Welcome{/tr}</h1>
                        <p>{tr _0=$tiki_version_name}Welcome to the <strong>Tiki %0</strong> installer.{/tr}<br>
                            {tr}Use this script to install a new database or upgrade your existing database.{/tr}</p>
                        <ul>
                            <li>{tr}For the latest information about this release, please read the{/tr} <a href="https://doc.tiki.org/Tiki{$tiki_version_short|urlencode}" target="_blank">{tr}Release Notes{/tr}</a>.</li>
                            <li>{tr}For complete documentation, please visit{/tr} <a href="https://doc.tiki.org" target="_blank">doc.tiki.org</a>.</li>
                            <li>{tr}For more information about Tiki, please visit{/tr} <a href="https://tiki.org" target="_blank">tiki.org</a>.</li>
                        </ul>
                        <form action="tiki-install.php" method="post" role="form">
                            <div class="d-flex flex-row flex-wrap align-items-center mb-3">
                                <label class="col-form-label" for="general-lang">{tr}Select your language{/tr}</label>
                                <select name="lang" id="general-lang" onchange="$('.install-steps').tikiModal(tr('Loading...')); $('input[name=lang]:hidden').val($(this).val()); this.form.submit();" title="{tr}Select your language{/tr}" class="form-control mx-2">
                                    {section name=ix loop=$languages}
                                        <option value="{$languages[ix].value|escape}"
                                                {if $prefs.site_language eq $languages[ix].value}selected="selected"{/if}>{$languages[ix].name}
                                        </option>
                                    {/section}
                                </select>
                            </div>
                            <input type="hidden" name="install_step" value="0">
                            {if $multi}
                                <input type="hidden" name="multi" value="{$multi}">
                            {/if}
                        </form>
                        <form action="tiki-install.php" method="post" role="form">
                            <div class="mb-3 text-center">
                                {if $multi}
                                    <input type="hidden" name="multi" value="{$multi}">
                                {/if}
                                <input type="hidden" name="lang" value="{if $lang}{$lang}{/if}">
                                <input type="hidden" name="install_step" value="1">
                                <input type="submit" class="btn btn-primary" value="{tr}Continue{/tr}">
                            </div>
                        </form>
                    </div>{* End of install-step0 *}

                    {elseif $install_step eq '1'}
                    <div class="install-step1">
                        <h1>{tr}License{/tr}</h1>
                        <p>{tr}Tiki is software distributed under the LGPL license.{/tr} </p>
                        <div class="embed-responsive  embed-responsive-21by9 mb-3 card">
                            <iframe src="license.txt" class="embed-responsive-item"> </iframe>
                        </div>
                        <form action="tiki-install.php" method="post" role="form">
                            <div class="mb-3 text-center">
                                {if $multi}<input type="hidden" name="multi" value="{$multi}">{/if}
                                {if $lang}<input type="hidden" name="lang" value="{$lang}">{/if}
                                <input type="hidden" name="install_step" value="2">
                                <input type="submit" class="btn btn-primary" value="{tr}Continue{/tr}" >
                            </div>
                        </form>

                    </div>{* End of install-step1 *}

                    {elseif $install_step eq '2'}
                    <div class="install-step2">
                        <h1>{tr}Review the System Requirements{/tr}</h1>
                            {remarksbox type=tip title="{tr}Tip{/tr}" close="n"}
                                {tr}Before installing Tiki, <a href="https://doc.tiki.org/Requirements" target="_blank" class="alert-link">review the documentation</a> and confirm that your system meets the minimum requirements.{/tr}
                            {/remarksbox}
                            <p>{tr}This installer will perform some basic checks automatically.{/tr} {tr}Please see: {/tr}<a href="tiki-check.php" target="_blank">{tr}a detailed report about your server.{/tr}</a></p>

                        {if $php_properties_missing}
                            {remarksbox type=warning title="{tr}Missing minimum requirements{/tr}" close="n"}
                                <ul>
                                {foreach from=$php_properties_missing item=missing}
                                    <li>{$missing}</li>
                                {/foreach}
                                </ul>
                            {/remarksbox}
                        {/if}

                        <h2>{tr}Memory{/tr}</h2>
                        {if $php_memory_limit <= 0}
                            {remarksbox type=confirm title="{tr}Success{/tr}" close="n"}
                            {icon name="ok"} {tr}Tiki has not detected your PHP memory_limit.{/tr} {tr}This probably means you have no set limit (all is well).{/tr}
                            {/remarksbox}
                        {elseif $php_memory_limit < 128 * 1024 * 1024}
                            {remarksbox type=warning title="{tr}Warning{/tr}" close="n"}
                                <p align="center">{tr}Tiki has detected your PHP memory limit at:{/tr} {$php_memory_limit|kbsize:true:0}</p>
                                <p>{tr}Tiki requires <strong>at least</strong> 128MB of PHP memory for script execution.{/tr} {tr}Allocating too little memory will cause Tiki to display blank pages.{/tr}</p>
                                <p>{tr}To change the memory limit, use the <strong>memory_limit</strong> key in your <strong>php.ini </strong> file (for example: memory_limit = 128M) and restart your webserver.{/tr}</p>
                            {/remarksbox}
                        {else}
                            {remarksbox type=confirm title="{tr}Success{/tr}" close="n"}
                            {icon name="ok"} {tr}Tiki has detected your PHP memory_limit at:{/tr} <strong>{$php_memory_limit|kbsize:true:0}</strong>.
                            {/remarksbox}
                        {/if}
                        <h2>{tr}Mail{/tr}</h2><a id="mail"> </a>
                        <p>{tr}Tiki uses the PHP <strong>mail</strong> function to send email notifications and messages.{/tr}</p>
                        {if $mail_test_performed ne 'y'}
                            <p>{tr}To test your system configuration, Tiki will attempt to send a test message to you.{/tr}</p>
                            <form action="tiki-install.php#mail" method="post" role="form">
                                <div class="mb-3 row mt-4">
                                    <label class="col-form-label" class="col-sm-2" for="email_test_to">{tr}Test email:{/tr}</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" name="email_test_to" id="email_test_to" value="{if isset($email_test_to)}{$email_test_to|escape}{/if}" placeholder="{tr}tiki@example.com{/tr}">
                                    </div>
                                    {if isset($email_test_err)}
                                        <span class="attention"><em>{$email_test_err}</em></span>
                                    {else}
                                        <div class="col-sm-4 mt-2"><i>{tr}Email address to send test to.{/tr}</i></div>
                                    {/if}
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="email_test_cc" id="email_test_cc" value="1">
                                        <label class="form-check-label" for="email_test_cc">{tr}Copy test mail to {/tr} {$email_test_tw}</label>
                                    </div>
                                </div>
                                <input type="hidden" name="install_step" value="2">
                                <input type="hidden" name="perform_mail_test" value="y">
                                <div class="text-center">
                                    <input type="submit" class="btn btn-primary" value="{tr}Send Test Message{/tr}">
                                </div>
                                {if $multi}<input type="hidden" name="multi" value="{$multi}">{/if}
                                {if $lang}<input type="hidden" name="lang" value="{$lang}">{/if}
                            </form>
                        {else}
                            {if $mail_test eq 'y'}
                                {remarksbox type=confirm title="{tr}Success{/tr}" close="n"}
                                {icon name="ok"} {tr}Tiki was able to send a test message to{/tr} {$email_test_to}.
                                {/remarksbox}
                            {else}
                                {remarksbox type=error title="{tr}Error{/tr}" close="n"}
                                {icon name="error"} {tr}Tiki was not able to send a test message.{/tr} {tr}Review your mail log for details.{/tr}
                                    <p>{tr}Review the mail settings in your <strong>php.ini</strong> file (for example: confirm that the <strong>sendmail_path</strong> is correct).{/tr} {tr}If your host requires SMTP authentication, additional configuration may be necessary.{/tr}</p>
                                {/remarksbox}
                            {/if}
                        {/if}
                        <h2>{tr}Image Processing{/tr}</h2>
                        {if $gd_test eq 'y'}
                            {remarksbox type=confirm title="{tr}Success{/tr}" close="n"}
                            {icon name="ok"} {tr}Tiki detected:{/tr} <strong>GD {if $lang eq 'he' or $lang eq 'ar'}{$gd_info|regex_replace:"/[()]/":""}{else}{$gd_info}{/if}</strong>
                            {if $sample_image eq 'y'}
                                <p>{icon name="ok"} {tr}Tiki can create images.{/tr}</p>
                            {else}
                                <div style="background: #ffffcc; border: 2px solid #ff0000; color:#000;">
                                    <p>{icon name="warning"} {tr}Tiki was not able to create a sample image. Please check your GD library configuration.{/tr}.</p>
                                </div>
                            {/if}
                            {/remarksbox}
                        {else}
                            {remarksbox type=error title="{tr}Error{/tr}" close="n"}
                            {icon name="error"} {tr}Tiki was not able to detect the GD library.{/tr}
                            {/remarksbox}
                        {/if}
                        <p>{tr}Tiki uses the GD library to process images for the Image Gallery and CAPTCHA support.{/tr}</p>
                        <form action="tiki-install.php" method="post" role="form">
                            <div class="mb-3 text-center">
                                <input type="hidden" name="install_step" value="3">
                                <input type="submit" class="btn btn-primary" value=" {tr}Continue{/tr} ">
                                {if $multi}<input type="hidden" name="multi" value="{$multi}">{/if}
                                {if $lang}<input type="hidden" name="lang" value="{$lang}">{/if}
                            </div>
                        </form>
                    </div>{* End of install-step2 *}

                    {elseif $install_step eq '3' or ($dbcon eq 'n' or $resetdb eq 'y')}
                    {* we do not have a valid db connection or db reset is requested *}
                    <div class="install-step3">
                        <h1>{tr}Set the Database Connection{/tr}</h1>
                        <p>{tr}Tiki requires an active database connection.{/tr} {tr}You must create the database and user <strong>before</strong> completing this page, unless your database user has also permissions to create new databases and not just use them.{/tr}</p>
                        {if $dbcon ne 'y'}
                            <div align="center" style="padding:1em">
                                {icon name="warning"} <span style="font-weight:bold">{tr}Tiki cannot find a database connection.{/tr}</span> {tr}This is normal for a new installation.{/tr}
                            </div>
                        {else}
                            {remarksbox type=confirm title="{tr}Success{/tr}" close="n"}
                            {if $dbname}
                                {tr}Tiki found an existing database connection in your local.php file.{/tr}<br>
                                <strong>{tr _0=$dbname}Database name: %0{/tr}</strong>
                            {else}
                                {tr}Tiki found an automatic database connection for your environment.{/tr}
                            {/if}
                            {/remarksbox}
                            <div class="mb-3 text-center">
                                <form action="tiki-install.php" method="post" role="form">
                                    <input type="hidden" name="install_step" value="4">
                                    {if $multi}<input type="hidden" name="multi" value="{$multi}">{/if}
                                    {if $lang}<input type="hidden" name="lang" value="{$lang}">{/if}
                                    <input type="submit" class="btn btn-primary" value=" {tr}Use Existing Connection{/tr} ">
                                </form>
                                <hr>
                                <a href="#" onclick="$('#installer_3_new_db_form').toggle();return false;" class="btn btn-warning">{tr}Modify database connection{/tr}</a>
                            </div>
                        {/if}
                        <div id="installer_3_new_db_form"{if $dbcon eq 'y'} style="display:none;"{/if}>
                            <p>{tr}Use this page to create a new database connection, or use the <a href="https://doc.tiki.org/Manual-Installation" target="_blank" title="manual installation">manual installation process</a>.{/tr} <a href="https://doc.tiki.org/Manual-Installation" target="_blank" title="{tr}Help{/tr}">{icon name="help"}</a></p>
                            <form action="tiki-install.php" method="post" role="form">
                                <input type="hidden" name="install_step" value="4">
                                {if $multi}
                                    <input type="hidden" name="multi" value="{$multi}">
                                {/if}
                                {if $lang}
                                    <input type="hidden" name="lang" value="{$lang}">
                                {/if}
                                <fieldset>
                                    <legend>{tr}Database information{/tr}</legend>
                                    <p>{tr}Enter your database connection information.{/tr}</p>
                                    <div class="mb-3 row">
                                        <label for="db" class="col-form-label">{tr}DBMS driver:{/tr}</label>
                                        <div class="mx-3">
                                            <select class="form-select" name="db" id="db">
                                                {foreach key=dsn item=dbname from=$dbservers}
                                                    {if $dsn|stristr:"mysql" || $dsn|stristr:"pdo"}
                                                        <option value="{$dsn}"{if isset($smarty.request.db) and $smarty.request.db eq $dsn} selected="selected"{/if}>{$dbname}</option>
                                                    {/if}
                                                {/foreach}
                                            </select>
                                            <a href="javascript:void(0)" onclick="flip('db_help');" title="{tr}Help{/tr}">
                                                {icon name="help"}
                                            </a>
                                            <div style="display:none" id="db_help">
                                                <p>{tr}Select the database driver to use with Tiki.{/tr}</p>
                                                <p>{tr}Only drivers supported by your PHP installation are listed here. If your driver is not in the list, try to install the appropriate PHP extension.{/tr}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label class="col-form-label" for="host">{tr}Host name:{/tr}</label>
                                        <div class="mx-3">
                                            <input type="text" class="form-control" name="host" id="host" value="{if isset($smarty.request.host)}{$smarty.request.host|escape:"html"}{elseif isset($preconfighost)}{$preconfighost|escape:"html"}{else}localhost{/if}" size="40" />
                                            <a href="javascript:void(0)" onclick="flip('host_help');" title="{tr}Help{/tr}">
                                                {icon name="help"}
                                            </a>
                                            <br><em>{tr}Enter the host name or IP for your database.{/tr}</em>
                                            <div style="display:none;" id="host_help">
                                                <p>{tr}Use <strong>localhost</strong> if the database is running on the same machine as Tiki.{/tr}</p>
                                                <p>{tr}For non-default port number use <strong>example.com;port=3307</strong>.{/tr}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label class="col-form-label" for="name">{tr}Database name:{/tr}</label>
                                        <div class="mx-3">
                                            <input type="text" class="form-control" id="name" name="name" size="40" value="{if isset($smarty.request.name)}{$smarty.request.name|escape:"html"}{elseif isset($preconfigname)}{$preconfigname|escape:"html"}{/if}" placeholder="{tr}Database name{/tr}"/>
                                            <a href="javascript:void(0)" onclick="flip('name_help');" title="{tr}Help{/tr}">
                                                {icon name="help"}
                                            </a>
                                            <br><em>{tr}Name of the database to be used. This database will be created if it does not exist and permissions allow creation.{/tr}</em>
                                            <div class="mx-3" style="display:none;" id="name_help">
                                                <p>{tr}You can create the database using Adminer, MySQL Workbench, phpMyAdmin, cPanel, or ask your hosting provider. If the database doesn't exist and the supplied username has permissions, it will be created.{/tr}</p>
                                                <p>{tr}If you are using a database which is already being used for something else (not recommended), check db/tiki.sql to make sure the table names used by Tiki are not already used.{/tr}</p>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>

                                <br>
                                <fieldset>
                                    <legend>{tr}Database user{/tr}</legend>
                                    <p>{tr}Enter a database user with administrator permission for the Tiki database.{/tr}</p>
                                    <div style="padding:5px;">
                                        <label class="col-form-label" for="user">{tr}User name:{/tr}</label> <input type="text" class="form-control" id="user" name="user" value="{if (isset($smarty.request.user))}{$smarty.request.user|escape:"html"}{elseif isset($preconfiguser)}{$preconfiguser|escape:"html"}{/if}" maxlength="80" placeholder="{tr}Database username{/tr}">
                                    </div>

                                    <div style="padding:5px;">
                                        {if isset($preconfigpass)}
                                            <label class="col-form-label" for="pass">{tr}Password:{/tr}</label> <input type="text" class="form-control" id="pass" name="pass" value="{$preconfigpass|escape:"html"}" autocomplete="new-password">
                                        {else}
                                            <label class="col-form-label" for="pass">{tr}Password:{/tr}</label> <input type="password" class="form-control" id="pass" name="pass" autocomplete="new-password">
                                        {/if}
                                    </div>

                                    <div style="padding:5px;">
                                        <input type="checkbox" id="create-new-user" name="create_new_user" />
                                        <label class="col-form-label" for="create-new-user">{tr}Create the above database user just for this Tiki database.{/tr}</label>&nbsp;
                                    </div>
                                </fieldset>

                                <br/>
                                <fieldset id="new-user-fieldset" style="display: none;">
                                    <legend>{tr}Administrative database user{/tr}</legend>
                                    <p>{tr}Enter database administrator user name and password.{/tr}<br>
                                        <em>{tr}This is a DB admin user which has permission to create new databases and new users.{/tr}</em></p>
                                    <div style="padding:5px;">
                                        <label class="col-form-label" for="user">{tr}DB admin user name:{/tr}</label> <input type="text" class="form-control" id="root_user" name="root_user" value="{if (isset($smarty.request.root_user))}{$smarty.request.root_user|escape:"html"}{elseif isset($preconfiguser)}{$preconfiguser|escape:"html"}{/if}" placeholder="{tr}DB admin user name{/tr}">
                                    </div>
                                    <div style="padding:5px;">
                                        <label class="col-form-label" for="pass">{tr}DB admin password:{/tr}</label> <input type="password" class="form-control" id="root_pass" name="root_pass" value="{if (isset($smarty.request.root_pass))}{$smarty.request.root_pass|escape:"html"}{/if}" autocomplete="new-password">
                                    </div>
                                </fieldset>
                                <script type='text/javascript'><!--//--><![CDATA[//><!--
                                    ;(function(){
                                        var user = document.getElementById('user');
                                        var create_new_user = document.getElementById('create-new-user');
                                        var new_user_fs = document.getElementById('new-user-fieldset');

                                        if(create_new_user.checked) {
                                            new_user_fs.style.display = 'block';
                                        }

                                        create_new_user.addEventListener('click', function(){
                                            if(create_new_user.checked) {
                                                new_user_fs.style.display = 'block';
                                            } else {
                                                new_user_fs.style.display = 'none';
                                            }
                                        });
                                    })();//--><!]]></script>

                                <br/>
                                <input type="hidden" name="resetdb" value="y">
                                <fieldset>
                                    <legend>{tr}Character set{/tr}</legend>
                                    <p>{tr}Highly recommended for new installations. However, if you are upgrading or migrating a previous tiki database, you are recommended to uncheck this box{/tr}.</p>
                                    <input type="checkbox" name="force_utf8" id="force_utf8" value="y" checked="checked">
                                    <label class="col-form-label" for="force_utf8">{tr}Always force connection to use UTF-8{/tr}</label>
                                    <p><a href="https://doc.tiki.org/Understanding-Encoding" onclick="window.open(this.href); return false;">{tr}More information{/tr}</a></p>
                                </fieldset>
                                <div class="mb-3 row text-center">
                                    <input type="submit" class="btn btn-info" name="dbinfo" value="{tr}Continue{/tr}">
                                </div>
                            </form>
                        </div>
                    </div>{* End of install-step3 *}

                    {elseif $install_step eq '4'}
                    <div class="install-step4">
                        <h1>{if $tikidb_created}{tr}Install & Upgrade{/tr}{else}{tr}Install{/tr}{/if}</h1>
                        {if $max_exec_set_failed eq 'y'}
                            {remarksbox type="warning" title="{tr}Warning{/tr}" close="n"}
                            {tr}Failed to set max_execution_time for PHP. You may experience problems when creating/upgrading the database using this installer on a slow system. This can manifest itself by a blank page.{/tr}
                            {/remarksbox}
                        {/if}
                        {if $tikidb_created}
                            {tr}This install will populate (or upgrade) the database.{/tr}<br><br>
                            {remarksbox type=tip title="{tr}Tip{/tr}" close="n"}
                            {tr}If you want to upgrade from a previous Tiki release, ensure that you have read and understood the <a href="https://doc.tiki.org/Upgrade" target="_blank" class="alert-link">Upgrade instructions</a>.{/tr}
                            {/remarksbox}
                        {else}
                            {tr}A new install will populate the database.{/tr}
                        {/if}
                        {if (($database_charset neq 'utf8mb4' and $database_charset neq 'utf8') or isset($legacy_collation)) and $tikidb_created}
                            {remarksbox type=error title="{tr}Encoding Issue{/tr}" close="n"}
                            {if isset($legacy_collation)}
                                <strong style="color: red">Something is wrong with the database encoding.</strong> The database has UTF-8 as default encoding but some tables in the database have a different collation, {$legacy_collation}. Converting to UTF-8 may solve this but may also make matters worse. You should investigate what happened or only proceed with backups.
                            {else}
                                {tr _0=$database_charset}<p>Your database encoding is <strong>not</strong> in UTF-8mb4.</p><p>Current encoding is <em>%0</em>. The languages that will be available for content on the site will be limited. If you plan on using languages not covered by the character set, you should re-create or alter the database so the default encoding is <em>utf8mb4</em>.</p>{/tr}
                            {/if}
                                <p><a href="https://doc.tiki.org/Understanding-Encoding" class="alert-link">{tr}More information{/tr}</a></p>
                                <form method="post" action="" role="form">
                                    <fieldset>
                                        <legend>{tr}Character Set Conversion{/tr}</legend>
                                        <p>{tr}Use at your own risk. If the data in the database currently contains improperly converted data, this may make matters worse. Suitable for new installations. Requires ALTER privilege on the database.{/tr}</p>
                                        <p>
                                            <input type="submit" class="btn btn-warning" name="convert_to_utf8" value="{tr}Convert database and tables to UTF-8{/tr}">
                                            <input type="hidden" name="install_step" value="4">
                                            {if $multi}<input type="hidden" name="multi" value="{$multi}">{/if}
                                            {if $lang}<input type="hidden" name="lang" value="{$lang}">{/if}
                                        </p>
                                    </fieldset>
                                </form>
                            {/remarksbox}
                        {else}
                            {remarksbox type=confirm title="{tr}Success{/tr}" close="n"}
                                {icon name="ok"} {tr}You are using a correct database encoding and allowed legacy collation as required to complete the installation.{/tr}
                            {/remarksbox}
                        {/if}
                        {if $dbdone eq 'n'}
                            {if $logged eq 'y'}{* we are logged if no admin account is found or if the admin user is logged in*}
                                <div class="install-upgrade">
                                <form method="post" action="tiki-install.php" role="form" class="card-deck">
                                    <input type="hidden" name="install_step" value="5">
                                    {if $multi}<input type="hidden" name="multi" value="{$multi}">{/if}
                                    {if $lang}<input type="hidden" name="lang" value="{$lang}">{/if}
                                    <div class="row">
                                    <div class="col-sm-6">
                                    <div class="db-install card h-100">
                                        <div class="card-body">
                                            <h3 class="card-title mb-3">{tr}Install{/tr}</h3>
                                            {if $tikidb_created}
                                                {remarksbox type="danger" title="{tr}Warning{/tr}" close="n"}
                                                {tr _0=$dbname}This will destroy your current database: %0.{/tr}
                                                {/remarksbox}
                                            {/if}
                                            {if $tikidb_created}
                                            <script type='text/javascript'><!--//--><![CDATA[//><!--
                                                {literal}
                                                function install() {
                                                    document.getElementById('install-link').style.display='none';
                                                    document.getElementById('install-table').style.visibility='';
                                                }
                                                {/literal}
                                                //--><!]]></script>
                                            <div id="install-link">
                                                <p class="text-center"><a class="btn btn-danger" href="javascript:install()">{tr}Reinstall the database{/tr}</a></p>
                                            </div>
                                            <div id="install-table" style="visibility:hidden">
                                                {else}
                                                <div id="install-table">
                                                    {/if}
                                                    {if $hasInnoDB}
                                                        <label class="col-form-label" for="dbEnginge">{tr}Select database engine{/tr}</label>
                                                        <select name="useInnoDB" id="dbEnginge" class="form-control">
                                                            <option value="0">{tr}MyISAM{/tr}</option>
                                                            <option selected="selected" value="1">{tr}InnoDB{/tr}</option>
                                                        </select>
                                                    {else}
                                                        <input type="hidden" name="useInnoDB" value="0">
                                                    {/if}
                                                    <p class="text-center">
                                                        <input type="submit" class="btn btn-warning" name="scratch" value="{if $tikidb_created}{tr}Reinstall{/tr}{else}{tr}Install{/tr}{/if}" style="margin: 32px;">
                                                    </p>
                                                </div>
                                            </div>{* End of install-table *}
                                        </div>
                                        </div>{* End of db-install *}
                                        {if $tikidb_created}
                                            <div class="col-sm-6">
                                            <div class="db-upgrade card h-100">
                                                <div class=" card-body">
                                                    <h3 class="card-title mb-3">{tr}Upgrade{/tr}</h3>
                                                    {if $tikidb_oldPerms gt 0}
                                                        {remarksbox type="warning" title="{tr}Warning: Category Permissions Will Not Be Upgraded{/tr}" close="n"}
                                                        {tr}Category permissions have been revamped since version 3. If you have been using category permissions, note that they may not work properly after upgrading to version 4 onwards, and it will be necessary to reconfigure them.{/tr}
                                                        {/remarksbox}
                                                    {/if}
                                                    {remarksbox type="info" title="{tr}OK{/tr}" close="n"}{tr}Automatically upgrade your existing database to version{/tr}
                                                        <strong>{$tiki_version_name}</strong>.
                                                    {/remarksbox}
                                                    <p class="text-center"><input type="submit" class="btn btn-primary" name="update" value="{tr}Upgrade{/tr}"></p>
                                                </div>{* End of db-upgrade *}
                                            </div>
                                            </div>
                                            </div>
                                        {/if}
                                </form>
                            {else}{* we are not logged then no admin account found and user not logged *}
                                <p>{icon name="warning"} <span style="font-weight:bold">{tr}This site has an admin account configured.{/tr}</span></p>
                                <p>{tr}Please log in with your admin password to continue.{/tr}</p>
                                <form name="loginbox" action="tiki-install.php" method="post">
                                    <input type="hidden" name="login" value="admin">
                                    {if $multi}<input type="hidden" name="multi" value="{$multi}">{/if}
                                    {if $lang}<input type="hidden" name="lang" value="{$lang}">{/if}
                                    <table>
                                        <tr><td class="module">{tr}User:{/tr}</td><td><input value="admin" disabled="disabled" size="20"></td></tr>
                                        <tr><td class="module">{tr}Pass:{/tr}</td><td><input type="password" name="pass" size="20"></td></tr>
                                        <tr><td colspan="2"><p align="center"><input type="submit" class="btn btn-primary btn-sm" name="login" value="{tr}Log in{/tr}"></p></td></tr>
                                    </table>
                                </form>
                            {/if}
                            </div>{* End of install-upgrade *}
                        {/if}
                    </div>{* End of install-step4 *}

                    {elseif $install_step eq '5' or ($dbdone ne 'n')}
                    <div class="install-step5">
                        <h1>{if isset($smarty.post.update)}{tr}Review the Upgrade{/tr}{else}{tr}Review the Installation{/tr}{/if}</h1>
                        {remarksbox type=confirm title="{if isset($smarty.post.update)}{tr}Upgrade complete{/tr}{else}{tr}Installation complete{/tr}{/if}" close="n"}
                            <p>{tr}Your database has been configured and Tiki is ready to run!{/tr}
                                {if isset($smarty.post.scratch)}
                                    {tr}If this is your first install, your admin password is <strong>admin</strong>.{/tr}
                                {/if}
                                {tr}You can now log in into Tiki as user <strong>admin</strong> and start configuring the application.{/tr}
                            </p>
                        {/remarksbox}
                        {if $installer->queries.successful|@count gt 0}
                            <p><span class="text-success">{icon name="ok"}
                                    {if isset($smarty.post.update)}
                                        <strong>{tr}Upgrade operations executed successfully:{/tr}</strong>
                        {else}
                                <strong>{tr}Installation operations executed successfully:{/tr}</strong>
                                    {/if}
                        </span>
                                {$installer->queries.successful|@count} {tr}SQL queries.{/tr}</p>
                        {else}
                            <p>{icon name="ok"} <span class="text-warning"><strong>{tr}Database was left unchanged.{/tr}</strong></span></p>
                        {/if}
                        <form action="tiki-install.php" method="post">
                            {if $installer->queries.failed|@count > 0}
                            <script type='text/javascript'><!--//--><![CDATA[//><!--
                                {literal}
                                function sql_failed() {
                                    document.getElementById('sql_failed_log').style.display='block';
                                }
                                {/literal}
                                //--><!]]></script>

                            <p><span class="text-danger">{icon name="error"} <strong>{tr}Operations failed:{/tr}</strong> {$installer->queries.failed|@count} {tr}SQL queries.{/tr}
                                    <a href="javascript:sql_failed()">{tr}Display details.{/tr}</a>
                            <div id="sql_failed_log" style="display:none">
                            <p><span class="text-warning">{tr}During an upgrade, it is normal to have SQL failures resulting with <strong>Table already exists</strong> messages.{/tr}</span></p>
                            {assign var='patch' value=''}
                            {foreach from=$installer->queries.failed item=item}
                            {if $patch ne $item[2]}
                            {if $patch ne ''}
                                </textarea>
                            {/if}
                            <p>
                                <input type="checkbox" name="validPatches[]" value="{$item[2]|escape}" id="ignore_{$item[2]|escape}">
                                <label class="col-form-label" for="ignore_{$item[2]|escape}">{$item[2]|escape}</label>
                            </p>
                            {assign var='patch' value=$item[2]}
                            <textarea rows="6" cols="80">
                                    {/if}
                                {$item[0]}
                                {$item[1]}
                                {/foreach}
                                </textarea>
                            <p>If you think that the errors of a patch can be ignored, please check the checkbox associated to it before clicking on continue.</p>
                            <p>{select_all checkbox_names='validPatches[]' label="{tr}Check all errors{/tr}"}</p>
                    </div>
                    {/if}

                    <p>&nbsp;</p>
                    <div class="text-center">
                        <input type="hidden" name="install_step" value="6">
                        <input type="hidden" name="install_type" value="{$install_type}">
                        <input type="submit" class="btn btn-primary" value=" {tr}Continue{/tr} ">
                        {if $multi}<input type="hidden" name="multi" value="{$multi}">{/if}
                        {if $lang}<input type="hidden" name="lang" value="{$lang}">{/if}
                    </div>
                    </form>
                </div>{* End of install-step5 *}

                {elseif $install_step eq '6'}
                <div class="install-step6">
                    <h1>{tr}Configure General Settings{/tr}</h1>
                    <form action="tiki-install.php" method="post">
                        <div class="clearfix">
                            <p>{tr}Complete these fields to configure common, general settings for your site.{/tr} {tr}The information you enter here can be changed later.{/tr}</p>
                            {remarksbox type=info title="{tr}Tip{/tr}" close="n"}
                            {tr}Refer to the <a href="https://doc.tiki.org/Admin-home" target="_blank" class="alert-link">documentation</a> for complete information on these, and other, settings.{/tr}
                            {/remarksbox}
                            <br>
                            <fieldset>
                                <legend>
                                    {tr}General{/tr} <a href="https://doc.tiki.org/general-admin" target="_blank" title="{tr}Help{/tr}">{icon name="help"}</a>
                                </legend>
                                <div class="mb-3 row mx-0">
                                    <label class="col-form-label" for="browsertitle">
                                        {tr}Browser title:{/tr}
                                    </label>
                                    <input class="form-control" type="text" size="40" name="browsertitle" id="browsertitle" value="{if !empty($prefs.browsertitle)}{$prefs.browsertitle|escape}{else}{tr}My Tiki{/tr}{/if}">
                                    <span class="form-text">
                                        {tr}This will appear in the browser title bar.{/tr}
                                    </span>
                                </div>
                                <div class="mb-3 row mx-0">
                                    <label class="col-form-label" for="sender_email">
                                        {tr}Sender email:{/tr}
                                    </label>
                                    <input type="text" class="form-control" size="40" name="sender_email" id="sender_email" value="{$prefs.sender_email|escape}" placeholder="{tr}tiki@example.com{/tr}">
                                    <span class="form-text">
                                        {tr}Email sent by your site will use this address.{/tr}
                                    </span>
                                </div>
                                <div class="p-3">
                                    <details>
                                        <summary><label>{tr}Network Proxy?{/tr}</label> {tr}Toggle section display{/tr}</summary>
                                        <div class="mx-3"><label for="use_proxy">{tr}Use proxy{/tr}</label> <input type="checkbox" name="use_proxy" id="use_proxy"{if $prefs.use_proxy eq 'y'} checked="checked"{/if}><a href="https://doc.tiki.org/General-Settings" target="_blank" title="{tr}Help{/tr}">{icon name="help"}</a></div>
                                        <div class="mx-3"><label for="proxy_host">{tr}Proxy host name{/tr}</label><input type="text" class="form-control" size="40" name="proxy_host" id="proxy_host" value="{$prefs.proxy_host|escape}"></div>
                                        <div class="mx-3"><label for="proxy_port">{tr}Port{/tr}</label><input type="text" class="form-control" size="40" name="proxy_port" id="proxy_port" value="{$prefs.proxy_port|escape}"></div>
                                        <div class="mx-3"><label for="proxy_user">{tr}Proxy username{/tr}</label><input type="text" class="form-control" size="40" name="proxy_user" id="proxy_user" value="{$prefs.proxy_user|escape}"></div>
                                        <div class="mx-3"><label for="proxy_pass">{tr}Proxy password{/tr}</label><input type="text" class="form-control" size="40" name="proxy_pass" id="proxy_pass" value="{$prefs.proxy_pass|escape}"></div>
                                    </details>
                                </div>
                            </fieldset>
                            <br>
                            <fieldset>
                                <legend>{tr}Secure Log in{/tr}
                                    <a href="https://doc.tiki.org/login-config" target="_blank" title="{tr}Help{/tr}">
                                        {icon name="help"}
                                    </a>
                                </legend>
                                {remarksbox type=info title="{tr}Tip{/tr}" close="n"}
                                {tr}It is recommended to choose the "Require secure (HTTPS) login" option for better security. A security certificate and dedicated IP address are required to implement a secure login.{/tr}
                                {/remarksbox}
                                <div style="padding:5px; clear:both"><label for="https_login">{tr}HTTPS login:{/tr}</label>
                                    <select class="form-select" name="https_login" id="https_login" onchange="hidedisabled('httpsoptions',this.value);">
                                        <option value="disabled"{if $prefs.https_login eq 'disabled'} selected="selected"{/if}>{tr}Disabled{/tr}</option>
                                        <option value="allowed"{if $prefs.https_login eq 'allowed'} selected="selected"{/if}>{tr}Allow secure (HTTPS) login{/tr}</option>
                                        <option value="encouraged"{if $prefs.https_login eq 'encouraged' or ($prefs.https_login eq '' and $detected_https eq 'on' )} selected="selected"{/if}>{tr}Encourage secure (HTTPS) login{/tr}</option>
                                        <option value="force_nocheck"{if $prefs.https_login eq 'force_nocheck'} selected="selected"{/if}>{tr}Consider we are always in HTTPS, but do not check{/tr}</option>
                                        <option value="required"{if $prefs.https_login eq 'required'} selected="selected"{/if}>{tr}Require secure (HTTPS) login{/tr}</option>
                                    </select>
                                </div>
                                <div id="httpsoptions" style="display:{if $prefs.https_login eq 'disabled' or ( $prefs.https_login eq '' and $detected_https eq '')}none{else}block{/if};">
                                    <div style="padding:5px">
                                        <label for="https_port">{tr}HTTPS port:{/tr}</label> <input type="text" class="form-control" name="https_port" id="https_port" size="5" value="{$prefs.https_port|escape}">
                                    </div>
                                    <div style="padding:5px;clear:both">
                                        <div style="float:left"><input type="checkbox" id="feature_show_stay_in_ssl_mode" name="feature_show_stay_in_ssl_mode" {if $prefs.feature_show_stay_in_ssl_mode eq 'y'}checked="checked"{/if}></div>
                                        <div style="margin-left:20px;"><label for="feature_show_stay_in_ssl_mode"> {tr}Users can choose to stay in SSL mode after an HTTPS login.{/tr}</label></div>
                                    </div>
                                    <div style="padding:5px;clear:both">
                                        <div style="float:left"><input type="checkbox" id="feature_switch_ssl_mode" name="feature_switch_ssl_mode" {if $prefs.feature_switch_ssl_mode eq 'y'}checked="checked"{/if}></div>
                                        <div style="margin-left:20px;"><label for="feature_switch_ssl_mode">{tr}Users can switch between secured or standard mode at login.{/tr}</label></div>
                                    </div>
                                </div>
                            </fieldset>
                            <br>
                            <fieldset>
                                <legend>{tr}Logging and Reporting{/tr}</legend>
                                <div class="adminoptionbox">
                                    <label for="general-error">{tr}PHP error reporting level:{/tr}</label>
                                    <select class="form-select" name="error_reporting_level" id="general-error">
                                        <option value="0" {if $prefs.error_reporting_level eq 0}selected="selected"{/if}>{tr}No error reporting{/tr}</option>
                                        <option value="2047" {if $prefs.error_reporting_level eq 2047}selected="selected"{/if}>{tr}Report all PHP errors except strict{/tr}</option>
                                        <option value="-1" {if $prefs.error_reporting_level eq -1}selected="selected"{/if}>{tr}Report all PHP errors{/tr}</option>
                                        <option value="2039" {if $prefs.error_reporting_level eq 2039 or $prefs.error_reporting_level eq ''}selected="selected"{/if}>{tr}Report all errors except notices{/tr}</option>
                                        <option value="1" {if $prefs.error_reporting_level eq 1039}selected="selected"{/if}>{tr}According to the PHP configuration{/tr}</option>
                                    </select>
                                    <div class="mt-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="error_reporting_adminonly" name="error_reporting_adminonly"{if $prefs.error_reporting_adminonly eq 'y'} checked="checked"{/if}>
                                            <label class="form-check-label" for="error_reporting_adminonly">{tr}Visible to Admin only{/tr}</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="smarty_notice_reporting" name="smarty_notice_reporting"{if $prefs.smarty_notice_reporting eq 'y'} checked="checked"{/if}>
                                            <label class="form-check-label" for="smarty_notice_reporting">{tr}Include Smarty notices{/tr}</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input mb-2" type="checkbox" id="log_tpl" name="log_tpl"{if $prefs.log_tpl eq 'y'} checked="checked"{/if}>
                                            <label class="form-check-label mb-2" for="log_tpl">{tr}Add HTML comment at start and end of each Smarty template (.tpl file){/tr}</label>
                                            {remarksbox type=warning title="{tr}Warning{/tr}" close="n"}
                                            {tr}Use only for development, not in production at a live site, because these warnings are added to emails as well, and are visible to users in the page source.{/tr}
                                            {/remarksbox}
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                            <br>
                            <fieldset>
                                <legend>
                                    {tr}Administrator{/tr}
                                </legend>
                                <div class="mb-3 row mx-0">
                                    <label class="col-form-label" for="admin_email">
                                        {tr}Admin email:{/tr}
                                    </label>
                                    <input type="text" class="form-control" size="40" name="admin_email" id="admin_email" value="{if isset($admin_email)}{$admin_email}{/if}" placeholder="{tr}admin@example.com{/tr}">
                                    <span class="form-text">
                                        {tr}This is the email address for your administrator account.{/tr}
                                    </span>
                                </div>
                            </fieldset>
                            {if not empty($htaccess_options)}
                                <br>
                                <fieldset>
                                    <legend>
                                        {tr}Set up htaccess file{/tr}
                                    </legend>
                                    <div class="mb-3 row mx-0">
                                        <label class="col-form-label" for="htaccess_process">
                                            {tr}Method:{/tr}
                                        </label>
                                        <select class="form-select" name="htaccess_process" id="htaccess_process">
                                            {foreach $htaccess_options as $k => $v}
                                                <option value="{$k}">
                                                    {$v}
                                                </option>
                                            {/foreach}
                                        </select>
                                        <span class="form-text">
                                            {tr}Select how to set up your htaccess file.{/tr}
                                        </span>
                                    </div>
                                </fieldset>
                            {/if}

                            {if $upgradefix eq 'y' && $install_type eq 'update'}
                                <fieldset>
                                    <legend><span class="text-warning">{icon name="warning"}</span> {tr}Upgrade fix{/tr}</legend>
                                    <p>{tr}Experiencing problems with the upgrade? Your administrator account lost its privileges? This may occur if you upgraded from a very old version of Tiki.</p>
                                    <p>We can fix it! Doing so will:{/tr}</p>
                                    <ol>
                                        <li>{tr}Create the <em>Admins</em> group, if missing{/tr}</li>
                                        <li>{tr}Grant <em>tiki_p_admin</em> to the group, if missing{/tr}</li>
                                        <li>{tr}Add the administrator account to the group, if missing{/tr}</li>
                                    </ol>
                                    <p><strong>{tr}To do so enter the name of the main admin account in the field below{/tr}</strong></p>
                                    <div class="mb-3 row"><label class="col-form-label col-sm-4" for="admin_account">{tr}Administrator account (optional):{/tr}</label><div class="col-sm-4"> <input type="text" name="admin_account" class="form-control"></div><div class="col-sm-4"><em>{tr}The default account is <strong>admin</strong>{/tr}</em></div></div>
                                    {if !empty($disableAccounts)}
                                        <hr>
                                        <label class="col-form-label" for="fix_disable_accounts">{tr}Check this box if you have a lot of disabled accounts after an upgrade to tiki4.{/tr}</label>
                                        <input type="checkbox" id="fix_disable_accounts" name="fix_disable_accounts">
                                        <br/>
                                        {tr}List of accounts that will be enabled:{/tr}
                                        <ul>
                                            {foreach from=$disableAccounts item=account}
                                                <li>{$account}</li>
                                            {/foreach}
                                        </ul>
                                    {/if}
                                </fieldset>
                            {/if}
                        </div>

                        <div class="text-center mt-3">
                            {if $multi}<input type="hidden" name="multi" value="{$multi}">{/if}
                            {if $lang}<input type="hidden" name="lang" value="{$lang}">{/if}
                            <input type="hidden" name="install_step" value="7">
                            <input type="hidden" name="install_type" value="{$install_type}">
                            <input type="hidden" name="general_settings" value="y">
                            <input type="submit" class="btn btn-primary" value="{tr}Continue{/tr}">
                        </div>
                    </form>
                </div>{* End of install-step6 *}

                {elseif $install_step eq '7'}
                <div class="install-step7">
                    <h1>{tr}Last Notes{/tr}</h1>
                    {remarksbox type=note title="{tr}Important{/tr}" close="n"}
                    {tr}Read the following information to ensure that your website data stays protected, your site healthy and you don't unnecessarily loose time or data while setting up your Tiki site now or while maintaining it in the future.{/tr}
                    {/remarksbox}
                    <form action="tiki-install.php" method="post">
                        <div class="clearfix">
                            <h4>{icon name="shield-alt"} {tr}Subscribe to Tiki Releases newsletter{/tr} - {tr}Critical & Security update{/tr}</h4>
                            <p>{tr}It is highly recommended that you subscribe to the Tiki Releases newsletter, so that you receive important notices about new releases and critical security updates.{/tr}
                                {tr}We don't share subscribed emails and we send very few of these newsletters per year.{/tr}</p>
                            <p class="mb-4">{tr}Please use the following link and subscribe:{/tr} <a href="https://tiki.org/tiki-newsletters.php?nlId=8&info=1" title="Subscribe" target="_blank" class="text-danger">{tr}Tiki Releases newsletter{/tr}</a></p>

                            <h4>{icon name="magic"}{icon name="filter"} {tr}First Wizards, then Control Panels with Preference Filter{/tr}</h4>
                            <p>{tr}Tiki contains thousands of options and parameters (what we call "preferences"), which can be overwhelming for a new site administrator.{/tr}
                                {tr}That's why we suggest you to start by using our <a class='alert-link' target='tikihelp' href='https://doc.tiki.org/Wizards'>Wizards</a> ({icon name='magic'}).{/tr}
                                {tr}Once basic parameters will be set, you will always be able to gain full control of the options using the Control Panels. Note: basic preferences will be displayed by default after a new install.{/tr}</p>

                            <p class="mb-4">{tr}You can modify the default filter choice at your own convenience to also display Advanced, Experimental or Unavailable preferences in Control Panels.{/tr}
                                {tr}You'll find the <a class='alert-link' target='tikihelp' href='https://doc.tiki.org/Preference-Filters'>Preference Filter</a> at the top of the Navigation Bar in the <a class='alert-link' target='tikihelp' href='https://doc.tiki.org/Control-Panels'>Control Panels</a> by clicking on the funnel icon ({icon name='filter'}) or use the search box provided.{/tr}<br />

                            <h4>{icon name="hdd"} {tr}Storing your uploaded files{/tr}</h4>
                            <p>{tr}To ease the install process and first access, Tiki saves your uploaded files (office documents, images, pdf, etc. attached to wiki pages, forum posts, tracker items, file galleries, ...) by default in its database.{/tr}
                                {tr}This works perfectly in most cases but it is not the recommended setup if you need to save many thousands of files or more.{/tr}
                            <p class="mb-4">{tr}In that case, consider switching from "<strong>Store to database</strong>" to "<strong>Store to directory</strong>", which you will find in the <em>Configuration Wizard - Set up File Gallery & Attachments</em> or in the <em>Control Panels - File Galleries</em> where you will be able to migrate your currently uploaded files from one to the other.{/tr}</p>

                            <h4>{icon name="group"} {tr}Tiki Community{/tr}</h4>
                            <p>{tr}The Tiki Community is a global network of developers, site operators, <a href='https://tiki.org/Consultants' target='_blank'>consultants</a> and end users.{/tr}</p>
                            <ul>
                                <li><a href='https://tiki.org/Join' target='_blank'>{tr}Join the Community{/tr}</a></li>
                                <li><a href='https://tiki.org/Help' target='_blank'>{tr}Get Help with Tiki{/tr}</a></li>
                                <li><a href='https://tiki.org/Consultants' target='_blank'>{tr}Hire a Tiki Service Provider{/tr}</a></li>
                                <li>{tr}Help to improve the <a href='https://dev.tiki.org/' target='_blank'>Code</a> and the <a href='https://doc.tiki.org/' target='_blank'>Documentation</a>{/tr}</li>
                            </ul>
                        </div>

                        <div class="text-center mt-3">
                            {if $multi}<input type="hidden" name="multi" value="{$multi}">{/if}
                            {if $lang}<input type="hidden" name="lang" value="{$lang}">{/if}
                            <input type="hidden" name="install_step" value="8">
                            <input type="hidden" name="install_type" value="{$install_type}">
                            <input type="submit" class="btn btn-primary" value="{tr}Continue{/tr}">
                        </div>
                    </form>
                </div>{* End of install-step7 *}

                {elseif $install_step eq '8'}
                <div class="install-step8">
                    <h1 class="pagetitle">{tr}Enter Your Tiki{/tr}</h1>
                    {remarksbox type='confirm' title="{tr}Ready to run{/tr}" close="n"}
                        <p>{tr}The installation is complete!{/tr} {tr}Your database has been configured and Tiki is ready to run.{/tr} </p>
                    {/remarksbox}
                    {if isset($htaccess_error) and $htaccess_error eq 'y'}
                        <h3>{tr}.htaccess File{/tr} <a title="{tr}Help{/tr}" href="https://doc.tiki.org/Installation" target="help">{icon name="help"}</a></h3>
                        {tr}We recommend enabling the <strong>.htaccess</strong> file for your Tiki{/tr}. {tr}This will enable you to use SEFURLs (search engine friendly URLs) and help improve site security{/tr}.
                        <p>{tr _0="<strong>_htaccess</strong>" _1="<strong>.htaccess</strong>"}To enable this file, simply copy the %0 file (located in the main directory of your Tiki installation) to %1.{/tr}</p>
                    {elseif not empty($htaccess_feedback)}
                        <p>{tr _0=$htaccess_feedback}Your .htaccess file has been set up (%0){/tr}</p>
                    {/if}

                    <p>
                        {if $install_type eq 'scratch'}
                            {tr}If this is your first install, your admin password is <strong>admin</strong>.{/tr}
                        {/if}
                        {tr}You can now log in into Tiki as user <strong>admin</strong> and start configuring the application.{/tr}
                    </p>
                    {if isset($smarty.post.update)}
                        <h3>{icon name='information'} {tr}Upgrade{/tr}</h3>
                        <p>{tr}If this is an upgrade, clean the Tiki caches manually (the <strong>temp/templates_c</strong> directory) or by using the <strong>Admin &gt; System</strong> option from the Admin menu.{/tr}</p>
                    {/if}
                    {if $tikidb_is20}
                        <div class="row mx-0 mb-4">
                            <div class="col-sm-6">
                                <form method="post" action="tiki-install.php" class="enter-tiki">
                                    {if $multi}
                                        <input type="hidden" name="multi" value="{$multi|escape}">
                                    {/if}
                                    <input type="hidden" name="install_type" value="{$install_type}">
                                    <input type="hidden" name="install_step" value="9">
                                    <input type="submit" value="{tr}Enter Tiki and Lock Installer{/tr} ({tr}Recommended{/tr})" class="btn btn-primary">
                                </form>
                            </div>
                            <div class="col-sm-6">
                                <form method="post" action="tiki-install.php" class="enter-tiki">
                                    <input type="hidden" name="nolockenter" value="1">
                                    {if $multi}
                                        <input type="hidden" name="multi" value="{$multi|escape}">
                                    {/if}
                                    <input type="hidden" name="install_type" value="{$install_type}">
                                    <input type="hidden" name="install_step" value="9">
                                    <input type="submit" value="{tr}Enter Tiki Without Locking Installer{/tr}" class="btn btn-warning">
                                    <br><em><span class="text-warning">{icon name="warning"}</span> {tr}Not recommended due to security risk{/tr}.</em>
                                </form>
                            </div>
                        </div>
                    {/if}
                    {if $install_type eq 'update'}
                        {if $double_encode_fix_attempted eq 'y'}
                            <p>{tr}You can now access the site normally. Report back any issues that you might find (if any) to the Tiki forums or bug tracker{/tr}</p>
                        {elseif not isset($legacy_collation)}
                            <form class="d-flex flex-row flex-wrap align-items-center" method="post" action="#" onsubmit="return confirm("{tr}Are you sure you want to attempt to fix the encoding of your entire database?{/tr}");" class="mt-5">
                            <fieldset>
                                <legend>{tr}Upgrading and running into encoding issues?{/tr}</legend>
                                <p>{tr}We can try to fix it, but <strong>make sure you have backups, and can restore them</strong>.{/tr}</p>
                                {if ($client_charset_in_file eq 'utf8' or $client_charset_in_file eq 'utf8mb4') and ($database_charset eq 'utf8mb4' or $database_charset eq 'utf8')}
                                    <div class="d-flex flex-wrap mx-0 align-items-center">
                                        <div class="input-group col-auto">
                                            <label class="col-form-label" for="previous_encoding">{tr}Previous table encoding:{/tr}</label>
                                            <select class="form-select ms-2" name="previous_encoding" id="previous_encoding">
                                                <option value="">{tr}Please select{/tr}</option>
                                                <option value="armscii8" title="Armenian, Binary">armscii8</option>
                                                <option value="ascii" title="West European (multilingual), Binary">ascii</option>
                                                <option value="big5" title="Traditional Chinese, Binary">big5</option>
                                                <option value="binary" title="Binary">binary</option>
                                                <option value="cp1250" title="Central European (multilingual), Binary">cp1250</option>
                                                <option value="cp1251" title="Cyrillic (multilingual), Binary">cp1251</option>
                                                <option value="cp1256" title="Arabic, Binary">cp1256</option>
                                                <option value="cp1257" title="Baltic (multilingual), Binary">cp1257</option>
                                                <option value="cp850" title="West European (multilingual), Binary">cp850</option>
                                                <option value="cp852" title="Central European (multilingual), Binary">cp852</option>
                                                <option value="cp866" title="Russian, Binary">cp866</option>
                                                <option value="cp932" title="Japanese, Binary">cp932</option>
                                                <option value="dec8" title="West European (multilingual), Binary">dec8</option>
                                                <option value="eucjpms" title="Japanese, Binary">eucjpms</option>
                                                <option value="euckr" title="Korean, Binary">euckr</option>
                                                <option value="gb2312" title="Simplified Chinese, Binary">gb2312</option>
                                                <option value="gbk" title="Simplified Chinese, Binary">gbk</option>
                                                <option value="geostd8" title="Georgian, Binary">geostd8</option>
                                                <option value="greek" title="Greek, Binary">greek</option>
                                                <option value="hebrew" title="Hebrew, Binary">hebrew</option>
                                                <option value="hp8" title="West European (multilingual), Binary">hp8</option>
                                                <option value="keybcs2" title="Czech-Slovak, Binary">keybcs2</option>
                                                <option value="koi8r" title="Russian, Binary">koi8r</option>
                                                <option value="koi8u" title="Ukrainian, Binary">koi8u</option>
                                                <option value="latin1" title="West European (multilingual), Binary">latin1</option>
                                                <option value="latin2" title="Central European (multilingual), Binary">latin2</option>
                                                <option value="latin5" title="Turkish, Binary">latin5</option>
                                                <option value="latin7" title="Baltic (multilingual), Binary">latin7</option>
                                                <option value="macce" title="Central European (multilingual), Binary">macce</option>
                                                <option value="macroman" title="West European (multilingual), Binary">macroman</option>
                                                <option value="sjis" title="Japanese, Binary">sjis</option>
                                                <option value="swe7" title="Swedish, Binary">swe7</option>
                                                <option value="tis620" title="Thai, Binary">tis620</option>
                                                <option value="ucs2" title="Unicode (multilingual), Binary">ucs2</option>
                                                <option value="ujis" title="Japanese, Binary">ujis</option>
                                            </select>
                                            <input type="submit" class="btn btn-danger btn-sm ml-2" name="fix_double_encoding" value="{tr}Click to fix double encoding (dangerous){/tr}">
                                        </div>
                                        <input type="hidden" name="install_step" value="8">
                                    </div>
                                {else}
                                    <p>{tr}Oops. You need to make sure client charset and database encoding are forced to UTF-8. Reset the database connection to continue.{/tr}</p>
                                {/if}
                            </fieldset>
                            </form>
                        {/if}
                    {/if}
                </div>{* End of install-step8 *}

                {/if}{* end elseif $install_step... *}

                <div class="content">
                    {if $virt}
                        <div class="box-shadow">
                            <div class="box">
                                <h3 class="box-title">{tr}MultiTiki Setup{/tr} <a title="{tr}Help{/tr}" href="https://doc.tiki.org/MultiTiki" target="help">{icon name='help'}</h3>
                                <div class="clearfix box-data">
                                    {if !empty({$multi})}
                                        <div><a href="#" onclick="$('#multi').submit();return false;">{tr}Default Installation{/tr}</a></div>
                                        <form method="post" action="tiki-install.php" id="multi">
                                            <input type="hidden" name="install_step" value="0">
                                            <input type="hidden" name="multi" value="">
                                        </form>
                                    {/if}
                                    {foreach key=k item=i from=$virt}
                                        <div>
                                            <tt>{if $i eq 'y'}<strong style="color:#00CC00">{tr}DB OK{/tr}</strong>{else}<strong style="color:#CC0000">{tr}No DB{/tr}</strong>{/if}</tt>
                                            {if $k eq $multi}
                                                <strong>{$k}</strong>
                                            {else}
                                                <a href="#" onclick="$('#virt{$i@index}').submit();return false;" class="linkmodule">{$k}</a>
                                                <form method="post" action="tiki-install.php" id="virt{$i@index}">
                                                    <input type="hidden" name="multi" value="{$k}">
                                                    <input type="hidden" name="install_step" value="0">
                                                </form>
                                            {/if}
                                        </div>
                                    {/foreach}
                                    <br>
                                    <div><strong>{tr}Adding a new host:{/tr}</strong></div>
                                    {tr}To add a new virtual host run the setup.sh with the domain name of the new host as a last parameter.{/tr}
                                    {if $multi} <h3> ({tr}MultiTiki{/tr})</h3> <h5>{$multi|default:"{tr}Default{/tr}"} </h5> {/if}
                                </div>
                            </div>
                        </div>
                    {/if}
                </div>
                {if $dbcon eq 'y' and ($install_step eq '0' or !$install_step)}
                    <div>
                        {remarksbox type=info title="{tr}Upgrade{/tr}" close="n"}
                        {tr}Are you upgrading an existing Tiki site?{/tr}
                        {tr}Go directly to the <strong>Install/Upgrade</strong> step.{/tr}
                        {if $dbcon eq 'y' or isset($smarty.post.scratch) or isset($smarty.post.update)}
                            {icon name="next" href="#" onclick="$('[name=install_step][value=4]').prop('disabled', false).click();return false;" title="{tr}Install/Upgrade{/tr}"}
                        {/if}
                        {/remarksbox}
                    </div>
                {/if}
            </div>
        </div>
    </div>{* End of install-header *}

    <div class="floating-links">
        <a href="https://tiki.org" target="_blank"
            title="{tr}Powered by{/tr} {tr}Tiki Wiki CMS Groupware{/tr} &copy; 2002–{$smarty.now|date_format:"%Y"} "
            class="btn"><img src="img/tiki/tikibutton.png" alt="{tr}Powered by Tiki Wiki CMS Groupware{/tr}"></a>
    </div>{* End of install-footer *}

    {*</div>*}{* End of install-body *}
</div>{* End of tiki-install container *}