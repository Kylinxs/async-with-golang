{extends 'layout_view.tpl'}

{block name="title"}
    {title}{$title|escape}{/title}
{/block}

{block name="content"}
    <table class="table table-striped">
        <tr>
            <th>{tr}Name{/tr}</th>
            <th>{tr}User{/tr}</th>
            <th>{tr}Verification{/tr}</th>
            <th>{tr}Created{/tr}</th>
            <th>{tr}Edit{/tr}</th>
            <th>{tr}Delete{/tr}</th>
        </tr>
        {foreach $webhooks as $webhook}
            <tr>
                <td>
                    {$webhook.name|escape}
                </td>
                <td>
                    {$webhook.user|escape}
                </td>
                <td>
                    {$webhook.verification} {$webhook.algo}
                </td>
                <td>
                    {$webhook.created|tiki_short_datetime}
                </td>
                <td>
                    <a href="{bootstrap_modal controller=webhook action=edit webhookId=$webhook.webhookId size='modal-lg'}">
                        {icon name="pencil"}
                    </a>
                </td>
                <td>
                    <a href="{service controller=webhook action=delete webhookId=$webhook.webhookId}" class="btn btn-link text-danger">
                        {icon name='delete'}
                    </a>
                </td>
            </tr>
        {foreachelse}
            {norecords _colspan=6}
        {/foreach}
    </table>
    <p>
        <a class="btn btn-info" href="{bootstrap_modal controller=webhook action=new size='modal-lg'}">
            {icon name="create"} {tr}Create{/tr}
        </a>
    </p>
{/block}
