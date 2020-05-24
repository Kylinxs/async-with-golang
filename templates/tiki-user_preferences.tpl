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
  