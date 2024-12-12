<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Trace;

use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\Config\SDK\Configuration\Internal\TreatNullAsUnsetNormalization;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * @implements ComponentProvider<SamplerInterface>
 */
final class SamplerAlwaysOn implements ComponentProvider
{

    /**
     * @param array{} $properties
     */
    public function createPlugin(array $properties, Context $context): SamplerInterface
    {
        return new AlwaysOnSampler();
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('always_on'))->attribute(TreatNullAsUnsetNormalization::ALLOW_EMPTY, true);
    }
}
