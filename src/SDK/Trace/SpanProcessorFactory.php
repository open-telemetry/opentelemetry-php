<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use InvalidArgumentException;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;

class SpanProcessorFactory
{
    public function fromConfig(object $config, ?SpanExporterInterface $exporter = null): SpanProcessorInterface
    {
        //@array $processors
        $processors = array_filter(explode(',', $config->trace->processor));
        if (1 !== count($processors)) {
            throw new InvalidArgumentException('Exactly 1 processor required');
        }
        $processor = $processors[0];
        switch ($processor) {
            case 'batch':
                return new BatchSpanProcessor($exporter);
            case 'simple':
                return new SimpleSpanProcessor($exporter);
            case 'noop':
            case 'none':
                return NoopSpanProcessor::getInstance();
            default:
                throw new InvalidArgumentException('Unknown processor: ' . $processor);
        }
    }
}
