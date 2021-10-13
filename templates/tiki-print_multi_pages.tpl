
{* Upload Mine *}
{* $Id$ *}<!DOCTYPE html>
<html id="print" lang="{if !empty($pageLang)}{$pageLang}{else}{$prefs.language}{/if}">
    <head>
        {include file='header.tpl'}
        <meta name="viewport" content="width=device-width, initial-scale=1" />
    </head>
    <body{html_body_attributes}>
    <span id="nav-button" class="fas fa-list tocbutton" onclick="toggleNav()"></span>
    <div class="" id="navcontainer">
        <nav id="sidetoc"></nav>
    </div>
    <div class="container">
        <div class="col-md-2 col-lg-2"></div>
        <div id="tiki-clean" class=" col-xs-12 col-sm-12 col-md-8 col-lg-8 ">
            <span id="print-button" class="fas fa-print printstructure" style="float:right" onclick="window.print()"></span>
            {section name=ix loop=$pages}
                {if $prefs.feature_page_title ne 'n'}
                    <h{math equation="x+1" x=$pages[ix].h}>
                    {if isset($pages[ix].pos)}
                        {$pages[ix].pos}
                    {/if}
                    {$pages[ix].pageName}
                    </h{math equation="x+1" x=$pages[ix].h}>
                {/if}
                <div class="wikitext">
                    {$pages[ix].parsed}
                    {wikiplugin _name=showreference pageid="{$pages[ix].id}" title="References:" showtitle="yes" removelines="yes"}
                    {/wikiplugin}
                </div>
            {/section}
        </div>
        {include file='footer.tpl'}
    </div>
    </body>
</html>