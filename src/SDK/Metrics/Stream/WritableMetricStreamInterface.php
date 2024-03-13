<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Stream;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

/**
 * @internal
 */
interface WritableMetricStreamInterface
{
    public function record(float|int $value, AttributesInterface $attributes, ContextInterface $context, int $timestamp): void;
}
