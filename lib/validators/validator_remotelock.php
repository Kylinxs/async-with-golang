<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function validator_remotelock($input, $parameter = '', $message = '')
{
    parse_str($parameter, $arr);

    if (! isset($arr['trackerId']) || ! isset($arr['itemId']) || $arr['itemId'] < 1) {
        return true;
    }

    return TikiLib::lib('tabular')->validateRemoteUnchanged($arr['trackerId'], $arr['itemId']);
}
