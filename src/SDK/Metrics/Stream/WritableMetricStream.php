<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Stream;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Attributes;

interface WritableMetricStream
{
    /**
     * @param float|int $value
     */
    public function record($value, Attributes $attributes, Context $context, int $timestamp): void;
}
