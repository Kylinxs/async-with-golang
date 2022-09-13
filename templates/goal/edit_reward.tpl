{extends "layout_view.tpl"}

{block name="title"}
    {title}{$title}{/title}
{/block}

{block name="content"}
    {remarksbox title="{tr}Changes will not be saved{/tr}"}
        {tr}Your changes to rewards are not saved until you save the goal.{/tr}
    {/remarksbox}
    <form class="reward-form" method="post" action="{service controller=goal action=edit_reward}">
        <div class="mb-3 row">
            <label class="col-form-label col-md-3">{tr}Type{/tr}</label>
            <div class="col-md-9">
                <select name="rewardType" class="form-select">
                    {foreach $rewards as $key => $info}
                        <option value="{$key|escape}" {if $reward.rewardType eq $key} selected {/if} data-arguments="{$info.arguments|json_encode|escape}">{$info.label|escape}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        {if !empty($rewards.credit)}
            <div class="mb-3 argument creditType">
                <label class="col-form-label col-md-3">{tr}Credit Type{/tr}</label>
                <div class="col-md-9">
                    <select name="creditType" class="form-select">
                        {foreach $rewards.credit.options as $creditType => $creditLabel}
                            <option value="{$creditType|escape}" {if $creditType eq $reward.creditType} selected {/if}>{$creditLabel|escape}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="mb-3 argument creditQuantity">
                <label class="col-form-label col-md-3">{tr}Credit Quantity{/tr}</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" name="creditQuantity" value="{$reward.creditQuantity|escape}">
                </div>
            </div>
        {/if}
        {if !empty($rewards.tracker_badge_add)}
            <div class="mb-3 argument trackerItemBadge">
                <label class="col-form-label col-md-3">{tr}Badge{/tr}</label>
                <div class="col-md-9">
                    {object_selector _name=trackerItemBadge _value="trackeritem:`$reward.trackerItemBadge`" tracker_id=$rewards.tracker_badge_add.tracker _class="form-control"}
                </div>
            </div>
        {/if}
        <div class="form-check offset-md-3">
            <input class="form-check-input" type="checkbox" name="hidden"  id="hidden" value="1" {if !empty($reward.hidden)}checked{/if}>
            <label class="form-check-label" for="hidden">
                {tr}Hide reward from users{/tr}
            </label>
        </div>
        <div class="submit offset-md-3">
            <input type="submit" class="btn btn-primary" title="{tr}Apply Changes{/tr}" value="{tr}Apply{/tr}">
        </div>
    </form>
    {jq}
        $('.reward-form select[name=rewardType]').change(function () {
            $('.reward-form .mb-3.argument').hide();

            $.each(this.selectedOptions, function (key, item) {
                $.each($(item).data('arguments'), function (key, arg) {
                    $('.reward-form .mb-3.argument.' + arg).show();
                });
            })
        }).change();
    {/jq}
{/block}
