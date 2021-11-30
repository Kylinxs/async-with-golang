{* $Id$ *}
{extends 'internal/ajax.tpl'}
{block name="title"}
    {title}{$title|escape}{/title}
{/block}
{block name="content"}
    <div class="card">
        {if !empty($confirmation_text)}
            <div class="card-header">
                {icon name='information' style="vertical-align:middle"} {$confirmation_text|escape}
            </div>
        {/if}
        {if !empty($confirm_detail)}
            {if is_array($confirm_detail)}
                <ul>
                    {foreach $confirm_detail as $detail}
                        <li>
                            {$detail|escape}
                        </li>
                    {/foreach}
                </ul>
            {else}
                {$confirm_detail|escape}
            {/if}
        {/if}
        <div class="card-body">
            <form id='confirm' action="{$confirmaction|escape}" method="post">
                {query _type='form_input' _keepall='y'}
                {ticket mode='confirm'}
                {* using HTML for the submit button as the smarty function does not seem to allow for type=submit *}
                <button
                    type="submit"
                    class="btn btn-success"
                    {if isset($confirmSubmitName)}name="{$confirmSubmitName}"{/if}
                    {if isset($confirmSubmitValue)}value="{$confirmSubmitValue}"{/if}
                    {if ! empty($ajax)}onclick="confirmAction(event)"{/if}
                >
                    {tr}Confirm action{/tr}
                </button>
                {button href="{$smarty.server.HTTP_REFERER}" _class="btn-link" _icon_name="reply" _text="{tr}Go back{/tr}"}
                {button href="{$prefs.tikiIndex|escape}" _class="btn-link" _icon_name="home" _text="{tr}Return to home page{/tr}"}
            </form>
        </div>
    </div>
{/block}
