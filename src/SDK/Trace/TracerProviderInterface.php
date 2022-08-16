<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;

interface TracerProviderInterface extends API\TracerProviderInterface
{
    public function forceFlush(?CancellationInterface $cancellation = null): bool;

    public function shutdown(?CancellationInterface $cancellation = null): bool;
}
