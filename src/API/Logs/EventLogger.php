<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

class EventLogger implements EventLoggerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private string $domain
    ) {
    }

    public function logEvent(string $eventName, LogRecord $logRecord): void
    {
        $logRecord->setAttributes([
            'event.name' => $eventName,
            'event.domain' => $this->domain,
        ]);
        $this->logger->emit($logRecord);
    }
}
