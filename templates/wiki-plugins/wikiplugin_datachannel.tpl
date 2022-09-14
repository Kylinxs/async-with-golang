<form method="post" action="#{$datachannel_execution}"{$form_class_attr}{$datachannel_form_onsubmit}>
    {foreach from=$datachannel_fields key=name item=label}
        {if $label eq "external"}
            <input type="hidden" name="{$name|escape}" value="">
        {elseif $datachannel_inputfields.$name eq "hidden"}
            <input type="hidden" name="{$name|escape}" value="{$label}">
        {else}
            <div class="mb-3 row">
                <label class="col-md-3 col-form-label">{$label|escape}:</label>
                <div class="col-md-9">
                    <input type="text" name="{$name|escape}" class="form-control">
                </div>
            </div>
        {/if}
    {/foreach}
    <div class="submit_row">
        <div class="mb-3 row">
            <label class="div"></label>
            <input type="hidden" name="datachannel_execution" value="{$datachannel_execution|escape}">
            <div class="col-md-9 offset-md-3">
                <input type="submit" class="btn btn-primary" value="{tr}{$button_label}{/tr}">
            </div>
        </div>
    </div>
</form>
