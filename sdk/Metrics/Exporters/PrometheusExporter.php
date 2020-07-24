<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Metrics\Exporters;

use OpenTelemetry\Metrics as API;
use OpenTelemetry\Sdk\Metrics\Exceptions\CantBeExported;
use OpenTelemetry\Sdk\Metrics\Exceptions\RetryableExportException;
use Prometheus\CollectorRegistry;
use OpenTelemetry\Sdk\Metrics\Counter;

class PrometheusExporter extends AbstractExporter
{
    /**
     * @var CollectorRegistry $registry
     */
    protected $registry;

    public function __construct(CollectorRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritDoc}
     */
    protected function doExport(iterable $metrics): void
    {
        foreach ($metrics as $metric) {
            switch (get_class($metric)) {
                case Counter::class:
                    $this->exportCounter($metric);
                    break;
                default:
                    throw new CantBeExported('Unknown metrics type: ' . $metric['type']);
            }
        }
    }

    protected function exportCounter(API\Metrics $metric): void
    {
        $record = $this->registry->getOrRegisterCounter(
            '',
            $metric->getName(),
            $metric->getDescription(),
            $metric->getLabels()
        );

        $record->incBy($metric->getValue(), $metric->getLabels());
    }
}
