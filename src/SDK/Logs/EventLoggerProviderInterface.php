<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

/**
 * @phan-suppress PhanDeprecatedInterface
 */
interface EventLoggerProviderInterface extends API\EventLoggerProviderInterface
{
    public function forceFlush(): bool;
}
