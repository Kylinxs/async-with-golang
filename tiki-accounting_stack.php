<?php

/**
 * @package tikiwiki
 */

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/***
 *
 * @var \TikiAccessLib  $access
 *
 * @var \AccountingLib  $accountinglib
 *
 *
 * @var \Smarty_Tiki    $smarty
 *
 * Define the current section
 * @var string $section
 */

$section = 'accounting';
require_once('tiki-setup.php');

// Feature available?
if ($prefs['feature_accounting'] != 'y') {
    $smarty->assign('msg', tra('This feature is disabled') . ': feature_accounting');
    $smarty->display('error.tpl');
    die;
}

$globalperms = Perms::get();
$objectperms = Perms::get([ 'type' => 'accounting book', 'object' => $bookId ]);

if (! ($globalperms->acct_book or $objectperms->acct_book_stack)) {
    $smarty->assign('msg', tra('You do not have the right to book into the stack'));
    $smarty->display('error.tpl');
    die;
}

if (! isset($_REQUEST['bookId'])) {
    $smarty->assign('msg', tra('Missing book id'));
    $smarty->display('error.tpl');
    die;
}
$bookId = $_REQUEST['bookId'];
$smarty->assign('bookId', $bookId);

if (! isset($_REQUEST['stackId'])) {
    $stackId = 0;
} else {
    $stackId = $_REQUEST['stackId'];
}

if (isset($_REQUEST['hideform'])) {
    $smarty->assign('hideform', $_REQUEST['hideform']);
} else {
    $smarty->assign('hideform', 0);
}

$accountinglib = TikiLib::lib('accounting');
$book = $accountinglib->getBook($bookId);
$smarty->assign('book', $book);

$accounts = $accountinglib->getAccounts($bookId, $all = true);
$smarty->assign('accounts', $accounts);

if ($_POST['stack_Year']) {
    $stackDate = new DateTime();
    $stackDate->setDate(
        $_POST['stack_Year'],
        $_POST['stack_Month'],
        $_POST['stack_Day']
    );
}

if (isset($_POST['action'])) {
    if ($_POST['action'] == 'book') {
        if ($stackId == 0  && $access->checkCsrf(true)) {
            // new entry
            $result = $accountinglib->stackBook(
                $bookId,
                $stackDate,
                $_POST['stackDescription'],
                $_POST['debitAccount'],
                $_POST['creditAccount'],
                $_POST['debitAmount'],
                $_POST['creditAmount'],
                $_POST['debitText'],
                $_POST['creditText']
            );
        } elseif ($access->checkCsrf(true)) {
            // modify old entry
            $result = $accountinglib->stackUpdate(
                $bookId,
                $stackId,
                $stackDate,
                $_POST['stackDescription'],
                $_POST['debitAccount'],
                $_POST['creditAccount'],
                $_POST['debitAmount'],
                $_POST['creditAmount'],
                $_POST['debitText'],
                $_POST['creditText']
            );
        }
        if (is_numeric($result)) {
            if (isset($_POST['statementId'])) {
                $accountinglib->updateStatementStack($bookId, $_POST['statementId'], $result);
            }
            $stackId = 0; //success means we can create a new entry
        }
    } elseif (
        $_POST['action'] == 'delete'
        && $access->checkCsrf(true)
    ) {
        $result = $accountinglib->stackDelete($bookId, $stackId);
        $stackId = 0;
    } elseif (
        $_POST['action'] == 'confirm'
        && $access->checkCsrf(true)
    ) {
        $result = $accountinglib->stackConfirm($bookId, $stackId);
        $stackId = 0;
    } else {
        // unknown action = nothing
        $result = 0;
    }
} else {
    $result = 0;
}

if (is_array($result)) {
    Feedback::error(['mes' => $result]);
    $smarty->assign('stackId', $stackId);
    $smarty->assign('stackDate', $stackDate);
    $smarty->assign('stackDescription', $_POST['stackDescription']);
    $smarty->assign('debitAccount', $_POST['debitAccount']);
    $smarty->assign('creditAccount', $_POST['creditAccount']);
    $smarty->assign('debitAmount', $_POST['debitAmount']);
    $smarty->assign('creditAmount', $_POST['creditAmount']);
    $smarty->assign('debitText', $_POST['debitText']);
    $smarty->assign('creditText', $_POST['creditText']);

    if (isset($_POST['statementId'])) {
        $smarty->assign('statementId', $_POST['statementId']);
    }
} else {
    if ($stackId != 0) {
        $stackEntry = $accountinglib->getStackTransaction($bookId, $_POST['stackId']);
        $smarty->assign('stackId', $stackId);
        $smarty->assign('stackDate', $stackEntry['stackDate']);
        $smarty->assign('stackDescription', $stackEntry['stackDescription']);
        $debitAccount = [];
        $debitAmount = [];
        $debitText = [];

        for ($i = 0, $iCountStackEntryDebit = count($stackEntry['debit']); $i < $iCountStackEntryDebit; $i++) {
            $debitAccount[] = $stackEntry['debit'][$i]['stackItemAccountId'];
            $debitAmount[] = $stackEntry['debit'][$i]['stackItemAmount'];
            $debitText[] = $stackEntry['debit'][$i]['stackItemText'];
        }

        $creditAccount = [];
        $creditAmount = [];
        $creditText = [];

        for ($i = 0, $iCountStackEntryCredit = count($stackEntry['credit']); $i < $iCountStackEntryCredit; $i++) {
            $creditAccount[] = $stackEntry['credit'][$i]['stackItemAccountId'];
            $creditAmount[] = $stackEntry['credit'][$i]['stackItemAmount'];
            $creditText[] = $stackEntry['credit'][$i]['stackItemText'];
        }

        $smarty->assign('debitAccount', $debitAccount);
        $smarty->assign('creditAccount', $creditAccount);
        $smarty->assign('debitAmount', $debitAmount);
        $smarty->assign('creditAmount', $creditAmount);
        $smarty->assign('debitText', $debitText);
        $smarty->assign('creditText', $creditText);
        if (! empty($_POST['action'])) {
            if ($_POST['action'] == 'book') {
                Feedback::success(tr('Stack %0 successfully modified in book %1', $stackId, $book['bookName']));
            } elseif ($_POST['action'] == 'delete') {
                Feedback::success(tr('Stack %0 successfully deleted from book %1', $stackId, $book['bookName']));
            }
        }
    } else {
        if (! empty($_POST['action'])) {
            if ($_POST['action'] == 'book') {
                Feedback::success(tr('Stack %0 recorded in book %1', $result, $book['bookName']));
            } elseif ($_POST['action'] == 'delete') {
                Feedback::success(tr('Stack %0 deleted from book %1', $_POST['stackId'], $book['bookName']));
            } elseif ($_POST['action'] == 'confirm' && $result === true) {
                Feedback::success(tr('Stack %0 confirmed and recorded as entry in book %1', $_POST['stackId'], $book['bookName']));
            }
        }
        $smarty->assign('stackId', $stackId);
        $smarty->assign('debitAccount', ['']);
        $smarty->assign('creditAccount', ['']);
        $smarty->assign('debitAmount', ['']);
        $smarty->assign('creditAmount', ['']);
        $smarty->assign('debitText', ['']);
        $smarty->assign('creditText', ['']);
    }
}

if ($globalperms->acct_book or $objectperms->acct_book) {
    $smarty->assign('canBook', true);
} else {
    $smarty->assign('canBook', false);
}

$stack = $accountinglib->getStack($bookId);
$smarty->assign('stack', $stack);
$smarty->assign('req_url', $_SERVER['REQUEST_URI']);
$smarty->assign('mid', 'tiki-accounting_stack.tpl');
$smarty->display('tiki.tpl');
