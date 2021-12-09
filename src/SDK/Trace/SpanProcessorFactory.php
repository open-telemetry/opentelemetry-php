<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use InvalidArgumentException;
use OpenTelemetry\SDK\Trace\Behavior\LoggerAwareTrait;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;

class SpanProcessorFactory
{
    use LoggerAwareTrait;

    public function fromEnvironment(?SpanExporterInterface $exporter = null): SpanProcessorInterface
    {
        $name = getenv('OTEL_PHP_TRACES_PROCESSOR');
        if (!$name) {
            throw new InvalidArgumentException('OTEL_PHP_TRACES_PROCESSOR not set');
        }
        switch ($name) {
            case 'batch':
                return $this->injectLogger(new BatchSpanProcessor($exporter));
            case 'simple':
                return $this->injectLogger(new SimpleSpanProcessor($exporter));
            case 'noop':
            case 'none':
                return $this->injectLogger(NoopSpanProcessor::getInstance());
            default:
                throw new InvalidArgumentException('Unknown processor: ' . $name);
        }
    }
}
