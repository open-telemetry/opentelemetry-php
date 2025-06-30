<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

final class SpanProcessorContext
{
    public function __construct(
        public readonly MeterProviderInterface $meterProvider,
        public readonly SpanExporterInterface $exporter,
        public readonly bool $emitInternalMetrics,
    ) {
    }
}
