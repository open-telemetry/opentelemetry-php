<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\Context\ContextInterface;

/**
 * @internal
 */
interface MetricWriterInterface
{
    /**
     * @param float|int $value
     * @param ContextInterface|false|null $context
     */
    public function record($value, iterable $attributes, $context, int $timestamp): void;
}
