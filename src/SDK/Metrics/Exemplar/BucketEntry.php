<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exemplar;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

/**
 * @internal
 */
final class BucketEntry
{
    /**
     * @var int|string
     */
    public $index;
    /**
     * @var float|int
     */
    public $value;
    public int $timestamp;
    public AttributesInterface $attributes;
    public ?string $traceId = null;
    public ?string $spanId = null;
}
