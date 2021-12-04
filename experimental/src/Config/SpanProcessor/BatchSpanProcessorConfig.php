<?php

declare(strict_types=1);

namespace OpenTelemetry\Experimental\Config\SpanProcessor;

use OpenTelemetry\Experimental\Config\ConfigInterface;
use OpenTelemetry\Experimental\Config\SpanProcessorConfigInterface;

class BatchSpanProcessorConfig implements ConfigInterface, SpanProcessorConfigInterface
{
    public ?int $scheduleDelay;
    public ?int $scheduleTimeout;
    public ?int $maxQueueSize;
    public ?int $maxExportBatchSize;

    public function __construct(array $userConfig, array $environmentConfig)
    {
        $this->scheduleDelay = (int)($userConfig['span.processor.batch.schedule_delay'] ?? $environmentConfig['OTEL_BSP_SCHEDULE_DELAY'] ?? 5000);
        $this->scheduleTimeout = (int)($userConfig['span.processor.batch.schedule_timeout'] ?? $environmentConfig['OTEL_BSP_EXPORT_TIMEOUT'] ?? 30000);
        $this->maxQueueSize = (int)($userConfig['span.processor.batch.max_queue_size'] ?? $environmentConfig['OTEL_BSP_MAX_QUEUE_SIZE'] ?? 2048);
        $this->maxExportBatchSize = (int)($userConfig['span.processor.batch.max_export_batch_size'] ?? $environmentConfig['OTEL_BSP_MAX_EXPORT_BATCH_SIZE'] ?? 512);
    }

    public static function provides(string $exporterName): bool
    {
        return $exporterName === 'batch';
    }
}
