
{extends 'layout_view.tpl'}

{block name="title"}
    <h3>{tr}Comments{/tr}
        <span class="lock">
            {if ! $parentId && $allow_lock}
                <a href="{bootstrap_modal controller=comment action=lock type=$type objectId=$objectId}" class="btn btn-link btn-sm tips" title="{tr}Comments unlocked:{/tr}{tr}Lock comments{/tr}">
                    {icon name="unlock"}
                </a>
            {/if}
            {if ! $parentId && $allow_unlock}
                <a href="{bootstrap_modal controller=comment action=unlock type=$type objectId=$objectId}" class="btn btn-link btn-sm tips" title="{tr}Comments locked:{/tr}{tr}Unlock comments{/tr}">
                    {icon name="lock"}
                </a>
            {/if}
        </span>
    </h3>
{/block}

{block name="content"}
    {if $allow_post and $prefs.comments_sort_mode eq 'commentDate_desc'}
        <div class="submit">
            <div class="buttons comment-form {if $prefs.wiki_comments_form_displayed_default eq 'y'}autoshow{/if}">
                <a class="btn btn-secondary custom-handling" href="{service controller=comment action=post type=$type objectId=$objectId}" data-bs-target="#add-comment-zone-{$objectId|replace:' ':''|replace:',':''|escape:'attr'}">{tr}Post new comment{/tr}</a>
            </div>
        </div>
        <div id="add-comment-zone-{$objectId|replace:' ':''|replace:',':''|escape:'attr'}" class="add-comment-zone"></div>
    {/if}

    {if $cant gt 0}
        {include file="comment/list_inner.tpl"}
        <script type="text/javascript">
            $(function() {
                $('#comment-container').applyColorbox();
            })
        </script>
    {else}
        {remarksbox type=info}
            {tr}There are no comments at this time.{/tr}
        {/remarksbox}
    {/if}

    {if $allow_post and $prefs.comments_sort_mode neq 'commentDate_desc'}
        <div class="submit">
            <div class="buttons comment-form {if $prefs.wiki_comments_form_displayed_default eq 'y'}autoshow{/if}">
                <a class="btn btn-secondary custom-handling" href="{service controller=comment action=post type=$type objectId=$objectId}" data-bs-target="#add-comment-zone-{$objectId|replace:' ':''|replace:',':''|escape:'attr'}">{tr}Post new comment{/tr}</a>
            </div>
        </div>
        <div id="add-comment-zone-{$objectId|replace:' ':''|replace:',':''|escape:'attr'}" class="add-comment-zone"></div>
    {/if}

    {if $prefs.feature_inline_comments eq 'y'}
        <a id="note-editor-comment" class="alert alert-info" style="display:none;" href="#">{tr}Add Comment{/tr}</a>
    {/if}

    <script type="text/javascript">
        var ajax_url = '{$base_url}';
        var objectId = '{$objectId|escape:'javascript'}';
    </script>
{/block}