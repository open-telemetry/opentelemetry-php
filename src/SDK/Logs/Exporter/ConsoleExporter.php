<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs\Exporter;

use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Common\Future\ErrorFuture;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\ReadableLogRecord;
use OpenTelemetry\SDK\Resource\ResourceInfo;

/**
 * A JSON console exporter for LogRecords. This is only useful for testing; the
 * output is human-readable, and is not compatible with the OTLP format.
 */
class ConsoleExporter implements LogRecordExporterInterface
{
    public function __construct(private readonly TransportInterface $transport)
    {
    }

    /**
     * @param iterable<mixed, ReadableLogRecord> $batch
     */
    #[\Override]
    public function export(iterable $batch, ?CancellationInterface $cancellation = null): FutureInterface
    {
        $resource = null;
        $scopes = [];
        foreach ($batch as $record) {
            if (!$resource) {
                $resource = $this->convertResource($record->getResource());
            }
            $key = $this->scopeKey($record->getInstrumentationScope());
            if (!array_key_exists($key, $scopes)) {
                $scopes[$key] = $this->convertInstrumentationScope($record->getInstrumentationScope());
            }
            $scopes[$key]['logs'][] = $this->convertLogRecord($record);
        }
        $output = [
            'resource' => $resource,
            'scopes' => array_values($scopes),
        ];
        $payload = json_encode($output, JSON_PRETTY_PRINT);
        if ($payload === false) {
            return new ErrorFuture(
                new \RuntimeException('Failed to encode log records to JSON: ' . json_last_error_msg())
            );
        }
        $this->transport->send($payload);

        return new CompletedFuture(true);
    }

    #[\Override]
    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return true;
    }

    #[\Override]
    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        return true;
    }
    private function convertLogRecord(ReadableLogRecord $record): array
    {
        $spanContext = $record->getSpanContext();

        return [
            'timestamp' => $record->getTimestamp(),
            'observed_timestamp' => $record->getObservedTimestamp(),
            'severity_number' => $record->getSeverityNumber(),
            'severity_text' => $record->getSeverityText(),
            'body' => $record->getBody(),
            'trace_id' => $spanContext !== null ? $spanContext->getTraceId() : '',
            'span_id' => $spanContext !== null ? $spanContext->getSpanId() : '',
            'trace_flags' => $spanContext !== null ? $spanContext->getTraceFlags() : null,
            'attributes' => $record->getAttributes()->toArray(),
            'dropped_attributes_count' => $record->getAttributes()->getDroppedAttributesCount(),
        ];
    }

    private function convertResource(ResourceInfo $resource): array
    {
        return [
            'attributes' => $resource->getAttributes()->toArray(),
            'dropped_attributes_count' => $resource->getAttributes()->getDroppedAttributesCount(),
        ];
    }

    private function scopeKey(InstrumentationScopeInterface $scope): string
    {
        return serialize([$scope->getName(), $scope->getVersion(), $scope->getSchemaUrl(), $scope->getAttributes()]);
    }

    private function convertInstrumentationScope(InstrumentationScopeInterface $scope): array
    {
        return [
            'name' => $scope->getName(),
            'version' => $scope->getVersion(),
            'attributes' => $scope->getAttributes()->toArray(),
            'dropped_attributes_count' => $scope->getAttributes()->getDroppedAttributesCount(),
            'schema_url' => $scope->getSchemaUrl(),
            'logs' => [],
        ];
    }
}
