
{extends 'layout_view.tpl'}

{block name="title"}
    {title}{$title|escape}{/title}
{/block}

{block name="content"}
    <form method="post" action="{service controller=comment action=edit threadId=$comment.threadId}">
        <div class="card">
            <div class="card-header">
                {tr}Edit Comment{/tr}
            </div>
            <fieldset>
                <input type="hidden" name="edit" value="1"/>
                <div class="card-body">
                {if $prefs.comments_notitle neq 'y'}
                    <div class="mb-3 row">
                        <label for="comment-title" class="clearfix comment-title">{tr}Title{/tr}</label>
                        <input type="text" id="comment-title" name="title" value="{$comment.title|escape}" class="form-control" placeholder="Comment title"/>
                    </div>
                {/if}
                {capture name=rows}{if $type eq 'forum'}{$prefs.default_rows_textarea_forum}{else}{$prefs.default_rows_textarea_comment}{/if}{/capture}
                {textarea codemirror='true' name=data comments="y" _wysiwyg="n" rows=$smarty.capture.rows _preview=$prefs.ajax_edit_previews}{$comment.data}{/textarea}
                </div>
                <div class="card-footer">
                    {if empty($comment.version)}
                        <div class="mb-3 comment-post">
                            <input type="submit" class="clearfix comment-editclass btn btn-primary" value="{tr}Save{/tr}"/>
                            <div class="btn btn-link">
                                <a href="#" onclick="$(this).closest('.comment-container').reload(); $(this).closest('.ui-dialog').remove(); return false;">{tr}Cancel{/tr}</a>
                            </div>
                        </div>
                    {else}
                        {if $diffInfo}
                            <div class="card bg-light">
                                <div class="card-body">
                                    {foreach $diffInfo as $info}
                                        <label>{$info.fieldName}</label> {*{$info.value} => {$info.new}<br>*}
                                        {trackeroutput fieldId=$info.fieldId list_mode='y' history=y process=y oldValue=$info.value value=$info.new diff_style='sidediff'}
                                    {/foreach}
                                </div>
                            </div>
                        {/if}
                        <div class="submit">
                            <input type="hidden" name="version" value="{$comment.version|escape}"/>
                            <input type="submit" class="comment-post btn btn-primary" value="{tr}Post{/tr}"/>
                            <div class="btn btn-link">
                                <a href="#" onclick="$(this).closest('.comment-container').reload(); $(this).closest('.ui-dialog').remove(); return false;">{tr}Cancel{/tr}</a>
                            </div>
                        </div>
                    {/if}
                </div>
            </fieldset>
        </div>
    </form>
{/block}