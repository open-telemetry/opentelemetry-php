<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\Exemplar;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\AttributesFactory;
use function random_int;

final class FixedSizeReservoir implements ExemplarReservoir {

    private BucketStorage $storage;
    private int $size;
    private int $measurements;

    public function __construct(AttributesFactory $attributes, int $size) {
        $this->storage = new BucketStorage($attributes, $size);
        $this->size = $size;
        $this->measurements = 0;
    }

    public function offer(int|string $index, float|int $value, Attributes $attributes, Context $context, int $timestamp, int $revision): void {
        $bucket = random_int(0, $this->measurements);
        $this->measurements++;
        if ($bucket < $this->size) {
            $this->storage->store($bucket, $index, $value, $attributes, $context, $timestamp, $revision);
        }
    }

    public function collect(array $dataPointAttributes, int $revision, int $limit): array {
        $this->measurements = 0;
        return $this->storage->collect($dataPointAttributes, $revision, $limit);
    }
}
