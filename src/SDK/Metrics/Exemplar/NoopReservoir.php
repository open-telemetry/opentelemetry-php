<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exemplar;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Attributes;

final class NoopReservoir implements ExemplarReservoir
{
    public function offer(int|string $index, float|int $value, Attributes $attributes, Context $context, int $timestamp, int $revision): void
    {
        // no-op
    }

    public function collect(array $dataPointAttributes, int $revision, int $limit): array
    {
        return [];
    }
}
