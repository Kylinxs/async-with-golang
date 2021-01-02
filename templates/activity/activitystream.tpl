{* note please do not remove ol and li as these are needed for "Show More" button to work *}
{if count($results) > 0}
    <ol>
        {foreach from=$results item=activity}
            <li>{activity info=$activity}</li>
        {/foreach}
    </ol>
{else}
    <p class="invalid">{tr}There is no activity to display in this stream.{/tr}</p>
{/if}
