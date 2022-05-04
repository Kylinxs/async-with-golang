
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Search_MySql_HighlightHelper implements Laminas\Filter\FilterInterface
{
    private $words = [];
    private $replacements = [];
    private $snippetHelper;

    public function __construct(array $words)
    {
        $this->words = $words;

        $counter = -1;

        $this->replacements = array_map(
            function ($word) use (&$counter) {
                $counter++;
                return "<b class=\"highlight_word highlight_word_$counter\">$word</b>";
            },
            $this->words
        );
        $this->snippetHelper = new Search_ResultSet_SnippetHelper();
    }

    public function filter($content)
    {
        $content = $this->snippetHelper->filter($content);
        $content = str_ireplace($this->words, $this->replacements, $content);
        return trim(strip_tags($content, '<b><i><em><strong><pre><code><span>'));
    }
}