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
use OpenTelemetry\SDK\Logs\Processor\BatchLogRecordProcessor;
use OpenTelemetry\SDK\Logs\Processor\MultiLogRecordProcessor;
use OpenTelemetry\SDK\Logs\Processor\NoopLogRecordProcessor;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;

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
                return NoopLogRecordProcessor::getInstance();
            case 1:
                return $processors[0];
            default:
                return new MultiLogRecordProcessor($processors);
        }
    }

    private function createProcessor(string $name, LogRecordExporterInterface $exporter, ?MeterProviderInterface $meterProvider = null): LogRecordProcessorInterface
    {
        switch ($name) {
            case KnownValues::VALUE_BATCH:
                return new BatchLogRecordProcessor(
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
                return new SimpleLogRecordProcessor($exporter);
            case Values::VALUE_NOOP:
            case Values::VALUE_NONE:
                return NoopLogRecordProcessor::getInstance();
            default:
                throw new InvalidArgumentException('Unknown processor: ' . $name);
        }
    }
}
