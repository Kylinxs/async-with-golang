{* $Id$ *}
{if $prefs.user_register_prettytracker eq 'y' and $prefs.user_register_prettytracker_tpl}
    <input id='pass1' type="password" name="pass" autocomplete="new-password" onkeypress="regCapsLock(event)" class="form-control" >
    {if $prefs.user_register_prettytracker_hide_mandatory neq 'y'}&nbsp;<strong class='mandatory_star text-danger tips' title=":{tr}This field is mandatory{/tr}">*</strong>{/if}
{else}
    <div class="mb-3 row">
        <label class="col-sm-4 col-form-label" for="pass1">{tr}New Password{/tr} {if $trackerEditFormId}<strong class='mandatory_star text-danger tips' title=":{tr}This field is mandatory{/tr}">*</strong>{/if}</label>
        <div class="col-sm-8">
            <input
                class="form-control"
                id='pass1'
                type="password"
                name="pass"
                autocomplete="new-password"
                value="{if !empty($smarty.post.pass)}{$smarty.post.pass}{/if}"
            >
            <div style="margin-left:5px;">
                <div id="mypassword_text">{icon name='ok' istyle='display:none'}{icon name='error' istyle='display:none' } <span id="mypassword_text_inner"></span></div>
                <div id="mypassword_bar" style="font-size: 5px; height: 2px; width: 0px;"></div>
            </div>
            {if $prefs.feature_jquery_validation neq 'y'}
                <div style="margin-top:5px">
                    {include file='password_help.tpl'}
                </div>
            {/if}
        </div>
    </div>
{/if}
