<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Propagator;

use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * @implements ComponentProvider<TextMapPropagatorInterface>
 */
final class TextMapPropagatorOtTrace implements ComponentProvider
{
    use LogsMessagesTrait;

    /**
     * @param array{} $properties
     */
    public function createPlugin(array $properties, Context $context): TextMapPropagatorInterface
    {
        self::logWarning('ottrace propagator not implemented');

        return NoopTextMapPropagator::getInstance();
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        return new ArrayNodeDefinition('ottrace');
    }
}
