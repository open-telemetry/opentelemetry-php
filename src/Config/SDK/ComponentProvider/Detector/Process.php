<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Detector;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Resource\Detectors\Process as ProcessDetector;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<ResourceDetectorInterface>
 */
final class Process implements ComponentProvider
{
    /**
     * @param array{} $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): ResourceDetectorInterface
    {
        return new ProcessDetector();
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        return $builder->arrayNode('process');
    }
}
