<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Environment\EnvironmentVariablesTrait;
use OpenTelemetry\SDK\Common\Environment\KnownValues as Values;
use OpenTelemetry\SDK\Common\Environment\Variables as Env;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;

class SpanProcessorFactory
{
    use EnvironmentVariablesTrait;

    public function fromEnvironment(?SpanExporterInterface $exporter = null): SpanProcessorInterface
    {
        $name = $this->getEnumFromEnvironment(Env::OTEL_PHP_TRACES_PROCESSOR);
        switch ($name) {
            case Values::VALUE_BATCH:
                return new BatchSpanProcessor($exporter);
            case Values::VALUE_SIMPLE:
                return new SimpleSpanProcessor($exporter);
            case Values::VALUE_NOOP:
            case Values::VALUE_NONE:
                return NoopSpanProcessor::getInstance();
            default:
                throw new InvalidArgumentException('Unknown processor: ' . $name);
        }
    }
}
