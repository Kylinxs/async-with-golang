
{tikimodule
decorations="{$module_params.decorations}"
error="{$error}"
flip="{$module_params.flip}"
nobox="{$module_params.nobox}"
nonums="{$module_params.nonums}"
notitle="{$module_params.notitle}"
overflow="{$module_params.overflow}"
title=$tpl_module_title
style="{$module_params.style}"
}
{if empty($error)}
    <div class="mod-git_detail cvsup">
        <span class="label">{tr}Last updated{/tr}</span>&nbsp;
        <span class="branch">(Git {$content.branch}:{$content.commit.hash|substring:0:8}):</span>&nbsp;
        <span class="date">{$content.mdate|tiki_long_datetime}</span>
    </div>
{else}
    {tr}No Git checkout or unable to determine last update{/tr}
{/if}
{/tikimodule}