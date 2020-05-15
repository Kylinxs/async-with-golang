{extends 'layout_view.tpl'}

{block name="title"}
    {title}{$title|escape}{/title}
{/block}

{block name="content"}
    <form method="post" action="{service controller=managestream action=deleteactivity}">
        <p>{tr}Are you certain you want to delete this activity? It will be removed permanently from the database and will affect any statistics that depend on it.{/tr}</p>
        <pre>ID {$activityId|escape}</pre>
        <div class="submit">
            {ticket mode='confirm'}
            <input type="hidden" name="activityId" value="{$activityId|escape}"/>
            <input type="submit" class="btn btn-primary" value="{tr}Delete{/tr}"/>
        </div>
    </form>
{/block}
