<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\API\Metrics\UpDownCounter;
use OpenTelemetry\SDK\Common\Time\ClockInterface;

final class SdkUpDownCounter implements UpDownCounter
{
    private MetricWriter $writer;
    private ReferenceCounter $referenceCounter;
    private ClockInterface $clock;

    public function __construct(MetricWriter $writer, ReferenceCounter $referenceCounter, ClockInterface $clock)
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
