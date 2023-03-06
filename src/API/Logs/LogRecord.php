<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;

class LogRecord
{
    public const NANOS_PER_MILLISECOND = 1_000_000;

    protected int $timestamp;
    protected ?int $observedTimestamp = null;
    protected ?ContextInterface $context = null;
    protected ?int $severityNumber = null;
    protected ?string $severityText = null;
    protected $body = null;
    protected array $attributes = [];

    public function __construct($body)
    {
        $this->timestamp = (int) (microtime(true) * self::NANOS_PER_MILLISECOND);
        $this->body = $body;
    }

    public function setObservedTimestamp(int $observedTimestamp): self
    {
        $this->observedTimestamp = $observedTimestamp;

        return $this;
    }

    public function setContext(ContextInterface $context): self
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

    public function setAttribute(string $name, string $value): self
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    public function toLogRecordData(): LogRecordData
    {
        $spanContext = Span::fromContext($this->context ?? Context::getCurrent())->getContext();

        //@todo improve how this is stored
        $data = [
            'timestamp' => $this->timestamp,
            'observed_timestamp' => $this->observedTimestamp,
            'severity_number' => $this->severityNumber,
            'severity_text' => $this->severityText,
            'body' => $this->body,
            'attributes' => $this->attributes,
        ];
        if ($spanContext->isValid()) {
            $data['trace_id'] = $spanContext->getTraceId();
            $data['span_id'] = $spanContext->getSpanId();
            $data['trace_flags'] = $spanContext->getTraceFlags();
        }

        return new LogRecordData($data);
    }
}
