{extends 'layout_view.tpl'}

{block name="title"}
    {title}{$title}{/title}
{/block}

{block name="content"}
    <form method="post" action="{service controller=webhook action=create}">
        {include file='webhook/form.tpl'}
        <div class="submit">
            <input
                type="submit"
                class="btn btn-primary"
                value="{tr}Create{/tr}"
            >
        </div>
    </form>
{/block}
