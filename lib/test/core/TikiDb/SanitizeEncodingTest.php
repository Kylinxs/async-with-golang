
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Tests\TikiDb;

use PHPUnit\Framework\TestCase;
use Tiki\TikiDb\SanitizeEncoding;
use TikiLib;

class SanitizeEncodingTest extends TestCase
{
    /**
     * @param $input
     * @param $field
     * @param $expected
     *
     * @dataProvider filterUtf8DataProvider
     */
    public function testFilterUtf8($input, $field, $expected)
    {
        $result = SanitizeEncoding::filterMysqlUtf8($input, ['utf8' => 'utf8'], $field);
        $this->assertEquals($expected, $result);
    }

    public function filterUtf8DataProvider()
    {
        $c = SanitizeEncoding::INVALID_CHAR_REPLACEMENT;

        return [
            ['', 'utf8', ''],
            ['', 'utf8mb4', ''],
            ['Latin: Vitrum edere possum; mihi non nocet.', 'utf8', 'Latin: Vitrum edere possum; mihi non nocet.'],
            ['Latin: Vitrum edere possum; mihi non nocet.', 'utf8mb4', 'Latin: Vitrum edere possum; mihi non nocet.'],
            [
                'Sanskrit: काचं शक्नोम्यत्तुम् । नोपहिनस्ति माम् ॥',
                'utf8',
                'Sanskrit: काचं शक्नोम्यत्तुम् । नोपहिनस्ति माम् ॥',
            ],
            [
                'Sanskrit: काचं शक्नोम्यत्तुम् । नोपहिनस्ति माम् ॥',
                'utf8mb4',
                'Sanskrit: काचं शक्नोम्यत्तुम् । नोपहिनस्ति माम् ॥',
            ],
            [
                'Sanskrit: kācaṃ śaknomyattum; nopahinasti mām.',
                'utf8',
                'Sanskrit: kācaṃ śaknomyattum; nopahinasti mām.',
            ],
            [
                'Sanskrit: kācaṃ śaknomyattum; nopahinasti mām.',
                'utf8mb4',
                'Sanskrit: kācaṃ śaknomyattum; nopahinasti mām.',
            ],
            [
                'Sample Emoji: 😀 😁 🐶 🐱 🏳 🏴',
                'utf8',
                'Sample Emoji: ' . $c . ' ' . $c . ' ' . $c . ' ' . $c . ' ' . $c . ' ' . $c,
            ],
            ['Sample Emoji: 😀 😁 🐶 🐱 🏳 🏴', 'utf8mb4', 'Sample Emoji: 😀 😁 🐶 🐱 🏳 🏴'],
            [0x01F600, 'utf8', 0x01F600], // Emoji as integer
            [0x01F600, 'utf8mb4', 0x01F600], // Emoji as integer
            [
                ['a' => 'Sanskrit: kācaṃ śaknomyattum; nopahinasti mām.', 'utf8' => 'Sample Emoji: 😀 😁 🐶 🐱 🏳 🏴'],
                null,
                [
                    'a' => 'Sanskrit: kācaṃ śaknomyattum; nopahinasti mām.',
                    'utf8' => 'Sample Emoji: ' . $c . ' ' . $c . ' ' . $c . ' ' . $c . ' ' . $c . ' ' . $c,
                ],
            ],
            [
                [
                    'a' => 'Sanskrit: kācaṃ śaknomyattum; nopahinasti mām.',
                    'utf8mb4' => 'Sample Emoji: 😀 😁 🐶 🐱 🏳 🏴',
                ],
                null,
                [
                    'a' => 'Sanskrit: kācaṃ śaknomyattum; nopahinasti mām.',
                    'utf8mb4' => 'Sample Emoji: 😀 😁 🐶 🐱 🏳 🏴',
                ],
            ],
        ];
    }

    public function testFilterUtf8EmptyFieldList()
    {
        $result = SanitizeEncoding::filterMysqlUtf8('Sample Emoji: 😀 😁 🐶 🐱 🏳 🏴', [], 'xxxx');
        $this->assertEquals('Sample Emoji: 😀 😁 🐶 🐱 🏳 🏴', $result);
    }

    public function testTikiDbUtf8Filter()
    {
        $c = SanitizeEncoding::INVALID_CHAR_REPLACEMENT;

        $fullUtf8String = 'Sample Emoji: 😀 😁 🐶 🐱 🏳 🏴';
        $filteredString = 'Sample Emoji: ' . $c . ' ' . $c . ' ' . $c . ' ' . $c . ' ' . $c . ' ' . $c;

        $tikiLib = TikiLib::lib('tiki');
        $table = $tikiLib->table('tiki_files');
        $record = $table->insert(['name' => $fullUtf8String, 'data' => $fullUtf8String]);
        $row = $table->fetchFullRow(['fileId' => $record]);

        if (in_array('name', $table->getUtf8Fields(), true)) { // utf8
            $this->assertEquals($filteredString, $row['name'], 'name should be filtered when using utf8');
        } else {  // utf8mb4
            $this->assertNotEquals($filteredString, $row['name'], 'name should be filtered when using utf8mb4');
        }
        $this->assertNotEquals($filteredString, $row['data'], 'data should not be filter since is LONGBLOB');
    }
}