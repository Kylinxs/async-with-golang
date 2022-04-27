
{* $Id$ *}
{remarksbox type="tip" title="{tr}Tip{/tr}"}
    {tr}To create or remove file galleries, select{/tr} <a class="alert-link" href="tiki-list_file_gallery.php">{tr}File Galleries{/tr}</a> {tr}from the application menu{/tr}.
    <hr>
    {tr}If you decide to store files in a directory you must ensure that the user cannot access directly to the directory.{/tr}
    {tr}You have two options to accomplish this:<br><ul><li>Use a directory outside your document root, make sure your php script can read and write to that directory</li><li>Use a directory inside the document root and use .htaccess to prevent the user from listing the directory contents</li></ul>{/tr}
    {tr}To configure the directory path use UNIX like paths for example files/ or c:/foo/files or /www/files/{/tr}
{/remarksbox}

<form action="tiki-admin.php?page=fgal" method="post" class="admin">
    {ticket}

    <div class="row">
        <div class="mb-3 col-lg-12 clearfix">
            <a role="link" class="btn btn-link tips" href="tiki-list_file_gallery.php" title="{tr}File galleries listing{/tr}">
                {icon name="list"} {tr}File Galleries{/tr}
            </a>
            {include file='admin/include_apply_top.tpl'}
        </div>
    </div>
    {tabset name="fgal_admin"}

        {tab name="{tr}General Settings{/tr}"}
            <br>

            <fieldset>
                <legend>{tr}Activate the feature{/tr}</legend>
                {preference name=feature_file_galleries visible="always"}
            </fieldset>

            {preference name=home_file_gallery}
            {preference name=fgal_list_description}
            {preference name='fgal_use_db'}
            <div class="adminoptionboxchild fgal_use_db_childcontainer n">
                <div class="mb-sm-3">
                    {preference name='fgal_preserve_filenames'}
                </div>
                <div class="mb-sm-3">
                    {preference name='fgal_use_dir'}
                    <button role="button" type="submit" class="btn btn-primary" name="move" value="to_fs">
                        {tr}Move files from database to directory{/tr}
                    </button>
                </div>
            </div>
            <div class="col-sm-8 offset-sm-4 mb-sm-3 adminoptionchild fgal_use_db_childcontainer y">
                <button role="button" type="submit" class="btn btn-primary" name="move" value="to_db">
                    {tr}Move files from directory to database{/tr}
                </button>
            </div>

            {preference name='fgal_podcast_dir'}

            <fieldset>
                <legend>{tr}Features{/tr}{help url="File+Gallery+Config"}</legend>

                {preference name='feature_file_galleries_rankings'}
                {preference name='feature_file_galleries_comments'}
                <div class="adminoptionboxchild" id="feature_file_galleries_comments_childcontainer">
                    <a class="link" href="tiki-admin.php?page=comments">{tr}Manage comment settings{/tr}</a>
                </div>
                {preference name='fgal_display_zip_option'}
                {preference name='fgal_limit_hits_per_file'}
                {preference name='fgal_prevent_negative_score'}

                {preference name='fgal_allow_duplicates'}
                {preference name='file_galleries_use_jquery_upload'}

                {preference name='feature_file_galleries_batch'}
                <div class="adminoptionboxchild" id="feature_file_galleries_batch_childcontainer">
                    {preference name='fgal_batch_dir'}
                </div>

                {preference name='feature_file_galleries_author'}
                {preference name='fgal_delete_after'}
                <div class="adminoptionboxchild" id="fgal_delete_after_childcontainer">
                    {preference name='fgal_delete_after_email'}
                </div>
                {preference name='fgal_keep_fileId'}
                {preference name='feature_use_fgal_for_user_files'}
                {preference name='feature_use_fgal_for_wiki_attachments'}
                {preference name='feature_file_galleries_save_draft'}
                {preference name='feature_file_galleries_templates'}
                {preference name='fgal_tracker_existing_search'}

                {preference name='fgal_fix_mime_type'}
                <div class="adminoptionboxchild" id="fgal_fix_mime_type_childcontainer">
                    <input
                        type="submit"
                        class="btn btn-primary btn-sm"
                        name="updateMime"
                        id="updateMime"
                        value="{tr}Update mime of all non archived octet-stream files{/tr}"
                    >
                </div>

                {preference name='fgal_clean_xml_always'}
                {preference name='fgal_allow_svg'}

                {preference name='fgal_upload_from_source'}
                <div class="adminoptionboxchild" id="fgal_upload_from_source_childcontainer">
                    {preference name='fgal_source_refresh_frequency'}
                    {preference name='fgal_source_show_refresh'}
                </div>
                {preference name='tiki_check_file_content'}
                {preference name='file_galleries_redirect_from_image_gallery'}
                {preference name='fgal_convert_documents_pdf'}
                {preference name='fgal_maximum_image_width_preview'}
            </fieldset>

            <fieldset>
                <legend>{tr}Quota{/tr}{help url="File+Gallery+Config#Quota"}</legend>
                {preference name='fgal_quota'}{tr}Used:{/tr} {$usedSize|kbsize}
                <div class="adminoptionboxchild" id="fgal_quota_childcontainer">
                    {if !empty($prefs.fgal_quota)}
                        {capture name='use'}{math equation="round((100*x)/(1024*1024*y))" x=$usedSize y=$prefs.fgal_quota}{/capture}
                        {quotabar length='100' value='$smarty.capture.use'}
                    {/if}
                </div>
                {preference name='fgal_quota_per_fgal'}
                <div class="adminoptionboxchild" id="fgal_quota_per_fgal_childcontainer">
                    {preference name='fgal_quota_default'}
                </div>
            </fieldset>

            <fieldset>
                <legend>{tr}Upload Regex{/tr}{help url="File+Gallery+Config#Filename_must_match:"}</legend>
                {preference name='fgal_match_regex'}
                {preference name='fgal_nmatch_regex'}
            </fieldset>

            <fieldset>
                <legend> {tr}Settings for Media Alchemyst{/tr}{help url="Media-Alchemyst"}</legend>
                {preference name=alchemy_ffmpeg_path}
                {preference name=alchemy_ffprobe_path}
                {preference name=alchemy_unoconv_path}
                {preference name=alchemy_unoconv_timeout}
                {preference name=alchemy_unoconv_port}
                {preference name=alchemy_gs_path}
                {preference name=alchemy_imagine_driver}
            </fieldset>

            <fieldset>
                <legend> {tr}Settings for Diagrams{/tr}</legend>
                {preference name='fgal_export_diagram_on_image_save'}
                {preference name='fgal_use_casperjs_to_export_images'}
                {preference name='fgal_use_drawio_services_to_export_images'}
                <div class="adminoptionboxchild" id="fgal_use_drawio_services_to_export_images_childcontainer">
                    {preference name='fgal_drawio_service_endpoint'}
                </div>
                {preference name='fgal_use_diagram_compression_by_default'}
            </fieldset>

            <fieldset>
                <legend> {tr}Settings for RecordRTC{/tr}</legend>
                {preference name='fgal_use_record_rtc_screen'}
            </fieldset>
        {/tab}

        {tab name="{tr}Plugins{/tr}"}
            <br>
            <fieldset class="mb-3 w-100">
                <legend>{tr}Plugins{/tr}</legend>
                {preference name=wikiplugin_files}
                {preference name=wikiplugin_file}
                {preference name=wikiplugin_img}
                {preference name=wikiplugin_colorbox}
                {preference name=wikiplugin_draw}
                {preference name=wikiplugin_annotation}
            </fieldset>
        {/tab}

        {tab name="{tr}Listings{/tr}"}
            <br>
            <span class="form-text">{tr}Configuration for gallery listings{/tr}</span>
            {remarksbox title="Note"}
                {tr _0='<em>' _1='</em>'}Changing these settings %0will not affect%1 existing file galleries. These changes %0will apply only%1 to new file galleries.{/tr}
            {/remarksbox}

            {preference name=fgal_default_view}
            {preference name=fgal_sortField}
            <div class="adminoptionboxchild" id="fgal_sortField_childcontainer">
                {preference name=fgal_sortDirection}
            </div>
            {preference name='fgal_quota_show'}
            {preference name='fgal_search'}
            {preference name='fgal_search_in_content'}
            {preference name='fgal_show_thumbactions'}
            {preference name='fgal_thumb_max_size'}
            {preference name='fgal_browse_name_max_length'}
            {preference name='fgal_image_max_size_x'}
            {preference name='fgal_image_max_size_y'}
            {preference name='fgal_list_ratio_hits'}
            {preference name='fgal_display_properties'}
            {preference name='fgal_display_replace'}
            {preference name='fgal_checked'}
            {preference name='fgal_icon_fileId'}
            {preference name='fgal_show_explorer'}
            {preference name='fgal_show_path'}
            {preference name='fgal_show_slideshow'}
            {preference name='fgal_list_ocr_state'}
            {preference name='fgal_list_id'}
            {preference name='fgal_list_type'}
            {preference name='fgal_list_name'}
            {preference name='fgal_list_size'}
            {preference name='fgal_list_created'}
            {preference name='fgal_list_lastModif'}
            {preference name='fgal_list_creator'}
            {preference name='fgal_list_author'}
            {preference name='fgal_list_last_user'}
            {preference name='fgal_list_comment'}
            {preference name='fgal_list_files'}
            {preference name='fgal_list_hits'}
            {preference name='fgal_list_lastDownload'}
            {preference name='fgal_list_lockedby'}
            {preference name='fgal_list_backlinks'}
            {preference name='fgal_list_deleteAfter'}
            {preference name='fgal_list_share'}
            {preference name='fgal_list_source'}
        {/tab}

        {if $section eq 'admin'}
            {tab name="{tr}Admin Listings{/tr}"}
                <br>
                <span class="form-text">{tr}Configuration for gallery administration listings{/tr}</span>
                <fieldset>
                    {preference name='fgal_list_id_admin'}
                    {preference name='fgal_list_type_admin'}
                    {preference name='fgal_list_description_admin'}
                    {preference name='fgal_list_name_admin'}
                    {preference name='fgal_list_size_admin'}
                    {preference name='fgal_list_created_admin'}
                    {preference name='fgal_list_lastModif_admin'}
                    {preference name='fgal_list_creator_admin'}
                    {preference name='fgal_list_author_admin'}
                    {preference name='fgal_list_last_user_admin'}
                    {preference name='fgal_list_comment_admin'}
                    {preference name='fgal_list_files_admin'}
                    {preference name='fgal_list_hits_admin'}
                    {preference name='fgal_list_lastDownload_admin'}
                    {preference name='fgal_list_lockedby_admin'}
                    {preference name='fgal_list_backlinks_admin'}
                    {preference name='fgal_list_deleteAfter_admin'}
                    {preference name='fgal_list_share_admin'}
                    {preference name='fgal_list_source_admin'}
                </fieldset>
            {/tab}
        {/if}


        {tab name="{tr}Search Indexing{/tr}"}
            <br>
            {preference name=fgal_enable_auto_indexing}
            {preference name=fgal_enable_email_indexing}
            {preference name=fgal_asynchronous_indexing}
            {preference name=ocr_enable}
                <div class="adminoptionboxchild" id="ocr_enable_childcontainer">
                    {preference name=ocr_every_file}
                    {preference name=ocr_file_level}
                    {preference name=ocr_limit_languages}
                    {preference name=ocr_tesseract_path}
                    {preference name=ocr_pdfimages_path}
                </div>
            <div class="adminoptionbox">
                <fieldset>
                    <legend>{tr}Handlers{/tr}{help url="Search-within-files" desc='{tr}If you want the content of the files which are in the File Gallery to be accessible by a search, and if you have a script that extracts the file content into a text, you can associate the script to the Mime type and the files content will be indexed.{/tr}'}</legend>
                    <div class="adminoptionbox">
                        <div class="adminoptionlabel">{tr}Add custom handlers to make your files &quot;searchable&quot; content{/tr}.
                            <ul>
                                <li>
                                    {tr}Use <strong>%1</strong> as the internal file name. For example, use <strong>strings %1</strong> to convert the document to text, using the Unix <strong>strings</strong> command{/tr}.
                                </li>
                                <li>
                                    {tr _0="<strong>" _1="</strong>"}To delete a handler, leave the %0System Command%1 field blank{/tr}.
                                </li>
                            </ul>
                        </div>
                    </div>

                    {if !empty($missingHandlers)}
                        {tr}Tiki is pre-configured to handle many common types. If any of those are listed here, it is because the command line tool is unavailable.{/tr}
                        {remarksbox type=warning title="{tr}Missing Handlers{/tr}"}
                            {foreach from=$missingHandlers item=mime}
                                {$mime|escape}
                                <br>
                            {/foreach}
                        {/remarksbox}
                        {if $vnd_ms_files_exist}
                            <div class="adminoptionbox">
                                {remarksbox type=info title="{tr}Mime Types{/tr}"}
                                    <p>
                                        {tr}Previous versions of Tiki may have assigned alternative mime-types to Microsoft Office files, such as "application/vnd.ms-word" and these need to be changed to be "application/msword" for the default file indexing to function properly.{/tr}
                                    </p>
                                    <input
                                        type="submit"
                                        class="btn btn-primary btn-sm"
                                        name="filegalfixvndmsfiles"
                                        value="{tr}Fix vnd.ms-* mime type files{/tr}"
                                    />
                                {/remarksbox}
                            </div>
                        {/if}
                    {/if}

                    <div class="adminoptionbox">
                        <div class="adminoptionlabel">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{tr}MIME Type{/tr}</th>
                                            <th>{tr}System Command{/tr}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    {foreach key=mime item=cmd from=$fgal_handlers}
                                        <tr>
                                            <td>{$mime}</td>
                                            <td>
                                                <input name="mimes[{$mime}]" class="form-control" type="text" value="{$cmd|escape:html}" />
                                            </td>
                                        </tr>
                                    {/foreach}
                                    <tr>
                                        <td class="odd">
                                            <input name="newMime" type="text" class="form-control" />
                                        </td>
                                        <td class="odd">
                                            <input name="newCmd" type="text" class="form-control" />
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="adminoptionbox">
                    <div class="adminoptionlabel">
                        <div align="center">
                            <input
                                type="submit"
                                class="btn btn-primary btn-sm"
                                name="filegalredosearch"
                                value="{tr}Reindex all files for search{/tr}"
                            >
                            {if $prefs.ocr_enable === 'y'}
                                <input
                                        type="submit"
                                        {if !$ocrStalled}disabled{/if}
                                        class="btn btn-primary btn-sm"
                                        name="ocrstalledreset"
                                        value="{tr}Reset {if $ocrStalled}{$ocrStalled} {/if}OCR 'stalled' files{/tr}"
                                >
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
        {/tab}

        {tab name="{tr}Enhancements{/tr}"}
            <br>

            <fieldset>
                <legend>{tr}Access{/tr}</legend>
                {preference name='feature_webdav'}
                {preference name='fgal_elfinder_feature'}
                <div class="adminoptionboxchild" id="fgal_elfinder_feature_childcontainer">
                    {preference name='fgal_elfinder_on_toolbar'}
                    {preference name='fgal_elfinder_debug'}
                </div>
                {preference name='feature_docs'}
                {preference name='fgal_pdfjs_feature'}
            </fieldset>
            <fieldset>
                <legend>{tr}H5P{/tr}</legend>
                {preference name='h5p_enabled'}
                <div class="adminoptionboxchild" id="h5p_enabled_childcontainer">
                    {preference name='h5p_filegal_id'}
                    {preference name='h5p_whitelist'}
                    {preference name='h5p_dev_mode'}
                    {preference name='h5p_track_user'}
                    {preference name='h5p_save_content_state'}
                    <div class="adminoptionboxchild" id="h5p_save_content_state_childcontainer">
                        {preference name='h5p_save_content_frequency'}
                    </div>
                    {preference name='h5p_export'}
                    {preference name='h5p_hub_is_enabled'}
                    {preference name='h5p_site_key'}
                    {preference name='h5p_h5p_site_uuid'}
                    {preference name='h5p_content_type_cache_updated_at'}
                    {preference name='h5p_check_h5p_requirements'}
                    {preference name='h5p_send_usage_statistics'}
                    {preference name='h5p_has_request_user_consent'}
                    {preference name='h5p_enable_lrs_content_types'}
                    {preference name='h5p_custom_js_files'}
                    {preference name='h5p_custom_css_files'}

                    {remarksbox type="info" title="{tr}H5P Info{/tr}"}
                        {tr}Service URL to purge unused libraries can be found here. Can be used in a cron task{/tr}<br>
                        <a href="{service controller='h5p' action='cron' token=$prefs.h5p_cron_token}" class="btn btn-link">
                            {service controller='h5p' action='cron' token=$prefs.h5p_cron_token}
                        </a>
                    {/remarksbox}
                </div>
            </fieldset>

            <fieldset>
                <legend>{tr}Draw{/tr}</legend>
                {preference name=feature_draw}
                <div class="adminoptionboxchild" id="feature_draw_childcontainer">
                    {preference name=feature_draw_hide_buttons}
                    {preference name=feature_draw_separate_base_image}
                    <div class="adminoptionboxchild" id="feature_draw_separate_base_image_childcontainer">
                        {preference name=feature_draw_in_userfiles}
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>{tr}SCORM{/tr}{help desc="SCORM is a package format for Learning Management Systems. Tiki can create tracker items based on the SCORM metadata. This allows you to manage SCORM packages within Tiki."}</legend>
                {preference name=scorm_enabled}
                <div class="adminoptionboxchild" id="scorm_enabled_childcontainer">
                    {preference name=scorm_tracker}
                </div>
            </fieldset>

            <fieldset>
                <legend>{tr}Vimeo{/tr}</legend>
                {preference name=vimeo_upload}
                {preference name=vimeo_delete}
                <div class="adminoptionboxchild" id="vimeo_upload_childcontainer">
                    {preference name=vimeo_default_gallery}
                    {preference name=vimeo_consumer_key}
                    {preference name=vimeo_consumer_secret}
                    {preference name=vimeo_access_token}
                    {preference name=vimeo_access_token_secret}
                </div>
            </fieldset>
        {/tab}
    {/tabset}
    {include file='admin/include_apply_bottom.tpl'}
</form>