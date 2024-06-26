<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\API\Logs as API;

class NoopEventLoggerProvider extends API\NoopEventLoggerProvider implements EventLoggerProviderInterface
{
    public static function getInstance(): self
    {
        static $instance;

        return $instance ??= new self();
    }

    public function forceFlush(): bool
    {
        return true;
    }
}
