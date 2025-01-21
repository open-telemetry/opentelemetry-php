<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs\Exporter;

use ArrayObject;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\ReadableLogRecord;

class InMemoryExporter implements LogRecordExporterInterface
{
    public function __construct(private readonly ArrayObject $storage = new ArrayObject())
    {
    }

    /**
     * @inheritDoc
     */
    public function export(iterable $batch, ?CancellationInterface $cancellation = null): FutureInterface
    {
        foreach ($batch as $record) {
            $this->storage->append($this->convert($record));
        }

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

    public function getStorage(): ArrayObject
    {
        return $this->storage;
    }

    private function convert(ReadableLogRecord $record): array
    {
        return [
            'timestamp' => $record->getTimestamp(),
            'observed_timestamp' => $record->getObservedTimestamp(),
            'severity_number' => $record->getSeverityNumber(),
            'severity_text' => $record->getSeverityText(),
            'body' => $record->getBody(),
            'attributes' => $record->getAttributes()->toArray(),
            'trace_id' => $record->getSpanContext()?->getTraceId(),
            'span_id' => $record->getSpanContext()?->getSpanId(),
            'trace_flags' => $record->getSpanContext()?->getTraceFlags(),
        ];
    }
}
