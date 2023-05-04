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
use OpenTelemetry\SDK\Logs\Processor\MultiLogsProcessor;
use OpenTelemetry\SDK\Logs\Processor\NoopLogsProcessor;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogsProcessor;

class LogRecordProcessorFactory
{
    public function create(LogRecordExporterInterface $exporter, ?MeterProviderInterface $meterProvider = null): LogRecordProcessorInterface
    {
        $processors = [];
        $list = Configuration::getList(Variables::OTEL_PHP_LOGS_PROCESSOR);
        foreach ($list as $name) {
            $processors[] = $this->createProcessor($name, $exporter, $meterProvider);
        }

        switch (count($processors)) {
            case 0:
                return NoopLogsProcessor::getInstance();
            case 1:
                return $processors[0];
            default:
                return new MultiLogsProcessor($processors);
        }
    }

    private function createProcessor($name, LogRecordExporterInterface $exporter, ?MeterProviderInterface $meterProvider = null): LogRecordProcessorInterface
    {
        switch ($name) {
            case KnownValues::VALUE_BATCH:
                return new BatchLogsProcessor(
                    $exporter,
                    ClockFactory::getDefault(),
                    Configuration::getInt(Variables::OTEL_BLRP_MAX_QUEUE_SIZE),
                    Configuration::getInt(Variables::OTEL_BLRP_SCHEDULE_DELAY),
                    Configuration::getInt(Variables::OTEL_BLRP_EXPORT_TIMEOUT),
                    Configuration::getInt(Variables::OTEL_BLRP_MAX_EXPORT_BATCH_SIZE),
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
