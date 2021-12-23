<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\Exemplar;

use OpenTelemetry\Context;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\Metrics\Data\Exemplar;

interface ExemplarReservoir {

    public function offer(int|string $index, float|int $value, Attributes $attributes, Context $context, int $timestamp): void;

    /**
     * @param array<Attributes> $dataPointAttributes
     * @return array<list<Exemplar>>
     */
    public function collect(array $dataPointAttributes, ?int $revision = null): array;
}
