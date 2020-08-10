<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Metrics\Exporters;

use OpenTelemetry\Metrics as API;
use OpenTelemetry\Sdk\Metrics\Counter;
use OpenTelemetry\Sdk\Metrics\Exceptions\CantBeExported;
use Prometheus\CollectorRegistry;

class PrometheusExporter extends AbstractExporter
{
    /**
     * @var CollectorRegistry $registry
     */
    protected $registry;

    /**
     * @var string $namespace
     */
    protected $namespace;

    public function __construct(CollectorRegistry $registry, string $namespace = '')
    {
        $this->registry = $registry;
        $this->namespace = $namespace;
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
                    throw new CantBeExported('Unknown metrics type: ' . get_class($metric));
            }
        }
    }

    protected function exportCounter(API\Counter $counter): void
    {
        $labels = ($counter instanceof API\LabelableMetric) ? $counter->getLabels() : [];

        $record = $this->registry->getOrRegisterCounter(
            $this->namespace,
            $counter->getName(),
            $counter->getDescription(),
            $labels
        );

        $record->incBy($counter->getValue(), $labels);
    }
}
