{extends "layout_view.tpl"}

{block name="title"}
    {title}{$title}{/title}
{/block}

{block name="navigation"}
    {include file='manager/nav.tpl'}
{/block}

{block name="content"}
   <div style="background: #ccc;" class="rounded p-3">
        <p>You can't run this command on web browser, copy it and run it in your terminal!</p>
        {include file='manager/command.tpl' command=$info}
   </div>
{/block}
