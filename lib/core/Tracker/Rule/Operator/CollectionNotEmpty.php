<?php

namespace Tiki\Lib\core\Tracker\Rule\Operator;

use Tiki\Lib\core\Tracker\Rule\Type\Collection;
use Tiki\Lib\core\Tracker\Rule\Type\Nothing;

class CollectionNotEmpty extends Operator
{
    public function __construct()
    {
        parent::__construct(tr('is not empty'), Nothing::class, '.val().length>0', [Collection::class]);
    }
}
