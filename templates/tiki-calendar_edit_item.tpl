
{title admpage="calendar"}{tr}Calendar event : {/tr}{$calitem.name|escape}{/title}

{if isset($smarty.get.modal) && $smarty.get.modal}
    <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title"></h4>
    </div>
{/if}
<form action="{$myurl|escape}" method="post" name="f" id="editcalitem" class="no-ajax">
    <fieldset class="tabcontent">
    <div class="modal-body">
        {if !$smarty.get.modal}
            <div class="t_navbar mb-4">
                {if $tiki_p_view_calendar eq 'y'}
                    {button href="tiki-calendar.php" _type="link" _text="{tr}View Calendars{/tr}" _icon_name="view"}
                {/if}
                {if $tiki_p_admin_calendar eq 'y'}
                    {button href="tiki-admin_calendars.php?calendarId=$calendarId" _type="link" _icon_name="edit" _text="{tr}Edit Calendar{/tr}"}
                {/if}
                {if $tiki_p_add_events eq 'y' and $id}
                    {button href="tiki-calendar_edit_item.php" _icon_name="add" _text="{tr}New event{/tr}"}
                {/if}
                {if $id}
                    {if $edit}
                        {button href="tiki-calendar_edit_item.php?viewcalitemId=$id" _icon_name="view" _text="{tr}View event{/tr}"}
                    {elseif $tiki_p_change_events eq 'y'}
                        {button href="tiki-calendar_edit_item.php?calitemId=$id" _icon_name="edit" _text="{tr}Edit/Delete event{/tr}"}
                    {/if}
                {/if}
                {if $tiki_p_admin_calendar eq 'y'}
                    {button href="tiki-admin_calendars.php" _icon_name="admin" _type="link" _text="{tr}Admin Calendars{/tr}"}
                {/if}
                {if !$edit}
                    {if $prefs.calendar_export_item == 'y' and $tiki_p_view_calendar eq 'y'}
                        {button href='tiki-calendar_export_ical.php? export=y&calendarItem='|cat:$id _icon_name="export" _type="link" _text="{tr}Export Event as iCal{/tr}"}
                    {/if}
                {/if}
            </div>
        {/if}

        <div class="wikitext">
            {if $edit}
                {if $preview}
                    <h2>
                        {tr}Preview{/tr}
                    </h2>
                    {$calitem.parsedName}
                    <div class="preview">
                        {$calitem.parsed}
                    </div>
                    <h2 class="my-3">
                        {if $id}
                            {tr}Edit Calendar Item{/tr}
                        {else}
                            {tr}New Calendar Item{/tr}
                        {/if}
                    </h2>
                {/if}
                <input type="hidden" name="save[user]" value="{$calitem.user|escape}">
                <input type="hidden" name="tzoffset" value="">
                {if $saveas}
                    <input type="hidden" name="saveas" value="1">
                {/if}
                {if $id}
                    <input type="hidden" name="save[calitemId]" value="{$id|escape}">
                {/if}
                {if not empty($smarty.request.trackerItemId)}
                    <input type="hidden" name="save[trackerItemId]" value="{$smarty.request.trackerItemId|escape}">
                {/if}
            {/if}
            {if $prefs.calendar_addtogooglecal == 'y'}
                {wikiplugin _name="addtogooglecal" calitemid=$id}{/wikiplugin}
            {/if}
            <div class="mb-3 row">
                <label for="calid" class="col-form-label col-sm-3">{tr}Calendar{/tr}</label>
                <div class="col-sm-9">
                    {if $edit}
                        {if $prefs.javascript_enabled eq 'n'}
                            {$calendar.name|escape}<br>{tr}or{/tr}&nbsp;
                            <input type="submit" class="btn btn-secondary btn-sm" name="changeCal" value="{tr}Go to{/tr}">
                        {/if}
                        <select name="save[calendarId]" id="calid" onchange="needToConfirm=false;$('#editcalitem').data('submitter', 'save[calendarId]').submit();" class="form-control">
                            {foreach item=it key=itid from=$listcals}
                                {if $it.tiki_p_add_events eq 'y'}
                                    {$calstyle = ''}
                                    {if not empty($it.custombgcolor)}
                                        {$calstyle='background-color:#'|cat:$it.custombgcolor|cat:';'}
                                    {/if}
                                    {if not empty($it.customfgcolor)}
                                        {$calstyle=$calstyle|cat:'color:#'|cat:$it.customfgcolor}
                                    {/if}
                                    {if $calstyle}
                                        {$calstyle = ' style="'|cat:$calstyle|cat:'"'}
                                    {/if}
                                    <option value="{$it.calendarId}"{$calstyle}
                                        {if isset($calitem.calendarId)}
                                            {if $calitem.calendarId eq $itid}
                                                selected="selected"
                                            {/if}
                                        {elseif $calendarView}
                                            {if $calendarView eq $itid}
                                                selected="selected"
                                            {/if}
                                        {else}
                                            {if $calendarId}
                                                {if $calendarId eq $itid}
                                                    selected="selected"
                                                {/if}
                                            {/if}
                                        {/if}
                                    >
                                        {$it.name|escape}
                                    </option>
                                {/if}
                            {/foreach}
                        </select>
                    {else}
                        <div class="summary" style="margin-bottom: 0; padding-top: 7px;">
                            {$listcals[$calitem.calendarId].name|escape}
                        </div>
                    {/if}
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-form-label col-sm-3">{tr}Title{/tr}</label>
                <div class="col-sm-9">
                    {if $edit}
                        <input type="text" name="save[name]" value="{$calitem.name|escape}" size="32" class="form-control">
                    {else}
                        <div class="summary" style="margin-bottom: 0; padding-top: 7px;">
                            {$calitem.name|escape}
                        </div>
                    {/if}
                </div>

            </div>
            <div class="mb-3 row">
                <label class="col-form-label col-sm-3">{tr}Created by{/tr}</label>
                <div class="col-sm-9">
                    <div class="summary" style="margin-bottom: 0; padding-top: 7px;">
                            {$calitem.user|escape}
                    </div>

                </div>
            </div>
            {if $edit or $recurrence.id gt 0}
                <div class="mb-3 row">
                    <label class="col-form-label col-sm-3">{tr}Recurrence{/tr}</label>
                    <div class="col-sm-9">
                        {if $edit}
                            {if $recurrence.id gt 0}
                                <input type="hidden" name="recurrent" value="1">
                                {tr}This event depends on a recurrence rule,{/tr}
                                {tr}starting on{/tr} {$recurrence.startPeriod|tiki_long_date},&nbsp;
                                {if $recurrence.endPeriod gt 0}
                                    {tr}ending by{/tr} {$recurrence.endPeriod|tiki_long_date}
                                {else}
                                    {tr}ending after{/tr} {$recurrence.nbRecurrences} {tr}events{/tr}
                                {/if}
                                {if $recurranceNumChangedEvents gt 1}
                                    {tr _0=$recurranceNumChangedEvents}(%0 events have been manually modified){/tr}
                                {elseif $recurranceNumChangedEvents gt 0}
                                    {tr _0=$recurranceNumChangedEvents}(%0 event has been manually modified){/tr}
                                {/if}
                                <br>
                            {else}
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input" id="id_recurrent" name="recurrent" value="1"{if $calitem.recurrenceId gt 0 or $recurrent eq 1} checked="checked" {/if}>
                                        {tr}This event depends on a recurrence rule{/tr}
                                    </label>
                                </div>
                            {/if}
                        {else}
                            <span class="summary">
                                {if $calitem.recurrenceId gt 0}
                                    {tr}This event depends on a recurrence rule{/tr}
                                {else}
                                    {tr}This event is not recurrent{/tr}
                                {/if}
                            </span>
                        {/if}
                    </div>
                </div> {* / .mb-3 *}
                <div class="row">
                    <div class="col-sm-9 offset-sm-3">
                        {if $edit}
                            <div id="recurrenceRules" style=" {if ( !($calitem.recurrenceId gt 0) and $recurrent neq 1 ) && $prefs.javascript_enabled eq 'y'} display:none; {/if}" >
                                {if $calitem.recurrenceId gt 0}
                                    <input type="hidden" name="recurrenceId" value="{$recurrence.id}">
                                {/if}
                                {if $recurrence.id gt 0}
                                    {if !empty($recurrence.weekly)}
                                        <input type="hidden" name="recurrenceType" value="weekly">{tr}On a weekly basis{/tr}<br>
                                    {/if}
                                {else}
                                    <input type="radio" id="id_recurrenceTypeW" name="recurrenceType" value="weekly" {if $recurrence.weekly or $recurrence.id eq 0} checked="checked" {/if} >
                                    <label for="id_recurrenceTypeW">
                                        {tr}On a weekly basis{/tr}
                                    </label>
                                {/if}
                                {if $recurrence.id eq 0 or $recurrence.weekly}
                                    <div class="mb-3 px-5">
                                        <div class="input-group">
                                            <span class="input-group-text">{tr}Each{/tr}</span>
                                            <select name="weekdays[]" class="form-control" multiple>
                                                <option value="SU"{if in_array('SU', $recurrence.weekdays)} selected="selected" {/if}>
                                                    {tr}Sunday{/tr}
                                                </option>
                                                <option value="MO"{if in_array('MO', $recurrence.weekdays)} selected="selected" {/if}>
                                                    {tr}Monday{/tr}
                                                </option>
                                                <option value="TU" {if in_array('TU', $recurrence.weekdays)} selected="selected" {/if}>
                                                    {tr}Tuesday{/tr}
                                                </option>
                                                <option value="WE" {if in_array('WE', $recurrence.weekdays)} selected="selected" {/if}>
                                                    {tr}Wednesday{/tr}
                                                </option>
                                                <option value="TH" {if in_array('TH', $recurrence.weekdays)} selected="selected" {/if}>
                                                    {tr}Thursday{/tr}
                                                </option>
                                                <option value="FR" {if in_array('FR', $recurrence.weekdays)} selected="selected" {/if}>
                                                    {tr}Friday{/tr}
                                                </option>
                                                <option value="SA" {if in_array('SA', $recurrence.weekdays)} selected="selected" {/if}>
                                                    {tr}Saturday{/tr}
                                                </option>
                                            </select>
                                            <span class="input-group-text">{tr}of the week{/tr}</span>
                                        </div>
                                        <hr/>
                                    </div>
                                {/if}
                                {if $recurrence.id gt 0}
                                    {if !empty($recurrence.monthly)}
                                        <input type="hidden" name="recurrenceType" value="monthly">{tr}On a monthly basis{/tr}<br>
                                    {/if}
                                {else}
                                    <input type="radio" id="id_recurrenceTypeM" name="recurrenceType" value="monthly" {if !empty($recurrence.monthly)} checked="checked" {/if} >
                                    <label for="id_recurrenceTypeM">
                                        {tr}On a monthly basis{/tr}
                                    </label>
                                {/if}
                                {if $recurrence.id eq 0 or $recurrence.monthly}
                                <div class="mb-3 px-5">
                                    <div class="input-group">
                                        <span class="input-group-text">{tr}Each{/tr}</span>
                                        <select name="dayOfMonth" class="form-control">
                                            {section name=k start=1 loop=32}
                                                <option value="{$smarty.section.k.index}" {if $recurrence.dayOfMonth eq $smarty.section.k.index} selected="selected" {/if} >
                                                    {if $smarty.section.k.index lt 10}
                                                        0
                                                    {/if}
                                                    {$smarty.section.k.index}
                                                </option>
                                            {/section}
                                        </select>
                                        <span class="input-group-text">{tr}of the month{/tr}</span>
                                    </div>
                                    <hr/>
                                </div>
                                {/if}
                                {if $recurrence.id gt 0}
                                    {if !empty($recurrence.yearly)}
                                        <input type="hidden" name="recurrenceType" value="yearly">{tr}On a yearly basis{/tr}<br>
                                    {/if}
                                {else}
                                    {* new recurrences default to yearly for now *}
                                    <input type="radio" id="id_recurrenceTypeY" name="recurrenceType" value="yearly">
                                    <label for="id_recurrenceTypeY">
                                        {tr}On a yearly basis{/tr}
                                    </label>
                                    <br>
                                {/if}
                                {if $recurrence.id eq 0 or $recurrence.yearly}
                                    <div class="mb-3 px-5">
                                        <div class="input-group">
                                            <span class="input-group-text">{tr}Each{/tr}</span>
                                            <select name="dateOfYear_day" class="form-control" onChange="checkDateOfYear(this.options[this.selectedIndex].value,document.forms['f'].elements['dateOfYear_month'].options[document.forms['f'].elements['dateOfYear_month'].selectedIndex].value);">
                                                {section name=k start=1 loop=32}
                                                    <option value="{$smarty.section.k.index}" {if $recurrence.dateOfYear_day eq $smarty.section.k.index} selected="selected" {/if} >
                                                        {if $smarty.section.k.index lt 10}
                                                            0
                                                        {/if}
                                                        {$smarty.section.k.index}
                                                    </option>
                                                {/section}
                                            </select>
                                            <span class="input-group-text">{tr}of{/tr}</span>
                                            <select name="dateOfYear_month" class="form-control" onChange="checkDateOfYear(document.forms['f'].elements['dateOfYear_day'].options[document.forms['f'].elements['dateOfYear_day'].selectedIndex].value,this.options[this.selectedIndex].value);">
                                                <option value="1" {if $recurrence.dateOfYear_month eq '1'} selected="selected" {/if}>
                                                    {tr}January{/tr}
                                                </option>
                                                <option value="2" {if $recurrence.dateOfYear_month eq '2'} selected="selected" {/if}>
                                                    {tr}February{/tr}
                                                </option>
                                                <option value="3" {if $recurrence.dateOfYear_month eq '3'} selected="selected" {/if}>
                                                    {tr}March{/tr}
                                                </option>
                                                <option value="4" {if $recurrence.dateOfYear_month eq '4'} selected="selected" {/if}>
                                                    {tr}April{/tr}
                                                </option>
                                                <option value="5" {if $recurrence.dateOfYear_month eq '5'} selected="selected" {/if}>
                                                    {tr}May{/tr}
                                                </option>
                                                <option value="6" {if $recurrence.dateOfYear_month eq '6'} selected="selected" {/if}>
                                                    {tr}June{/tr}
                                                </option>
                                                <option value="7" {if $recurrence.dateOfYear_month eq '7'} selected="selected" {/if}>
                                                    {tr}July{/tr}
                                                </option>
                                                <option value="8" {if $recurrence.dateOfYear_month eq '8'} selected="selected" {/if}>
                                                    {tr}August{/tr}
                                                </option>
                                                <option value="9" {if $recurrence.dateOfYear_month eq '9'} selected="selected" {/if}>
                                                    {tr}September{/tr}
                                                </option>
                                                <option value="10" {if $recurrence.dateOfYear_month eq '10'} selected="selected" {/if}>
                                                    {tr}October{/tr}</option>
                                                <option value="11" {if $recurrence.dateOfYear_month eq '11'} selected="selected" {/if}>
                                                    {tr}November{/tr}
                                                </option>
                                                <option value="12" {if $recurrence.dateOfYear_month eq '12'} selected="selected" {/if}>
                                                    {tr}December{/tr}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div id="errorDateOfYear" class="text-danger offset-sm-1"></div>
                                    <hr>
                                {/if}
                                {if $recurrence.id gt 0}
                                    <input type="hidden" name="startPeriod" value="{$recurrence.startPeriod}">
                                    <input type="hidden" name="nbRecurrences" value="{$recurrence.nbRecurrences}">
                                    <input type="hidden" name="endPeriod" value="{$recurrence.endPeriod}">
                                    {tr}Starting on{/tr} {$recurrence.startPeriod|tiki_long_date},&nbsp;
                                    {if $recurrence.endPeriod gt 0}
                                        {tr}ending by{/tr} {$recurrence.endPeriod|tiki_long_date}
                                    {else}
                                        {tr}ending after{/tr} {$recurrence.nbRecurrences} {tr}events{/tr}
                                    {/if}.
                                {else}
                                    {tr}Start date{/tr}<br>
                                    {if $prefs.feature_jscalendar eq 'y' and $prefs.javascript_enabled eq 'y'}
                                        <div class="offset-sm-1 col-sm-6 input-group">
                                            {if empty($recurrence.startPeriod)}{$startPeriod = $calitem.start}{else}{$startPeriod = $recurrence.startPeriod}{/if}
                                            {jscalendar id="startPeriod" date=$startPeriod fieldname="startPeriod" align="Bc" showtime='n'}
                                        </div>
                                    {else}
                                        <div class="offset-sm-1">
                                            {html_select_date prefix="startPeriod_" time=$recurrence.startPeriod field_order=$prefs.display_field_order start_year=$prefs.calendar_start_year end_year=$prefs.calendar_end_year}
                                        </div>
                                    {/if}
                                    <hr/>
                                    <input type="radio" id="id_endTypeNb" name="endType" value="nb" {if $recurrence.nbRecurrences or $calitem.calitemId eq 0 or empty($recurrence.id)} checked="checked" {/if}>
                                    <label for="id_endTypeNb">
                                        &nbsp;{tr}End after{/tr}
                                    </label>
                                    <div class="offset-sm-1 col-sm-6 input-group">
                                        <input type="number" min="1" name="nbRecurrences" size="3" class="form-control" style="z-index: 0"
                                               value="{if $recurrence.nbRecurrences gt 0}{$recurrence.nbRecurrences}{else}1{/if}">

                                        <div class="input-group-text mr-4">
                                            <span class="input-group-text">
                                                {if $recurrence.nbRecurrences gt 1}{tr}occurrences{/tr}{else}{tr}occurrence{/tr}{/if}
                                            </span>
                                        </div>
                                    </div>
                                    <br>
                                    <input type="radio" id="id_endTypeDt" name="endType" value="dt" {if $recurrence.endPeriod gt 0} checked="checked" {/if}>
                                    <label for="id_endTypeDt">
                                        &nbsp;{tr}End before{/tr}
                                    </label><br>
                                    {if $prefs.feature_jscalendar eq 'y' and $prefs.javascript_enabled eq 'y'}
                                        <div class="offset-sm-1 col-sm-6 input-group">
                                            {jscalendar id="endPeriod" date=$recurrence.endPeriod fieldname="endPeriod" align="Bc" showtime='n'}
                                        </div>
                                    {else}
                                        <div class="offset-sm-1">
                                            {html_select_date prefix="endPeriod_" time=$recurrence.endPeriod field_order=$prefs.display_field_order start_year=$prefs.calendar_start_year end_year=$prefs.calendar_end_year}
                                        </div>
                                    {/if}
                                    <br><br><hr>
                                {/if}
                            {else}
                                {if $recurrence.id > 0}
                                    {if $recurrence.nbRecurrences eq 1}
                                        {tr}Event occurs once on{/tr}&nbsp;{$recurrence.startPeriod|tiki_long_date}
                                    {/if}
                                    {if $recurrence.nbRecurrences gt 1 or $recurrence.endPeriod gt 0}
                                        {tr}Event is repeated{/tr}&nbsp;
                                        {if $recurrence.nbRecurrences gt 1}
                                            {$recurrence.nbRecurrences} {tr}times,{/tr}&nbsp;
                                        {/if}
                                        {if !empty($recurrence.weekly)}
                                            {tr}on{/tr}&nbsp
                                            {foreach $recurrence.weekdays as $day}{strip}
                                                {if $day@iteration eq $day@total}
                                                    &nbsp;{tr}and{/tr}&nbsp;
                                                {elseif not $day@last and not $day@first}
                                                    ,&nbsp;
                                                {/if}
                                                {tr}{$daysnames[$day]}s{/tr}
                                            {/strip}{/foreach}
                                        {elseif $recurrence.monthly}
                                            {tr}on{/tr}&nbsp;{$recurrence.dayOfMonth} {tr}of every month{/tr}
                                        {else}
                                            {tr}on each{/tr}&nbsp;{$recurrence.dateOfYear_day} {tr}of{/tr} {tr}{$monthnames[$recurrence.dateOfYear_month]}{/tr}
                                        {/if}
                                        <br>
                                        {tr}starting{/tr} {$recurrence.startPeriod|tiki_long_date}
                                        {if $recurrence.endPeriod gt 0}
                                            , {tr}ending{/tr}&nbsp;{$recurrence.endPeriod|tiki_long_date}
                                        {/if}.
                                    {/if}
                                {/if}
                            {/if}
                        </div>
                    </div>
                </div> {* / .row *}
            {/if}{* end recurrence *}
            <div class="row mt-md-3 mb-3 date">
                <label class="col-form-label col-sm-3">{tr}Start{/tr}</label>
                {if $edit}
                    <div class="col-sm-{if $prefs.feature_jscalendar eq 'y' and $prefs.javascript_enabled eq 'y'}5{else}4{/if} start">
                        {if $prefs.feature_jscalendar eq 'y' and $prefs.javascript_enabled eq 'y'}
                            {jscalendar id="start" date=$calitem.start fieldname="save[date_start]" showtime='y' isutc='y' notAfter='.date .end .datetime'}
                        {else}
                            {html_select_date prefix="start_date_" time=$calitem.start field_order=$prefs.display_field_order start_year=$prefs.calendar_start_year end_year=$prefs.calendar_end_year}
                        {/if}
                    </div>
                    {if $prefs.feature_jscalendar eq 'n' or $prefs.javascript_enabled eq 'n'}
                        <div class="col-sm-3 start time">
                            {html_select_time prefix="start_" display_seconds=false time=$calitem.start minute_interval=$prefs.calendar_minute_interval use_24_hours=$use_24hr_clock class='form-control date noselect2'}
                        </div>
                    {/if}
                    <div class="col-sm-2">
                        <div class="form-check">
                            <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" name="allday" id="allday" value="true" {if !empty($calitem.allday)} checked="checked"{/if}>
                                {tr}All day{/tr}
                            </label>
                        </div>
                    </div>
                {else}
                    <div class="col-sm-9">
                        <div class="summary" style="margin-bottom: 0; padding-top: 7px;">
                            {if !empty($calitem.allday)}
                                <abbr class="dtstart" title="{$calitem.start|tiki_short_date:'n'}">
                                    {$calitem.start|tiki_long_date}
                                </abbr>
                            {else}
                                <abbr class="dtstart" title="{$calitem.start|isodate}">
                                    {$calitem.start|tiki_long_datetime}
                                </abbr>
                            {/if}
                        </div>
                    </div>
                {/if}
            </div> {* / .mb-3 *}
            <div class="row mt-md-3 mb-3 date">
                <label class="col-form-label col-sm-3">{tr}End{/tr}</label>
                {if $edit}
                    <input type="hidden" name="save[end_or_duration]" value="end" id="end_or_duration">
                    <div class="col-sm-{if $prefs.feature_jscalendar eq 'y' and $prefs.javascript_enabled eq 'y'}5{else}4{/if} end ">
                            {if $prefs.feature_jscalendar eq 'y' and $prefs.javascript_enabled eq 'y'}
                            {jscalendar id="end" date=$calitem.end fieldname="save[date_end]" showtime='y' isutc='y' notBefore='.date .start .datetime'}
                            {else}
                                {html_select_date prefix="end_date_" time=$calitem.end field_order=$prefs.display_field_order start_year=$prefs.calendar_start_year end_year=$prefs.calendar_end_year}
                            {/if}
                    </div>
                    {if $prefs.feature_jscalendar eq 'n' or $prefs.javascript_enabled eq 'n'}
                        <div class="col-sm-3 end time">
                            {html_select_time prefix="end_" display_seconds=false time=$calitem.end minute_interval=$prefs.calendar_minute_interval use_24_hours=$use_24hr_clock class='form-control date noselect2'}
                        </div>
                    {/if}
                    <div class="col-sm-{if $prefs.feature_jscalendar eq 'y' and $prefs.javascript_enabled eq 'y'}5{else}4{/if} duration time" style="display:none;">
                        {html_select_time prefix="duration_" display_seconds=false time=$calitem.duration|default:'01:00' minute_interval=$prefs.calendar_minute_interval class='form-control date noselect2'}
                    </div>
                    <div class="col-sm-2 time">
                        <a href="#" id="durationBtn" class="btn btn-sm btn-secondary">
                            {tr}Show duration{/tr}
                        </a>
                    </div>
                {else}
                    <div class="col-sm-9">
                        <div class="summary" style="margin-bottom: 0; padding-top: 7px;">
                            {if !empty($calitem.allday)}
                                {if !empty($calitem.end)}
                                    <abbr class="dtend" title="{$calitem.end|tiki_short_date:'n'}">
                                {/if}
                                {$calitem.end|tiki_long_date}
                                {if !empty($calitem.end)}
                                    </abbr>
                                {/if}
                            {else}
                                {if !empty($calitem.end)}
                                    <abbr class="dtend" title="{$calitem.end|isodate}">
                                {/if}
                                {$calitem.end|tiki_long_datetime}
                                {if !empty($calitem.end)}
                                    </abbr>
                                {/if}
                            {/if}
                        </div>
                    </div>
                {/if}
                {if $impossibleDates}
                    <br>
                    <span style="color:#900;">
                        {tr}Events cannot end before they start{/tr}
                    </span>
                {/if}
            </div> {* / .mb-3 *}
            {if $edit or !empty($calitem.parsed)}
                <div class="mb-3 row">
                    <label class="col-form-label col-sm-3">{tr}Description{/tr}</label>
                    <div class="col-sm-9">
                        {if $edit}
                            {strip}
                                {textarea name="save[description]" id="editwiki" cols=40 rows=10}
                                    {$calitem.description}
                                {/textarea}
                            {/strip}
                        {else}
                            <div{if $prefs.calendar_description_is_html neq 'y'} class="description"{/if}  style="margin-bottom: 0; padding-top: 7px;">
                                {$calitem.parsed|default:"<i>{tr}No description{/tr}</i>"}
                            </div>
                        {/if}
                    </div>
                </div>
            {/if}
            {if $calendar.customstatus ne 'n'}
                <div class="mb-3 row">
                    <label class="col-form-label col-sm-3">{tr}Status{/tr}</label>
                    <div class="col-sm-9 btn-group btn-group-toggle"  data-bs-toggle="buttons">
                        <div class="statusbox">
                            {if $edit}
                                <label class="btn btn-primary active">
                                    <input type="radio" name="save[status]" value="0" autocomplete="off"
                                        {if (!empty($calitem) and $calitem.status eq 0) or (empty($calitem) and $calendar.defaulteventstatus eq 0)}
                                            checked="checked"
                                        {/if}
                                    >
                                    {tr}Tentative{/tr}
                                </label>
                            {else}
                                {tr}Tentative{/tr}
                            {/if}
                        </div>
                        <div class="statusbox">
                            {if $edit}
                                <label class="btn btn-primary active">
                                    <input  type="radio" name="save[status]" value="1" {if $calitem.status eq 1} checked="checked" {/if} >
                                    {tr}Confirmed{/tr}
                                </label>
                            {else}
                                {tr}Confirmed{/tr}
                            {/if}
                        </div>
                        <div class="statusbox">
                            {if $edit}
                                <label class="btn btn-primary active">
                                    <input id="status2" type="radio" name="save[status]" value="2" {if $calitem.status eq 2} checked="checked" {/if} >
                                    {tr}Cancelled{/tr}
                                </label>
                            {else}
                                {tr}Cancelled{/tr}
                            {/if}
                        </div>
                    </div>

                </div> {* / .mb-3 *}
            {/if}
            {if $calendar.custompriorities eq 'y'}
                <div class="mb-3 row clearfix">
                    <label class="col-form-label col-sm-3">{tr}Priority{/tr}</label>
                    <div class="col-sm-2">
                        {if $edit}
                            <select name="save[priority]" style="background-color:#{$listprioritycolors[$calitem.priority]};" onchange="this.style.bacgroundColor='#'+this.selectedIndex.value;" class="form-control">
                                {foreach item=it from=$listpriorities}
                                    <option value="{$it}" style="background-color:#{$listprioritycolors[$it]};" {if $calitem.priority eq $it} selected="selected" {/if}>
                                        {$it}
                                    </option>
                                {/foreach}
                            </select>
                        {else}
                            <span style="background-color:#{$listprioritycolors[$calitem.priority]};font-size:150%;width:90%;padding:1px 4px">
                                {$calitem.priority}
                            </span>
                        {/if}
                    </div>
                </div> {* / .mb-3 *}
            {/if}

            {* Form group global categorization *}
            {include file='categorize.tpl'}

            <div class="mb-3 row" style="display:{if $calendar.customcategories eq 'y'}block{else}none{/if};" id="calcat">
                <label class="col-form-label col-sm-3">
                    {tr}Classification{/tr}
                </label>
                <div class="col-sm-9">
                    {if $edit}
                        {if count($listcats)}
                            <select name="save[categoryId]" class="form-control">
                                <option value="">
                                </option>
                                {foreach item=it from=$listcats}
                                    <option value="{$it.categoryId}" {if $calitem.categoryId eq $it.categoryId} selected="selected" {/if}>
                                        {$it.name|escape}
                                    </option>
                                {/foreach}
                            </select>
                            {tr}or new{/tr}
                        {/if}
                        <input class="form-control" type="text" name="save[newcat]" value="">
                    {else}
                        <span class="category">
                            {$calitem.categoryName|escape}
                        </span>
                    {/if}
                </div>
            </div> {* / .mb-3 *}
            <div class="mb-3 row" style="display:{if $calendar.customlocations eq 'y'}block{else}none{/if};" id="calloc">
                <label class="col-form-label col-sm-3">{tr}Location{/tr}</label>
                <div class="col-sm-9">
                    {if $edit}
                        {if count($listlocs)}
                            <select name="save[locationId]" class="form-control">
                                <option value="">
                                </option>
                                {foreach item=it from=$listlocs}
                                    <option value="{$it.locationId}" {if $calitem.locationId eq $it.locationId} selected="selected" {/if}>
                                        {$it.name|escape}
                                    </option>
                                {/foreach}
                            </select>
                            {tr}or new{/tr}
                        {/if}
                        <input class="form-control" type="text" name="save[newloc]" value="">
                    {else}
                        <span class="location">
                            {$calitem.locationName|escape}
                        </span>
                    {/if}
                </div>
            </div> {* / .mb-3 *}
            {if $calendar.customurl ne 'n'}
                <div class="mb-3 row" style="display:{if $calendar.customcategories eq 'y'}block{else}none{/if};">
                    <label class="col-form-label col-sm-3">{tr}URL{/tr}</label>
                    <div class="col-sm-9">
                        {if $edit}
                            <input type="text" name="save[url]" value="{$calitem.url}" size="32" class="form-control">
                        {else}
                            <a class="url" href="{$calitem.url}">
                                {$calitem.url|escape}
                            </a>
                        {/if}
                    </div>
                </div> {* / .mb-3 *}
            {/if}
            <div class="mb-3 row" style="display:{if $calendar.customlanguages eq 'y'}block{else}none{/if};" id="callang">
                <label class="col-form-label col-sm-3">{tr}Language{/tr}</label>
                <div class="col-sm-9">
                    {if $edit}
                        <select name="save[lang]" class="form-control">
                            <option value="">
                            </option>
                            {foreach item=it from=$listlanguages}
                                <option value="{$it.value}" {if $calitem.lang eq $it.value} selected="selected" {/if}>
                                    {$it.name}
                                </option>
                            {/foreach}
                        </select>
                    {else}
                        {$calitem.lang|langname}
                    {/if}
                </div>
            </div> {* / .mb-3 *}
            {if !empty($groupforalert) && $showeachuser eq 'y'}
                <div class="mb-3 row">
                    <label class="col-form-label col-sm-3">{tr}Choose users to alert{/tr}</label>
                    <div class="col-sm-9">
                        {section name=idx loop=$listusertoalert}
                            {if $showeachuser eq 'n'}
                                <input type="hidden" name="listtoalert[]" value="{$listusertoalert[idx].user}">
                            {else}
                                <input type="checkbox" class="form-check-input" name="listtoalert[]" value="{$listusertoalert[idx].user}"> {$listusertoalert[idx].user}
                            {/if}
                        {/section}
                    </div>
                </div> {* / .mb-3 *}
            {/if}
            <div class="mb-3 row" style="display:{if $calendar.customparticipants eq 'y'}block{else}none{/if};" id="calorg">
                <label class="col-form-label col-sm-3">{tr}Organized by{/tr}</label>
                <div class="col-sm-9">
                    {if isset($calitem.organizers)}
                        {if $edit}
                            {user_selector name='save[organizers]' select=$calitem.organizers multiple='true' allowNone='y' editable='y'}
                        {else}
                            {foreach item=org from=$calitem.organizers}
                                {$org|userlink}<br>
                            {/foreach}
                        {/if}
                    {/if}
                </div>
            </div> {* / .mb-3 *}
            <div class="mb-3 row" style="display:{if $calendar.customparticipants eq 'y'}block{else}none{/if};" id="calpart">
                <label class="col-form-label col-sm-3">{tr}Participants{/tr}</label>
                <div class="col-sm-9">
                    {if isset($calitem.participants)}
                        {if $edit}
                            {user_selector name='participants' select=$calitem.selected_participants multiple='true' allowNone='y' editable='y' realnames='n'}
                            <br>
                            <div class="row">
                                <div class="col-sm-9">
                                    <input type="text" name="add_participant_email" id="add_participant_email" value="" placeholder="or invite email address..." class="form-control">
                                </div>
                                <div class="col-sm-3">
                                    <input type="button" class="btn btn-primary" value="Add" id="invite_emails">
                                </div>
                            </div>
                            <br>
                            <table cellpadding="0" cellspacing="0" border="0" class="table normal table-bordered" id="participant_roles">
                            <tr>
                                <th>{tr}Invitee{/tr}</th>
                                <th>{tr}Status{/tr}</th>
                                <th>{tr}Role{/tr}</th>
                                <th></th>
                            </tr>
                            {foreach item=ppl from=$calitem.participants}
                            <tr data-user="{$ppl.username|escape}">
                                <td>{$ppl.username|userlink}</td>
                                <td>
                                    <select name="save[participant_partstat][{$ppl.username}]" class="form-control">
                                        <option value="NEEDS-ACTION">NEEDS-ACTION</option>
                                        <option value="ACCEPTED" {if $ppl.partstat eq 'ACCEPTED'}selected{/if}>ACCEPTED</option>
                                        <option value="TENTATIVE" {if $ppl.partstat eq 'TENTATIVE'}selected{/if}>TENTATIVE</option>
                                        <option value="DECLINED" {if $ppl.partstat eq 'DECLINED'}selected{/if}>DECLINED</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="save[participant_roles][{$ppl.username}]" class="form-control">
                                        <option value="0">{tr}chair{/tr}</option>
                                        <option value="1" {if $ppl.role eq '1'}selected{/if}>{tr}required participant{/tr}</option>
                                        <option value="2" {if $ppl.role eq '2'}selected{/if}>{tr}optional participant{/tr}</option>
                                        <option value="3" {if $ppl.role eq '3'}selected{/if}>{tr}non-participant{/tr}</option>
                                    </select>
                                </td>
                                <td>
                                    <a href="#" class="delete-participant"><span class="icon icon-remove fas fa-times"></span></a>
                                </td>
                            </tr>
                            {/foreach}
                            </table>
                            <input type="checkbox" name="save[process_itip]" value="1" checked> Send calendar invitations and event updates via email
                        {else}
                            {assign var='in_particip' value='n'}
                            {foreach item=ppl from=$calitem.participants}
                                {$ppl.username|userlink}
                                {if $listroles[$ppl.role]}
                                    ({$listroles[$ppl.role]})
                                {/if}
                                <br>
                                {if $ppl.username eq $user}
                                    {assign var='in_particip' value='y'}
                                {/if}
                            {/foreach}
                            {if $tiki_p_calendar_add_my_particip eq 'y'}
                                {if $in_particip eq 'y'}
                                    {button _text="{tr}Withdraw me from the list of participants{/tr}" href="?del_me=y&viewcalitemId=$id"}
                                {else}
                                    {button _text="{tr}Add me to the list of participants{/tr}" href="?add_me=y&viewcalitemId=$id"}
                                {/if}
                            {/if}
                            {if $tiki_p_calendar_add_guest_particip eq 'y'}
                                {* Nested forms do not work
                                    <form action="tiki-calendar_edit_item.php" method="post">
                                        <input type ="hidden" name="viewcalitemId" value="{$id}">
                                        <input type="text" name="guests">{help desc="{tr}Format:{/tr} {tr}Participant names separated by comma{/tr}" url='calendar'}
                                        <input type="button" class="btn btn-primary btn-sm" name="add_guest" value="Add guests">
                                    </form>
                                *}
                            {/if}
                        {/if}
                    {/if}
                </div>
            </div> {* / .mb-3 *}
            {if $edit}
                {if $recurrence.id gt 0}
                    <div class="row">
                        <div class="col-sm-9 offset-sm-3">
                            <input type="radio" id="id_affectEvt" name="affect" value="event">
                            <label for="id_affectEvt">
                                {tr}Update this event only{/tr}
                            </label><br>
                            {if $recurranceNumChangedEvents}
                                <input type="radio" id="id_affectMan" name="affect" value="manually" checked="checked">
                                <label for="id_affectMan">
                                    {tr}Update every unchanged event in this recurrence series{/tr}
                                </label><br>
                            {/if}
                            <input type="radio" id="id_affectAll" name="affect" value="all"{if $recurranceNumChangedEvents eq '0'} checked="checked"{/if}>
                            <label for="id_affectAll">
                                {tr}Update every event in this recurrence series{/tr}
                            </label>
                        </div>
                    </div>
                {/if}
                {if !$user and $prefs.feature_antibot eq 'y'}
                    {include file='antibot.tpl'}
                {/if}
            {/if}
        </div> {* /.wikitext *}
        {if $prefs.feature_jscalendar eq 'y' and $prefs.javascript_enabled eq 'y'}
            {js_insert_icon type="jscalendar"}
        {/if}
    </div> {* /.modal-body *}
    {if $edit}
        <div class="modal-footer">
            <div class="submit">
                <input type="submit" class="btn btn-secondary" name="preview" value="{tr}Preview{/tr}" onclick="needToConfirm=false;$('#editcalitem').data('submitter', 'preview');">
                <input type="submit" class="btn btn-primary" name="act" value="{tr}Save{/tr}" onclick="needToConfirm=false;$('#editcalitem').data('submitter', 'act');">
                {if $tiki_p_add_events eq 'y' and empty($saveas) and not empty($id)}
                    {button href='tiki-calendar_edit_item.php?saveas=1&calitemId='|cat:$id _text="{tr}Copy to a new event{/tr}" _class='saveas' _id="copy_to_new_event"}
                {/if}
                {if $id}
                    <input type="submit" class="btn btn-danger" onclick="needToConfirm=false;document.location='tiki-calendar_edit_item.php?calitemId={$id}&amp;delete=y';return false;" value="{tr}Delete event{/tr}">
                {/if}
                {if !empty($recurrence.id)}
                    <input type="submit" class="btn btn-danger" onclick="needToConfirm=false;document.location='tiki-calendar_edit_item.php?recurrenceId={$recurrence.id}&amp;delete=y';return false;" value="{tr}Delete recurrent events{/tr}">
                {/if}
                {if $prefs.calendar_export_item == 'y' and not empty($id)}
                    {button href='tiki-calendar_export_ical.php? export=y&calendarItem='|cat:$id _text="{tr}Export Event as iCal{/tr}"}
                {/if}
                <input type="submit" class="btn btn-link" onclick="needToConfirm=false;document.location='{$referer|escape:'html'}';return false;" value="{tr}Cancel{/tr}">
            </div>
        </div>
    {/if}
    </fieldset>
</form>