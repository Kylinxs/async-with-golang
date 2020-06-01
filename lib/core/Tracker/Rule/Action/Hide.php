
<?php

namespace Tiki\Lib\core\Tracker\Rule\Action;

use Tiki\Lib\core\Tracker\Rule\Type\Field;
use Tiki\Lib\core\Tracker\Rule\Type\Nothing;

class Hide extends Action
{
    public function __construct()
    {
        parent::__construct(tr('Hide'), Nothing::class, '.hide()', [Field::class]);
    }
}