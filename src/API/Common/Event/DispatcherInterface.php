<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Common\Event;

use CloudEvents\V1\CloudEventInterface;

interface DispatcherInterface
{
    public function dispatch(CloudEventInterface $event): void;
    public function listen(string $type, callable $listener, int $priority = 0): void;
}
