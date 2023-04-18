<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

class EventLogger implements EventLoggerInterface
{
    private LoggerInterface $logger;
    private string $domain;

    public function __construct(LoggerInterface $logger, string $domain)
    {
        $this->logger = $logger;
        $this->domain = $domain;
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
