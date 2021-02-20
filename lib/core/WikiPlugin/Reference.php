
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\WikiPlugin;

/**
 * Class Reference handles the common parts of plugins addreference and showreference
 */
class Reference
{
    const STYLE_AMA = 'ama';
    const STYLE_MLA = 'mla';

    /**
     * Returns the current citation style
     *
     * @return string Citation Style
     */
    public static function getCitationStyle()
    {
        global $prefs;

        if (! empty($prefs['feature_references_style']) && $prefs['feature_references_style'] === self::STYLE_MLA) {
            return self::STYLE_MLA;
        }

        return self::STYLE_AMA;
    }

    /**
     * List of tags and maps to the keys in the values array
     *
     * @return array map tags to array keys
     */
    public static function getTagsToParse()
    {
        return [
            '~biblio_code~' => 'biblio_code',
            '~author~' => 'author',
            '~title~' => 'title',
            '~year~' => 'year',
            '~part~' => 'part',
            '~uri~' => 'uri',
            '~code~' => 'code',
            '~publisher~' => 'publisher',
            '~location~' => 'location',
        ];
    }

    /**
     * Process a given reference according to the template and/or citation style
     *
     * @param $tags
     * @param $ref
     * @param $values
     * @param string $referenceStyle
     * @return mixed|string
     */
    public static function parseTemplate($tags, $ref, $values, $referenceStyle = self::STYLE_AMA)
    {
        $charsUsedForSpace = " \t\n\r\0\x0B,.";
        $text = '';

        if (! isset($values[$ref])) {
            return $text;
        }

        if (isset($values[$ref]) && isset($values[$ref]['template'])) {
            $text = $values[$ref]['template'];
        }

        if ($text == '') {
            if ($referenceStyle === 'mla') {
                $text = '~author~, ~title~, ~part~, ~publisher~, ~year~, ~location~, ~uri~, ~code~';
            } else {
                $text = '~title~, ~part~, ~author~, ~location~, ~year~, ~publisher~, ~code~';
            }
        }

        if ($text != '') {
            foreach ($tags as $tag => $val) {
                if ($values[$ref][$val] == '') {
                    $replaceTag = $tag;

                    $pos = strpos($text, $tag);
                    $len = strlen($tag);

                    if ($pos > 0) {
                        $prevWhiteSpace = $text[$pos - 1];
                        if (strpos($charsUsedForSpace, $prevWhiteSpace) !== false && $pos) {
                            $replaceTag = $text[$pos - 1] . $replaceTag;
                        }
                    }

                    $endPos = $pos + $len;
                    if ($endPos < strlen($text)) {
                        $postWhiteSpace = $text[$endPos];
                        if (strpos($charsUsedForSpace, $postWhiteSpace) !== false && $pos) {
                            $replaceTag .= $text[$endPos];
                        }
                    }

                    $text = str_replace($replaceTag, $values[$ref][$val], $text);
                } else {
                    $text = str_replace($tag, $values[$ref][$val], $text);
                }
            }
            $text = trim($text, $charsUsedForSpace);
        }

        return $text;
    }

    /**
     * Trim a bibliographic code, removing the chars used as space and as separator (,)
     *
     * @param string $code the code to trim
     * @return string clean code
     */
    public static function trimBibliographicCode($code)
    {
        return trim($code, " \t\n\r\0\x0B,");
    }

    /**
     * Extract all the bibliographic codes, for all calls to ADDREFERENCE in the text
     *
     * @param string $text the text to process
     * @param bool $removeDuplicates will remove bibliographic codes duplicated in the result
     * @return array the list of bibliographic codes found
     */
    public static function extractBibliographicCodesFromText($text, $removeDuplicates = false)
    {
        $regex = "/{ADDREFERENCE\(?\ ?biblio_code=\"(.*)\"\)?}.*({ADDREFERENCE})?/siU";
        preg_match_all($regex, $text, $matches);

        $result = [];

        foreach ($matches[1] as $match) {
            $codeList = explode(',', $match);
            foreach ($codeList as $code) {
                $cleanCode = self::trimBibliographicCode($code);
                if (! empty($cleanCode)) {
                    $result[] = $cleanCode;
                }
            }
        }

        if ($removeDuplicates) {
            $result = array_unique($result);
        }

        return $result;
    }
}