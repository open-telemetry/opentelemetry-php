<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\API\Metrics\Histogram;
use OpenTelemetry\SDK\Common\Time\ClockInterface;

final class SdkHistogram implements Histogram
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

    public function record($amount, iterable $attributes = [], $context = null): void
    {
        $this->writer->record($amount, $attributes, $context, $this->clock->now());
    }
}
