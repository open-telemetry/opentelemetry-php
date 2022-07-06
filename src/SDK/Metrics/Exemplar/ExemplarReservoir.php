<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exemplar;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\Data\Exemplar;

interface ExemplarReservoir
{
    /**
     * @param int|string $index
     * @param float|int $value
     */
    public function offer($index, $value, AttributesInterface $attributes, Context $context, int $timestamp, int $revision): void;

    /**
     * @param array<AttributesInterface> $dataPointAttributes
     * @return array<list<Exemplar>>
     */
    public function collect(array $dataPointAttributes, int $revision, int $limit): array;
}
