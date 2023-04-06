<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use InvalidArgumentException;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;
use OpenTelemetry\SDK\Common\Configuration\KnownValues as Values;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Logs\Processor\BatchLogsProcessor;
use OpenTelemetry\SDK\Logs\Processor\NoopLogsProcessor;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogsProcessor;

class LogRecordProcessorFactory
{
    /**
     * @return array<LogRecordProcessorInterface>
     */
    public function create(LogRecordExporterInterface $exporter, ?MeterProviderInterface $meterProvider = null): array
    {
        $processors = [];
        $list = Configuration::getList(Variables::OTEL_PHP_LOGS_PROCESSOR);
        foreach ($list as $name) {
            $processors[] = $this->createProcessor($name, $exporter, $meterProvider);
        }

        return $processors;
    }

    private function createProcessor($name, LogRecordExporterInterface $exporter, ?MeterProviderInterface $meterProvider = null): LogRecordProcessorInterface
    {
        switch ($name) {
            case KnownValues::VALUE_BATCH:
                return new BatchLogsProcessor(
                    $exporter,
                    ClockFactory::getDefault(),
                    BatchLogsProcessor::DEFAULT_MAX_QUEUE_SIZE,
                    BatchLogsProcessor::DEFAULT_SCHEDULE_DELAY,
                    BatchLogsProcessor::DEFAULT_EXPORT_TIMEOUT,
                    BatchLogsProcessor::DEFAULT_MAX_EXPORT_BATCH_SIZE,
                    true,
                    $meterProvider,
                );
            case KnownValues::VALUE_SIMPLE:
                return new SimpleLogsProcessor($exporter);
            case Values::VALUE_NOOP:
            case Values::VALUE_NONE:
                return NoopLogsProcessor::getInstance();
            default:
                throw new InvalidArgumentException('Unknown processor: ' . $name);
        }
    }
}
