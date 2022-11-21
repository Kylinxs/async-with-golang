<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Tiki_Event_Customizer_RuleSet
{
    private $parser;
    private $rules = [];

    public function __construct()
    {
        $this->parser = new Math_Formula_Parser();
    }

    public function addRule($function)
    {
        $this->rules[] = $this->parser->parse($function);
    }

    public function getRules()
    {
        return $this->rules;
    }
}
