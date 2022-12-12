
{remarksbox type="tip" title="{tr}Tip{/tr}"}{tr}You can see users rank by score in the module users_rank, for that go to{/tr} "<a class="alert-link" href="tiki-admin_modules.php">{tr}Admin modules{/tr}</a>".{/remarksbox}

<form action="tiki-admin.php?page=score" method="post">
    {ticket}
    <div class="t_navbar mb-4 clearfix">
        {include file='admin/include_apply_top.tpl'}
    </div>

    <fieldset class="mb-3 w-100">
        <legend>{tr}Activate the feature{/tr}</legend>
        {preference name=feature_score visible="always"}
    </fieldset>

    <fieldset class="mb-3 w-100">
        <legend>{tr}Cause scores older than a certain number of days to expire{/tr}</legend>
        {preference name=feature_score_expday visible="always"}
    </fieldset>

    <fieldset class="mb-3 w-100">
        <legend>{tr}Scoring rules{/tr}</legend>
        <div class="table-responsive">
            <table id="score-table" class="table">
                <tr>
                    <td><b>{tr}Unique rule ID{/tr}</b>
                        <a href="http://doc.tiki.org/Score" target="_blank" data-bs-toggle="popover" data-bs-trigger="hover" title="{tr}Rule{/tr}" data-bs-content="{tr}A label or ID to help identify which event was triggered to get points.{/tr}">
                            <span class="icon icon-help fas fa-question-circle fa-fw "></span>
                        </a>
                    </td>
                    <td><b>{tr}Pts recipient type{/tr}</b>
                        <a href="http://doc.tiki.org/Score" target="_blank" data-bs-toggle="popover" data-bs-trigger="hover" title="{tr}Recipient Type{/tr}" data-bs-content="{tr}The object type of the point recipient. Usually 'user' but can also be article, trackeritem, etc. Can also use '(eval type)' to get the type of the object being triggered by the event.{/tr}">
                            <span class="icon icon-help fas fa-question-circle fa-fw "></span>
                        </a>
                    </td>
                    <td><b>{tr}Pts recipient{/tr}</b>
                        <a href="http://doc.tiki.org/Score" target="_blank" data-bs-toggle="popover" data-bs-trigger="hover" title="{tr}The ID of the points recipient{/tr}" data-bs-content="{tr}This is the value for the ID of the recipient. It is retrieved by evaluating the event parameters. Using 'user' for example, would retrieve the user triggering the event. 'object' would retrieve the ID of the object on which the event is being triggered.{/tr}">
                            <span class="icon icon-help fas fa-question-circle fa-fw "></span>
                        </a>
                    </td>
                    <td><b>{tr}Points{/tr}</b>
                        <a href="http://doc.tiki.org/Score" target="_blank" data-bs-toggle="popover" data-bs-trigger="hover" title="{tr}Points Given{/tr}" data-bs-content="{tr}This is the numerical value of the points being given.{/tr}">
                            <span class="icon icon-help fas fa-question-circle fa-fw "></span>
                        </a>
                    </td>
                    <td class="text-end"><b>{tr}Actions{/tr}</b></td>
                </tr>

                {foreach $events as $event}
                    <tbody class="event-section" data-section="{$event['event']}">
                    <tr>
                        <td colspan="2"><b>{tr}Triggering event{/tr}</b>: {$event['event']}</td>
                        <td colspan="3" class="text-end"><b>{tr}Reversal event{/tr}</b>:
                            <select class="reverse-event-select" name="events[{$event['event']}][reversalEvent]" class="form-select">
                                <option value="">{tr}None{/tr}</option>
                                {foreach from=$eventTypes item=eventName}
                                    <option value="{$eventName|escape}"{if $event['reversalEvent'] eq $eventName} selected{/if}>
                                        {$eventName|escape}
                                    </option>
                                {/foreach}
                            </select>
                        </td>
                    </tr>
                    {foreach $event['scores'] as $key=>$score}
                        {if $score->expiration || $score->validObjectIds}
                            {assign hide_advanced 0}
                        {else}
                            {assign hide_advanced 1}
                        {/if}
                        <tr class="condition-row">
                            <td>
                                <input type="text" size="30" name="events[{$event['event']}][{$key}][ruleId]" value="{$score->ruleId}">
                            </td>
                            <td>
                                <input type="text" size="20" name="events[{$event['event']}][{$key}][recipientType]" value="{$score->recipientType}">
                            </td>
                            <td>
                                <input type="text" size="30" name="events[{$event['event']}][{$key}][recipient]" value="{$score->recipient}">
                            </td>
                            <td>
                                <input type="text" size="10" name="events[{$event['event']}][{$key}][score]" value="{$score->score}">
                            </td>
                            <td class="text-end">
                                {if $hide_advanced}<a class="advanced" href="#">{icon name='ellipsis-h'}</a>{/if}
                                <a class="delete-row" href="#">{icon name='delete'}</a>
                            </td>
                        </tr>
                        <tr class="advanced-row {if $hide_advanced eq 1}hide{/if}">
                            <td class="text-end">{tr}Valid Triggering Object IDs{/tr}
                                <a href="http://doc.tiki.org/Score" target="_blank" data-bs-toggle="popover" data-bs-trigger="hover" title="{tr}Valid Object Ids{/tr}" data-bs-content="{tr}This is a comma-separated list of object ids for which the event is valid{/tr}">
                                    <span class="icon icon-help fas fa-question-circle fa-fw "></span>
                                </a>
                            </td>
                            <td>
                                <input type="text" size="20" name="events[{$event['event']}][{$key}][validObjectIds]" value="{$score->validObjectIds}">
                            </td>
                            <td class="text-end">{tr}Min. Time Between Scoring{/tr}
                                <a href="http://doc.tiki.org/Score" target="_blank" data-bs-toggle="popover" data-bs-trigger="hover" title="{tr}Time between scoring{/tr}" data-bs-content="{tr}This is the amount of time in seconds that a user must wait before again being able to get points for this event{/tr}">
                                    <span class="icon icon-help fas fa-question-circle fa-fw "></span>
                                </a>
                            </td>
                            <td>
                                <input type="text" size="10" name="events[{$event['event']}][{$key}][expiration]" value="{$score->expiration}">
                            </td>
                            <td></td>
                        </tr>
                    {/foreach}
                    </tbody>
                {/foreach}
            </table>
        </div>

        <hr>
        <div class="mb-3 row clearfix">
            <div class="col-lg-4 col-sm-6">
                <select id="eventSelect" name="event" class="form-select">
                    {foreach from=$eventTypes item=eventName}
                        <option value="{$eventName|escape}"{if $rule.eventType eq $eventName} selected{/if}>
                            {$eventName|escape}
                        </option>
                    {/foreach}
                </select>
            </div>
            <a id="addEventBtn" href="#" class="btn btn-primary">{tr}Add a Scoring Event{/tr}</a>
        </div>
    </fieldset>

    {include file='admin/include_apply_bottom.tpl'}
</form>

{jq}
$('[data-bs-toggle="popover"]').popover();

$('#addEventBtn').click(function(ev) {
    ev.preventDefault();
    var securityTimeout = checkTimeout();
    if (! securityTimeout) {
        return false;
    }
    var evType = $('#eventSelect').val();

    //if section already exists
    if ($('[data-section="'+evType+'"]').length > 0) {
        var appendElement = $('[data-section="'+evType+'"]');
        var rowOnly = 'y';
        var rowCount = $('[data-section="'+evType+'"] .condition-row').length;
    } else {
        var appendElement = $("#score-table");
    }

    $.ajax(
        $.service(
            'score',
            'create_score_event',
            {
                eventType: evType,
                rowOnly: rowOnly,
                rowCount: rowCount
            }
        )
    ).done(function(data) {
        appendElement.append(data);
    });
});
$('.delete-row').click(function(ev) {
    ev.preventDefault();
    var securityTimeout = checkTimeout();
    if (! securityTimeout) {
        return false;
    }
    var currentRow = $(this).closest('.condition-row');
    if ($(currentRow).siblings('.condition-row').length > 0) {
        $(currentRow).next('.advanced-row').remove();
        $(currentRow).remove();
    } else {
        $(currentRow).closest('tbody').remove();
    }
});
$('a.advanced').click(function(ev) {
    ev.preventDefault();
    var securityTimeout = checkTimeout();
    if (! securityTimeout) {
        return false;
    }
    $(this).closest('.condition-row').next('.advanced-row').removeClass('hide');
});
{/jq}