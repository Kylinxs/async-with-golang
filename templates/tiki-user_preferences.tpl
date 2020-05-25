{* $Id$ *}
{if $userwatch ne $user}
    {title help="User Preferences"}{tr}User Preferences:{/tr} {$userwatch}{/title}
{else}
    {title help="User Preferences"}{tr}User Preferences{/tr}{/title}
{/if}
{if $userwatch eq $user or $userwatch eq ""}
    {include file='tiki-mytiki_bar.tpl'}
{/if}
{if $tiki_p_admin_users eq 'y'}
    <div class="t_navbar btn-group mb-3">
        {assign var=thisuser value=$userinfo.login}
        {button href="tiki-assignuser.php?assign_user=$thisuser" _type="link" _text="{tr}Assign Group{/tr}"}
        {button href="tiki-user_information.php?view_user=$thisuser" _type="link" _text="{tr}User Information{/tr}"}
    </div>
{/if}
{tabset name="mytiki_user_preference"}
    {if $prefs.feature_userPreferences eq 'y'}
        {tab name="{tr}Personal Information{/tr}"}
            <h2>{tr}Personal Information{/tr}</h2>
            <form role="form" action="tiki-user_preferences.php" method="post">
                {ticket}
                <input type="hidden" name="view_user" value="{$userwatch|escape}">
                <div class="mb-3 row">
                    <label class="col-form-label col-md-4" for="userIn">
                        {tr}User{/tr}
                    </label>
                    <div class="col-md-8">
                        <input class="form-control" disabled value="{$userinfo.login|escape}">
                        <span class="form-text">
                            {tr}Last login:{/tr} {$userinfo.lastLogin|tiki_long_datetime}
                        </span>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-form-label col-md-4" for="realName">
                        {tr}Real Name{/tr}
                    </label>
                    <div class="col-md-8">
                        <input class="form-control" type="text" name="realName" value="{$user_prefs.realName|escape}"
                        {if $prefs.auth_ldap_nameattr eq '' || $prefs.auth_method ne 'ldap'}{else}disabled{/if}>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-form-label col-md-4">
                        {tr}Profile picture{/tr}
                    </label>
                    <div class="col-md-8">
                        {$avatar}
                        {if $prefs.user_use_gravatar eq 'y'}
                            <a class="link" href="https://doc.tiki.org/Gravatar" target="_blank">{tr}Pick user profile picture{/tr}</a>
                        {else}
                            <a class="link" href="tiki-pick_avatar.php{if $userwatch ne $user}?view_user={$userwatch}{/if}">{tr}Pick user profile picture{/tr}</a>
                        {/if}
                    </div>
                </div>
                {if $prefs.feature_community_gender eq 'y'}
                    <div class="mb-3 row">
                        <label class="col-form-label col-md-4" for="gender">
                            {tr}Gender{/tr}
                        </label>
                        <div class="col-md-8">
                            <label>
                                <input type="radio" name="gender" value="Male" {if $user_prefs.gender eq 'Male'}checked="checked"{/if}> {tr}Male{/tr}
                            </label>
                            <label>
                                <input type="radio" name="gender" value="Female" {if $user_prefs.gender eq 'Female'}checked="checked"{/if}> {tr}Female{/tr}
                            </label>
                            <label>
                                <input type="radio" name="gender" value="Hidden" {if $user_prefs.gender eq 'Hidden'}checked="checked"{/if}> {tr}Hidden{/tr}
                            </label>
                        </div>
                    </div>
                {/if}
                <div class="mb-3 row">
                    <label class="col-form-label col-md-4" for="country">
                        {tr}Country{/tr}
                    </label>
                    {*{if isset($user_prefs.country) && $user_prefs.country != "None" && $user_prefs.country != "Other"}*}
                        {*{$userinfo.login|countryflag}*}
                    {*{/if}*}
                    <div class="col-md-8">
                        <select name="country" id="country" class="form-select">
                            <option value="Other" {if $user_prefs.country eq "Other"}selected="selected"{/if}>
                                {tr}Other{/tr}
                            </option>
                            {foreach from=$flags item=flag key=fval}{strip}
                                {if $fval ne "Other"}
                                    <option value="{$fval|escape}" {if $user_prefs.country eq $fval}selected="selected"{/if}>
                                        {$flag|stringfix}
                                    </option>
                                {/if}
                            {/strip}{/foreach}
                        </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-form-label col-md-4" for="location">
                        {tr}Location{/tr}
                    </label>
                    <div class="col-md-8 mb-5" style="height: 250px;" data-geo-center="{defaultmapcenter}" data-target-field="location">
                        <div class="map-container" style="height: 250px;" data-geo-center="{defaultmapcenter}" data-target-field="location"></div>
                    </div>
                    <input type="hidden" name="location" id="location" value="{$location|escape}">
                </div>
                <div class="mb-3 row">
                    <label class="col-form-label col-md-4" for="homePage">
                        {tr}Homepage URL{/tr}
                    </label>
                    <div class="col-md-8">
                        <input type="text" class="form-control" name="homePage" value="{$user_prefs.homePage|escape}">
                    </div>
                </div>
                {if $prefs.feature_wiki eq 'y' and $prefs.feature_wiki_userpage eq 'y'}
                    <div class="mb-3 row">
                        <label class="col-form-label col-md-4">
                            {tr}Your Personal Wiki Page{/tr}
                        </label>
                        <div class="col-md-8">
                            {if $userPageExists eq 'y'}
                                <a class="link" href="tiki-index.php?page={$prefs.feature_wiki_userpage_prefix}{$userinfo.login}" title="View">
                                    {$prefs.feature_wiki_userpage_prefix}{$userinfo.login|escape}
                                </a>
                                (<a class="link" href="tiki-editpage.php?page={$prefs.feature_wiki_userpage_prefix}{$userinfo.login}">
                                    {tr}Edit{/tr}
                                </a>)
                            {else}
                                {$prefs.feature_wiki_userpage_prefix}{$userinfo.login|escape}
                                (<a class="link" href="tiki-editpage.php?page={$prefs.feature_wiki_userpage_prefix}{$userinfo.login}">
                                {tr}Create{/tr}
                            </a>)
                            {/if}
                        </div>
                    </div>
                {/if}
                {if $prefs.userTracker eq 'y' && $usertrackerId}
                    <div class="mb-3 row">
                        {if $tiki_p_admin eq 'y' and !empty($userwatch) and $userwatch neq $user}
                            <label class="col-form-label col-md-4">{tr}User's personal tracker information{/tr}</label>
                            <div class="col-md-8">
                                <a class="link" href="tiki-view_tracker_item.php?trackerId={$usertrackerId}&user={$userwatch|escape:url}&view=+user">
                                    {tr}View extra information{/tr}
                                </a>
                            </div>
                        {else}
                            <label class="col-form-label col-md-4">{tr}Your personal tracker information{/tr}</label>
                            <div class="col-md-8">
                                <a class="link" href="tiki-view_tracker_item.php?view=+user">
                                    {tr}View extra information{/tr}
                                </a>
                            </div>
                        {/if}
                    </div>
                {/if}
                {* Custom fields *}
                {section name=ir loop=$customfields}
                    {if $customfields[ir].show}
                        <label>{$customfields[ir].label}:
                        <input type="{$customfields[ir].type}" name="{$customfields[ir].prefName}"
                            value="{$customfields[ir].value}" size="{$customfields[ir].size}"></label>
                    {/if}
                {/section}
                <div class="mb-3 row">
                    <label class="col-form-label col-md-4" for="user_information">
                        {tr}User Information{/tr}
                    </label>
                    <div class="col-md-8">
                        <select class="form-select" id="user_information" name="user_information">
                            <option value='private' {if $user_prefs.user_information eq 'private'}selected="selected"{/if}>
                                {tr}Private{/tr}
                            </option>
                            <option value='public' {if $user_prefs.user_information eq 'public'}selected="selected"{/if}>
                                {tr}Public{/tr}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="submit text-center">
                    <input type="submit" class="btn btn-primary" name="new_info" value="{tr}Save changes{/tr}">
                </div>
            </form>
        {/tab}
        {tab name="{tr}Preferences{/tr}"}
            <h2>{tr}Preferences{/tr}</h2>
            <legend>{tr}General settings{/tr}</legend>
            <form role="form" action="tiki-user_preferences.php" method="post">
                {ticket}
                <input type="hidden" name="view_user" value="{$userwatch|escape}">
                <div class="mb-3 row">
                    <label class="col-form-label col-md-4" for="email_isPublic">
                        {tr}Is email public?{/tr}
                    </label>
                    <div class="col-md-8">
                        {if !empty($userinfo.email)}
                            <select id="email_isPublic" name="email_isPublic" class="form-select">
                                {section name=ix loop=$scramblingMethods}
                                    <option value="{$scramblingMethods[ix]|escape}" {if $user_prefs.email_isPublic eq $scramblingMethods[ix]}selected="selected"{/if}>
                                        {$scramblingEmails[ix]}
                                    </option>
                                {/section}
                            </select>
                            <span class="form-text">{tr}If email is public, select a scrambling method to prevent spam{/tr}</span>
                        {else}
                            <p class="form-control-plaintext">{tr}Unavailable - please set your email below{/tr}</p>
                        {/if}
                    </div>
                </div>
                {if $prefs.feature_perspective eq 'y' and $perspectives|@count gt 0}
                <div class="mb-3 row">
                    <label class="col-form-label col-md-4" for="perspective_preferred">
                        {tr}Preferred perspective{/tr}
                    </label>
                    <div class="col-md-8">
                        <select id="perspective_preferred" name="perspective_preferred" class="form-select">
                            <option value="">----</option>
                            {foreach from=$perspectives item=persp}
                                <option value="{$persp.perspectiveId|escape}"{if $persp.perspectiveId eq $user_prefs.perspective_preferred} selected="selected"{/if}>{$persp.name|escape}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                {/if}
                <div class="mb-3 row">
                    <label class="col-form-label col-md-4" for="mailCharset">
                        {tr}Email character set{/tr}
                    </label>
                    <div class="col-md-8">
                        <select id="mailCharset" name="mailCharset" class="form-select">
                            {section name=ix loop=$mailCharsets}
                                <option value="{$mailCharsets[ix]|escape}" {if $user_prefs.mailCharset eq $mailCharsets[ix]}selected="selected"{/if}>
                                    {$mailCharsets[ix]}
                                </option>
                            {/section}
                        </select>
                        <span class="form-text">{tr}Special character set for your email application{/tr}</span>
                    </div>
                </div>
                {if $prefs.change_theme eq 'y' && empty($group_theme)}
                    <div class="mb-3 row">
                        <label class="col-form-label col-md-4" for="mytheme">
                            {tr}Theme{/tr}
                        </label>
                        <div class="col-md-8">
                            <select id="mytheme" name="mytheme" class="form-select">
                                {assign var="userwatch_themeoption" value="{$userwatch_theme}{if $userwatch_themeOption}/{$userwatch_themeOption}{/if}"}
                                <option value="" class="text-muted bg-info">{tr}Site theme{/tr} ({$prefs.theme}{if !empty($prefs.theme_option)}/{$prefs.theme_option}{/if})</option>
                                {foreach from=$available_themesandoptions key=theme item=theme_name}
                                    <option value="{$theme|escape}" {if $userwatch_themeoption eq $theme}selected="selected"{/if}>{$theme_name|ucwords}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                {/if}
                {if $prefs.change_language eq 'y'}
                    <div class="mb-3 row clearfix">
                        <label class="col-form-label col-md-4" for="language">
                            {tr}Language{/tr}
                        </label>
                        <div class="col-md-8">
                            <select id="language" name="language" class="form-select">
                                {section name=ix loop=$languages}
                                    <option value="{$languages[ix].value|escape}" {if $user_prefs.language eq $languages[ix].value}selected="selected"{/if}>
                                        {$languages[ix].name}
                                    </option>
                                {/section}
                                <option value='' {if !$user_prefs.language}selected="selected"{/if}>
                                    {tr}Site default{/tr}
                                </option>
                            </select>
                        </div>
                    </div>
                {/if}
                {if $tiki_p_admin eq 'y'}
                    <div class="mb-3 row clearfix">
                        <label class="col-form-label col-md-4" for="languageAdmin">
                            {tr}Admin Language{/tr}
                        </label>
                        <div class="col-md-8">
                            <select id="languageAdmin" name="languageAdmin" class="form-select">
                                {section name=ix loop=$languages}
                                    <option value="{$languages[ix].value|escape}" {if $user_prefs.language_admin eq $languages[ix].value}selected="selected"{/if}>
                                        {$languages[ix].name}
                                    </option>
                                {/section}
                                <option value='' {if !$user_prefs.language_admin}selected="selected"{/if}>
                                    {tr}Site default{/tr}
                                </option>
                            </select>
                        </div>
                    </div>
                {/if}
                {if $prefs.feature_multilingual eq 'y'}
                    {if !empty($user_prefs.read_language)}
                        <div id="read-lang-div" class="mb-3 row clearfix">
                    {else}
                        <div class="mb-3 row clearfix">
                            <div class="col-md-8 offset-md-4">
                                <a href="javascript:void(0)" onclick="document.getElementById('read-lang-div').style.display='block';this.style.display='none';">
                                    {tr}Can you read more languages?{/tr}
                                </a>
                            </div>
                        </div>
                        <div id="read-lang-div" style="display: none" class="mb-3 row clearfix">
                    {/if}
                    <label class="col-form-label col-md-4" for="read-language">{tr}Other languages you can read{/tr}</label>
                    <div class="col-md-8">
                        <select class="form-select" id="read-language" name="_blank" onchange="document.getElementById('read-language-input').value+=' '+this.options[this.selectedIndex].value+' '">
                            <option value="">{tr}Select language...{/tr}</option>
                            {section name=ix loop=$languages}
                                <option value="{$languages[ix].value|escape}">
                                    {$languages[ix].name}
                                </option>
                            {/section}
                        </select>
                        <div class="form-text">{tr}Select from the dropdown to add automatically to the list below{/tr}</div>
                    </div>
                    <label for="read-language-input" class="col-md-8 offset-md-4">
                        <input class="form-control" id="read-language-input" type="text" name="read_language" value="{$user_prefs.read_language}">
                    </label>
                    </div>
                {/if}
                <div class="mb-3 row clearfix">
                    <label class="col-form-label col-md-4" for="userbreadCrumb">
                        {tr}Number of visited pages to remember{/tr}
                    </label>
                    <div class="col-md-8">
                        <select id="userbreadCrumb" name="userbreadCrumb" class="form-control">
                            <option value="1" {if $user_prefs.userbreadCrumb eq 1}selected="selected"{/if}>1</option>
                            <option value="2" {if $user_prefs.userbreadCrumb eq 2}selected="selected"{/if}>2</option>
                            <option value="3" {if $user_prefs.userbreadCrumb eq 3}selected="selected"{/if}>3</option>
                            <option value="4" {if $user_prefs.userbreadCrumb eq 4}selected="selected"{/if}>4</option>
                            <option value="5" {if $user_prefs.userbreadCrumb eq 5}selected="selected"{/if}>5</option>
                            <option value="10" {if $user_prefs.userbreadCrumb eq 10}selected="selected"{/if}>10</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3 row clearfix">
                    <label class="col-form-label col-md-4" for="display_timezone">
                        {tr}Displayed timezone{/tr}
                    </label>
                    <div class="col-md-8">
                        <select name="display_timezone" class="form-control" id="display_timezone"{if $warning_site_timezone_set eq 'y'} disabled{/if}>
                            <option value="" style="font-style:italic;">
                                {tr}Detect user time zone if browser allows, otherwise site default{/tr}
                            </option>
                            <option value="Site" style="font-style:italic;border-bottom:1px dashed #666;"
                                    {if isset($user_prefs.display_timezone) and $user_prefs.display_timezone eq 'Site'} selected="selected"{/if}>
                                {tr}Site default{/tr}
                            </option>
                            {foreach key=tz item=tzinfo from=$timezones}
                                {math equation="floor(x / (3600000))" x=$tzinfo.offset assign=offset}
                                {math equation="(x - (y*3600000)) / 60000" y=$offset x=$tzinfo.offset assign=offset_min format="%02d"}
                                <option value="{$tz|escape}"{if isset($user_prefs.display_timezone) and $user_prefs.display_timezone eq $tz} selected="selected"{/if}>
                                    {$tz|escape} (UTC{if $offset >= 0}+{/if}{$offset}h{if $offset_min gt 0}{$offset_min}{/if})
                                </option>
                            {/foreach}
                        </select>
                        {if $warning_site_timezone_set eq 'y'}
                            <br/><strong>{tr}Warning:{/tr}</strong> <i>{tr _0=$display_timezone}Site time zone <strong>%0</strong> is enforced and overrides user preferences{/tr}</i>
                        {/if}
                    </div>
                </div>

                <div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="display_12hr_clock" {if $user_prefs.display_12hr_clock eq 'y'}checked="checked"{/if}>{tr}Use 12-hour clock in time selectors{/tr}
                    </label>
                </div>
                {if 1 eq 1 || $prefs.feature_community_mouseover eq 'y'}
                    <div class="form-check">
                        <label class="form-check-label">
                            <input class="form-check-input" type="checkbox" name="show_mouseover_user_info" {if $show_mouseover_user_info eq 'y'}checked="checked"{/if}>{tr}Display info tooltip on mouseover for every user who allows his/her information to be public{/tr}
                        </label>
                    </div>
                {/if}

                {if $prefs.feature_messages eq 'y' and $tiki_p_messages eq 'y'}
                    <legend>{tr}User Messages{/tr}</legend>
                    <div class="mb-3 row clearfix">
                        <label class="col-form-label col-md-4" for="mess_maxRecords">
                            {tr}Messages per page{/tr}
                        </label>
                        <div class="col-md-8">
                            <select id="mess_maxRecords" name="mess_maxRecords" class="form-control">
                                <option value="2" {if $user_prefs.mess_maxRecords eq 2}selected="selected"{/if}>2</option>
                                <option value="5" {if $user_prefs.mess_maxRecords eq 5}selected="selected"{/if}>5</option>
                                <option value="10" {if empty($user_prefs.mess_maxRecords) or $user_prefs.mess_maxRecords eq 10}selected="selected"{/if}>10</option>
                                <option value="20" {if $user_prefs.mess_maxRecords eq 20}selected="selected"{/if}>20</option>
                                <option value="30" {if $user_prefs.mess_maxRecords eq 30}selected="selected"{/if}>30</option>
                                <option value="40" {if $user_prefs.mess_maxRecords eq 40}selected="selected"{/if}>40</option>
                                <option value="50" {if $user_prefs.mess_maxRecords eq 50}selected="selected"{/if}>50</option>
                            </select>
                        </div>
                    </div>
                    <div class="clearfix">
                        {if 1 eq 1 || $prefs.allowmsg_is_optional eq 'y'}
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox" name="allowMsgs" {if $user_prefs.allowMsgs eq 'y'}checked="checked"{/if}>
                                    {tr}Allow messages from other users{/tr}
                                </label>
                            </div>
                        {/if}
                        <div class="form-check">
                            <label class="form-check-label">
                                <input class="form-check-input" type="checkbox" name="mess_sendReadStatus" {if $user_prefs.mess_sendReadStatus eq 'y'}checked="checked"{/if}>
                                {tr}Notify sender when reading his mail{/tr}
                            </label>
                        </div>
                    </div>
                    <div class="mb-3 row clearfix">
                        <label class="col-form-label col-md-4" for="minPrio">
                            {tr}Message priority notification{/tr}
                        </label>
                        <div class="col-md-8">
                            <select class="form-select" id="minPrio" name="minPrio">
                                <option value="1" {if $user_prefs.minPrio eq 1}selected="selected"{/if}>1 -{tr}Lowest{/tr}-</option>
                                <option value="2" {if $user_prefs.minPrio eq 2}selected="selected"{/if}>2 -{tr}Low{/tr}-</option>
                                <option value="3" {if $user_prefs.minPrio eq 3}selected="selected"{/if}>3 -{tr}Normal{/tr}-</option>
                                <option value="4" {if $user_prefs.minPrio eq 4}selected="selected"{/if}>4 -{tr}High{/tr}-</option>
                                <option value="5" {if $user_prefs.minPrio eq 5}selected="selected"{/if}>5 -{tr}Very High{/tr}-</option>
                                <option value="6" {if $user_prefs.minPrio eq 6}selected="selected"{/if}>{tr}none{/tr}</option>
                            </select>
                            <span class="form-text">{tr}Send me an email for messages with priority equal to or greater than{/tr}</span>
                        </div>
                    </div>
                    <div class="mb-3 row clearfix">
                        <label class="col-form-label col-md-4" for="mess_archiveAfter" >
                            {tr}Read message auto-archiving{/tr}
                        </label>
                        <div class="col-md-8">
                            <select id="mess_archiveAfter" name="mess_archiveAfter" class="form-control">
                                <option value="0" {if $user_prefs.mess_archiveAfter eq 0}selected="selected"{/if}>{tr}never{/tr}</option>
                                <option value="1" {if $user_prefs.mess_archiveAfter eq 1}selected="selected"{/if}>1</option>
                                <option value="2" {if $user_prefs.mess_archiveAfter eq 2}selected="selected"{/if}>2</option>
                                <option value="5" {if $user_prefs.mess_archiveAfter eq 5}selected="selected"{/if}>5</option>
                                <option value="10" {if $user_prefs.mess_archiveAfter eq 10}selected="selected"{/if}>10</option>
                                <option value="20" {if $user_prefs.mess_archiveAfter eq 20}selected="selected"{/if}>20</option>
                                <option value="30" {if $user_prefs.mess_archiveAfter eq 30}selected="selected"{/if}>30</option>
                                <option value="40" {if $user_prefs.mess_archiveAfter eq 40}selected="selected"{/if}>40</option>
                                <option value="50" {if $user_prefs.mess_archiveAfter eq 50}selected="selected"{/if}>50</option>
                                <option value="60" {if $user_prefs.mess_archiveAfter eq 60}selected="selected"{/if}>60</option>
                            </select>
                            <span class="form-text">{tr}Auto-archive read messages after selected days{/tr}</span>
                        </div>
                    </div>
                {/if}
                {if $prefs.feature_tasks eq 'y' and $tiki_p_tasks eq 'y'}
                    <legend>{tr}User Tasks{/tr}</legend>
                    <div class="mb-3 row">
                        <label class="col-form-label col-md-4" for="tasks_maxRecords">
                            {tr}Tasks per page{/tr}
                        </label>
                        <div class="col-md-8">
                            <select class="form-select" id="tasks_maxRecords" name="tasks_maxRecords">
                                <option value="2" {if $user_prefs.tasks_maxRecords eq 2}selected="selected"{/if}>2</option>
                                <option value="5" {if $user_prefs.tasks_maxRecords eq 5}selected="selected"{/if}>5</option>
                                <option value="10" {if $user_prefs.tasks_maxRecords eq 10}selected="selected"{/if}>10</option>
                                <option value="20" {if $user_prefs.tasks_maxRecords eq 20}selected="selected"{/if}>20</option>
                                <option value="30" {if $user_prefs.tasks_maxRecords eq 30}selected="selected"{/if}>30</option>
                                <option value="40" {if $user_prefs.tasks_maxRecords eq 40}selected="selected"{/if}>40</option>
                                <option value="50" {if $user_prefs.tasks_maxRecords eq 50}selected="selected"{/if}>50</option>
                            </select>
                        </div>
                    </div>
                {/if}
                <legend>{tr}My Account{/tr}</legend>
                {if $prefs.xmpp_feature eq 'y'}
                    <div class="mb-3 row mb-2">
                        <label class="col-form-label col-md-4" for="xmpp_username">
                            {tr}XMPP account JID{/tr}
                        </label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="xmpp_jid" id="xmpp_jid" value="{$user_prefs.xmpp_jid|escape}">
                            <p><small>{tr}If empty, Tiki will provide default value{/tr}</small></p>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-form-label col-md-4" for="xmpp_password">
                            {tr}XMPP account password{/tr}
                        </label>
                        <div class="col-md-8">
                            <input type="password" class="form-control" name="xmpp_password" id="xmpp_password" value="{$user_prefs.xmpp_password|escape}" autocomplete="new-password">
                            <p><small>This password will be stored in database</small></p>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-form-label col-md-4" for="xmpp_server_http_bind">
                            {tr}XMPP http-bind URL{/tr}
                        </label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="xmpp_custom_server_http_bind" id="xmpp_custom_server_http_bind" value="{$user_prefs.xmpp_custom_server_http_bind|escape}">
                            <p><small>{tr}You have to provide this when using custom XMPP server{/tr}</small></p>
                        </div>
                    </div>
                {/if}

                <div class="row justify-content-end mb-2">
                    <div class="col-md-8">
                    {if $prefs.feature_wiki eq 'y'}
                        <div class="form-check">
                            <label class="form-check-label">
                                <input class="form-check-input" type="checkbox" name="mytiki_pages" {if $user_prefs.mytiki_pages eq 'y'}checked="checked"{/if}>{tr}My pages{/tr}
                            </label>
                        </div>
                    {/if}
                    {if $prefs.feature_blogs eq 'y'}
                        <div class="form-check">
                            <label class="form-check-label">
                                <input class="form-check-input" type="checkbox" name="mytiki_blogs" {if $user_prefs.mytiki_blogs eq 'y'}checked="checked"{/if}>{tr}My blogs{/tr}
                            </label>
                        </div>
                    {/if}
                    {if $prefs.feature_messages eq 'y' and $tiki_p_messages eq 'y'}
                        <div class="form-check">
                            <label class="form-check-label">
                                <input class="form-check-input" type="checkbox" name="mytiki_msgs" {if $user_prefs.mytiki_msgs eq 'y'}checked="checked"{/if}>{tr}My messages{/tr}
                            </label>
                        </div>
                    {/if}
                    {if $prefs.feature_tasks eq 'y' and $tiki_p_tasks eq 'y'}
                        <div class="form-check">
                            <label class="form-check-label">
                                <input class="form-check-input" type="checkbox" name="mytiki_tasks" {if $user_prefs.mytiki_tasks eq 'y'}checked="checked"{/if}>{tr}My tasks{/tr}
                            </label>
                        </div>
                    {/if}
                    {if $prefs.feature_forums eq 'y' and $tiki_p_forum_read eq 'y'}
                        <div class="form-check">
                            <label class="form-check-label">
                                <input class="form-check-input" type="checkbox" name="mytiki_forum_topics" {if $user_prefs.mytiki_forum_topics eq 'y'}checked="checked"{/if}>{tr}My forum topics{/tr}
                            </label>
                        </div>
                        <div class="form-check">
                            <label class="form-check-label">
                                <input class="form-check-input" type="checkbox" name="mytiki_forum_replies" {if $user_prefs.mytiki_forum_replies eq 'y'}checked="checked"{/if}>{tr}My forum replies{/tr}
                            </label>
                        </div>
                    {/if}
                    {if $prefs.feature_trackers eq 'y'}
                        <div class="form-check">
                            <label class="form-check-label">
                                <input class="form-check-input" type="checkbox" name="mytiki_items" {if $user_prefs.mytiki_items eq 'y'}checked="checked"{/if}>{tr}My user items{/tr}
                            </label>
                        </div>
                    {/if}
                    {if $prefs.feature_articles eq 'y'}
                        <div class="form-check">
                            <label class="form-check-label">
                                <input class="form-check-input" type="checkbox" name="mytiki_articles" {if $user_prefs.mytiki_articles eq 'y'}checked="checked"{/if}>{tr}My articles{/tr}
                            </label>
                        </div>
                    {/if}
                </div>
                </div>
                {if $prefs.feature_userlevels eq 'y'}
                    <div class="mb-3 row clearfix">
                        <label class="col-form-label col-md-4" for="mylevel">
                            {tr}My level{/tr}
                        </label>
                        <div class="col-md-8">
                            <select class="form-select" name="mylevel" id="mylevel">
                                {foreach key=levn item=lev from=$prefs.userlevels}
                                    <option value="{$levn}"{if $user_prefs.mylevel eq $levn} selected="selected"{/if}>{$lev}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                {/if}
                <div class="mb-3 row">
                        <label class="col-form-label col-md-4" for="remember_closed_rboxes">
                            {tr}Keep closed remarksbox hidden{/tr}
                        </label>
                        <div class="col-md-8">
                            <input type="checkbox" name="remember_closed_rboxes" id="remember_closed_rboxes" {if $user_prefs.remember_closed_rboxes eq 'y'}checked="checked"{/if}>
                            <p class="text-info">
                                {tr}Remember which remarksbox (alert box) you have closed and don't show them again{/tr}.<br>
                            </p>
                        </div>
                    <label class="col-form-label col-md-4">
                        {tr}Reset remark boxes visibility{/tr}
                    </label>
                    <div class="col-md-8">
                        {button _text="{tr}Reset{/tr}" _onclick="if (confirm('{tr}This will reset the visibility of all the tips, notices and warning remarks boxes you have closed.{/tr}')) {ldelim}deleteCookie('rbox');{rdelim}return false;" _class='btn-sm'}
                    </div>
                </div>

                {if $prefs.webmonetization_enabled eq 'y'}
                    <legend>{tr}Web Monetization{/tr}</legend>
                    <div class="mb-3 row clearfix">
                        <label class="col-form-label col-md-4" for="webmonetization_payment_pointer">
                            {tr}Payment pointer{/tr}
                        </label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="webmonetization_payment_pointer" id="webmonetization_payment_pointer" value="{$user_prefs.webmonetization_payment_pointer}">
                