{* $Id$ *}
<ul class="list-unstyled media-list">
    {foreach from=$results item=row}
        <li class="media">
            {if $icon and $icon.field}
                <img class="me-3" src="{$row[$icon.field]}" alt="{tr}media image{/tr}">
            {/if}
            <div class="flex-grow-1 ms-3">
                <h4 class="mt-0 mb-1">{object_link type=$row.object_type id=$row.object_id}</h4>
                {if $body and $body.field}
                    {if $body.mode eq 'raw'}
                        {$row[$body.field]}
                    {else}
                        {$row[$body.field]|escape}
                    {/if}
                {/if}
            </div>
        </li>
    {/foreach}
</ul>
{pagination_links resultset=$results}{/pagination_links}
