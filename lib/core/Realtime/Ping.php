<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Realtime;

use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/**
 * Echo/Ping service
 * Send back a response for any incoming message
 */
class Ping implements MessageComponentInterface
{
    public function onOpen(ConnectionInterface $conn)
    {
        // noop
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        if ($msg == 'ping') {
            $from->send('pong');
        } else {
            $from->send('Unrecognized message');
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        // noop
    }

    public function onError(ConnectionInterface $conn, Exception $e)
    {
        // TODO: log
        echo $e->getMessage();
        $conn->close();
    }
}
