<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

/**
 * @deprecated
 * @phan-suppress PhanDeprecatedInterface
 */
class NoopEventLoggerProvider implements EventLoggerProviderInterface
{
    /**
     * @phan-suppress PhanDeprecatedClass
     */
    public static function getInstance(): self
    {
        static $instance;

        return $instance ??= new self();
    }

    #[\Override]
    public function getEventLogger(
        string $name,
        ?string $version = null,
        ?string $schemaUrl = null,
        iterable $attributes = [],
    ): EventLoggerInterface {
        return NoopEventLogger::instance();
    }
}
