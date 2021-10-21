<ul class="list-unstyled">
    {foreach from=$comments item=comment}
        <li class="d-flex comment mt-3 mb-4{if $comment.archived eq 'y'} archived{* well well-sm*}{/if} {if $allow_moderate}{if $comment.approved eq 'n'} pending bg-warning{elseif $comment.approved eq 'r'} rejected bg-danger{/if}{/if}{*{if ! $parentId && $prefs.feature_wiki_paragraph_formatting eq 'y'} inline{/if}*}" data-comment-thread-id="{$comment.threadId|escape}">
            <div class="align-self-start me-3">
                <span class="avatar">{$comment.userName|avatarize:'':'img/noavatar.png'}</span>
            </div>
            <div class="flex-grow-1 ms-3">
                <div class="comment-item">
                    <h4 class="mt-0">
                        {if $prefs.comments_notitle neq 'y'}
                            <div class="comment-title">
                                {$comment.title}
                                {if $prefs.comments_heading_links eq 'y'}
                                    <a class="heading-link" href="{if ($comment.threadId neq $comments_parentId)}#threadId{$comment.threadId}{/if}">{icon name="link"}</a>
                                {/if}
                            </div>
                        {/if}
                        <div class="comment-info">
                            {tr _0=$comment.userName|userlink}%0{/tr}{if $prefs.comments_threshold_indent neq '0' && $level && $level gte $prefs.comments_threshold_indent}>{tr _0=$repliedTo.userName|userlink}%0{/tr}{/if} <small class="date">{tr _0=$comment.commentDate|tiki_short_datetime}%0{/tr}</small>
                            {if $prefs.comments_heading_links eq 'y' and  $prefs.comments_notitle eq 'y'}
                                <a class="heading-link" href="{if ($comment.threadId neq $comments_parentId)}#threadId{$comment.threadId}{/if}">{icon name="link"}</a>
                            {/if}
                        </div>
                    </h4>
                    <div class="comment-body">
                        <span class="d-flex ps-2 border-start border-5 comment-replied-to">{if $prefs.comments_threshold_indent neq '0' && $level && $level gte $prefs.comments_threshold_indent}{tr _0=$repliedTo.data|truncate:15}Replied to %0{/tr}{/if} </span>
                        {$comment.parsed}
                    </div>
                    <div class="buttons comment-form comment-footer mt-2">
                        {block name="buttons"}
                            {if $allow_post && $comment.locked neq 'y'}
                                <a class='btn btn-primary btn-sm' href="{service controller=comment action=post type=$type objectId=$objectId parentId=$comment.threadId}">{tr}Reply{/tr}</a>
                            {/if}
                            {if !empty($comment.can_edit)}
                                <a class='btn btn-secondary btn-sm' href="{service controller=comment action=edit threadId=$comment.threadId}">{tr}Edit{/tr}</a>
                            {/if}
                            {if $allow_remove}
                                <a class="btn btn-danger btn-sm" href="{service controller=comment action=remove threadId=$comment.threadId}">{tr}Delete{/tr}</a>
                            {/if}
                            {if $allow_archive}
                                {if $comment.archived eq 'y'}
                                    <span class="label label-primary">{tr}Archived{/tr}</span>
                                    <a class="btn btn-info btn-sm" href="{service controller=comment action=archive do=unarchive threadId=$comment.threadId}">{tr}Unarchive{/tr}</a>
                                {else}
                                    <a class="btn btn-success btn-sm" href="{service controller=comment action=archive do=archive threadId=$comment.threadId}">{tr}Archive{/tr}</a>
                                {/if}
                            {/if}
                        {/block}
                        {if $allow_moderate and $comment.approved neq 'y'}
                            {if $comment.approved eq 'n'}
                                <span class="label label-warning">{tr}Pending{/tr}</span>
                            {/if}
                            {if $comment.approved eq 'r'}
                                <span class="label label-danger">{tr}Rejected{/tr}</span>
                            {/if}
                            <a href="{service controller=comment action=moderate do=approve threadId=$comment.threadId}" class="btn btn-primary btn-sm tips" title="{tr}Approve{/tr}">{icon name="ok"}</a>
                            {if $comment.approved eq 'n'}
                                <a href="{service controller=comment action=moderate do=reject threadId=$comment.threadId}" class="btn btn-danger btn-sm tips" title="{tr}Reject{/tr}">{icon name="remove"}</a>
                            {/if}
                        {/if}
                        {if $comment.userName ne $user and $comment.approved eq 'y' and $allow_vote}
                            <form class="commentRatingForm" method="post">
                                {rating type="comment" id=$comment.threadId}
                                <input type="hidden" name="id" value="{$comment.threadId}" />
                                <input type="hidden" name="type" value="comment" />
                            </form>
                            {jq}
                                var crf = $('form.commentRatingForm').submit(function() {
                                    var vals = $(this).serialize();
                                    $.tikiModal(tr('Loading...'));
                                    $.post($.service('rating', 'vote'), vals, function() {
                                        $.tikiModal();
                                        $.notify(tr('Thanks for rating!'));
                                    });
                                    return false;
                                });
                            {/jq}
                        {/if}
                        {if $prefs.wiki_comments_simple_ratings eq 'y' && ($tiki_p_ratings_view_results eq 'y' or $tiki_p_admin eq 'y')}
                            {rating_result type="comment" id=$comment.threadId}
                        {/if}
                        {if !empty($comment.diffInfo)}
                            <div class="{*well*}"><pre style="display: none;">{$comment.diffInfo|var_dump}</pre>
                                <h4 class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target=".version{$comment.diffInfo[0].version}" aria-expanded="false" aria-controls="collapseExample">
                                    Version {$comment.diffInfo[0].version}
                                    {icon name='history'}
                                </h4>
                                <div class="collapse table-responsive version{$comment.diffInfo[0].version}">
                                    <table class="table">
                                        {foreach $comment.diffInfo as $info}
                                            <label>{$info.fieldName}</label>
                                            {trackeroutput fieldId=$info.fieldId list_mode='y' history=y process=y oldValue=$info.value value=$info.new diff_style='sidediff'}
                                        {/foreach}
                                    </table>
                                </div>
                            </div>
                        {/if}

                    </div>{* End of comment-footer *}
                </div>{* End of comment-item *}
                {if ! $level || $level lt 5}
                    {if $comment.replies_info.numReplies gt 0}
                        {include file='comment/list_inner.tpl' comments=$comment.replies_info.replies cant=$comment.replies_info.numReplies parentId=$comment.threadId level=(level) ? $level+1 : 0 repliedTo=$comment}
                    {/if}
                {/if}
            </div>{* End of flex-grow-1 ms-3 *}
        </li>
        {if $prefs.comments_threshold_indent neq '0' && $level && $level gte $prefs.comments_threshold_indent}
            {if $comment.replies_info.numReplies gt 0}
                {include file='comment/list_inner.tpl' comments=$comment.replies_info.replies cant=$comment.replies_info.numReplies parentId=$comment.threadId level=(level) ? $level+1 : 0 repliedTo=$comment}
            {/if}
        {/if}
    {/foreach}
</ul>
{pagination_links cant=$cant step=$maxRecords offset=$offset offset_jsvar='comment_offset' _onclick=$paginationOnClick}{/pagination_links}
