<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs\Exporter;

use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\ReadableLogRecord;
use OpenTelemetry\SDK\Resource\ResourceInfo;

class ConsoleExporter implements LogRecordExporterInterface
{
    /**
     * @param iterable<mixed, ReadableLogRecord> $batch
     */
    public function export(iterable $batch, ?CancellationInterface $cancellation = null): FutureInterface
    {
        $resource = null;
        $scope = null;
        foreach ($batch as $record) {
            if (!$resource) {
                $resource = $this->convertResource($record->getResource());
            }
            if (!$scope) {
                $scope = $this->convertInstrumentationScope($record->getInstrumentationScope());
                $scope['logs'] = [];
            }
            $scope['logs'][] = $this->convertLogRecord($record);
        }
        $output = [
            'resource' => $resource,
            'scope' => $scope,
        ];
        echo json_encode($output, JSON_PRETTY_PRINT) . PHP_EOL;

        return new CompletedFuture(true);
    }

    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return true;
    }

    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        return true;
    }
    private function convertLogRecord(ReadableLogRecord $record): array
    {
        return [
            'timestamp' => $record->getTimestamp(),
            'observed_timestamp' => $record->getObservedTimestamp(),
            'severity_number' => $record->getSeverityNumber(),
            'severity_text' => $record->getSeverityText(),
            'body' => $record->getBody(),
            'trace_id' => $record->getTraceId(),
            'span_id' => $record->getSpanId(),
            'trace_flags' => $record->getTraceFlags(),
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
    private function convertInstrumentationScope(InstrumentationScopeInterface $scope): array
    {
        return [
            'name' => $scope->getName(),
            'version' => $scope->getVersion(),
            'attributes' => $scope->getAttributes()->toArray(),
            'dropped_attributes_count' => $scope->getAttributes()->getDroppedAttributesCount(),
            'schema_url' => $scope->getSchemaUrl(),
        ];
    }
}
