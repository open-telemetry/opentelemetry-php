<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exemplar;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\Data\Exemplar;

interface ExemplarReservoirInterface
{
    /**
     * @param int|string $index
     * @param float|int $value
     */
    public function offer($index, $value, AttributesInterface $attributes, ContextInterface $context, int $timestamp, int $revision): void;

    /**
     * @param array<AttributesInterface> $dataPointAttributes
     * @return array<list<Exemplar>>
     */
    public function collect(array $dataPointAttributes, int $revision, int $limit): array;
}
