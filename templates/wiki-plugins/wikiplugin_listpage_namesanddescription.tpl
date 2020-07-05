{* $Id$ *}
{strip}
<ul>
    {section name=ix loop=$listpages}
        <li>
            <a href="{$listpages[ix].pageName|sefurl}" class="link" title="{tr}view{/tr}">
                {if !empty($showPageAlias) and $showPageAlias eq 'y' and !empty($listpages[ix].page_alias)}
                    {$listpages[ix].page_alias}
                {else}
                    {$listpages[ix].pageName}
                {/if}
            </a>
            {if $prefs.wiki_list_description eq 'y' && $listpages[ix].description neq ""}
                <span> - {$listpages[ix].description|truncate:80:"...":false}</span>
            {/if}
        </li>
    {/section}
    {if  $showNumberOfPages eq 'y'}
        {tr}Number of result:{/tr}{$listpages|@count}
    {/if}
</ul>
{/strip}
