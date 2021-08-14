
{* $Id$ *}

<div class="t_navbar mb-4 btn-group">
    {button class="btn btn-primary" href="messu-mailbox.php" _class="btn btn-primary" _text="{tr}Mailbox{/tr}"}
    {button class="btn btn-primary" href="messu-compose.php" _class="btn btn-primary" _text="{tr}Compose{/tr}"}

    {if $tiki_p_broadcast eq 'y'}
        {button class="btn btn-primary" href="messu-broadcast.php" _class="btn btn-primary" _text="{tr}Broadcast{/tr}"}
    {/if}

    {button class="btn btn-primary" href="messu-sent.php" _class="btn btn-info" _text="{tr}Sent{/tr}"}
    {button class="btn btn-primary" href="messu-archive.php" _class="btn btn-info" _text="{tr}Archive{/tr}"}

    {if isset($mess_archiveAfter) && $mess_archiveAfter>0}
        ({tr}Auto-archive age for read messages:{/tr} {$mess_archiveAfter} {tr}days{/tr})
    {/if}
</div>