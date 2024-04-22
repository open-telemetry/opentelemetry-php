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
    private readonly ProtobufSerializer $serializer;

    public function __construct(?ProtobufSerializer $serializer = null)
    {
        $this->serializer = $serializer ?? ProtobufSerializer::getDefault();
    }

    /**
     * @param iterable<ReadableLogRecord> $logs
     * @psalm-suppress InvalidArgument
     */
    public function convert(iterable $logs): ExportLogsServiceRequest
    {
        $pExportLogsServiceRequest = new ExportLogsServiceRequest();
        $scopeLogs = [];
        $resourceLogs = [];
        $resourceCache = [];
        $scopeCache = [];

        foreach ($logs as $log) {
            $resource = $log->getResource();
            $instrumentationScope = $log->getInstrumentationScope();

            $resourceId = $resourceCache[spl_object_id($resource)] ??= serialize([
                $resource->getSchemaUrl(),
                $resource->getAttributes()->toArray(),
                $resource->getAttributes()->getDroppedAttributesCount(),
            ]);
            $instrumentationScopeId = $scopeCache[spl_object_id($instrumentationScope)] ??= serialize([
                $instrumentationScope->getName(),
                $instrumentationScope->getVersion(),
                $instrumentationScope->getSchemaUrl(),
                $instrumentationScope->getAttributes()->toArray(),
                $instrumentationScope->getAttributes()->getDroppedAttributesCount(),
            ]);

            if (($pResourceLogs = $resourceLogs[$resourceId] ?? null) === null) {
                /** @psalm-suppress InvalidArgument */
                $pExportLogsServiceRequest->getResourceLogs()[]
                    = $resourceLogs[$resourceId]
                    = $pResourceLogs
                    = $this->convertResourceLogs($resource);
            }

            if (($pScopeLogs = $scopeLogs[$resourceId][$instrumentationScopeId] ?? null) === null) {
                $pResourceLogs->getScopeLogs()[]
                    = $scopeLogs[$resourceId][$instrumentationScopeId]
                    = $pScopeLogs
                    = $this->convertInstrumentationScope($instrumentationScope);
            }

            $pScopeLogs->getLogRecords()[] = $this->convertLogRecord($log);
        }

        return $pExportLogsServiceRequest;
    }

    private function convertLogRecord(ReadableLogRecord $record): LogRecord
    {
        $pLogRecord = new LogRecord();
        $pLogRecord->setBody(AttributesConverter::convertAnyValue($record->getBody()));
        $pLogRecord->setTimeUnixNano($record->getTimestamp() ?? 0);
        $pLogRecord->setObservedTimeUnixNano($record->getObservedTimestamp() ?? 0);
        $spanContext = $record->getSpanContext();
        if ($spanContext !== null && $spanContext->isValid()) {
            $pLogRecord->setTraceId($this->serializer->serializeTraceId($spanContext->getTraceIdBinary()));
            $pLogRecord->setSpanId($this->serializer->serializeSpanId($spanContext->getSpanIdBinary()));
            $pLogRecord->setFlags($spanContext->getTraceFlags());
        }
        $severityNumber = $record->getSeverityNumber();
        if ($severityNumber !== null) {
            $pLogRecord->setSeverityNumber($severityNumber);
        }
        $severityText = $record->getSeverityText();
        if ($severityText !== null) {
            $pLogRecord->setSeverityText($severityText);
        }
        $this->setAttributes($pLogRecord, $record->getAttributes());
        $pLogRecord->setDroppedAttributesCount($record->getAttributes()->getDroppedAttributesCount());

        return $pLogRecord;
    }

    private function convertInstrumentationScope(InstrumentationScopeInterface $instrumentationScope): ScopeLogs
    {
        $pScopeLogs = new ScopeLogs();
        $pInstrumentationScope = new InstrumentationScope();
        $pInstrumentationScope->setName($instrumentationScope->getName());
        $pInstrumentationScope->setVersion((string) $instrumentationScope->getVersion());
        $this->setAttributes($pInstrumentationScope, $instrumentationScope->getAttributes());
        $pInstrumentationScope->setDroppedAttributesCount($instrumentationScope->getAttributes()->getDroppedAttributesCount());
        $pScopeLogs->setScope($pInstrumentationScope);
        $pScopeLogs->setSchemaUrl((string) $instrumentationScope->getSchemaUrl());

        return $pScopeLogs;
    }

    private function convertResourceLogs(ResourceInfo $resource): ResourceLogs
    {
        $pResourceLogs = new ResourceLogs();
        $pResource = new Resource_();
        $this->setAttributes($pResource, $resource->getAttributes());
        $pResource->setDroppedAttributesCount($resource->getAttributes()->getDroppedAttributesCount());
        $pResourceLogs->setResource($pResource);

        return $pResourceLogs;
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
