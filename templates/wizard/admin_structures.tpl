{* $Id$ *}

<div class="d-flex">
    <div class="flex-shrink-0">
        <span class="fa-stack fa-lg" style="width: 100px;" title="Configuration Wizard">
            <i class="fas fa-cog fa-stack-2x"></i>
            <i class="fas fa-flip-horizontal fa-magic fa-stack-1x ms-4 mt-4"></i>
        </span>
    </div>
    <div class="flex-grow-1 ms-3">
        {icon name="structure" size=3 iclass="float-sm-end"}
        <h4 class="">{tr}Hierarchical organization of wiki pages{/tr}</h4>
        {tr}Structures organize a group of wiki pages into a tree or book that can be easily navigated by users. Creating a structure is the fastest method for creating multiple pages at once. It's a great way to make a book, as well as to allow users to create new pages that will be automatically found in a common table of contents. New pages can inherit permissions from the structure homepage{/tr}.
        <br><br>
        <fieldset>
            <legend>{tr}Structures options{/tr}</legend>
            <div class="admin clearfix featurelist">
                {preference name=feature_wiki_open_as_structure}
                {preference name=feature_wiki_make_structure}
                {if $isCategories eq true}
                    {preference name=feature_wiki_categorize_structure}
                {/if}
                {preference name=feature_wiki_multiprint}
                {preference name=feature_listorphanStructure}
                {preference name=feature_wiki_no_inherit_perms_structure}
                {preference name=feature_wiki_structure_drilldownmenu}
                {preference name=wiki_structure_bar_position}
            </div>
            <br>
            <em>{tr}See also{/tr} <a href="https://doc.tiki.org/Structures" target="_blank">{tr}Structures{/tr} @ doc.tiki.org</a></em>
        </fieldset>
    </div>
</div>
