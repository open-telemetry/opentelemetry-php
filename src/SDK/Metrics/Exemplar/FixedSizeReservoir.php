<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exemplar;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use function random_int;

final class FixedSizeReservoir implements ExemplarReservoirInterface
{
    private BucketStorage $storage;
    private int $size;
    private int $measurements = 0;

    public function __construct(AttributesFactoryInterface $attributesFactory, int $size = 4)
    {
        $this->storage = new BucketStorage($attributesFactory, $size);
        $this->size = $size;
    }

    public function offer($index, $value, AttributesInterface $attributes, ContextInterface $context, int $timestamp, int $revision): void
    {
        $bucket = random_int(0, $this->measurements);
        $this->measurements++;
        if ($bucket < $this->size) {
            $this->storage->store($bucket, $index, $value, $attributes, $context, $timestamp, $revision);
        }
    }

    public function collect(array $dataPointAttributes, int $revision, int $limit): array
    {
        $this->measurements = 0;

        return $this->storage->collect($dataPointAttributes, $revision, $limit);
    }
}
