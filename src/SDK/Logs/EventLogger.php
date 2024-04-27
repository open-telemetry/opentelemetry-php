<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use function microtime;
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
    ) {
    }

    public function emit(
        string $name,
        mixed $payload = null,
        ?int $timestamp = null,
        ?ContextInterface $context = null,
        Severity|int|null $severityNumber = null,
        ?array $attributes = [],
    ): void {
        $logRecord = new LogRecord();
        $attributes += ['event.name' => $name];
        $logRecord->setAttributes($attributes);
        $payload && $logRecord->setBody($payload);
        $logRecord->setTimestamp($timestamp ?? (int) (microtime(true)*LogRecord::NANOS_PER_SECOND));
        $context && $logRecord->setContext($context);
        $logRecord->setSeverityNumber($severityNumber ?? Severity::INFO);

        $this->logger->emit($logRecord);
    }
}
