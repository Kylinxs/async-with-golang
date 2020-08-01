
<div class="t_navbar mb-4">
    <div class="btn-group">
        <a class="btn btn-primary" href="{bootstrap_modal controller=tabular action=filter tabularId=$tabularId target=list _params=$baseArguments}">
            {icon name=filter} {tr}Filter{/tr}
        </a>
        {permission name=tabular_export type=tabular object=$tabularId}
            <div class="btn-group">
                <a type="button" class="btn btn-primary" href="{service controller=tabular action=export_full_csv tabularId=$tabularId}">
                    {icon name=export} {tr}Export{/tr}
                </a>
                <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu ps-3">
                    <a class="dropdown-item" href="{bootstrap_modal controller=tabular action=filter tabularId=$tabularId target=export _params=$baseArguments}">
                        {icon name=export} {tr}Export Partial{/tr}
                    </a>
                    <a class="dropdown-item" href="tiki-searchindex.php?tabularId=1&amp;filter~tracker_id={$tabularId}">
                        {icon name=export} {tr}Export Custom{/tr}
                    </a>
                </div>
            </div>
        {/permission}
        {permission name=tabular_import type=tabular object=$tabularId}
            <a class="btn btn-primary" href="{bootstrap_modal controller=tabular action=import_csv tabularId=$tabularId target=list _params=$baseArguments}">
                {icon name=import} {tr}Import{/tr}
            </a>
        {/permission}
        {permission name=admin_trackers}
            <a class="btn btn-primary" href="{bootstrap_modal controller='tabular' action='duplicate' tabularId=$tabularId}">
                {icon name='copy'} {tr}Duplicate{/tr}
            </a>
        {/permission}
    </div>
    {if $mode neq 'edit'}
        {permission name=tabular_edit type=tabular object=$tabularId}
            <a class="btn btn-link" href="{service controller=tabular action=edit tabularId=$tabularId}">{icon name=edit} {tr}Edit{/tr}</a>
        {/permission}
    {else}
        {permission name=tabular_list type=tabular object=$tabularId}
            <a class="btn btn-link" href="{service controller=tabular action=list tabularId=$tabularId}">{icon name=list} {tr}List{/tr}</a>
        {/permission}
    {/if}
    {permission name=admin_trackers}
        <a class="btn btn-link" href="{service controller=tabular action=create}">{icon name=create} {tr}New{/tr}</a>
        <a class="btn btn-link" href="{service controller=tabular action=manage}">{icon name=home} {tr}Manage{/tr}</a>
    {/permission}
</div>