{strip}
<div style="display:inline" id="currency_output_{$id}" class="currency_output">
{if $prepend}
    <span class="formunit">{$prepend|escape}</span>
{/if}
{if empty($locale)}
    {assign var=locale value='en_US'}
{else}
    {assign var=locale value=$locale}
{/if}
{if $currency}
    {assign var=currency value=$currency}
{elseif empty($defaultCurrency)}
    {assign var=currency value='USD'}
{else}
    {assign var=currency value=$defaultCurrency}
{/if}
{if empty($symbol)}
    {assign var=part1a value='%(!#10n'}
    {assign var=part1b value='%(#10n'}
{else}
    {assign var=part1a value='%(!#10'}
    {assign var=part1b value='%(#10'}
{/if}
{if (isset($reloff) and $reloff gt 0) and ($allSymbol ne 1)}
    {assign var=format value=$part1a|cat:$symbol}
    {$amount|money_format:$locale:$currency:$format:0}
{else}
    {assign var=format value=$part1b|cat:$symbol}
    {$amount|money_format:$locale:$currency:$format:1}
{/if}
{if $append}
    <span class="formunit">{$append|escape}</span>
{/if}
</div>
{if $conversions}
    <div class="d-none currency_output_{$id}" style="position:absolute; z-index: 1000;">
        <div class="modal-content">
            <div class="modal-body">
    {foreach from=$conversions key=currency item=amount}
        {if (isset($reloff) and $reloff gt 0) and ($allSymbol ne 1)}
            {assign var=format value=$part1a|cat:$symbol}
            {$amount|money_format:$locale:$currency:$format:0}
        {else}
            {assign var=format value=$part1b|cat:$symbol}
            {$amount|money_format:$locale:$currency:$format:1}
        {/if}
        <br>
    {/foreach}
            </div>
        </div>
    </div>
{/if}
{/strip}
