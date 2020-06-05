{tr}Notice{/tr}

{tr _0=$schedulerName _1=$healingTimeout}Scheduler "%0" has been automatically marked as healed after being stalled for over %1 minutes.{/tr}


{tr}Details{/tr}
{tr}Site Name:{/tr} {$siteName}
{if !empty($siteUrl)}
    {tr}Site URL:{/tr} {$siteUrl}
{/if}
{tr}Server:{/tr} {$server}
{tr}Webroot:{/tr} {$webroot}
