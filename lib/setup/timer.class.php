<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class timer
{
    public $timer;

    public function parseMicro($micro)
    {
        list($micro, $sec) = explode(' ', microtime());

        return $sec + $micro;
    }

    public function start($timer = 'default', $restart = false)
    {
        //if (isset($this->timer[$timer]) && !$restart) {
            // report error - timer already exists
        //}
        $this->timer[$timer] = $this->parseMicro(microtime());
    }

    public function stop($timer = 'default')
    {
        $result = $this->elapsed($timer);
        unset($this->timer[$timer]);
        return $result;
    }

    public function elapsed($timer = 'default')
    {
        return $this->parseMicro(microtime()) - $this->timer[$timer];
    }
}
