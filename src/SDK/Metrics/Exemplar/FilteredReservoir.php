<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exemplar;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

/**
 * The exemplar spec is not yet stable, and can change at any time.
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/sdk.md#exemplar
 */
final class FilteredReservoir implements ExemplarReservoirInterface
{
    private ExemplarReservoirInterface $reservoir;
    private ExemplarFilterInterface $filter;

    public function __construct(ExemplarReservoirInterface $reservoir, ExemplarFilterInterface $filter)
    {
        $this->reservoir = $reservoir;
        $this->filter = $filter;
    }

    public function offer($index, $value, AttributesInterface $attributes, ContextInterface $context, int $timestamp): void
    {
        if ($this->filter->accepts($value, $attributes, $context, $timestamp)) {
            $this->reservoir->offer($index, $value, $attributes, $context, $timestamp);
        }
    }

    public function collect(array $dataPointAttributes): array
    {
        return $this->reservoir->collect($dataPointAttributes);
    }
}
