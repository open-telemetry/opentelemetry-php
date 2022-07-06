<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exemplar;

use function count;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

final class HistogramBucketReservoir implements ExemplarReservoir
{
    private BucketStorage $storage;
    /**
     * @var list<float|int>
     */
    private array $boundaries;

    /**
     * @param list<float|int> $boundaries
     */
    public function __construct(AttributesFactoryInterface $attributesFactory, array $boundaries)
    {
        $this->storage = new BucketStorage($attributesFactory, count($boundaries) + 1);
        $this->boundaries = $boundaries;
    }

    public function offer($index, $value, AttributesInterface $attributes, Context $context, int $timestamp, int $revision): void
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
