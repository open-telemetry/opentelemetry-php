<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\API\Logs\EventLoggerInterface;
use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\API\Logs\Severity;
use OpenTelemetry\Context\ContextInterface;

class EventLogger implements EventLoggerInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ClockInterface $clock,
    ) {
    }

    public function emit(
        string $name,
        mixed $payload = null,
        ?int $timestamp = null,
        ?ContextInterface $context = null,
        ?Severity $severityNumber = null,
        iterable $attributes = [],
    ): void {
        $logRecord = new LogRecord();
        $logRecord->setAttribute('event.name', $name);
        $logRecord->setAttributes($attributes);
        $logRecord->setBody($payload);
        $logRecord->setTimestamp($timestamp ?? $this->clock->now());
        $context && $logRecord->setContext($context);
        $logRecord->setSeverityNumber($severityNumber ?? Severity::INFO);

        $this->logger->emit($logRecord);
    }
}
