
{* $Id$ *}
{if $mail_action eq 'new'}{tr _0=$prefs.mail_template_custom_text _1=$mail_user|username}%0wiki page "%s" created by %1{/tr}
{elseif $mail_action eq 'delete'}{tr _0=$prefs.mail_template_custom_text _1=$mail_user|username}%0wiki page "%s" deleted by %1{/tr}
{elseif $mail_action eq 'attach'}{tr _0=$prefs.mail_template_custom_text}A file was attached to %0"%s"{/tr}
{else}{tr _0=$prefs.mail_template_custom_text _1=$mail_user|username}%0wiki page "%s" changed by %1{/tr}{/if}