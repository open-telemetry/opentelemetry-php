<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

class NoopLoggerProvider implements LoggerProviderInterface
{
    public static function getInstance(): self
    {
        static $instance;

        return $instance ??= new self();
    }

    public function getLogger(string $name, ?string $version = null, ?string $schemaUrl = null, iterable $attributes = []): LoggerInterface
    {
        return NoopLogger::getInstance();
    }
}
