<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\API\Logs as API;

interface LoggerProviderInterface extends API\LoggerProviderInterface
{
    public function shutdown(): bool;
    public function forceFlush(): bool;
}
