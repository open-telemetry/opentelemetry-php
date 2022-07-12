<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use ArrayAccess;

final class MeterInstruments
{
    public ?int $startTimestamp = null;
    /**
     * @var array<string, array<string, array{MetricObserverInterface, ReferenceCounterInterface, ArrayAccess}>>
     */
    public array $observers = [];
    /**
     * @var array<string, array<string, array{MetricWriterInterface, ReferenceCounterInterface}>>
     */
    public array $writers = [];
}
