<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use InvalidArgumentException;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\Config\SDK\ComponentProvider\Logs\LogRecordProcessorBatch;
use OpenTelemetry\Config\SDK\ComponentProvider\Logs\LogRecordProcessorSimple;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;
use OpenTelemetry\SDK\Common\Configuration\KnownValues as Values;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Logs\Processor\MultiLogRecordProcessor;
use OpenTelemetry\SDK\Logs\Processor\NoopLogRecordProcessor;

/**
 * ComponentProvider-based LogRecordProcessor factory that reads configuration from environment variables
 * and uses the modern ComponentProvider system for component creation.
 */
class ComponentProviderBasedLogRecordProcessorFactory
{
    private Context $context;

    public function __construct(?MeterProviderInterface $meterProvider = null)
    {
        $this->context = new Context(
            meterProvider: $meterProvider,
        );
    }

    public function create(LogRecordExporterInterface $exporter): LogRecordProcessorInterface
    {
        $processors = [];
        $list = Configuration::getList(Variables::OTEL_PHP_LOGS_PROCESSOR);
        foreach ($list as $name) {
            $processors[] = $this->createProcessor($name, $exporter);
        }

        return match (count($processors)) {
            0 => NoopLogRecordProcessor::getInstance(),
            1 => $processors[0],
            default => new MultiLogRecordProcessor($processors),
        };
    }

    private function createProcessor(string $name, LogRecordExporterInterface $exporter): LogRecordProcessorInterface
    {
        return match ($name) {
            KnownValues::VALUE_BATCH => $this->createBatchProcessor($exporter),
            KnownValues::VALUE_SIMPLE => $this->createSimpleProcessor($exporter),
            Values::VALUE_NOOP, Values::VALUE_NONE => NoopLogRecordProcessor::getInstance(),
            default => throw new InvalidArgumentException('Unknown processor: ' . $name),
        };
    }

    private function createBatchProcessor(LogRecordExporterInterface $exporter): LogRecordProcessorInterface
    {
        $provider = new LogRecordProcessorBatch();

        // Create configuration array from environment variables
        $config = [
            'schedule_delay' => Configuration::getInt(Variables::OTEL_BLRP_SCHEDULE_DELAY, 5000),
            'export_timeout' => Configuration::getInt(Variables::OTEL_BLRP_EXPORT_TIMEOUT, 30000),
            'max_queue_size' => Configuration::getInt(Variables::OTEL_BLRP_MAX_QUEUE_SIZE, 2048),
            'max_export_batch_size' => Configuration::getInt(Variables::OTEL_BLRP_MAX_EXPORT_BATCH_SIZE, 512),
            'exporter' => new LogRecordExporterComponentPlugin($exporter),
        ];

        return $provider->createPlugin($config, $this->context);
    }

    private function createSimpleProcessor(LogRecordExporterInterface $exporter): LogRecordProcessorInterface
    {
        $provider = new LogRecordProcessorSimple();

        $config = [
            'exporter' => new LogRecordExporterComponentPlugin($exporter),
        ];

        return $provider->createPlugin($config, $this->context);
    }
}

/**
 * Simple ComponentPlugin wrapper for existing LogRecordExporter instances
 */
class LogRecordExporterComponentPlugin
{
    public function __construct(private readonly LogRecordExporterInterface $exporter) {}

    public function create(Context $context): LogRecordExporterInterface
    {
        return $this->exporter;
    }
}
