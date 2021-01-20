
<div id="testingstatus" style="display:none">{$field.status|escape}</div>
{$myId = $field.fieldId|escape|cat:'_'|cat:$item.itemId|escape}
<h5 id="showtikiorg{$myId}{if isset($context.list_mode)}_view{/if}" class="showactive{$myId}" {if $field.status neq 'ACTIV'}style="display: none;"{/if}>
    {tr}This bug has been demonstrated on {$field.options_map.domain|escape}{/tr}
</h5>
<h5 class="shownone{$myId}" {if $field.status neq 'NONE'}style="display: none;"{/if}>
    {tr}Please demonstrate your bug on {$field.options_map.domain|escape}{/tr}
</h5>
{if !$field.id}
    {remarksbox type="info" title="{tr}Bug needs to be created first{/tr}" close="n"}
        <p>{tr}You will be able to demonstrate your bug on a {$field.options_map.domain|escape} instance once it has been created.{/tr}</p>
    {/remarksbox}
{else}
    <div class="showsnapshot{$myId}" style="display: none;">
        {remarksbox type="error" title="{tr}Show.tiki.org snapshot creation is in progress{/tr}" close="n"}
            <p>{tr _0="<a class=\"snapshoturl{$myId}\" href=\"http://{$field.snapshoturl|escape}\" target=\"_blank\">http://{$field.snapshoturl|escape}</a>"}Show.tiki.org snapshot creation is in progress... Please monitor %0 for progress.{/tr}
                <strong>{tr}Note that if you get a popup asking for a username/password, please just enter "show" and "show".{/tr}</strong>
            </p>
        {/remarksbox}
    </div>
    <div class="showresetok{$myId}" style="display: none;">
        {remarksbox type="info" title="{tr}Password reset{/tr}" close="n"}
            <p>{tr}Password reset was successful{/tr}</p>
        {/remarksbox}