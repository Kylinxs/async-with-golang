
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class QueueLib extends TikiDb_Bridge
{
    private $queue;

    public function __construct()
    {
        $this->queue = $this->table('tiki_queue');
    }

    public function pushAll($queue, array $messages)
    {
        foreach ($messages as $message) {
            $this->push($queue, $message);
        }
    }

    public function push($queue, array $message)
    {
        $this->queue->insert(
            [
                'queue' => $queue,
                'timestamp' => TikiLib::lib('tiki')->now,
                'message' => json_encode($message),
            ]
        );
    }

    public function clear($queue)
    {
        $this->queue->deleteMultiple(['queue' => $queue,]);
    }

    public function count($queue)
    {
        return $this->queue->fetchCount(['queue' => $queue,]);
    }

    public function pull($queue, $count = 1)
    {
        $handler = uniqid();

        // Mark entries as in processing
        $this->queue->updateMultiple(
            ['handler' => $handler],
            [
                'queue' => $queue,
                'handler' => null,
            ],
            $count
        );

        // Obtain the marked list
        $messages = $this->queue->fetchColumn('message', ['handler' => $handler,]);

        // Delete from the queue
        $this->queue->deleteMultiple(['handler' => $handler,]);

        // Strip duplicate messages
        $messages = array_unique($messages);
        if (count($messages)) {
            return array_map('json_decode', $messages, array_fill(0, count($messages), true));
        }

        return [];
    }
}