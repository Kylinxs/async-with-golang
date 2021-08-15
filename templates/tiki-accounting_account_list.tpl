
{* $Id$ *}
<table class="table">
    <tr>
        <th>{tr}Account{/tr}</th>
        <th>{tr}Account name{/tr}</th>
        <th>{tr}Notes{/tr}</a></th>
        <th>{tr}Budget{/tr}</th>
        <th>{tr}Locked{/tr}</th>
        <th>{tr}Debit{/tr}</th>
        <th>{tr}Credit{/tr}</th>
        <th>{tr}Tax{/tr}</th>
        {if $tiki_p_acct_manage_accounts=='y'}
            <th></th>
        {/if}
    </tr>
    {foreach from=$accounts item=a}{cycle values="odd,even" assign="style"}
        <tr class="{$style}">
            <td class="text-end"><a href="tiki-accounting_account.php?bookId={$bookId}&accountId={$a.accountId}">{$a.accountId}</a></td>
            <td><a href="tiki-accounting_account.php?bookId={$bookId}&accountId={$a.accountId}">{$a.accountName|escape}</a></td>
            <td>{$a.accountNotes|escape}</td>
            <td class="text-end">
                {if $book.bookCurrencyPos==-1}{$book.bookCurrency}{/if}
                {$a.accountBudget|number_format:$book.bookDecimals:$book.bookDecPoint:$book.bookThousand}
                {if $book.bookCurrencyPos==1}{$book.bookCurrency}{/if}
            </td>
            <td>{if $a.accountLocked==1}{tr}Yes{/tr}{else}{tr}No{/tr}{/if}</td>
            <td class="text-end">
                {if $book.bookCurrencyPos==-1}{$book.bookCurrency}{/if}
                {$a.debit|number_format:$book.bookDecimals:$book.bookDecPoint:$book.bookThousand}
                {if $book.bookCurrencyPos==1}{$book.bookCurrency}{/if}
            </td>
            <td class="text-end">
                {if $book.bookCurrencyPos==-1}{$book.bookCurrency}{/if}
                {$a.credit|number_format:$book.bookDecimals:$book.bookDecPoint:$book.bookThousand}
                {if $book.bookCurrencyPos==1}{$book.bookCurrency}{/if}
            </td>
            <td>{$a.accountTax}</td>
            {if $tiki_p_acct_manage_accounts=='y'}
                <td class="action">
                    {actions}
                        {strip}
                            <action>
                                <a href="tiki-accounting_account.php?bookId={$bookId|escape:'attr'}&accountId={$a.accountId|escape:'attr'}&action=edit" class="iconmenu">
                                    {icon name="edit" _menu_text='y' _menu_icon='y' alt="{tr}Edit{/tr}"}
                                </a>
                            </action>
                            {if $a.accountLocked==1}
                                {$iconName = 'unlock'}
                                {$iconLabel = "{tr}Unlock{/tr}"}
                                {$confirmMsg = "{tr}Unlock account?{/tr}"}
                            {else}
                                {$iconName = 'lock'}
                                {$iconLabel = "{tr}Lock{/tr}"}
                                {$confirmMsg = "{tr}Lock account?{/tr}"}
                            {/if}
                            <action>
                                <form action="tiki-accounting_account.php" method="post">
                                    {ticket}
                                    <input type="hidden" name="bookId" value="{$bookId|escape:'attr'}">
                                    <input type="hidden" name="accountId" value="{$a.accountId|escape:'attr'}">
                                    <button
                                        type="submit"
                                        name="action"
                                        value="lock"
                                        class="btn btn-link link-list"
                                    >
                                        {icon name="$iconName"} {$iconLabel}
                                    </button>
                                </form>
                            </action>
                            <action>
                                <a href="tiki-accounting_account.php?bookId={$bookId|escape:'attr'}&accountId={$a.accountId|escape:'attr'}&action=delete"
                                    class="iconmenu"
                                    onclick="confirmPopup('{tr}Remove account?{/tr}', '{ticket mode=get}')"
                                >
                                    {icon name="remove" _menu_text='y' _menu_icon='y' alt="{tr}Remove{/tr}"}
                                </a>
                            </action>
                        {/strip}
                    {/actions}
                </td>
            {/if}
        </tr>
    {/foreach}
</table>
{button href="tiki-accounting_account.php?action=new&bookId={$bookId|escape:'attr'}" _text="{tr}Create a new account{/tr}"}
<a class="icon" href="tiki-accounting_export.php?action=print&bookId={$bookId|escape:'attr'}&what=accounts" target="new">
    {icon name="print" alt="{tr}printable version{/tr}"}
</a>
<a class="icon" href="tiki-accounting_export.php?action=settings&bookId={$bookId|escape:'attr'}&what=accounts">
    {icon name="export" alt="{tr}export table{/tr}"}
</a>