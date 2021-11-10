<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use Exception;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;

class SpanProcessorFactory
{
    public function fromEnvironment(?SpanExporterInterface $exporter = null): SpanProcessorInterface
    {
        $name = getenv('OTEL_TRACES_PROCESSOR');
        if (!$name) {
            throw new Exception('OTEL_TRACES_PROCESSOR not set');
        }
        switch ($name) {
            case 'batch':
                return new BatchSpanProcessor($exporter, SystemClock::getInstance());
            case 'simple':
                return new SimpleSpanProcessor($exporter);
            case 'noop':
                return NoopSpanProcessor::getInstance();
            default:
                throw new Exception('Unknown processor: ' . $name);
        }
    }
}
