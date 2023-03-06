<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\API\Logs\LogRecordData;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;

class ReadableLogRecord
{
    private InstrumentationScopeInterface $scope;
    private LoggerSharedState $loggerSharedState;
    private LogRecordData $logRecordData;

    public function __construct(InstrumentationScopeInterface $scope, LoggerSharedState $loggerSharedState, LogRecordData $logRecordData)
    {
        $this->scope = $scope;
        $this->loggerSharedState = $loggerSharedState;
        $this->logRecordData = $logRecordData;
    }
    public function getInstrumentationScope(): InstrumentationScopeInterface
    {
        return $this->scope;
    }
    public function getResource(): ResourceInfo
    {
        return $this->loggerSharedState->getResource();
    }
    public function getLogRecordData(): LogRecordData
    {
        return $this->logRecordData;
    }

    public function getTimestamp(): int
    {
        return $this->logRecordData->data['timestamp'];
    }

    public function getObservedTimestamp(): int
    {
        return $this->logRecordData->data['observed_timestamp'] ?? 0;
    }
    public function getTraceId(): string
    {
        return $this->logRecordData->data['trace_id'] ?? '';
    }
    public function getSpanId(): string
    {
        return $this->logRecordData->data['span_id'] ?? '';
    }
    public function getTraceFlags(): int
    {
        return $this->logRecordData->data['trace_flags'] ?? 0;
    }
    public function getSeverityNumber(): int
    {
        return $this->logRecordData->data['severity_number'] ?? 0;
    }
    public function getSeverityText(): string
    {
        return $this->logRecordData->data['severity_text'] ?? '';
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->logRecordData->data['body'];
    }

    public function getAttributes(): AttributesInterface
    {
        $factory = $this->loggerSharedState->getLogRecordLimits()->getAttributeFactory();
        $builder = $factory->builder($this->logRecordData->data['attributes']);

        return $builder->build();
    }
}
