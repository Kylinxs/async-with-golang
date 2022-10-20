{* $Id$ *}
{remarksbox type="tip" title="{tr}Tip{/tr}"}{tr}Allows a copyright to be determined for various objects{/tr}.{/remarksbox}
<form role="form" action="tiki-admin.php?page=copyright" method="post" class="admin">
    {ticket}
    <div class="t_navbar mb-4 clearfix">
        {include file='admin/include_apply_top.tpl'}
    </div>
    <fieldset>
        <legend>{tr}Activate the feature{/tr}</legend>
        {preference name=feature_copyright visible="alwa