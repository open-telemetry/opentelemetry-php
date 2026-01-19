<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\API\Logs\LogRecordBuilderInterface;
use OpenTelemetry\API\Logs\Severity;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Exception\StackTraceFormatter;
use Throwable;

/**
 * @internal
 */
final class LogRecordBuilder implements LogRecordBuilderInterface
{
    private readonly LoggerInterface $logger;
    private readonly LogRecord $logRecord;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->logRecord = new LogRecord();
    }

    #[\Override]
    public function setTimestamp(int $timestamp): LogRecordBuilderInterface
    {
        $this->logRecord->setTimestamp($timestamp);

        return $this;
    }

    #[\Override]
    public function setObservedTimestamp(int $timestamp): LogRecordBuilderInterface
    {
        $this->logRecord->setObservedTimestamp($timestamp);

        return $this;
    }

    #[\Override]
    public function setContext(ContextInterface|false|null $context): LogRecordBuilderInterface
    {
        if ($context === false) {
            $context = Context::getRoot();
        }

        $this->logRecord->setContext($context);

        return $this;
    }

    #[\Override]
    public function setSeverityNumber(int|Severity $severityNumber): LogRecordBuilderInterface
    {
        if ($severityNumber instanceof Severity) {
            $severityNumber = $severityNumber->value;
        }

        $this->logRecord->setSeverityNumber($severityNumber);

        return $this;
    }

    #[\Override]
    public function setSeverityText(string $severityText): LogRecordBuilderInterface
    {
        $this->logRecord->setSeverityText($severityText);

        return $this;
    }

    #[\Override]
    public function setBody(mixed $body): LogRecordBuilderInterface
    {
        $this->logRecord->setBody($body);

        return $this;
    }

    #[\Override]
    public function setAttribute(string $key, mixed $value): LogRecordBuilderInterface
    {
        $this->logRecord->setAttribute($key, $value);

        return $this;
    }

    #[\Override]
    public function setAttributes(iterable $attributes): LogRecordBuilderInterface
    {
        $this->logRecord->setAttributes($attributes);

        return $this;
    }

    #[\Override]
    public function setException(Throwable $exception): LogRecordBuilderInterface
    {
        $this->logRecord->setAttribute('exception.message', $exception->getMessage());
        $this->logRecord->setAttribute('exception.type', $exception::class);
        $this->logRecord->setAttribute('exception.stacktrace', StackTraceFormatter::format($exception));

        return $this;
    }

    #[\Override]
    public function setEventName(string $eventName): LogRecordBuilderInterface
    {
        $this->logRecord->setEventName($eventName);

        return $this;
    }

    #[\Override]
    public function emit(): void
    {
        $this->logger->emit($this->logRecord);
    }
}
