
{extends "layout_view.tpl"}

{block name="title"}
    {title}{$title}{/title}
{/block}

{block name="navigation"}
    {include file='manager/nav.tpl'}
{/block}

{block name="content"}
    <div class="rounded bg-dark text-light p-3" id="ws-response-container"></div>
{/block}