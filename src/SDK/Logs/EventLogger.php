<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\API\Logs\EventLoggerInterface;
use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\API\Logs\Severity;
use OpenTelemetry\Context\Context;
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

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.32.0/specification/logs/event-sdk.md#emit-event
     */
    public function emit(
        string $name,
        mixed $payload = null,
        ?int $timestamp = null,
        ?ContextInterface $context = null,
        ?Severity $severityNumber = null,
        array $attributes = [],
    ): void {
        $logRecord = new LogRecord();
        $logRecord->setAttributes(['event.name' => $name] + $attributes);
        $logRecord->setBody($payload);
        $logRecord->setTimestamp($timestamp ?? $this->clock->now());
        $logRecord->setContext($context ?? Context::getCurrent());
        $logRecord->setSeverityNumber($severityNumber ?? Severity::INFO);

        $this->logger->emit($logRecord);
    }
}
