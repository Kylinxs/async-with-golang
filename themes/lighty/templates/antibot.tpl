
{* $Id$ *}
{if empty($user) || $user eq 'anonymous' || !empty($showantibot)}
    <style>
    .antibot.row {
        margin-left: 0;
        margin-right: 0;
    }
    </style>
    {if $showmandatory eq 'y'}
        {$inputclass = 'col-sm-11 col-md-5'} {* leave room for the * *}
    {else}
        {$inputclass = 'col-md-6'}
    {/if}
    {$captchaclass = 'col-sm-3 mb-1'}
    {if $form === 'register'}
        {$labelclass = 'col-md-4 col-sm-3'}
        {$inputclass = 'col-md-4 col-sm-6'}
        {$captchaclass = 'col-md-5 col-sm-6 offset-md-4 offset-sm-3'}
    {/if}
    <div class="antibot row">
        {if $captchalib->type eq 'recaptcha' || $captchalib->type eq 'recaptcha20'}
            <div class="mb-3 row clearfix">
                <div class="{$captchaclass}">
                    {$captchalib->render()}
                </div>
            </div>
        {elseif $captchalib->type eq 'questions'}
            <input type="hidden" name="captcha[id]" id="captchaId" value="{$captchalib->generate()}">
            <div class="mb-3 row">
                <label class="col-md-4 col-sm-3 col-form-label">
                    {$captchalib->render()}
                </label>
                {if $showmandatory eq 'y'}
                    <div class="{if $form === 'register' && !empty($inputclass)}{$inputclass}{else}col-md-7 col-sm-8 col-xs-11{/if}">
                        <input class="form-control" type="text" maxlength="8" size="22" name="captcha[input]" id="antibotcode">
                    </div>
                    <div class="col-md-1 col-sm-1">
                        <span class='text-danger tips' title=":{tr}This field is manadatory{/tr}">*</span>
                    </div>
                {else}
                    <div class="{if $form === 'register' && !empty($inputclass)}{$inputclass}{else}col-md-8 col-sm-9 col-xs-12{/if}">
                        <input class="form-control" type="text" maxlength="8" size="22" name="captcha[input]" id="antibotcode">
                    </div>
                {/if}
            </div>
        {else}
            {* Default captcha *}
            <input type="hidden" name="captcha[id]" id="captchaId" value="{$captchalib->generate()}">
            <center>
            <div class="mb-3 row">
                <div class="{$captchaclass}">
                    {if $captchalib->type eq 'default'}
                        <img id="captchaImg" src="{$captchalib->getPath()}" alt="{tr}Anti-Bot verification code image{/tr}" height="34">
                    {else}
                        {* dumb captcha *}
                        {$captchalib->render()}
                    {/if}
                </div>
                {if $captchalib->type eq 'default'}
                    <div class="col-sm-3">
                        {button _id='captchaRegenerate' _class='' href='#antibot' _text="{tr}Try another code{/tr}" _icon_name="refresh" _onclick="generateCaptcha();return false;"}
                    </div>
                {/if}
                <div>
                <div class="{if !empty($inputclass)}{$inputclass}{else}col-md-8 col-sm-9{/if}">
                    <input class="form-control" placeholder="PLACE THE CODE HERE" type="text" maxlength="8" name="captcha[input]" id="antibotcode">
                </div>
                <div class="clearfix visible-md-block"></div>
                {if $showmandatory eq 'y'}
                    <div class="col-md-1 col-sm-1">
                        <span class='text-danger tips' title=":{tr}This field is manadatory{/tr}">*</span>
                    </div>
                {/if}
            </div>
            </div>
            </center>
        {/if}
    </div>

    {jq}
        function antibotVerification(element, rule) {
            if (!jqueryTiki.validate) return;

            var form = $(".antibot").parents('form');
            if (!form.data("validator")) {
                form.validate({});
            }
            element.rules( "add", rule);
        }
    {/jq}

    {if $captchalib->type eq 'recaptcha'}
        {jq}
            var existCondition = setInterval(function() {
                if ($('#recaptcha_response_field').length) {
                    clearInterval(existCondition);
                    antibotVerification($("#recaptcha_response_field"), {required: true});
                }
            }, 100); // wait for captcha to load

        {/jq}
    {elseif $captchalib->type eq 'recaptcha20'}
        {jq}
            var existCondition = setInterval(function() {
                if ($('#g-recaptcha-response').length) {
                    clearInterval(existCondition);
                    antibotVerification($("#g-recaptcha-response"), {required: true});
                }
            }, 100); // wait for captcha to load
        {/jq}
    {else}
        {jq}
            antibotVerification($("#antibotcode"),  {
                required: true,
                remote: {
                    url: "validate-ajax.php",
                    type: "post",
                    data: {
                        validator: "captcha",
                        parameter: function() {
                            return $("#captchaId").val();
                        },
                        input: function() {
                            return $("#antibotcode").val();
                        }
                    }
                }
            });
        {/jq}
    {/if}

{/if}