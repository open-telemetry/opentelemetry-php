<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Stream;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\Data\Exemplar;

/**
 * @internal
 *
 * @template T
 */
final class Metric
{
    /**
     * @param array<AttributesInterface> $attributes
     * @param array<T> $summaries
     * @param array<Exemplar> $exemplars
     */
    public function __construct(
        public array $attributes,
        public array $summaries,
        public int $timestamp,
        public array $exemplars = [],
    ) {
    }
}
