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
     * @param array<Attributes> $attributes
     * @param array<T> $summaries
     */
    public function __construct(
        public array $attributes,
        public array $summaries,
        public int $timestamp,
        public int $revision,
    ) {
    }
}
