<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tracker\Rule;

use Tiki\Lib\core\Tracker\Rule\Action;
use Tiki\Lib\core\Tracker\Rule\Column;
use Tiki\Lib\core\Tracker\Rule\Operator;
use Tiki\Lib\core\Tracker\Rule\Type;

class Definition
{
    /**
     * Scans lib/core/Tracker/Rule for Action, Operator or Type objects
     * and gets ui-predicate arrays for each by type
     *
     * @return array
     */
    public static function get(): array
    {
        $out = [];
        $definition = [];
        $dirs = array_filter(glob(__DIR__ . '/*'), 'is_dir');

        foreach ($dirs as $dir) {
            $group = basename($dir);

            if (in_array($group, ['Target', 'LogicalType'])) {
                continue;
            }
            $files = array_diff(scandir($dir), ['.', '..', 'index.php']);
            foreach ($files as $file) {
                $class = substr(basename($file), 0, -4);
                if ($class !== $group) {
                    $className = '\\Tiki\\Lib\\core\\Tracker\\Rule\\' . $group . '\\' . $class;
                    $object = new $className();
                    $definition[strtolower($group) . 's'][] = $object;
                }
            }
        }

        /** @var Type\Type $typeObject */
        foreach ($definition['types'] as $typeObject) {
            /** @var Operator\Operator $operator */
            foreach ($definition['operators'] as $operator) {
                if (in_array(get_class($typeObject), $operator->getTypes())) {
                    $typeObject->addOperator($operator);
                }
            }
            /** @var Action\Action $action */
            foreach ($definition['actions'] as $action) {
                if (in_array(get_class($typeObject), $action->getTypes())) {
                    $typeObject->addOperator($action);
                }
            }
        }

        foreach ($definition as $name => $objects) {
            $out[$name] = array_map(
                function ($object) {
                    /** @var Column $object */
                    return $object->get();
                },
                $objects
            );
        }

        return $out;
    }
}
