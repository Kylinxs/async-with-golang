
{* $Id$ *}
{* Used by smarty_function_filegal_uploader() when $prefs.file_galleries_use_jquery_upload is enabled *}
{* The fileinput-button span is used to style the file input field as button *}
<div class="mb-3 row">
    <div class="col-md-12">
        <div class="card bg-light fileupload mb-0">
            <div class="card-body">
                <h3 class="text-center">{icon name="cloud-upload"} {tr}Drop files or {/tr}
                    <div class="btn btn-primary fileinput-button">
                        <span>{tr}Choose files{/tr}</span>
                        {* The file input field used as target for the file upload widget *}
                        <input id="fileupload" type="file" name="files[]" multiple>
                    </div>
                </h3>
            </div>
        </div>
    </div>
</div>
<div class="mb-3 row">
    <div id="files" class="files text-center col-md-12"></div>
</div>
<div class="col-sm-12">
<div class="form-check">
    <label for="autoupload" class="form-check-label">{* auto-upload user pref *}
        <input class="form-check-input" type="checkbox" id="autoupload" name="autoupload"{if $prefs.filegals_autoupload eq 'y'} checked="checked"{/if}>
        {tr}Automatic upload{/tr}
    </label>{* The container for the uploaded files *}
</div>
</div>
<div class="d-none">
    {icon name='file' id='file_icon'}
    {icon name='pdf' id='pdf_icon'}
    {icon name='video' id='video_icon'}
    {icon name='audio' id='audio_icon'}
    {icon name='zip' id='zip_icon'}
</div>