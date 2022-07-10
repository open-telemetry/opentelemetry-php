<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Trace as API;

interface TracerProviderInterface extends API\TracerProviderInterface
{
    public function forceFlush(): bool;

    public function shutdown(): bool;
}
