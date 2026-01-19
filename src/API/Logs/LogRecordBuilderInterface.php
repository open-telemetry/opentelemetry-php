<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

use OpenTelemetry\Context\ContextInterface;
use Throwable;

interface LogRecordBuilderInterface
{
    /**
     * Time when the event occurred measured by the origin clock, i.e. the time at the source.
     *
     * @param int $timestamp nanoseconds since UNIX epoch
     * @see https://opentelemetry.io/docs/specs/otel/logs/data-model/#field-timestamp
     */
    public function setTimestamp(int $timestamp): LogRecordBuilderInterface;

    /**
     * Time when the event was observed by the collection system
     *
     * @param int $timestamp nanoseconds since UNIX epoch
     * @see https://opentelemetry.io/docs/specs/otel/logs/data-model/#field-observedtimestamp
     */
    public function setObservedTimestamp(int $timestamp): LogRecordBuilderInterface;

    /**
     * @param ContextInterface|false|null $context the log context, null to use the current
     *        context, false to use no context
     * @see https://opentelemetry.io/docs/specs/otel/logs/data-model/#trace-context-fields
     */
    public function setContext(ContextInterface|false|null $context): LogRecordBuilderInterface;

    /**
     * Numerical value of the severity.
     *
     * @see https://opentelemetry.io/docs/specs/otel/logs/data-model/#field-severitynumber
     */
    public function setSeverityNumber(int|Severity $severityNumber): LogRecordBuilderInterface;

    /**
     * The original string representation of the severity as it is known at the source.
     *
     * @see https://opentelemetry.io/docs/specs/otel/logs/data-model/#field-severitytext
     */
    public function setSeverityText(string $severityText): LogRecordBuilderInterface;

    /**
     * @see https://opentelemetry.io/docs/specs/otel/logs/data-model/#field-body
     */
    public function setBody(mixed $body): LogRecordBuilderInterface;

    /**
     * @see https://opentelemetry.io/docs/specs/otel/logs/data-model/#field-attributes
     */
    public function setAttribute(string $key, mixed $value): LogRecordBuilderInterface;

    /**
     * @see https://opentelemetry.io/docs/specs/otel/logs/data-model/#field-attributes
     */
    public function setAttributes(iterable $attributes): LogRecordBuilderInterface;

    /**
     * Sets the `exception.message` `exception.type`, and `exception.stacktrace` attributes.
     *
     * @see https://opentelemetry.io/docs/specs/semconv/exceptions/exceptions-logs/#attributes
     */
    public function setException(Throwable $exception): LogRecordBuilderInterface;

    /**
     * @see https://opentelemetry.io/docs/specs/otel/logs/data-model/#field-eventname
     */
    public function setEventName(string $eventName): LogRecordBuilderInterface;

    /**
     * @see https://opentelemetry.io/docs/specs/otel/logs/api/#emit-a-logrecord
     */
    public function emit(): void;
}
