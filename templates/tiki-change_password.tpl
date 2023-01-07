
{* $Id$ *}
{if isset($new_user_validation) && $new_user_validation eq 'y'}
    {title}{tr}Your account has been validated.{/tr}{/title}
    {remarksbox type="warning" title="{tr}Warning{/tr}" close="n"}{tr}You have to choose a password to use this account.{/tr}{/remarksbox}
{else}
    {assign var='new_user_validation' value='n'}
{/if}
<div class="row">
<div class="col-md-10 offset-md-1">
    <form role="form" method="post" action="tiki-change_password.php">
        <div class="card">
            {if !empty($oldpass) and $new_user_validation eq 'y'}
                <input type="hidden" name="oldpass" value="{$oldpass|escape}">
            {elseif !empty($smarty.request.actpass)}
                <input type="hidden" name="actpass" value="{$smarty.request.actpass|escape}">
            {/if}
            {if $new_user_validation eq 'y'}
                <input type="hidden" name="new_user_validation" value="y">
            {/if}
            <div class="card-header text-center">
                {if $new_user_validation neq 'y'}
                    <h3 class="card-title">{tr}Change password{/tr}</h3>
                {else}
                    <h3 class="card-title">{tr}Set password{/tr}</h3>
                {/if}
            </div>
            <div class="card-body">
                <div class="clearfix">
                    {include file='password_jq.tpl'}
                    <div class="text-center" id="divRegCapson" style="display:none;">
                        {remarksbox type="warning" title="{tr}Warning{/tr}" close="n"}{tr}CapsLock is on.{/tr}{/remarksbox}
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-md-4 col-form-label" for="user">{tr}Username{/tr}</label>
                    <div class="col-md-8">
                        {if empty($userlogin)}
                            <input type="text" class="form-control" id="user" name="user" autocomplete="username">
                        {else}
                            <input type="hidden" id="user" name="user" value="{$userlogin|escape}">
                            <input type="text" class="form-control" id="user-autocomplete" name="user-autocomplete" disabled="disabled" value="{$userlogin|escape}" autocomplete="username">
                        {/if}
                    </div>
                </div>
                {if empty($smarty.request.actpass) and ($new_user_validation neq 'y' or empty($oldpass))}
                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label" for="oldpass">{tr}Old Password{/tr}</label>
                        <div class="col-md-8">
                            <input type="password" class="form-control" name="oldpass" id="oldpass" placeholder="{tr}Old Password{/tr}" autocomplete="current-password">
                        </div>
                    </div>
                {/if}
                <div class="mb-3 row">
                    <label class="col-md-4 col-form-label" for="pass1">{tr}New Password{/tr}</label>
                    <div class="col-md-8">
                        <input required type="password" class="form-control" placeholder="{tr}New Password{/tr}" name="pass" id="pass1" autocomplete="new-password">
                        <div style="margin-left:5px;">
                            <div id="mypassword_text">{icon name='ok' istyle='display:none'}{icon name='error' istyle='display:none' } <span id="mypassword_text_inner"></span></div>
                            <div id="mypassword_bar" style="font-size: 5px; height: 2px; width: 0px;"></div>
                        </div>
                        <div style="margin-top:5px">
                            {include file='password_help.tpl'}
                        </div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-md-4 col-form-label" for="pass2">{tr}Repeat Password{/tr}</label>
                    <div class="col-md-8">
                        <input required type="password" class="form-control" name="passAgain" id="pass2" placeholder="{tr}Repeat Password{/tr}" autocomplete="new-password">
                        <div id="mypassword2_text">
                            <div id="match" style="display:none">
                                {icon name='ok' istyle='color:#0ca908'} {tr}Passwords match{/tr}
                            </div>
                            <div id="nomatch" style="display:none">
                                {icon name='error' istyle='color:#ff0000'} {tr}Passwords do not match{/tr}
                            </div>
                        </div>
                    </div>
                </div>
                {if $prefs.generate_password eq 'y'}
                    <div class="mb-3 row">
                        <div class="col-md-4 offset-md-4">
                            <span id="genPass">{button href="#" _text="{tr}Generate a password{/tr}"}</span>
                        </div>
                        <div class="col-md-4">
                            <input id='genepass' class="form-control" name="genepass" type="text" tabindex="0" style="display:none">
                        </div>
                    </div>
                {/if}
                {if empty($email)}
                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label" for="email">{tr}Email{/tr}</label>
                        <div class="col-md-8">
                            <input required type="email" class="form-control" name="email" id="email" placeholder="{tr}Email{/tr}" value="{if not empty($email)}{$email|escape}{/if}" autocomplete="email">
                        </div>
                    </div>
                {/if}
            </div>
            <div class="card-footer text-center">
                <input type="submit" class="btn btn-primary" name="change" onclick="return checkPasswordsMatch('#pass2', '#pass1', '#mypassword2_text');" value="{tr}Apply{/tr}"><span id="validate"></span>
            </div>
        </div>
    </form>
</div>
</div>