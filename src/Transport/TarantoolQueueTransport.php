<?php

declare(strict_types=1);

namespace OpenTelemetry\Transport;

use OpenTelemetry\Transport;
use Tarantool\Queue\Queue;
use Exception;

class TarantoolQueueTransport implements Transport
{
    private $queue;

    public function write(array $data) : bool
    {
        return $this->getQueue()->put($data) ? true : false;
    }

    public function getQueue() : Queue
    {
        if (!$this->queue) {
            throw new Exception("Queue should be set");
        }
        return $this->queue;
    }

    public function setQueue(Queue $queue) : self
    {
        $this->queue = $queue;
        return $this;
    }
}