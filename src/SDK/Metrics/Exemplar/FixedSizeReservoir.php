<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exemplar;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use function random_int;

final class FixedSizeReservoir implements ExemplarReservoirInterface
{
    private readonly BucketStorage $storage;
    private readonly int $size;
    private int $measurements = 0;

    public function __construct(int $size = 4)
    {
        $this->storage = new BucketStorage($size);
        $this->size = $size;
    }

    #[\Override]
    public function offer($index, $value, AttributesInterface $attributes, ContextInterface $context, int $timestamp): void
    {
        $bucket = random_int(0, $this->measurements);
        $this->measurements++;
        if ($bucket < $this->size) {
            $this->storage->store($bucket, $index, $value, $attributes, $context, $timestamp);
        }
    }

    #[\Override]
    public function collect(array $dataPointAttributes): array
    {
        $this->measurements = 0;

        return $this->storage->collect($dataPointAttributes);
    }
}
