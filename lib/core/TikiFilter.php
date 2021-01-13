<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

use Laminas\Filter\Boolean;
use Laminas\Filter\Digits;
use Laminas\Filter\FilterInterface;
use Laminas\Filter\PregReplace;
use Laminas\Filter\StripTags;
use Laminas\Filter\ToInt;
use Laminas\Filter\ToFloat;

/**
 * Class TikiFilter
 *
 * Just offers a get method to obtain an instance of a Laminas\Filter\FilterInterface implementation, either stock (Zend) or custom.
 * The objects are "filters" in an extended sense. Data is not necessarily just filtered, but can be otherwise altered.
 * For example, special characters can be escaped.
 *
 * FIXME: The filter() method may perform lossy data alteration quietly, which complicates debugging. See https://github.com/zendframework/zend-filter/issues/63
 */
class TikiFilter
{
    /**
     * Provides an object implementing Laminas\Filter\FilterInterface based on the input
     *
     * @param FilterInterface|string $filter        A filter shortcut name, or the filter itself.
     * @return TikiFilter_Alnum|TikiFilter_Alpha|TikiFilter_AttributeType|TikiFilter_HtmlPurifier|TikiFilter_IsoDate|TikiFilter_Lang|TikiFilter_None|TikiFilter_PregFilter|TikiFilter_PreventXss|TikiFilter_RawUnsafe|TikiFilter_RelativeURL|TikiFilter_WikiContent|Boolean|Digits|FilterInterface|PregReplace|StripTags|ToInt
     *
     * @link https://dev.tiki.org/Filtering+Best+Practices
     * @link https://zendframework.github.io/zend-filter/
     */
    public static function get($filter)
    {
        if ($filter instanceof FilterInterface) {
            return $filter;
        }

        /**
         * Filters are listed in order from most strict to least. To select the most optimal filter,
         * choose the first filter on the list that satisfies your requirements.
         *
         * Filters are organized by return type.
         * Each filter has been tested with a string and can be seen under "Test Return" The string is:
         * " :/g.,:|4h&#Δ δ_🍘コン onclick<b><script> "
         */
        switch ($filter) {
            /** Integer return types **/
            case 'int':
                // Test Return 0
                // Transforms a scalar phrase into an integer. eg. '-4 is less than 0' returns -4, while '' returns 0
                return new ToInt();

        /** Float return types **/
            case 'float':
                return new ToFloat();

            /** Boolean return types **/
            case 'bool':
                // Test Return (true)
                // False upon:  false, 0, '0', 0.0, '', array(), null, 'false', 'no', 'n' and php casting equivalent to false.
                // True upon:   Everything else returns true. Case insensitive evaluation.
                return new Boolean([
                    'type'          => Boolean::TYPE_ALL,
                    'translations'  => ['n' => false, 'N' => false]
                ]);

            /** Special Filters (may return mixed types or blank sting upon error) **/
            case 'isodate':
                // Test Return (null)
                // may return null
                return new TikiFilter_IsoDate();
            case 'isodatetime':
                // Test Return (null)
                // may return null
                return new TikiFilter_IsoDate('Y-m-d H:i:s');
            case 'iso8601':
                // Test Return (null)
                // may return null
                return new TikiFilter_IsoDate('Y-m-d\TH:i:s');
            case 'attribute_type':
                // Test Return (false)
                // may return false
                return new TikiFilter_AttributeType();
            case 'lang':
                // Test Return ""
                // may return a blank string
                // Allows values for languages (such as 'en') available 