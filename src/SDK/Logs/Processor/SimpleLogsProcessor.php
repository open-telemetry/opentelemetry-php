<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs\Processor;

use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\Context\Context;
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
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/logs/sdk.md#onemit
     */
    public function onEmit(ReadWriteLogRecord $record, ?ContextInterface $context = null): void
    {
        if ($context) {
            // @todo where can this be located to remove duplication? AbstractLogsProcessor?? trait?
            $spanContext = Span::fromContext($this->context ?? Context::getCurrent())->getContext();
            if ($spanContext->isValid()) {
                $record->setTraceId($spanContext->getTraceId());
                $record->setSpanId($spanContext->getSpanId());
                $record->setTraceFlags($spanContext->getTraceFlags());
            }
        }
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
