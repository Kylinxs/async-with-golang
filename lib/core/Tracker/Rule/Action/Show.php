<?php

namespace Tiki\Lib\core\Tracker\Rule\Action;

use Tiki\Lib\core\Tracker\Rule\Type\Field;
use Tiki\Lib\core\Tracker\Rule\Type\Nothing;

class Show extends Action
{
    public function __construct()
    {
        parent::__construct(tr('Show'), Nothing::class, '.show()', [Field::class]);
    }
}
