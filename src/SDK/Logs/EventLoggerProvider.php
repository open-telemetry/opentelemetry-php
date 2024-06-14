<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Logs\EventLoggerInterface;

class EventLoggerProvider implements EventLoggerProviderInterface
{
    public function __construct(private readonly LoggerProviderInterface $loggerProvider)
    {
    }

    public function getEventLogger(string $name, ?string $version = null, ?string $schemaUrl = null, iterable $attributes = []): EventLoggerInterface
    {
        return new EventLogger(
            $this->loggerProvider->getLogger($name, $version, $schemaUrl, $attributes),
            Clock::getDefault(),
        );
    }

    public function forceFlush(): bool
    {
        return $this->loggerProvider->forceFlush();
    }
}
