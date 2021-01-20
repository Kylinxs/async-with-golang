<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Math_Formula_Function_Div extends Math_Formula_Function
{
    public function evaluate($element)
    {
        $elements = [];

        foreach ($element as $child) {
            $evaluatedChild = $this->evaluateChild($child);
            $elements[] = ! empty($evaluatedChild) ? $evaluatedChild : 0;
        }

        $out = array_shift($elements);
        if (! $out instanceof Math_Formula_Applicator) {
            foreach ($elements as $element) {
                if ($element instanceof Math_Formula_Applicator) {
                    $out = $element->clone($out);
                    break;
                }
            }
        }

        foreach ($elements as $element) {
            if ($out instanceof Math_Formula_Applicator) {
                $out = $out->div($element);
            } elseif ($element && is_numeric($out) && is_numeric($element)) {
                $out /= $element;
            } else {
                // suppress error with division by zero only when inspecting formula as it will always have 'zero' data
                if (! $this->suppress_error) {
                    $this->error(tr('Trying to divide non-numeric arguments "%0"', implode(',', array_merge([$out], $elements))));
                }
                return false;
            }
        }

        return $out;
    }
}
