
{* $Id$ *}
{if empty($user)}

    {include file='password_jq.tpl'}
    <div class="alert alert-warning" id="divRegCapson" style="display: none;">{icon name='error' style="vertical-align:middle"} {tr}CapsLock is on.{/tr}</div>
    {if $allowRegister eq 'y'}
        <div class="row">
            <div class="col-sm-12">
                {if $userTrackerData}
                    {$userTrackerData}
                {else}
                    <form action="tiki-register.php{if !empty($prefs.registerKey)}?key={$prefs.registerKey|escape:'url'}{/if}" method="post" name="RegForm">
                        {if !empty($smarty.request.invite)}<input type='hidden' name='invite' value='{$smarty.request.invite|escape}'>{/if}
                        {include file="register-form.tpl"}
                        {if $merged_prefs.feature_antibot eq 'y'}{include file='antibot.tpl' form='register'}{/if}
                        <div class="row mb-4">
                            <div class="col-sm-8 offset-sm-4">
                              <input type="hidden" name="register" value="1">
                              <button class="btn btn-primary registerSubmit submit" name="register" type="submit">{tr}Register{/tr} {*<i class="fa fa-check"></i>*}</button>
                            </div>
                        </div>
                    </form>
                {/if}
            </div>
        </div>
        <div class="col-sm-8 offset-sm-4">
            {remarksbox type="note" title="{tr}Note{/tr}"}
                {if $prefs.feature_wiki_protect_email eq 'y'}
                    {assign var=sender_email value=$prefs.sender_email|default:"this domain"|escape:'hexentity'}
                {else}
                    {assign var=sender_email value=$prefs.sender_email|default:"this domain"|escape}
                {/if}
                {tr _0="$sender_email"}If you use an email filter, be sure to add %0 to your accepted list{/tr}
            {/remarksbox}
        </div>
    {/if}
{else}
    {include file='modules/mod-login_box.tpl' nobox='y'}
{/if}