<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Stream;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

/**
 * @internal
 */
interface WritableMetricStreamInterface
{
    /**
     * @param float|int $value
     */
    public function record($value, AttributesInterface $attributes, Context $context, int $timestamp): void;
}
