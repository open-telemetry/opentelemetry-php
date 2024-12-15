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
 * @psalm-suppress UndefinedClass
 */
#[PackageDependency('open-telemetry/contrib-aws', '>=1.0.0beta12')]
final class TextMapPropagatorXray implements ComponentProvider
{
    use LogsMessagesTrait;

    /**
     * @psalm-suppress InvalidReturnType,InvalidReturnStatement
     * @param array{} $properties
     */
    public function createPlugin(array $properties, Context $context): TextMapPropagatorInterface
    {
        // @phpstan-ignore-next-line
        return new Propagator();
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        return new ArrayNodeDefinition('xray');
    }
}
