
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

require_once(__DIR__ . '/../../language/Exception.php');
require_once(__DIR__ . '/../../language/WriteFile.php');

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;

class Language_WriteFileTest extends TikiTestCase
{
    protected $obj;

    protected function setUp(): void
    {
        // setup a mock filesystem
        $lang = vfsStream::setup('lang');
        $this->langFile = new vfsStreamFile('language.php');
        $lang->addChild($this->langFile);

        $this->parseFile = $this->getMockBuilder('Language_File')
                                ->onlyMethods(['getTranslations'])
                                ->setConstructorArgs([vfsStream::url('lang/language.php')])
                                ->getMock();

        $this->filePath = vfsStream::url('lang/language.php');

        $this->obj = new Language_WriteFile($this->parseFile);
    }

    public function testConstructShouldRaiseExceptionIfFileIsNotWritable(): void
    {
        $this->langFile->chmod(0444);
        $this->expectException('Language_Exception');
        new Language_WriteFile($this->parseFile);
    }

    public function testWriteStringsToFileShouldReturnFalseIfEmptyParam(): void
    {
        $this->assertFalse($this->obj->writeStringsToFile([]));
    }

    public function testWriteStringsToFileShouldWriteSimpleStrings(): void
    {
        $this->parseFile->expects($this->once())->method('getTranslations')->willReturn([]);

        $obj = $this->getMockBuilder('Language_WriteFile')
                    ->onlyMethods(['fileHeader'])
                    ->setConstructorArgs([$this->parseFile])
                    ->getMock();

        $obj->expects($this->once())->method('fileHeader')->willReturn("// File header\n\n");

        $strings = [
            'First string' => ['name' => 'First string'],
            'Second string' => ['name' => 'Second string'],
            'etc' => ['name' => 'etc'],
        ];

        $obj->writeStringsToFile($strings);

        // check if a backup of old language file (in this case an empty file) was created
        $this->assertFileExists($this->filePath . '.old');

        $this->assertFileEquals(
            __DIR__ . '/fixtures/language_simple.php',
            $this->filePath
        );
    }

    public function writeStringsToFileProvider(): array
    {
        $strings = [
            'First string' => ['name' => 'First string', 'files' => ['file1', 'file3']],
            'Second string' => ['name' => 'Second string', 'files' => ['file2']],
            'Used string' => ['name' => 'Used string', 'files' => ['file3']],
            'Translation is the same as English string' => ['name' => 'Translation is the same as English string', 'files' => ['file5', 'file1']],
            'etc' => ['name' => 'etc', 'files' => ['file4']],
        ];

        return [[$strings]];
    }

    /**
     * @dataProvider writeStringsToFileProvider
     * @param $strings
     */
    public function testWriteStringsToFileShouldKeepTranslationsEvenIfTheyAreEqualToEnglishString($strings): void
    {
        $this->parseFile->expects($this->once())->method('getTranslations')->willReturn(
            [
                'Unused string'                             => 'Some translation',
                'Used string'                               => 'Another translation',
                'Translation is the same as English string' => 'Translation is the same as English string',
            ]
        );

        $obj = $this->getMockBuilder('Language_WriteFile')
                    ->onlyMethods(['fileHeader'])
                    ->setConstructorArgs([$this->parseFile])
                    ->getMock();

        $obj->expects($this->once())->method('fileHeader')->willReturn("// File header\n\n");

        $obj->writeStringsToFile($strings);

        $this->assertFileEquals(
            __DIR__ . '/fixtures/language_with_translations.php',
            $this->filePath
        );
    }

    /**
     * @dataProvider writeStringsToFileProvider
     * @param $strings
     */
    public function testWriteStringsToFileShouldIgnoreUnusedStrings($strings): void
    {
        $this->parseFile->expects($this->once())->method('getTranslations')->willReturn(
            [
                'Unused string'                             => 'Some translation',
                'Used string'                               => 'Another translation',
                'Translation is the same as English string' => 'Translation is the same as English string',
            ]
        );

        $obj = $this->getMockBuilder('Language_WriteFile')
                ->onlyMethods(['fileHeader'])
                ->setConstructorArgs([$this->parseFile])
                ->getMock();

        $obj->expects($this->once())->method('fileHeader')->willReturn("// File header\n\n");

        $obj->writeStringsToFile($strings);

        $this->assertFileEquals(
            __DIR__ . '/fixtures/language_with_translations.php',
            $this->filePath
        );
    }

    /**
     * @dataProvider writeStringsToFileProvider
     * @param $strings
     */
    public function testWriteStringsToFileShouldOutputFileWhereStringsWasFound($strings): void
    {
        $this->parseFile->expects($this->once())->method('getTranslations')->willReturn(
            [
                'Unused string'                             => 'Some translation',
                'Used string'                               => 'Another translation',
                'Translation is the same as English string' => 'Translation is the same as English string',
            ]
        );

        $obj = $this->getMockBuilder('Language_WriteFile')
                    ->onlyMethods(['fileHeader'])
                    ->setConstructorArgs([$this->parseFile])
                    ->getMock();

        $obj->expects($this->once())->method('fileHeader')->willReturn("// File header\n\n");

        $obj->writeStringsToFile($strings, true);

        $this->assertFileEquals(
            __DIR__ . '/fixtures/language_with_translations_and_file_paths.php',
            $this->filePath
        );
    }

    /**
     * @dataProvider writeStringsToFileProvider
     * @param $strings
     */
    public function testWriteStringsToFileShouldConsiderStringsWithPunctuationInEndASpecialCase($strings): void
    {
        $this->parseFile->expects($this->once())->method('getTranslations')->willReturn(
            [
                'Unused string'                             => 'Some translation',
                'Used string'                               => 'Another translation',
                'Translation is the same as English string' => 'Translation is the same as English string',
                'Login'                                     => 'Another translation',
                'Add user:'                                 => 'Translation',
            ]
        );

        $obj = $this->getMockBuilder('Language_WriteFile')
                    ->onlyMethods(['fileHeader'])
                    ->setConstructorArgs([$this->parseFile])
                    ->getMock();

        $obj->expects($this->once())->method('fileHeader')->willReturn("// File header\n\n");

        $strings['Login:'] = ['name' => 'Login:'];
        $strings['Add user:'] = ['name' => 'Add user:'];
        $strings['All users:'] = ['name' => 'All users:'];

        $obj->writeStringsToFile($strings);

        $this->assertFileEquals(
            __DIR__ . '/fixtures/language_punctuations.php',
            $this->filePath
        );
    }

    /**
     * @dataProvider writeStringsToFileProvider
     * @param $strings
     */
    public function testWriteStringsToFileShouldProperlyHandleSpecialCharactersInsideStrings($strings): void
    {
        $this->parseFile->expects($this->once())->method('getTranslations')->willReturn(
            [
                'Unused string'                                        => 'Some translation',
                'Used string'                                          => 'Another translation',
                'Translation is the same as English string'            => 'Translation is the same as English string',
                "Congratulations!\n\nYour server can send emails.\n\n" => "Gratulation!\n\nDein Server kann Emails senden.\n\n",
            ]
        );

        $obj = $this->getMockBuilder('Language_WriteFile')
                    ->onlyMethods(['fileHeader'])
                    ->setConstructorArgs([$this->parseFile])
                    ->getMock();

        $obj->expects($this->once())->method('fileHeader')->willReturn("// File header\n\n");

        $strings["Congratulations!\n\nYour server can send emails.\n\n"] = ['name' => "Congratulations!\n\nYour server can send emails.\n\n"];
        $strings['Handling actions of plugin "%s" failed'] = ['name' => 'Handling actions of plugin "%s" failed'];

        $obj->writeStringsToFile($strings);

        $this->assertFileEquals(
            __DIR__ . '/fixtures/language_escape_special_characters.php',
            $this->filePath
        );
    }

    public function testWriteStringsToFileShouldNotKeepTranslationsWithPunctuationOnSuccessiveCalls(): void
    {
        $this->parseFile->expects($this->at(0))
                        ->method('getTranslations')->willReturn(['Errors' => 'Ошибки',]);

        $obj = $this->getMockBuilder('Language_WriteFile')
                    ->onlyMethods(['fileHeader'])
                    ->setConstructorArgs([$this->parseFile])
                    ->getMock();

        $obj->method('fileHeader')->willReturn("// File header\n\n");

        $strings = [
            'Errors' => ['name' => 'Errors'],
            'Errors:' => ['name' => 'Errors:'],
        ];

        $obj->writeStringsToFile($strings);

        $this->parseFile->expects($this->at(0))
                        ->method('getTranslations')->willReturn(['Errors:' => 'خطاها:',]);

        $this->assertFileEquals(
            __DIR__ . '/fixtures/language_writestringstofile_first_call.php',
            $this->filePath
        );

        $obj->writeStringsToFile($strings);

        $this->assertFileEquals(
            __DIR__ . '/fixtures/language_writestringstofile_second_call.php',
            $this->filePath
        );
    }
}