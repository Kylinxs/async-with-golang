{* $Id$ *}<!DOCTYPE html>
<html lang="{if !empty($pageLang)}{$pageLang}{else}{$prefs.language}{/if}"{if !empty($page_id)} id="page_{$page_id}"{/if}>
<head>
    {include file='header.tpl'}
</head>
<body{html_body_attributes}>

{* Index we display a wiki page here *}
{if $prefs.feature_bidi eq 'y'}
<div dir="rtl">
{/if}
{if $prefs.feature_ajax eq 'y'}
    {include file='tiki-ajax_header.tpl'}
{/if}
{if $is_slideshow eq 'y'}
    <div class="reveal">
        <div class="slides">
{else}
    <div id="main">
        <div id="tiki-center">
            <div id="role_main">
{/if}

{$mid_data}
{if $is_slideshow eq 'y'}
    </div>
        <div id="ss-settings-holder" title="Click for slideshow operations"><span class="fas fa-cogs" style="font-size:1rem;color:#666" id="ss-settings"></span></div>
        <div id="ss-options" class="d-flex flex-row justify-content-around align-content-end flex-wrap">
            <div class="p-2">
                <select id="showtheme" class="form-control">
                    <option value="">{tr}Change Theme{/tr}</option>
                    {$themeOptions}
                </select>
            </div>
            <div class="p-2">
                <select id="showtransition" class="form-control">
                    <option value="">{tr}Change Transition{/tr}</option>
                    <option value="zoom">{tr}Zoom{/tr}</option>
                    <option value="fade">{tr}Fade{/tr}</option>
                    <option value="slide">{tr}Slide{/tr}</option>
                    <option value="convex">{tr}Convex{/tr}</option>
                    <option value="concave">{tr}Concave{/tr}</option>
                    <option value="">{tr}Off{/tr}</option>
                </select>
            </div>
            <div class="p-2" id="reveal-controls"><span class="fas fa-fast-backward mr-1"  id="firstSlide" title="Go to First Slide"></span><span class="fas fa-step-backward mr-1" id="prevSlide" title="Go to Previous Slide"></span><span class="fas fa-play-circle mr-1" id="play"></span><span class="fas fa-undo mr-1 icon-inactive" id="loop" title="Auto-play in loop"></span><span class="fas fa-step-forward mr-1"  id="nextSlide" title="Go to Next Slide"></span><span class="fas fa-fast-forward"  id="lastSlide" title="Go to Last Slide"></span></div>
            <div class="p-2" id="listSlides"><span class="fas fa-list mr-1"   title="List Slides"></span> List Slides</div>

            {if $prefs.feature_slideshow_pdfexport eq 'y'}
                <div class="p-2"><a href="tiki-slideshow.php?page={$page}&pdf=1&landscape=1" target="_blank" id="exportPDF"><span class="far fa-file-pdf"></span> {tr}Export PDF{/tr}</a></div>
                <div class="p-2"><a href="tiki-slideshow.php?page={$page}&pdf=1&printslides=1" target="_blank"><span class="fas fa-print"></span> {tr} Handouts{/tr}</a></div>
            {/if}

            <div class="p-2"><a href="tiki-index.php?page={$page}"><span class="fas fa-sign-out-alt"></span> {tr}Exit Slideshow{/tr}</a></div>
        </div>
{else}
            </div>
        </div>
    </div>
{/if}
{if $prefs.feature_bidi eq 'y'}
    </div>
{/if}
{include file='footer.tpl'}
</body>
</html>
