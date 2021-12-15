
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class AccountingLibTest extends PHPUnit\Framework\TestCase
{
    /** @var TestHelpers */
    protected $testHelpers;

    protected function setUp(): void
    {
        require_once(__DIR__ . '/../../TestHelpers.php');
        $this->testHelpers = new TestHelpers();

        $this->testHelpers->simulateTikiScriptContext('tiki-accounting_books.php', 'admin');
    }

    protected function tearDown(): void
    {
        $this->testHelpers->stopSimulatingTikiScriptContext();
    }

    public function testBasicUsage()
    {
        /** @var AccountingLib $accountinglib */
        $accountinglib = TikiLib::lib('accounting');

        // Create a Book
        $bookA = $accountinglib->createBook(
            'Book A',
            'n',
            '2017-01-01 14:20:26',
            '2017-12-31 14:20:26',
            'EUR',
            '1',
            '2',
            ',',
            '.',
            ';',
            'CR',
            '"',
            'y'
        );
        $this->assertGreaterThan(0, $bookA);

        // create 2 accounts
        $accountNameSnakeOil = 'Snake Oil Company ' . uniqid();
        $accountResult = $accountinglib->createAccount(
            $bookA,
            '1',
            $accountNameSnakeOil,
            'Some Notes',
            '1000',
            '0',
            0
        );
        $this->assertTrue($accountResult);

        $accountResult = $accountinglib->createAccount(
            $bookA,
            '2',
            'ACME Corporation ' . uniqid(),
            'More Notes',
            '2000',
            '0',
            0
        );
        $this->assertTrue($accountResult);

        // retrieve the first account created
        $accountList = $accountinglib->getAccounts($bookA);
        $accountSnakeOil = array_shift($accountList);

        //assert was Snake Oil
        $this->assertEquals($accountNameSnakeOil, $accountSnakeOil['accountName']);

        // add 2 entries to the books in account Snake Oil
        $journalDate = new DateTime();
        $journalDate->setDate(2017, 01, 02);

        $entry001 = $accountinglib->book(
            $bookA,
            $journalDate,
            'entry 001',
            [$accountSnakeOil['accountId']],
            [$accountSnakeOil['accountId']],
            [10],
            [10],
            ['entry 001'],
            ['entry 001']
        );
        $this->assertGreaterThan(0, $entry001);

        $entry002 = $accountinglib->book(
            $bookA,
            $journalDate,
            'entry 002',
            [$accountSnakeOil['accountId']],
            [$accountSnakeOil['accountId']],
            [20],
            [20],
            ['entry 002'],
            ['entry 002']
        );
        $this->assertGreaterThan(0, $entry002);

        $accountList = $accountinglib->getExtendedAccounts($bookA, true);

        $this->assertIsArray($accountList);
        $this->assertCount(2, $accountList);

        $account = null;
        foreach ($accountList as $item) {
            if ($item['accountId'] == $accountSnakeOil['accountId']) {
                $account = $item;
            }
        }
        $this->assertIsArray($account);
        $this->assertEquals(30, $account['credit']);
        $this->assertEquals(30, $account['debit']);
    }
}