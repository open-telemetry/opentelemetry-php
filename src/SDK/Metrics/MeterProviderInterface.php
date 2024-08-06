<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Common\InstrumentationScope\Configurable;

interface MeterProviderInterface extends \OpenTelemetry\API\Metrics\MeterProviderInterface, Configurable
{
    public function shutdown(): bool;

    public function forceFlush(): bool;
}
