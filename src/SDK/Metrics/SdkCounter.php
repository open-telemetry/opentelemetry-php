<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\API\Metrics\Counter;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Clock;

final class SdkCounter implements Counter
{
    private MetricWriter $writer;
    private ReferenceCounter $referenceCounter;
    private Clock $clock;

    public function __construct(MetricWriter $writer, ReferenceCounter $referenceCounter, Clock $clock)
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

    public function add(float|int $amount, iterable $attributes = [], Context|false|null $context = null): void
    {
        $this->writer->record($amount, $attributes, $context, $this->clock->nanotime());
    }
}
