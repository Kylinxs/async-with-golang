{* $Id$ *}{tr}Hi{/tr} {$user_name},

{if $lock_status eq 'lock'}
{tr _0=$mail_site}Your account has been locked on %0. Please contact the site Administrator.{/tr}
{else}
{tr _0=$mail_site}Your account has been unlocked on %0.{/tr}
{/if}