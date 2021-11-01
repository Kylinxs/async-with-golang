<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * Class JitFilter_Element
 * @method Laminas\I18n\Filter\Alpha|TikiFilter_Alpha alpha
 * @method Laminas\I18n\Filter\Alnum|TikiFilter_Alnum alnum
 * @method Laminas\Filter\Digits digits
 * @method Laminas\Filter\ToInt int
 * @method Laminas\Filter\StripTags username
 * @method Laminas\Filter\StripTags groupname
 * @method Laminas\Filter\StripTags pagename
 * @method Laminas\Filter\StripTags topicname
 * @method Laminas\Filter\StripTags themename
 * @method Laminas\Filter\StripTags email
 * @method Laminas\Filter\StripTags url
 * @method Laminas\Filter\StripTags text
 * @method Laminas\Filter\StripTags date
 * @method Laminas\Filter\StripTags time
 * @method Laminas\Filter\StripTags datetime
 * @method Laminas\Filter\StripTags striptags
 * @method TikiFilter_Word word
 * @method TikiFilter_PreventXss xss
 * @method TikiFilter_HtmlPurifier purifier
 * @method TikiFilter_WikiContent wikicontent
 * @method TikiFilter_RawUnsafe rawhtml_unsafe
 * @method TikiFilter_None none
 * @method string lang
 * @method string imgsize
 * @method TikiFilter_AttributeType attribute_type
 * @method bool bool
 */
class JitFilter_Element
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function filter($filter)
    {
        $filter = TikiFilter::get($filter);

        return $filter->filter($this->value);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->filter($name);
    }
}
