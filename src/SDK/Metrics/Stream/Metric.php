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
     * @var array<AttributesInterface>
     */
    public array $attributes;
    /**
     * @var array<T>
     */
    public array $summaries;
    public int $timestamp;
    /**
     * @var array<Exemplar>
     */
    public array $exemplars;

    /**
     * @param array<AttributesInterface> $attributes
     * @param array<T> $summaries
     * @param array<Exemplar> $exemplars
     */
    public function __construct(array $attributes, array $summaries, int $timestamp, array $exemplars = [])
    {
        $this->attributes = $attributes;
        $this->summaries = $summaries;
        $this->timestamp = $timestamp;
        $this->exemplars = $exemplars;
    }
}
