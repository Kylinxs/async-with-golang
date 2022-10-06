
{if !empty($filegals_manager) and !isset($smarty.request.simpleMode)}
    {assign var=simpleMode value='y'}
{else}
    {assign var=simpleMode value='n'}
{/if}
{if !empty($filegals_manager)}
    {assign var=seturl value=$fileId|sefurl:display}
    {capture name=alink assign=alink}href="#" onclick="window.opener.insertAt('{$filegals_manager}','{$syntax|escape}');checkClose();return false;" title="{tr}Click here to use the file{/tr}" class="tips"{/capture}
{else}
    {assign var=alink value=''}
{/if}
<div class="d-flex">
    {if $view neq 'page'}
    {$type = $name|iconify:null:null:null:'filetype'}
    {if $type eq 'image/png' or $type eq 'image/jpeg' or $type eq 'image/jpg'
    or $type eq 'image/gif' or $type eq 'image/x-ms-bmp'}
        {$imagetypes = 'y'}
    {else}
        {$imagetypes = 'n'}
    {/if}
    <div class="media-left mr-3">
        {if $imagetypes eq 'y' or $prefs.theme_iconset eq 'legacy'}
            {if !empty($filegals_manager)}
                <a {$alink}>
                    <img src="{$fileId|sefurl:thumbnail}"><br>
                        <span class="thumbcaption">
                            {tr}Click here to use the file{/tr}
                        </span>
                </a>
            {else}
                <img src="{$fileId|sefurl:thumbnail}">
            {/if}
        {else}
            {$name|iconify:$type:null:3}
        {/if}
    </div>
    <div class="flex-grow-1 ms-3">
        {if !empty($filegals_manager)}
            <a {$alink}>{$name|escape} ({$size|kbsize})</a>
        {else}
            <b>{$name|escape} ({$size|kbsize})</b>
        {/if}
        {if $feedback_message != ''}
            <div class="upload_note">
                {$feedback_message}
            </div>
        {/if}
        {else}
        <div>
        {/if}
            <div class="mb-3" style="margin-top: 1em;">
            {button href="#" _onclick="javascript:flip('uploadinfos$fileId');flip('close_uploadinfos$fileId','inline');return false;" _text="{tr}Syntax Tips{/tr}"}
                {if isset($ocrdata)}
                    {button href="#" _onclick="javascript:flip('ocrdata$fileId');flip('close_ocrdata$fileId','inline');return false;" _text="{tr}OCR Data{/tr}"}
                {/if}
                    <span id="close_uploadinfos{$fileId}" style="display:none">
                        {button href="#" _onclick="javascript:flip('uploadinfos$fileId');flip('close_uploadinfos$fileId','inline');return false;" _text="({tr}Hide{/tr})"}
                    </span>
                {if isset($ocrdata)}
                    <span id="close_ocrdata{$fileId}" style="display:none">
                        {button href="#" _onclick="javascript:flip('ocrdata$fileId');flip('close_ocrdata$fileId','inline');return false;" _text="({tr}Hide{/tr})"}
                    </span>
                {/if}
        </div>
        <div style="{if $prefs.javascript_enabled eq 'y'}display:none;{/if}" id="uploadinfos{$fileId}">
            <div class="row">
                <div class="col-sm-6 text-end">
                    {tr}Link to file from a Wiki page:{/tr}
                </div>
                <div class="col-sm-6">
                    <code>[{$fileId|sefurl:file}|{$name|escape}]</code>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 text-end">
                    <strong><em>{tr}For image files:{/tr}</em></strong>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 text-end">
                    {tr}Display full size:{/tr}
                </div>
                <div class="col-sm-6">
                    <code>{ldelim}img fileId="{$fileId}"{rdelim}</code>
                </div>
            </div>
            {if $prefs.feature_shadowbox eq 'y'}
                <div class="row">
                    <div class="col-sm-6 text-end">
                        {tr}Display thumbnail that enlarges:{/tr}
                    </div>
                    <div class="col-sm-6">
                        <code>{ldelim}img fileId="{$fileId}" thumb="box"{rdelim}</code>
                    </div>
                </div>
            {/if}
        </div>

        {if isset($ocrdata)}
            <div style="{if $prefs.javascript_enabled eq 'y'}display:none;{/if}" id="ocrdata{$fileId}">

                {remarksbox type="tip" title="{tr}Extracted OCR Data{/tr}"}
                    <i>Using {$ocrlangs}</i>
                    <hr>
                {$ocrdata}
                {/remarksbox}
            </div>
        {/if}

    </div>
</div>
<hr>