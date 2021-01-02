<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Standards\TikiIgnore\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\AbstractVariableSniff;
use PHP_CodeSniffer\Util\Tokens;
use Tiki\Standards\TikiIgnore\Helpers\IgnoreListTrait;

require_once __DIR__ . DIRECTORY_SEPARATOR . '../../Helpers/IgnoreListTrait.php';

class IgnorePropertyDeclarationSniff extends AbstractVariableSniff
{
    use IgnoreListTrait;

    protected const SNIFF_UNDERSCORE = 'PSR2.Classes.PropertyDeclaration.Underscore';
    protected const SNIFF_VISIBILITY = 'PSR2.Classes.PropertyDeclaration.ScopeMissing';
    protected const SNIFF_VAR_USED = 'PSR2.Classes.PropertyDeclaration.VarUsed';

    public function __construct()
    {
        $this->loadIgnoreList([self::SNIFF_UNDERSCORE, self::SNIFF_VISIBILITY, self::SNIFF_VAR_USED]);
        parent::__construct();
    }

    /**
     * Processes the function tokens within the class.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file where this token was found.
     * @param int                         $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processMemberVar(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if ($tokens[$stackPtr]['content'][1] === '_') {
            if ($this->inIgnoreList(self::SNIFF_UNDERSCORE, $phpcsFile->path, $tokens[$stackPtr]['content'])) {
                $this->ignoreToken(self::SNIFF_UNDERSCORE, $phpcsFile, $tokens, $stackPtr);
            }
        }

        // Detect multiple properties defined at the same time. Throw an error
        // for this, but also only process the first property in the list so we don't
        // repeat errors.
        $find   = Tokens::$scopeModifiers;
        $find[] = T_VARIABLE;
        $find[] = T_VAR;
        $find[] = T_SEMICOLON;
        $find[] = T_OPEN_CURLY_BRACKET;

        $prev = $phpcsFile->findPrevious($find, ($stackPtr - 1));
        if ($tokens[$prev]['code'] === T_VARIABLE) {
            return;
        }

        if ($tokens[$prev]['code'] === T_VAR) {
            if ($this->inIgnoreList(self::SNIFF_VAR_USED, $phpcsFile->path, $tokens[$stackPtr]['content'])) {
                $this->ignoreToken(self::SNIFF_VAR_USED, $phpcsFile, $tokens, $stackPtr);
            }
        }

        try {
            $propertyInfo = $phpcsFile->getMemberProperties($stackPtr);
            if (empty($propertyInfo) === true) {
                return;
            }
        } catch (\Exception $e) {
            // Turns out not to be a property after all.
            return;
        }

        if ($propertyInfo['scope_specified'] === false) {
            if ($this->inIgnoreList(self::SNIFF_VISIBILITY, $phpcsFile->path, $tokens[$stackPtr]['content'])) {
                $this->ignoreToken(self::SNIFF_VISIBILITY, $phpcsFile, $tokens, $stackPtr);
            }
        }
    }


    /**
     * Processes normal variables.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file where this token was found.
     * @param int                         $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processVariable(File $phpcsFile, $stackPtr)
    {
        /*
            We don't care about normal variables.
        */
    }

    /**
     * Processes variables in double quoted strings.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file where this token was found.
     * @param int                         $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processVariableInString(File $phpcsFile, $stackPtr)
    {
        /*
            We don't care about normal variables.
        */
    }
}
