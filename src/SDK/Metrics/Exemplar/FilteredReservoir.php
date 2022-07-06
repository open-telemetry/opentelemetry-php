<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exemplar;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

final class FilteredReservoir implements ExemplarReservoir
{
    private ExemplarReservoir $reservoir;
    private ExemplarFilter $filter;

    public function __construct(ExemplarReservoir $reservoir, ExemplarFilter $filter)
    {
        $this->reservoir = $reservoir;
        $this->filter = $filter;
    }

    public function offer($index, $value, AttributesInterface $attributes, Context $context, int $timestamp, int $revision): void
    {
        if ($this->filter->accepts($value, $attributes, $context, $timestamp)) {
            $this->reservoir->offer($index, $value, $attributes, $context, $timestamp, $revision);
        }
    }

    public function collect(array $dataPointAttributes, int $revision, int $limit): array
    {
        return $this->reservoir->collect($dataPointAttributes, $revision, $limit);
    }
}
