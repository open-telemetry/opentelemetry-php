<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurable;

interface TracerProviderInterface extends API\TracerProviderInterface, Configurable
{
    public function forceFlush(?CancellationInterface $cancellation = null): bool;

    public function shutdown(?CancellationInterface $cancellation = null): bool;
}
