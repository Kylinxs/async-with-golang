
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
    $smarty->assign('msg', tra("This feature is disabled") . ": feature_accounting");
    $smarty->display("error.tpl");
    die;
}

if (! isset($_REQUEST['bookId'])) {
    $smarty->assign('msg', tra("Missing book id"));
    $smarty->display("error.tpl");
    die;
}
$bookId = $_REQUEST['bookId'];
$smarty->assign('bookId', $bookId);

if (! isset($_REQUEST['journalId'])) {
    $smarty->assign('msg', tra("Missing journal id"));
    $smarty->display("error.tpl");
    die;
}
$journalId = $_REQUEST['journalId'];
$smarty->assign('journalId', $journalId);

$globalperms = Perms::get();
$objectperms = Perms::get([ 'type' => 'accounting book', 'object' => $bookId ]);
if (! ($globalperms->acct_view or $objectperms->acct_book)) {
    $smarty->assign('msg', tra("You do not have the right to cancel transactions"));
    $smarty->display("error.tpl");
    die;
}

$accountinglib = TikiLib::lib('accounting');
$book = $accountinglib->getBook($bookId);
$smarty->assign('book', $book);

$entry = $accountinglib->getTransaction($bookId, $journalId);
if ($entry === false) {
    $smarty->assign('msg', tra("Error retrieving data from journal"));
    $smarty->display("error.tpl");
    die;
}
$smarty->assign('entry', $entry);

if ($access->checkCsrf(true)) {
    $accountinglib->cancelTransaction($bookId, $journalId);
    if (! empty($errors)) {
        Feedback::error(['mes' => $errors]);
    } else {
        Feedback::success(tr('Journal shown below successfully canceled'));
    }
}

$smarty->assign('mid', 'tiki-accounting_cancel.tpl');
$smarty->display("tiki.tpl");