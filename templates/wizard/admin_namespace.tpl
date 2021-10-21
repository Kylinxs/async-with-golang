{* $Id$ *}
<div class="d-flex">
    <div class="me-4">
        <span class="fa-stack fa-lg" style="width: 100px;" title="Configuration Wizard">
        <i class="fas fa-cog fa-stack-2x"></i>
        <i class="fas fa-flip-horizontal fa-magic fa-stack-1x ms-4 mt-4"></i>
    </span>
    </div>
    <div class="flex-grow-1 ms-3">
        {icon name="wrench" size=3 iclass="float-sm-end"}
        <div class="row">
            <div class="col-lg-9">
                {tr}The namespace separator should not{/tr}
                <ul>
                    <li>{tr}contain any of the characters not allowed in wiki page names, typically{/tr} /?#[]@$&amp;+;=&lt;&gt;</li>
                    <li>{tr}conflict with wiki syntax tagging{/tr}</li>
                </ul>
            </div>
        </div>
        <fieldset>
            <legend>{tr}Namespace settings{/tr}{help url="Namespaces"}</legend>
            {preference name=namespace_separator}
            {if isset($isStructures) and $isStructures eq true}
                {preference name=namespace_indicator_in_structure}
            {/if}
            <br/>
            <b>{tr}Settings that may be affected by the namespace separator:{/tr}</b><br/>
            {icon name="file-text-o" size=2 iclass="float-sm-end"}

            {tr}To use :: as a separator, you should also use ::: as the wiki center tag syntax{/tr}.<br/>
            {tr}Note: a conversion of :: to ::: for existing pages must be done manually{/tr}
            {preference name=feature_use_three_colon_centertag}

            {preference name=wiki_pagename_strip}
            <br>
            <em>{tr}See also{/tr} <a href="http://doc.tiki.org/Namespaces" target="_blank">{tr}Namespaces{/tr} @ doc.tiki.org</a></em>
        </fieldset>
    </div>
</div>
