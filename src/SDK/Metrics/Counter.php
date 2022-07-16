<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\API\Metrics\CounterInterface;
use OpenTelemetry\SDK\Common\Time\ClockInterface;

/**
 * @internal
 */
final class Counter implements CounterInterface
{
    private MetricWriterInterface $writer;
    private ReferenceCounterInterface $referenceCounter;
    private ClockInterface $clock;

    public function __construct(MetricWriterInterface $writer, ReferenceCounterInterface $referenceCounter, ClockInterface $clock)
    {
        $this->writer = $writer;
        $this->referenceCounter = $referenceCounter;
        $this->clock = $clock;

        $this->referenceCounter->acquire();
    }

    public function __destruct()
    {
        $this->referenceCounter->release();
    }

    public function add($amount, iterable $attributes = [], $context = null): void
    {
        $this->writer->record($amount, $attributes, $context, $this->clock->now());
    }
}
