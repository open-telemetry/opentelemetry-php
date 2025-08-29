<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exemplar;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

final class NoopReservoir implements ExemplarReservoirInterface
{
    /**
     * @psalm-param 0 $index
     * @psalm-param 5 $value
     */
    #[\Override]
    public function offer(int $index, int $value, AttributesInterface $attributes, ContextInterface $context, int $timestamp): void
    {
        // no-op
    }

    #[\Override]
    public function collect(array $dataPointAttributes): array
    {
        return [];
    }
}
