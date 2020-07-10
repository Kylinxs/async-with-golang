
{* $Id$ *}{tr}Hi,{/tr}

{if $mail_again}
{tr _0=$mail_user _1=$mail_email _2=$mail_site _3=$prefs.mail_template_custom_text}%0 <%1> has requested a new password on %2, but you need to validate his %3 account first{/tr}
{else}
{$mail_user} <{$mail_email}> {tr}has requested an account on{/tr} {$mail_site}
{if isset($chosenGroup)}
{tr}Group:{/tr} {$chosenGroup}{/if}
{/if}
{if isset($item)}

{tr}User Tracker{/tr}
{foreach item=field_value from=$item.field_values}
    {$field_value.name}: {trackeroutput field=$field_value item=$item list_mode='csv' showlinks='n'}
{/foreach}
{/if}

{tr}To validate that {$prefs.mail_template_custom_text}account, please follow the link:{/tr}

{$validation_url}

{tr}Assign to a group:{/tr} {$assignuser_url}
{tr}View user's data:{/tr} {$userpref_url}


{tr}Best regards{/tr}
