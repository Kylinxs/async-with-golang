<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * @group unit
 *
 */

class Multilingual_Aligner_SentenceAlignmentsTest extends TikiTestCase
{
    public function disabledTestReminder()
    {
        $this->fail("remember to reactivate all tests in SentenceAlignments");
    }

    protected function setUp(): void
    {
        $this->alignments = new Multilingual_Aligner_SentenceAlignments();
    }

    ////////////////////////////////////////////////////////////////
    // Documentation tests
    //    These tests illustrate how to use this class.
    ////////////////////////////////////////////////////////////////

    /**
     * @group multilingual
     */
    public function thisIsHowYouCreateAsentenceAlignments()
    {
        $aligner = new Multilingual_Aligner_SentenceAlignments();
    }

    ////////////////////////////////////////////////////////////////
    // Note: In the rest of these tests, you can assume that
    //       $this->alignments is an instance of SentenceAlignments
    //       created as above.
    ////////////////////////////////////////////////////////////////

    /*
     * In the remainder of these tests, you can assume that
     * $this->alignments alignments contains an instance of
     * SentenceAligners built as in the above test.
     */
    /**
     * @group multilingual
     */
    public function testThisIsHowYouAddSentences()
    {
        $en_sentence = "hello world";
        $fr_sentence = "bonjour le monde";
        $this->alignments->addSentencePair($en_sentence, 'en', $fr_sentence, 'fr');

        // dummy test to suppress warning. Do these kinds of tests belong here?
        $this->asserttrue(true);
    }

    /**
     * @group multilingual
     */
    public function disabledTestThisIsHowYouRetrieveAsentenceInTheOtherLanguage()
    {
        $en_sentence = "hello world";
        $fr_sentence = $this->alignments->getSentenceInOtherLanguage($en_sentence, 'en');
    }



    ////////////////////////////////////////////////////////////////
    // Internal tests
    //    These tests check the internal workings of the class.
    ////////////////////////////////////////////////////////////////
}
