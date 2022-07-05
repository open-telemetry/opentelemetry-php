<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exemplar;

use function count;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\AttributesFactory;

final class HistogramBucketReservoir implements ExemplarReservoir
{
    private BucketStorage $storage;
    private array $boundaries;

    public function __construct(AttributesFactory $attributes, array $boundaries)
    {
        $this->storage = new BucketStorage($attributes, count($boundaries) + 1);
        $this->boundaries = $boundaries;
    }

    public function offer($index, $value, Attributes $attributes, Context $context, int $timestamp, int $revision): void
    {
        for ($i = 0; $i < count($this->boundaries) && $this->boundaries[$i] < $value; $i++) {
        }
        $this->storage->store($i, $index, $value, $attributes, $context, $timestamp, $revision);
    }

    public function collect(array $dataPointAttributes, int $revision, int $limit): array
    {
        return $this->storage->collect($dataPointAttributes, $revision, $limit);
    }
}
