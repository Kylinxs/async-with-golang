{extends 'layout_view.tpl'}

{block name="title"}
    {title}{$title|escape}{/title}
{/block}

{block name="content"}
<form method="post" action="{service controller=ml action=delete mlmId=$mlmId}">
    <p>{tr}Are you sure you want to delete this model?{/tr}</p>
    <div class="submit">
        <input type="submit" class="btn btn-danger" value="{tr}Delete model{/tr}">
    </div>
</form>
{/block}
