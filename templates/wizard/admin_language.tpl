{* $Id$ *}

<div class="d-flex">
    <div class="me-4">
        <span class="fa-stack fa-lg" style="width: 100px;" title="Configuration Wizard">
            <i class="fas fa-cog fa-stack-2x"></i>
            <i class="fas fa-flip-horizontal fa-magic fa-stack-1x ms-4 mt-4"></i>
        </span>
    </div>
    <div class="flex-grow-1 ms-3">
        {icon name="admin_i18n" size=3 iclass="adminWizardIconright"}
        <h4 class="mt-0 mb-4">{tr}Select the site language{/tr}</h4>
        <fieldset>
            <legend>{tr}Language options{/tr}</legend>

            {preference name=language}
            <br>
            {preference name=feature_multilingual visible="always"}
            {preference name=lang_use_db}
        </fieldset>
    </div>
</div>
