<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables as Env;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;

class BatchSpanProcessorFactory implements SpanProcessorFactoryInterface
{
    public function create(SpanProcessorContext $context): SpanProcessorInterface
    {
        return new BatchSpanProcessor(
            $context->exporter,
            Clock::getDefault(),
            Configuration::getInt(Env::OTEL_BSP_MAX_QUEUE_SIZE, BatchSpanProcessor::DEFAULT_MAX_QUEUE_SIZE),
            Configuration::getInt(Env::OTEL_BSP_SCHEDULE_DELAY, BatchSpanProcessor::DEFAULT_SCHEDULE_DELAY),
            Configuration::getInt(Env::OTEL_BSP_EXPORT_TIMEOUT, BatchSpanProcessor::DEFAULT_EXPORT_TIMEOUT),
            Configuration::getInt(Env::OTEL_BSP_MAX_EXPORT_BATCH_SIZE, BatchSpanProcessor::DEFAULT_MAX_EXPORT_BATCH_SIZE),
            true, //autoflush
            $context->emitInternalMetrics ? $context->meterProvider : null,
        );
    }

    public function type(): string
    {
        return 'batch';
    }

    public function priority(): int
    {
        return 0;
    }
}
