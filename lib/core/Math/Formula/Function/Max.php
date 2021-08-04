
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Math_Formula_Function_Max extends Math_Formula_Function
{
    public function evaluate($element)
    {
        $out = $this->evaluateChild($element[0]);

        foreach ($element as $child) {
            $evaluated = $this->evaluateChild($child);
            if ($out instanceof Math_Formula_Applicator) {
                if ($out->lessThan($evaluated)) {
                    $out = $evaluated;
                }
            } elseif ($evaluated instanceof Math_Formula_Applicator) {
                if ($evaluated->moreThan($out)) {
                    $out = $evaluated;
                }
            } else {
                $out = max($out, $evaluated);
            }
        }

        return $out;
    }
}