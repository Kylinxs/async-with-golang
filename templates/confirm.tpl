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
     