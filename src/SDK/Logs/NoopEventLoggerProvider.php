<?php

declare(strict_types=1);

namespace OpenTelelemetry\SDK\Logs;

/**
 * @phan-suppress PhanDeprecatedInterface
 */
class NoopEventLoggerProvider extends API\NoopEventLoggerProviderimplementsEventLoggerProviderInterface
{
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
