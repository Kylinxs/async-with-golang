{* $Id$ *}
{title help="accounting"}
    {$book.bookName}:
    {if $action=="new"}{tr}Create new account{/tr}{else}{tr}Edit Account{/tr}{/if}
    {$account.accountId} {$account.accountName}
{/title}
<div id="account_form">
    <form method="post" action="tiki-accounting_account.php">
        <input class="form-control" type="hidden" name="bookId" value="{$bookId|escape:'attr'}">
        <input class="form-control" type="hidden" name="accountId" value="{$account.accountId|escape:'attr'}">
        {ticket}
        <fieldset>
            <legend>{tr}Account properties{/tr}</legend>
            <div class="mb-3 row">
                <label class="col-form-label col-md-4">{tr}Account number{/tr} <span class="text-danger">*</span></label>
                <div class="col-md-8">
                    <input class="form-control" class="form-control" type="text" name="newAccountId" id="newAccountId" {if !$account.changeable}readonly{/if} value="{$account.accountId}">
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-form-label col-md-4">{tr}Account name{/tr} <span class="text-danger">*</span></label>
                <div class="col-md-8">
                    <input class="form-control" type="text" name="accountName" id="accountName" value="{$account.accountName}">
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-form-label col-md-4">{tr}Notes{/tr}</label>
                <div class="col-md-8">
                    <textarea class="form-control" name="accountNotes" id="accountNotes" cols="40" rows="3">{$account.accountNotes}</textarea>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-form-label col-md-4">{tr}Budget{/tr} <span class="text-danger">*</span></label>
                <div class="col-md-8">
                    <input class="form-control" type="text" name="accountBudget" id="accountBudget" value="{$account.accountBudget}">
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-form-label col-md-4">{tr}Locked{/tr}</label>
                <div class="col-md-8">
                    <div class="radio">
                        <label>
                            <input type="radio" name="accountLocked" id="accountLocked" {if $account.accountLocked==1}checked="checked"{/if} value="1">
                            {tr}Yes{/tr}
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="accountLocked" id="accountUnlocked" {if $account.accountLocked!=1}checked="checked"{/if} value="0">
                            {tr}No{/tr}
                        </label>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <div class="offset-md-4">
                    {if $action=='new'}
                        {$label = "{tr}Create account{/tr}"}
                    {else}
                        {$label = "{tr}Modify account{/tr}"}
                    {/if}
                    <button type="submit" class="btn btn-primary" name="action" value="{$action|escape:'attr'}">
                        {$label}
                    </button>
                    {if $account.changeable==1 && $action=="edit"}
                        <button
                            type="submit"
                            class="btn btn-warning"
                            name="action"
                            value="delete"
                            onclick="confirmPopup('{tr _0="{$account.accountName|escape:'attr'}" _1="{$book.bookName|escape:'attr'}"}Delete account %0 from book %1?{/tr}')"
                        >
                            {tr}Delete this account{/tr}
                        </button>
                    {/if}
                    {button href="tiki-accounting.php?bookId=$bookId" _text="{tr}Back to book page{/tr}"}
                </div>
            </div>
        </fieldset>
    </form>
</div>
{if isset($journal)}
    <div id="account_journal">
        {include file='tiki-accounting_journal.tpl'}
    </div>
{/if}
