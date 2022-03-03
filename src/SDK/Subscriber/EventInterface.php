<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Subscriber;

interface EventInterface
{
    public function getObject():array;
}
