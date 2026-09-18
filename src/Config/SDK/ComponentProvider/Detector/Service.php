<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Detector;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Resource\Detectors\Composite;
use OpenTelemetry\SDK\Resource\Detectors\Service as ServiceDetector;
use OpenTelemetry\SDK\Resource\Detectors\ServiceInstance;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Enables the service resource detector, which populates service.name (from
 * OTEL_SERVICE_NAME) and service.instance.id (a per-process UUID).
 *
 * Note: service.instance.id is a random UUID generated once per process. In
 * shared-nothing PHP setups (FPM, Apache), this ID changes on every request and
 * may not be meaningful. Configure a more stable identifier via resource.attributes
 * if required.
 *
 * @implements ComponentProvider<ResourceDetectorInterface>
 */
final class Service implements ComponentProvider
{
    /**
     * @param array{} $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): ResourceDetectorInterface
    {
        return new Composite([
            new ServiceDetector(),
            new ServiceInstance(),
        ]);
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        return $builder->arrayNode('service');
    }
}
