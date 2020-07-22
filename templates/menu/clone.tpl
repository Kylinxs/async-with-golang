{extends 'layout_view.tpl'}

{block name="title"}
    {title}{$title|escape}{/title}
{/block}

{block name="content"}
    <form action="{service controller=menu action=clone}" method="post" role="form">
        <div class="mb-3 row mx-2">
            <label for="name" class="col-form-label">
                {tr}Name{/tr}
            </label>
            <input class="form-control" name="name" id="name" value="{tr _0=$info.name|escape}%0 Copy{/tr}">
            <div class="small">
                {if !empty($info.menuId)}
                    {tr}Clone of Menu Id:{/tr} {$info.menuId|escape}
                {/if}
                {if $symbol}
                    <a class="btn btn-link btn-sm tips" title="{tr}Symbol Information{/tr}|{tr}Symbol:{/tr} <strong>{$symbol.object}</strong><br>{tr}Profile Name:{/tr} <strong>{$symbol.profile}</strong><br>{tr}Profile Source:{/tr} <strong>{$symbol.domain}</strong>">
                        {icon name="information"}
                    </a>
                {/if}
            </div>
        </div>
        <div class="mb-3 row mx-2">
            <label for="description" class="col-form-label">
                {tr}Description{/tr}
            </label>
            <textarea name="description" id="description" class="form-control">{$info.description|escape}</textarea>
        </div>

        <div class="submit">
            {ticket mode=confirm}
            <input type="hidden" name="menuId" value="{$info.menuId|escape}">
            <input type="submit" class="btn btn-primary" name="clone" value="{tr}Clone{/tr}">
        </div>
    </form>
{/block}
