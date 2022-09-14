
{* $Id$ *}
{if $prefs.user_register_prettytracker eq 'y' and $prefs.user_register_prettytracker_tpl}
    <input type="password" name="passcode" id="passcode" autocomplete="new-password" onkeypress="regCapsLock(event)" class="form-control" >
    {if $prefs.user_register_prettytracker_hide_mandatory neq 'y'}&nbsp;<strong class='mandatory_star text-danger tips' title=":{tr}This field is mandatory{/tr}">*</strong>{/if}
{else}
    {if $prefs.useRegisterPasscode eq 'y'}
        <div class="mb-3 row">
            <label class="col-sm-4 col-form-label" for="passcode">{tr}Passcode to register{/tr} <strong class='mandatory_star text-danger tips' title=":{tr}This field is mandatory{/tr}">*</strong>
            </label>
            <div class="col-sm-8">
                <input class="form-control" required="" type="password" name="passcode" id="passcode" autocomplete="new-password" onkeypress="regCapsLock(event)" value="{if !empty($smarty.post.passcode)}{$smarty.post.passcode}{/if}">
                <em class="form-text">{tr}Not your password.{/tr} <span id="passcode-help" style="display:none">{tr}To request a passcode, {if $prefs.feature_contact eq 'y'}<a href="tiki-contact.php">{/if}
                    contact the system administrator{if $prefs.feature_contact eq 'y'}</a>{/if}{/tr}.</span>
                </em>
            </div>
        </div>
    {/if}
{/if}
{if $prefs.useRegisterPasscode eq 'y' and !empty($prefs.registerPasscode) and $prefs.showRegisterPasscode eq 'y'}
    {jq}
        $('span#passcode-help')
        .html("{tr}The passcode (to block robots from registration) is:{/tr} <b>{{$prefs.registerPasscode}}</b>").css('display', 'inline');
    {/jq}
{else}
    {jq}
        $('span#passcode-help').css('display', 'inline');
    {/jq}
{/if}