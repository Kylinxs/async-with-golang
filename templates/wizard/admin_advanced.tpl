{* $Id$ *}
<div class="d-flex">
    <div class="me-4">
        <span class="fa-stack fa-lg" style="width: 100px;" title="Configuration Wizard">
            <i class="fas fa-cog fa-stack-2x"></i>
            <i class="fas fa-flip-horizontal fa-magic fa-stack-1x ms-4 mt-4"></i>
        </span>
    </div>
    <div class="flex-grow-1 ms-3">
        {icon name="admin_workspace" size=3 iclass="float-sm-end"}
        {icon name="wrench" size=3 iclass="float-sm-end"}
        <h4 class="mt-0 mb-4">{tr}If you are an experienced Tiki site administrator, consider whether the advanced features below would be useful for your use case. They are useful for creating a similar set of Tiki objects for different groups of users with like permissions{/tr}.</h4>
        <fieldset>
            <legend>{tr}Workspaces{/tr}</legend>
            {preference name=workspace_ui}
            <em>{tr}See also{/tr} <a href="https://doc.tiki.org/Workspaces UI" target="_blank">{tr}Workspaces UI in doc.tiki.org{/tr}</a></em>
        </fieldset>
        <fieldset>
            <legend>{tr}Dependencies{/tr}</legend>
            <div class="admin clearfix featurelist">
                {preference name=feature_categories}
                {preference name=feature_perspective}
                {preference name=namespace_enabled}
                <div class="adminoptionboxchild">
                    {tr}Enable using the same wiki page name in different contexts{/tr}. {tr}E.g. ns1:_:MyPage and ns2:_:MyPage{/tr}.
                </div>
            </div>
            <br>
            <em>{tr}See also{/tr} <a href="tiki-admin.php?page=workspace" target="_blank">{tr}Workspaces & Areas admin panel{/tr}</a></em>
        </fieldset>
    </div>
</div>
