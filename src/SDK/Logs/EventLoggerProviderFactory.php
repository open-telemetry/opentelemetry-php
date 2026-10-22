<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\SDK\Sdk;

/**
 * @deprecated
 */
class EventLoggerProviderFactory
{
    /**
     * @phan-suppress PhanDeprecatedClass
     */
    public function create(LoggerProviderInterface $loggerProvider): EventLoggerProviderInterface
    {
        if (Sdk::isDisabled()) {
            return NoopEventLoggerProvider::getInstance();
        }

        return new EventLoggerProvider($loggerProvider);
    }
}
