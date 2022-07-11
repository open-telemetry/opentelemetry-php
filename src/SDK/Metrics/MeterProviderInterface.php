<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

interface MeterProviderInterface extends \OpenTelemetry\API\Metrics\MeterProviderInterface
{
    public function shutdown(): bool;

    public function forceFlush(): bool;
}
