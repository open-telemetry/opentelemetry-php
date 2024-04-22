<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exemplar;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

/**
 * @internal
 */
final class BucketEntry
{
    public int|string $index;
    public float|int $value;
    public int $timestamp;
    public AttributesInterface $attributes;
    public ?string $traceId = null;
    public ?string $spanId = null;
}
