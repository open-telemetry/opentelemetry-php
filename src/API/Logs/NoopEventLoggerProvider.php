<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

class NoopEventLoggerProvider implements EventLoggerProviderInterface
{
    public static function getInstance(): self
    {
        static $instance;

        return $instance ??= new self(NoopLoggerProvider::getInstance());
    }

    public function getEventLogger(
        string $name,
        ?string $version = null,
        ?string $schemaUrl = null,
        iterable $attributes = [],
    ): EventLoggerInterface {
        return NoopEventLogger::instance();
    }

    // @phpstan-ignore-next-line
    public function __construct(private readonly LoggerProviderInterface $loggerProvider)
    {
    }
}
