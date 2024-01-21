<?php declare(strict_types=1);
namespace OpenTelemetry\Config\SDK\ComponentProvider\Trace;

use Nevay\OTelSDK\Configuration\ComponentProvider;
use Nevay\OTelSDK\Configuration\ComponentProviderRegistry;
use Nevay\OTelSDK\Configuration\Context;
use OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

final class SamplerTraceIdRatioBased implements ComponentProvider {

    /**
     * @param array{
     *     ratio: float,
     * } $properties
     */
    public function createPlugin(array $properties, Context $context): SamplerInterface {
        return new TraceIdRatioBasedSampler(
            probability: $properties['ratio'],
        );
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition {
        $node = new ArrayNodeDefinition('trace_id_ratio_based');
        $node
            ->children()
                ->floatNode('ratio')->min(0)->max(1)->isRequired()->end()
            ->end()
        ;

        return $node;
    }
}
