<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/logs/data-model.md#log-and-event-record-definition
 */
class ReadableLogRecord extends LogRecord
{
    private InstrumentationScopeInterface $scope;
    private LoggerSharedState $loggerSharedState;

    public function __construct(InstrumentationScopeInterface $scope, LoggerSharedState $loggerSharedState, LogRecord $logRecord)
    {
        $this->scope = $scope;
        $this->loggerSharedState = $loggerSharedState;

        parent::__construct($logRecord->body);
        $this->timestamp = $logRecord->timestamp;
        $this->observedTimestamp = $logRecord->observedTimestamp;
        $this->context = $logRecord->context;
        $this->severityNumber = $logRecord->severityNumber;
        $this->severityText = $logRecord->severityText;
        $this->attributes = $logRecord->attributes;
    }

    public function getInstrumentationScope(): InstrumentationScopeInterface
    {
        return $this->scope;
    }

    public function getResource(): ResourceInfo
    {
        return $this->loggerSharedState->getResource();
    }

    public function getTimestamp(): ?int
    {
        return $this->timestamp;
    }

    public function getObservedTimestamp(): ?int
    {
        return $this->observedTimestamp;
    }

    public function getContext(): ?ContextInterface
    {
        return $this->context;
    }

    public function getSeverityNumber(): ?int
    {
        return $this->severityNumber;
    }

    public function getSeverityText(): ?string
    {
        return $this->severityText;
    }

    /**
     * @return mixed|null
     */
    public function getBody()
    {
        return $this->body;
    }

    public function getAttributes(): AttributesInterface
    {
        $factory = $this->loggerSharedState->getLogRecordLimits()->getAttributeFactory();
        $builder = $factory->builder($this->attributes);

        return $builder->build();
    }
}
