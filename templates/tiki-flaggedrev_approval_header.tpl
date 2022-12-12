{* $Id$ *}
{if $prefs.flaggedrev_approval eq 'y' and $revision_approval}
    {if ($revision_approved or $revision_displayed) and $revision_approved neq $lastVersion and ($tiki_p_wiki_view_latest eq 'y' or $tiki_p_edit eq 'y')}
        {if $lastVersion eq $revision_displayed}
            {remarksbox type=warning title="{tr}Content waiting for approval{/tr}"}
                <p>
                    {tr}You are currently viewing the latest version of the page.{/tr}
                    {if $revision_approved}
                        {tr}You can also view the {self_link}latest approved version{/self_link}.{/tr}
                    {/if}
                    {if $tiki_p_wiki_approve eq 'y'}
                        {tr}You can approve this revision and make it available to a wider audience. Make sure you review all the changes before approving.{/tr}
                    {/if}
                </p>
                {if $tiki_p_wiki_approve eq 'y'}
                    <form method="post" action="{$page|sefurl}">
                        {if $revision_approved}
                            <p><a href="tiki-pagehistory.php?page={$page|escape:'url'}&compare&oldver={$revision_approved|escape:'url'}&diff_style={$prefs.default_wiki_diff_style|escape:'url'}" class="alert-link">{tr}Show changes since last approved revision{/tr}</a></p>
                        {else}
                            <p>{tr}This page has no prior approved revision. <strong>All of the content must be reviewed.</strong>{/tr}</p>
                        {/if}
                        <div class="submit row">
                          <input type="hidden" name="revision" value="{$revision_displayed|escape}">
                          <div class="col-md-6">
                            <input type="submit" class="btn btn-primary btn-sm" name="approve" value="{tr}Approve current revision{/tr}">
                          </div>
                            {* TODO work on layout here *}
                            <div class="col-md-6">
                              <input type="text" name="reason" placeholder="Why is this not approved?">
                              <input type="submit" class="btn btn-primary btn-sm" name="reject" value="{tr}Reject current revision{/tr}">
                              <br/>
                              <input type="checkbox" class="form-check-input" name="delete_revision" value="on"> <label for="delete_version">Permanently delete this revision</label>
                            </div>
                        </div>
                    </form>
                {/if}
            {/remarksbox}
        {else}
            {remarksbox type=comment title="{tr}Content waiting for approval{/tr}"}{* One ministry using flagged revisions has upgraded this message from a comment to a warning, to attract more attention to it. There is no intermediary level between comment and warning. Chealer 2017-06-01 *}
                <p>
                    {tr}You are currently viewing the approved version of the page.{/tr}
                    {if $revision_approved and $tiki_p_wiki_view_latest eq 'y'}
                        {tr}You can also view the {self_link latest=1}latest version{/self_link}.{/tr}
                    {/if}
                </p>
            {/remarksbox}
        {/if}
    {elseif ! $revision_approved and $tiki_p_wiki_view_latest eq 'y'}{* How does this case differ from the last? If this one happens when viewing an unapproved version other than the latest, shouldn't this be stated explicitly? Chealer 2017-06-01 *}
        {remarksbox type=comment title="{tr}Content waiting for approval{/tr}"}
            <p>
                {tr}View the {self_link latest=1}latest version{/self_link}.{/tr}
            </p>
        {/remarksbox}
    {/if}
{/if}
