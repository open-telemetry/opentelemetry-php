<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

final class MeterInstruments
{
    public ?int $startTimestamp = null;
    /**
     * @var array<string, array<string, array{MetricObserverInterface, ReferenceCounterInterface}>>
     */
    public array $observers = [];
    /**
     * @var array<string, array<string, array{MetricWriterInterface, ReferenceCounterInterface}>>
     */
    public array $writers = [];
}
