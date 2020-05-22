<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

require_once(__DIR__ . '/../test/TestRunnerWithBaseline.php');
require_once(__DIR__ . '/../debug/Tracer.php');

class TestRunnerWithBaselineTest extends PHPUnit\Framework\TestCase
{
    public $runner;
    private $old_cmdline = null;

    protected function setUp(): void
    {
        global $argv;

        $this->runner = new TestRunnerWithBaseLine();

        /* Remember what the command line args were, because some of
           the tests below may need to mess around with them, and we
           want to restore them to their original state afterwards.
        */
        $this->old_cmdline = $argv;
    }

    protected function tearDown(): void
    {
        $argv = $this->old_cmdline;
    }


    /**
     * @dataProvider dataProviderProcessPhpunitLogData
     * @param $log_data
     * @param $exp_failures
     * @param $exp_errors
     * @param $exp_pass
     * @param $message
     */
    public function testProcessPhpunitLogData($log_data, $exp_failures, $exp_errors, $exp_pass, $message): void
    {
        $issues = $this->runner->processPhpunitLogData($log_data);
        $this->assertEquals(
            $exp_failures,
            $issues['failures'],
            "$message\nThe list of FAILURES was not collected properly."
        );

        $this->assertEquals(
            $exp_errors,
            $issues['errors'],
            "$message\nThe list of ERRORS was not collected properly."
        );

        $this->assertEquals(
            $exp_pass,
            $issues['pass'],
            "$message\nThe list of PASS was not collected properly."
        );
    }

    public function dataProviderProcessPhpunitLogData(): array
    {
        return
            [

              [
                  [
                      ['event' => 'test', 'test' => 'SomeClass::testSomeMethodThatPasses', 'status' => 'pass'],
                  ],
                  [],
                  [],
                  ['SomeClass::testSomeMethodThatPasses'],
                  "Case 0 description: No failures nor errors, just a pass."
              ],

              [
                  [
                      ['event' => 'test', 'test' => 'SomeClass::testSomeMethodThatFails', 'status' => 'fail'],
                      ['event' => 'test', 'test' => 'SomeOtherClass::testSomeMethodThatRaisesErrors', 'status' => 'error']
                  ],
                  ['SomeClass::testSomeMethodThatFails'],
                  ['SomeOtherClass::testSomeMethodThatRaisesErrors'],
                  [],
                  "Case 1 description: one failure and one error."
              ],

              [
                  [
                      ['event' => 'test', 'test' => 'SomeClass::testSomeMethodThatFails', 'status' => 'fail'],
                      ['event' => 'test', 'test' => 'SomeClass::testSomeOtherMethodThatFails', 'status' => 'fail']
                  ],
                  ['SomeClass::testSomeMethodThatFails', 'SomeClass::testSomeOtherMethodThatFails'],
                  [],
                  [],
                  "Case 2 description: more than one issue of type failure."
                ],

              [
                  [
                      ['event' => 'test', 'test' => 'SomeClass::testSomeMethodThatRaiseAnError', 'status' => 'error'],
                      ['event' => 'test', 'test' => 'SomeClass::testSomeOtherMethodThatRaisesAnError', 'status' => 'error']
                  ],
                  [],
                  ['SomeClass::testSomeMethodThatRaiseAnError', 'SomeClass::testSomeOtherMethodThatRaisesAnError'],
                  [],
                  "Case 3 description: more than one issue of type error."
              ],

              [
                [
                    ['event' => 'testStart', 'test' => 'SomeClass::testSomeTestThatNeverFinished'],
                ],
                ['SomeClass::testSomeTestThatNeverFinished'],
                [],
                [],
                "Case 4 description: A test for which a 'testStart' event is issued, but no event is issued for the test results. Assume that this is a failure."
              ],

            ];
    }

    /**
     * @dataProvider dataProviderCompareTwoTestRuns
     * @param $baseline_issues
     * @param $current_issues
     * @param $exp_differences
     * @param $message
     */
    public function testCompareTwoTestRuns($baseline_issues, $current_issues, $exp_differences, $message): void
    {
        $got_differences = $this->runner->compareTwoTestRuns($baseline_issues, $current_issues);
        $this->assertEquals(
            $exp_differences,
            $got_differences,
            "$message\nDifferences against the baseline were not correct."
        );
    }

    public function dataProviderCompareTwoTestRuns(): array
    {
        return
            [
              [

                  ['failures' => [], 'errors' => [], 'pass' => []],
                  ['failures' => ['SomeClass::testThatFails'], 'errors' => ['SomeClass::testThatRaisesError'],
                        'pass' => []],
                  [
                      'failures_introduced' => ['SomeClass::testThatFails'],
                      'failures_fixed' => [],
                      'errors_introduced' => ['SomeClass::testThatRaisesError'],
                      'errors_fixed' => []
                  ],
                  "Case description: Introduced one error and one failure."
              ],

              [
                  ['failures' => ['SomeClass::testThatFails'], 'errors' => ['SomeClass::testThatRaisesError', 'pass' => []]],
                  ['failures' => [], 'errors' => [],
                      'pass' => ['SomeClass::testThatFails', 'SomeClass::testThatRaisesError']],
                  [
                      'failures_introduced' => [],
                      'failures_fixed' => ['SomeClass::testThatFails'],
                      'errors_introduced' => [],
                      'errors_fixed' => ['SomeClass::testThatRaisesError']
                  ],
                  "Case description: Fixed one error and one failure."
              ],

                [
                    ['failures' => ['SomeClass::testThaUsedToFail'], 'errors' => ['SomeClass::testThatUsedToRaiseAnError'],
                          'pass' => []],
                    ['failures' => ['SomeClass::testThatStartedFailing'], 'errors' => ['SomeClass::testThatStartedRaisingAnError'],
                          'pass' => ['SomeClass::testThaUsedToFail', 'SomeClass::testThatUsedToRaiseAnError']],
                    [
                        'failures_introduced' => ['SomeClass::testThatStartedFailing'],
                        'failures_fixed' => ['SomeClass::testThaUsedToFail'],
                        'errors_introduced' => ['SomeClass::testThatStartedRaisingAnError'],
                        'errors_fixed' => ['SomeClass::testThatUsedToRaiseAnError']
                    ],
                    "Case description: Fixed one error and one failure AND introduced one of each."
                ],


            ];
    }
}
