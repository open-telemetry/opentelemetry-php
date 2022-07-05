<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Stream;

use OpenTelemetry\SDK\Attributes;

/**
 * @template T
 */
final class Metric
{

    /**
     * @var array<Attributes>
     */
    public array $attributes;
    /**
     * @var array<T>
     */
    public array $summaries;
    public int $timestamp;
    public int $revision;
    /**
     * @param array<Attributes> $attributes
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
