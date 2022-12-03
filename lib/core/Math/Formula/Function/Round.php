
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Math_Formula_Function_Round extends Math_Formula_Function
{
    public function evaluate($element)
    {
        $elements = [];

        if (count($element) > 2) {
            $this->error(tr('Too many arguments on round.'));
        }

        foreach ($element as $child) {
            $elements[] = $this->evaluateChild($child);
        }

        $number = array_shift($elements);
        $decimals = (int)array_shift($elements);

        if ($number instanceof Math_Formula_Applicator) {
            return $number->round($decimals);
        } else {
            return round($number, $decimals);
        }
    }
}