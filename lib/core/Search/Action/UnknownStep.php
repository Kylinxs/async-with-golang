
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Search_Action_UnknownStep implements Search_Action_Step
{
    private $actionName;

    public function __construct($action = null)
    {
        $this->actionName = $action;
    }

    public function getFields()
    {
        return [];
    }

    public function validate(array $entry)
    {
        throw new Search_Action_Exception(tr('Unknown search action step: %0', $this->actionName));
    }

    public function execute(array $entry)
    {
    }

    public function requiresInput()
    {
        return false;
    }

    public function getName()
    {
        return $this->actionName;
    }
}