<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\Exemplar;

use OpenTelemetry\SDK\Attributes;

/**
 * @internal
 */
final class BucketEntry {

    public int|string $index;
    public float|int $value;
    public int $timestamp;
    public Attributes $attributes;
    public ?string $traceId;
    public ?string $spanId;

    public int $revision;
}
