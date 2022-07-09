<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

interface ReferenceCounterInterface
{
    public function acquire(bool $persistent = false): void;

    public function release(): void;
}
