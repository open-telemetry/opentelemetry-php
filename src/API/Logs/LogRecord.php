<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

use OpenTelemetry\Context\ContextInterface;

class LogRecord
{
    public const NANOS_PER_SECOND = 1_000_000_000;

    protected ?int $timestamp = null;
    protected ?int $observedTimestamp = null;
    protected ?ContextInterface $context = null;
    protected int $severityNumber = 0;
    protected ?string $severityText = null;
    protected $body = null;
    protected array $attributes = [];

    /**
     * @param mixed $body
     */
    public function __construct($body = null)
    {
        $this->body = $body;
    }

    /**
     * @param int $timestamp Timestamp, in nanoseconds since the unix epoch, when the event occurred.
     * @see https://opentelemetry.io/docs/reference/specification/logs/data-model/#field-timestamp
     */
    public function setTimestamp(int $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function setContext(?ContextInterface $context = null): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @param int $severityNumber Severity number
     * @see https://opentelemetry.io/docs/reference/specification/logs/data-model/#field-severitynumber
     */
    public function setSeverityNumber(int $severityNumber): self
    {
        $this->severityNumber = $severityNumber;

        return $this;
    }

    /**
     * @param string $severityText Severity text, also known as log level
     * @see https://opentelemetry.io/docs/reference/specification/logs/data-model/#field-severitynumber
     */
    public function setSeverityText(string $severityText): self
    {
        $this->severityText = $severityText;

        return $this;
    }

    /**
     * @param iterable $attributes Additional information about the specific event occurrence.
     * @see https://opentelemetry.io/docs/reference/specification/logs/data-model/#field-attributes
     */
    public function setAttributes(iterable $attributes): self
    {
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }

        return $this;
    }

    public function setAttribute(string $name, $value): self
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @param mixed $body The log record body
     */
    public function setBody($body = null): self
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @param int|null $observedTimestamp Time, in nanoseconds since the unix epoch, when the event was observed by the collection system.
     */
    public function setObservedTimestamp(int $observedTimestamp = null): self
    {
        $this->observedTimestamp = $observedTimestamp;

        return $this;
    }
}
