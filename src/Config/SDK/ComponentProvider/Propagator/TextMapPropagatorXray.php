<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Propagator;

use Nevay\SPI\ServiceProviderDependency\PackageDependency;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\Aws\Xray\Propagator;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * @implements ComponentProvider<TextMapPropagatorInterface>
 * @todo xray propagator exists in contrib, should it be moved to core?
 */
#[PackageDependency('open-telemetry/contrib-aws', '>=1.0.0beta12')]
final class TextMapPropagatorXray implements ComponentProvider
{
    use LogsMessagesTrait;

    /**
     * @param array{} $properties
     */
    public function createPlugin(array $properties, Context $context): TextMapPropagatorInterface
    {
        return new Propagator();
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        return new ArrayNodeDefinition('xray');
    }
}
