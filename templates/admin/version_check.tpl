
{remarksbox type="info" title="{tr}Tiki version{/tr}" close="n"}
    {capture assign="lastup"}{svn_lastup}{/capture}
    {capture assign="svnrev"}{svn_rev}{/capture}
    {if !empty($lastup)}
        {tr}Last update from SVN{/tr} ({$tiki_version}): {$lastup|tiki_long_datetime}
    {else}
        {$tiki_version}
    {/if}
    {if $svnrev}
        - REV {$svnrev}
    {/if}
    ({$db_engine_type})
{/remarksbox}
<div class="adminoptionbox">
    {preference name=tiki_release_cycle}
    {preference name=feature_version_checks}
    <div id="feature_version_checks_childcontainer">
        {preference name=tiki_version_check_frequency}
    </div>
</div>