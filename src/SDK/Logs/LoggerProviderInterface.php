<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\API\Logs as API;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurable;

interface LoggerProviderInterface extends API\LoggerProviderInterface, Configurable
{
    public function shutdown(): bool;
    public function forceFlush(): bool;
}
