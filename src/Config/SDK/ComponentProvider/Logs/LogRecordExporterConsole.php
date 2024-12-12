<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Logs;

use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\Config\SDK\Configuration\Internal\TreatNullAsUnsetNormalization;
use OpenTelemetry\SDK\Logs\Exporter\ConsoleExporter;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Registry;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * @implements ComponentProvider<LogRecordExporterInterface>
 */
final class LogRecordExporterConsole implements ComponentProvider
{

    /**
     * @param array{} $properties
     */
    public function createPlugin(array $properties, Context $context): LogRecordExporterInterface
    {
        return new ConsoleExporter(Registry::transportFactory('stream')->create(
            endpoint: 'php://stdout',
            contentType: 'application/json',
        ));
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('console'))->attribute(TreatNullAsUnsetNormalization::ALLOW_EMPTY, true);
    }
}
