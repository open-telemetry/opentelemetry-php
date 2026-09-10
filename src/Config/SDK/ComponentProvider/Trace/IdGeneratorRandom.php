<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Trace;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Trace\IdGeneratorInterface;
use OpenTelemetry\SDK\Trace\RandomIdGenerator;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * ID generator that randomly generates TraceIds and SpanIds (spec default).
 *
 * Spec key: random.
 *
 * @see https://github.com/open-telemetry/opentelemetry-configuration/blob/main/schema/tracer_provider.yaml
 *
 * @implements ComponentProvider<IdGeneratorInterface>
 */
final class IdGeneratorRandom implements ComponentProvider
{
    /**
     * @param array{} $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): IdGeneratorInterface
    {
        return new RandomIdGenerator();
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        return $builder->arrayNode('random');
    }
}
