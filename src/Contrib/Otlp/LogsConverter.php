<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use Opentelemetry\Proto\Collector\Logs\V1\ExportLogsServiceRequest;
use Opentelemetry\Proto\Common\V1\InstrumentationScope;
use Opentelemetry\Proto\Common\V1\KeyValue;
use Opentelemetry\Proto\Logs\V1\LogRecord;
use Opentelemetry\Proto\Logs\V1\ResourceLogs;
use Opentelemetry\Proto\Logs\V1\ScopeLogs;
use Opentelemetry\Proto\Resource\V1\Resource as Resource_;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Logs\ReadableLogRecord;
use OpenTelemetry\SDK\Resource\ResourceInfo;

class LogsConverter
{
    /**
     * @param iterable<ReadableLogRecord> $logs
     * @psalm-suppress InvalidArgument
     */
    public function convert(iterable $logs): ExportLogsServiceRequest
    {
        $pExportLogsServiceRequest = new ExportLogsServiceRequest();
        $scopeLogs = new ScopeLogs();
        $resourceLogs = new ResourceLogs();

        foreach ($logs as $log) {
            if ($resourceLogs->getResource() === null) {
                $resourceLogs->setResource($this->convertResource($log->getResource()));
            }
            if ($scopeLogs->getScope() === null) {
                $scopeLogs->setScope($this->convertInstrumentationScope($log->getInstrumentationScope()));
            }
            $scopeLogs->getLogRecords()[] = $this->convertLogRecord($log);
        }
        $resourceLogs->getScopeLogs()[] = $scopeLogs;

        $pExportLogsServiceRequest->getResourceLogs()[] = $resourceLogs;

        return $pExportLogsServiceRequest;
    }

    private function convertLogRecord(ReadableLogRecord $record): LogRecord
    {
        $pLogRecord = new LogRecord();
        $pLogRecord->setBody(AttributesConverter::convertAnyValue($record->getBody())); //@todo don't use attributes converter
        $pLogRecord->setTimeUnixNano($record->getTimestamp());
        $record->getObservedTimestamp() && $pLogRecord->setObservedTimeUnixNano($record->getObservedTimestamp());
        $pLogRecord->setTraceId($record->getTraceId());
        $pLogRecord->setSpanId($record->getSpanId());
        $pLogRecord->setFlags($record->getTraceFlags());
        $pLogRecord->setSeverityNumber($record->getSeverityNumber());
        $pLogRecord->setSeverityText($record->getSeverityText());
        $this->setAttributes($pLogRecord, $record->getAttributes());
        $pLogRecord->setDroppedAttributesCount($record->getAttributes()->getDroppedAttributesCount());

        return $pLogRecord;
    }

    private function convertInstrumentationScope(InstrumentationScopeInterface $instrumentationScope): InstrumentationScope
    {
        $pInstrumentationScope = new InstrumentationScope();
        $pInstrumentationScope->setName($instrumentationScope->getName());
        $pInstrumentationScope->setVersion((string) $instrumentationScope->getVersion());
        $this->setAttributes($pInstrumentationScope, $instrumentationScope->getAttributes());
        $pInstrumentationScope->setDroppedAttributesCount($instrumentationScope->getAttributes()->getDroppedAttributesCount());

        return $pInstrumentationScope;
    }

    private function convertResource(ResourceInfo $resource): Resource_
    {
        $pResource = new Resource_();
        $this->setAttributes($pResource, $resource->getAttributes());
        $pResource->setDroppedAttributesCount($resource->getAttributes()->getDroppedAttributesCount());

        return $pResource;
    }

    /**
     * @param Resource_|LogRecord|InstrumentationScope $pElement
     */
    private function setAttributes($pElement, AttributesInterface $attributes): void
    {
        foreach ($attributes as $key => $value) {
            /** @psalm-suppress InvalidArgument */
            $pElement->getAttributes()[] = (new KeyValue())
                ->setKey($key)
                ->setValue(AttributesConverter::convertAnyValue($value));
        }
        $pElement->setDroppedAttributesCount($attributes->getDroppedAttributesCount());
    }
}
