<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exemplar;

use OpenTelemetry\SDK\Attributes;

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
    public Attributes $attributes;
    public ?string $traceId;
    public ?string $spanId;

    public int $revision;
}
