
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Math_Formula_Function_Subtotal extends Math_Formula_Function
{
    public function evaluate($element)
    {
        $allowed = ['list', 'group', 'aggregate', 'separators', 'formula', 'having', 'transformers'];

        if ($extra = $element->getExtraValues($allowed)) {
            $this->error(tr('Unexpected values: %0', implode(', ', $extra)));
        }

        $list = $element->list;
        if (! $list || count($list) != 1) {
            $this->error(tra('Field must be provided and contain one argument: list'));
        }
        $list = $this->evaluateChild($list[0]);

        $group = $element->group;
        if (! $group || count($group) != 1) {
            $this->error(tra('Field must be provided and contain one argument: group.'));
        }
        $group = $group[0];

        $aggregate = $element->aggregate;
        if (! $aggregate || count($aggregate) < 1) {
            $this->error(tra('Field must be provided and contain at least one argument: aggregate.'));
        }

        $separators = $element->separators;
        if (! $separators || count($separators) != 2) {
            $separators = ["|", "\n"];
        } else {
            $separators = [$this->evaluateChild($separators[0]), $this->evaluateChild($separators[1])];
        }

        $formula = $element->formula;
        if (! $formula) {
            $formula = [];
        }

        $having = $element->having;
        if (! $having) {
            $having = [];
        }

        $transformers = $element->transformers;
        if (! $transformers) {
            $transformers = [];
        }

        $out = [];

        // group values by field
        if (is_array($list)) {
            foreach ($list as $values) {
                if (! isset($values[$group])) {
                    continue;
                }
                $group_value = trim($values[$group]);
                if (! isset($out[$group_value])) {
                    $out[$group_value] = ['group' => $group_value];
                    foreach ($aggregate as $position => $field) {
                        $out[$group_value][$position] = [];
                    }
                }
                foreach ($aggregate as $position => $field) {
                    if (is_string($field) && ! isset($values[$field])) {
                        $value = 0;
                    } else {
                        $value = $this->evaluateChild($field, $values);
                    }
                    $out[$group_value][$position][] = $value;
                }
            }
        }

        // evaluate aggregate function for each field
        foreach ($out as $group_value => $rows) {
            foreach ($aggregate as $position => $field) {
                $simple = false;
                if (is_string($formula[$position])) {
                    $function = str_replace(' ', '', ucwords(str_replace('-', ' ', $formula[$position] ?? 'add')));
                    $class = 'Math_Formula_Function_' . $function;
                    if (class_exists($class)) {
                        $op = new $class();
                        $out[$group_value][$position] = $op->evaluateTemplate($rows[$position], function ($child) {
                            return $child;
                        });
                        $simple = true;
                    }
                }
                if (! $simple) {
                    $out[$group_value][$position] = $this->evaluateChild($formula[$position], ['$1' => $rows[$position]]);
                }
                // process having clause
                if (isset($having[$position])) {
                    $pass = $this->evaluateChild($having[$position], ['$1' => $out[$group_value][$position]]);
                    if (! $pass) {
                        unset($out[$group_value]);
                        break;
                    }
                }
                // transform
                if (isset($transformers[$position])) {
                    $out[$group_value][$position] = $this->evaluateChild($transformers[$position], ['$1' => $out[$group_value][$position]]);
                }
            }
        }

        return implode($separators[1], array_map(function ($row) use ($separators) {
            return implode($separators[0], $row);
        }, $out));
    }
}