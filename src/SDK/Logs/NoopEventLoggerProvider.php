<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\API\Logs as API;

/**
 * @deprecated
 * @phan-suppress PhanDeprecatedInterface
 * @phan-suppress PhanDeprecatedClass
 */
class NoopEventLoggerProvider extends API\NoopEventLoggerProvider implements EventLoggerProviderInterface
{
    /**
     * @phan-suppress PhanDeprecatedClass
     */
    #[\Override]
    public static function getInstance(): self
    {
        static $instance;

        return $instance ??= new self();
    }

    #[\Override]
    public function forceFlush(): bool
    {
        return true;
    }
}
