<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs\Noop;

use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\LoggerProviderInterface;

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
