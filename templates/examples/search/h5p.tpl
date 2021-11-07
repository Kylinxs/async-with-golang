{* Template for Plugin List to generate data for H5P content

Notes: Syntax likely to change and improve before being added as an official list template
       Probably only really works with the timeline content type so far

Example wiki syntax (using the tracker from https://profiles.tiki.org/Tracker_as_Calendar_19):

{LIST()}
  {filter type="trackeritem"}
  {filter field="tracker_id" exact="10"}
  {sort mode="tracker_field_trac_as_cal_start_date_nasc"}
  {OUTPUT(template="templates/examples/search/h5p.tpl")}
    {settings type="timeline" param="date" fileId="3720"}
    {column label="headline" field="title"}
    {column label="text" field="tracker_field_trac_as_cal_location"}
    {column label="startDate" field="startDate"}
    {column label="endDate" field="endDate"}
{OUTPUT}
  {FORMAT( name="startDate")}{display name="tracker_field_trac_as_cal_start_date" format="datetime"}{FORMAT}
  {FORMAT( name="endDate")}{display name="tracker_field_trac_as_cal_end_date" format="datetime"}{FORMAT}
{LIST}
*}


{if not empty($column.field)}
    {$column = [$column]}{* if there is only one column then it will not be in an array *}
{/if}
{$data = []}

{foreach from=$results item=row}
    {$datarow = []}
    {foreach from=$column item=col}
        {if !empty($row[$col.field])}
            {$value = $row[$col.field]|nonp}
            {$decoded = $value|json_decode}
            {if $decoded !== null}
                {$value = $decoded}
            {/if}
            {$datarow[$col.label] = $value}
        {/if}
    {/foreach}
    {$data[] = $datarow}
{/foreach}
{$output = [$settings.type => [$settings.param => $data]]}

{service_inline controller='h5p' action='embed' fileId=$settings.fileId extra=$output}
