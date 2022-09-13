<body>
{include file='tiki-calendar_edit_item.tpl'}
{if $headerlib}
    {$headerlib->output_js()}
    {if isset($smarty.request.full)}
        {$headerlib->output_js_files()}
    {else}
        <script type="text/javascript" src="lib/jquery_tiki/calendar_edit_item.js"></script>
        {if $prefs.feature_notify_users_mention eq 'y' and $prefs.feature_tag_users eq 'y'}
            <script type="text/javascript" src="lib/jquery_tiki/user_mentions.js"></script>
        {/if}
    {/if}
{/if}
</body>
