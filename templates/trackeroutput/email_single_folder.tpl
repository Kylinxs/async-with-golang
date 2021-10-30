
<table class="table table-striped table-hover">
  <thead>
  <tr>
    <th>{tr}Sender{/tr}</th>
    <th>{tr}Recipient{/tr}</th>
    <th>{tr}Subject{/tr}</th>
    <th>{tr}Date{/tr}</th>
    <th>{tr}Flags{/tr}</th>
  </tr>
  </thead>
  <tbody>
  {foreach from=$emails item=email}
    <tr {if !$email.flags['seen']} style="font-weight: bold"{/if}>
      <td>
        {if !empty($email.sender)}
          {$email.sender|escape}
        {else}
          {$email.from|escape}
        {/if}
      </td>
      <td>{$email.recipient|escape}</td>
      <td><a href="{$email.view_path}">{if !empty($email.subject)}{$email.subject|escape}{else}{tr}(None){/tr}{/if}</a></td>
      <td>{$email.date|tiki_short_datetime}</td>
      <td>
        {foreach from=$email.flags key=flag item=flagName}
          {if $flag neq 'seen'}
            <span title="{$flagName}">{$flagName|substr:0:1}</span>
          {/if}
        {/foreach}
      </td>
    </tr>
  {/foreach}
  </tbody>
</table>