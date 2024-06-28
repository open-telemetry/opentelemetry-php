<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\API\Logs as API;

interface EventLoggerProviderInterface extends API\EventLoggerProviderInterface
{
    public function forceFlush(): bool;
}
