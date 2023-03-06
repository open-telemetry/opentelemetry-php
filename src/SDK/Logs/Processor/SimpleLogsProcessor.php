<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs\Processor;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\ReadWriteLogRecord;

class SimpleLogsProcessor implements LogRecordProcessorInterface
{
    private LogRecordExporterInterface $exporter;
    public function __construct(LogRecordExporterInterface $exporter)
    {
        $this->exporter = $exporter;
    }

    /**
     * @todo accept cancellation as param?
     * @todo context is not used (already added to $record in LogRecordData)
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/logs/sdk.md#onemit
     */
    public function onEmit(ReadWriteLogRecord $record, ?ContextInterface $context = null): void
    {
        $this->exporter->export([$record]);
    }

    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        return $this->exporter->shutdown($cancellation);
    }

    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return $this->exporter->forceFlush($cancellation);
    }
}
