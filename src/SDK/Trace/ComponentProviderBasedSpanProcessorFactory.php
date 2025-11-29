<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use InvalidArgumentException;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanProcessorBatch;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanProcessorSimple;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\KnownValues as Values;
use OpenTelemetry\SDK\Common\Configuration\Variables as Env;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Metrics\NoopMeterProvider;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor;

/**
 * ComponentProvider-based SpanProcessor factory that reads configuration from environment variables
 * and uses the modern ComponentProvider system for component creation.
 */
class ComponentProviderBasedSpanProcessorFactory
{
    private Context $context;

    public function __construct(?MeterProviderInterface $meterProvider = null)
    {
        $this->context = new Context(
            meterProvider: $meterProvider ?? new NoopMeterProvider(),
        );
    }

    public function create(?SpanExporterInterface $exporter = null): SpanProcessorInterface
    {
        if ($exporter === null) {
            return new NoopSpanProcessor();
        }

        $processorType = Configuration::getEnum(Env::OTEL_PHP_TRACES_PROCESSOR);

        return match ($processorType) {
            Values::VALUE_BATCH => $this->createBatchProcessor($exporter),
            Values::VALUE_SIMPLE => $this->createSimpleProcessor($exporter),
            Values::VALUE_NOOP, Values::VALUE_NONE => NoopSpanProcessor::getInstance(),
            default => throw new InvalidArgumentException('Unknown processor: ' . $processorType),
        };
    }

    private function createBatchProcessor(SpanExporterInterface $exporter): SpanProcessorInterface
    {
        $provider = new SpanProcessorBatch();

        // Create configuration array from environment variables
        $config = [
            'schedule_delay' => Configuration::getInt(Env::OTEL_BSP_SCHEDULE_DELAY, BatchSpanProcessor::DEFAULT_SCHEDULE_DELAY),
            'export_timeout' => Configuration::getInt(Env::OTEL_BSP_EXPORT_TIMEOUT, BatchSpanProcessor::DEFAULT_EXPORT_TIMEOUT),
            'max_queue_size' => Configuration::getInt(Env::OTEL_BSP_MAX_QUEUE_SIZE, BatchSpanProcessor::DEFAULT_MAX_QUEUE_SIZE),
            'max_export_batch_size' => Configuration::getInt(Env::OTEL_BSP_MAX_EXPORT_BATCH_SIZE, BatchSpanProcessor::DEFAULT_MAX_EXPORT_BATCH_SIZE),
            'exporter' => new ExporterComponentPlugin($exporter),
        ];

        return $provider->createPlugin($config, $this->context);
    }

    private function createSimpleProcessor(SpanExporterInterface $exporter): SpanProcessorInterface
    {
        $provider = new SpanProcessorSimple();

        $config = [
            'exporter' => new ExporterComponentPlugin($exporter),
        ];

        return $provider->createPlugin($config, $this->context);
    }
}

/**
 * Simple ComponentPlugin wrapper for existing SpanExporter instances
 */
class ExporterComponentPlugin
{
    public function __construct(private readonly SpanExporterInterface $exporter) {}

    public function create(Context $context): SpanExporterInterface
    {
        return $this->exporter;
    }
}
