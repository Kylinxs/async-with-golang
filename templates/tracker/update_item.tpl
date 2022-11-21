{extends 'layout_view.tpl'}

{block name="title"}
    {title}{$title|escape}{/title}
{/block}

{block name="navigation"}
    {if $skip_form neq 'y'}
        {include file='tracker_actions.tpl'}
    {/if}
{/block}

{block name="content"}
    <div class="previewTrackerItem"></div>
    {if $skip_form eq 'y'}
        <form method="post" action="{service controller=tracker
        action=update_item
        trackerId={$trackerId}
        itemId={$itemId}
        }">
            <p>
                {$skip_form_message}
            </p>
            <div class="submit">
                <input type="hidden" name="status" value="{$status|escape}">
                {foreach from=$forced key=permName item=value}
                    <input type="hidden" name="forced~{$permName|escape}" value="{$value|escape}">
                {/foreach}
                {if $skip_preview neq 'y'}
                    <input type="button" class="btn btn-primary previewItemBtn" title="{tr}Preview your changes.{/tr}" name="preview" value="{tr}Preview{/tr}">
                {/if}
                <input type="hidden" name="redirect" value="{$redirect|escape}">
                <input type="hidden" name="conflictoverride" value="{$conflictoverride|escape}">
                <input type="submit" class="btn btn-primary" value="{$button_label}" onclick="needToConfirm=false;">
            </div>
        </form>
    {else}
        <form method="post" action="{service controller=tracker action=update_item format=$format editItemPretty=$editItemPretty suppressFeedback=$suppressFeedback}" id="updateItemForm{$trackerId|escape}">
            {trackerfields trackerId=$trackerId fields=$fields status=$status itemId=$itemId format=$format editItemPretty=$editItemPretty}
            <div class="submit">
                {if $skip_preview neq 'y'}
                    <input type="button" class="btn btn-secondary previewItemBtn" title="{tr}Preview your changes.{/tr}" name="preview" value="{tr}Preview{/tr}">
                {/if}
                {if not empty($saveAndComment) and $saveAndComment neq 'n'}
                    <input type="hidden" name="saveAndComment" id="saveAndComment" value="">
                    <input type="submit" class="btn btn-primary" onclick="$('#saveAndComment').val(1);" value="{tr}Save and Comment{/tr}">
                {/if}
                <input type="hidden" name="itemId" value="{$itemId|escape}">
                <input type="hidden" name="trackerId" value="{$trackerId|escape}">
                {foreach from=$forced key=permName item=value}
                    <input type="hidden" name="forced~{$permName|escape}" 