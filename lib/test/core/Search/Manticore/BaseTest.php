
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Search\Manticore;

class BaseTest extends \Search_Index_BaseTest
{
    use IndexBuilder;

    protected function setUp(): void
    {
        $this->index = $this->getIndex();
        $this->index->destroy();

        $this->populate($this->index);
    }

    protected function tearDown(): void
    {
        if ($this->index) {
            $this->index->destroy();
        }
    }

    protected function assertResultCount($count, $filterMethod, $argument)
    {
        if ($filterMethod == 'filterTextRange') {
            $this->addWarning('Manticore does not support text range searches.');
            return;
        } else {
            $arguments = func_get_args();
            $arguments = array_slice($arguments, 2);
            return parent::assertResultCount($count, $filterMethod, ...$arguments);
        }
    }
}