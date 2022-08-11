<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Stream;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

/**
 * @internal
 *
 * @template T
 */
final class Metric
{

    /**
     * @var array<AttributesInterface>
     */
    public array $attributes;
    /**
     * @var array<T>
     */
    public array $summaries;
    public int $timestamp;
    public int $revision;
    /**
     * @param array<AttributesInterface> $attributes
     * @param array<T> $summaries
     */
    public function __construct(array $attributes, array $summaries, int $timestamp, int $revision)
    {
        $this->attributes = $attributes;
        $this->summaries = $summaries;
        $this->timestamp = $timestamp;
        $this->revision = $revision;
    }
}
