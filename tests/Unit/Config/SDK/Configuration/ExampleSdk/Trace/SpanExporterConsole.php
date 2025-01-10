<?php

declare(strict_types=1);

namespace ExampleSDK\ComponentProvider\Trace;

use BadMethodCallException;
use ExampleSDK\Trace\SpanExporter;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

final class SpanExporterConsole implements ComponentProvider
{
    /**
     * @param array{} $properties
     */
    public function createPlugin(array $properties, Context $context): SpanExporter
    {
        throw new BadMethodCallException('not implemented');
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        return new ArrayNodeDefinition('console');
    }
}
