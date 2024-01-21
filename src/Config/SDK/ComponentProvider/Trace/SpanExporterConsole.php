<?php declare(strict_types=1);
namespace OpenTelemetry\Config\SDK\ComponentProvider\Trace;

use Nevay\OTelSDK\Configuration\ComponentProvider;
use Nevay\OTelSDK\Configuration\ComponentProviderRegistry;
use Nevay\OTelSDK\Configuration\Context;
use OpenTelemetry\SDK\Registry;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

final class SpanExporterConsole implements ComponentProvider {

    /**
     * @param array{} $properties
     */
    public function createPlugin(array $properties, Context $context): SpanExporterInterface {
        return new ConsoleSpanExporter(Registry::transportFactory('stream')->create(
            endpoint: 'php://stdout',
            contentType: 'application/json',
        ));
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition {
        return new ArrayNodeDefinition('console');
    }
}
