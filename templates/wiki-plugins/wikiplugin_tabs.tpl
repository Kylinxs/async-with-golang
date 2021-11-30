{* $Id$
 * smarty template for tabs wiki plugin
 *}
{if isset($is_slideshow) and $is_slideshow eq 'y'}
    {foreach from=$tabs key=i item=tab}
        {$tabcontent[$i]}
    {/foreach}
{else}
~np~{tabset toggle=$toggle params=$params name=$tabsetname|escape}
    {section name=ix loop=$tabs}{tab params=$params name=$tabs[ix]|escape}{$tabcontent[ix]}{/tab}{/section}
{/tabset}~/np~
{/if}
