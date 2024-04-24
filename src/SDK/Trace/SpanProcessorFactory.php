<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use InvalidArgumentException;
use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\KnownValues as Values;
use OpenTelemetry\SDK\Common\Configuration\Variables as Env;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Metrics\NoopMeterProvider;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;

class SpanProcessorFactory
{
    public function create(?SpanExporterInterface $exporter = null, ?MeterProviderInterface $meterProvider = null): SpanProcessorInterface
    {
        if ($exporter === null) {
            return new NoopSpanProcessor();
        }

        $name = Configuration::getEnum(Env::OTEL_PHP_TRACES_PROCESSOR);

        return match ($name) {
            Values::VALUE_BATCH => new BatchSpanProcessor(
                $exporter,
                Clock::getDefault(),
                Configuration::getInt(Env::OTEL_BSP_MAX_QUEUE_SIZE, BatchSpanProcessor::DEFAULT_MAX_QUEUE_SIZE),
                Configuration::getInt(Env::OTEL_BSP_SCHEDULE_DELAY, BatchSpanProcessor::DEFAULT_SCHEDULE_DELAY),
                Configuration::getInt(Env::OTEL_BSP_EXPORT_TIMEOUT, BatchSpanProcessor::DEFAULT_EXPORT_TIMEOUT),
                Configuration::getInt(Env::OTEL_BSP_MAX_EXPORT_BATCH_SIZE, BatchSpanProcessor::DEFAULT_MAX_EXPORT_BATCH_SIZE),
                true, //autoflush
                $meterProvider ?? new NoopMeterProvider(),
            ),
            Values::VALUE_SIMPLE => new SimpleSpanProcessor($exporter),
            Values::VALUE_NOOP, Values::VALUE_NONE => NoopSpanProcessor::getInstance(),
            default => throw new InvalidArgumentException('Unknown processor: ' . $name),
        };
    }
}
