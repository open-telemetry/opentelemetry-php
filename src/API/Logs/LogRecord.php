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

    public function __construct($body = null)
    {
        $this->body = $body;
    }

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

    public function setSeverityNumber(int $severityNumber): self
    {
        $this->severityNumber = $severityNumber;

        return $this;
    }

    public function setSeverityText(string $severityText): self
    {
        $this->severityText = $severityText;

        return $this;
    }

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

    public function setBody($body = null): self
    {
        $this->body = $body;

        return $this;
    }

    public function setObservedTimestamp(int $observedTimestamp = null): self
    {
        $this->observedTimestamp = $observedTimestamp;

        return $this;
    }
}
