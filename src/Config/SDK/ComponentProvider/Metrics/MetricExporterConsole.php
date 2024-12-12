<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Metrics;

use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\Config\SDK\Configuration\Internal\TreatNullAsUnsetNormalization;
use OpenTelemetry\SDK\Metrics\MetricExporter\ConsoleMetricExporter;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * @implements ComponentProvider<MetricExporterInterface>
 */
final class MetricExporterConsole implements ComponentProvider
{

    /**
     * @param array{} $properties
     */
    public function createPlugin(array $properties, Context $context): MetricExporterInterface
    {
        return new ConsoleMetricExporter();
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('console'))->attribute(TreatNullAsUnsetNormalization::ALLOW_EMPTY, true);
    }
}
