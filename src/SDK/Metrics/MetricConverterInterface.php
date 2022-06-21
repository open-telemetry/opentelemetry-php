<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

interface MetricConverterInterface
{
    public function convert(iterable $metrics): array;
}
