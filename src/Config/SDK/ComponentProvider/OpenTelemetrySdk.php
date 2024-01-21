<?php declare(strict_types=1);
namespace OpenTelemetry\Config\SDK\ComponentProvider;

use Nevay\OTelSDK\Configuration\ComponentPlugin;
use Nevay\OTelSDK\Configuration\ComponentProvider;
use Nevay\OTelSDK\Configuration\ComponentProviderRegistry;
use Nevay\OTelSDK\Configuration\Context;
use Nevay\OTelSDK\Configuration\Validation;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\SdkBuilder;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SpanLimits;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\SDK\Trace\TracerProvider;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * @internal
 */
final class OpenTelemetrySdk implements ComponentProvider {

    /**
     * @param array{
     *     file_format: '0.1',
     *     disabled: bool,
     *     resource: array{
     *         attributes: array,
     *         schema_url: ?string,
     *     },
     *     attribute_limits: array{
     *         attribute_value_length_limit: ?int<0, max>,
     *         attribute_count_limit: ?int<0, max>,
     *     },
     *     propagator: ?ComponentPlugin<TextMapPropagatorInterface>,
     *     tracer_provider: array{
     *         limits: array{
     *             attribute_value_length_limit: ?int<0, max>,
     *             attribute_count_limit: ?int<0, max>,
     *             event_count_limit: ?int<0, max>,
     *             link_count_limit: ?int<0, max>,
     *             event_attribute_count_limit: ?int<0, max>,
     *             link_attribute_count_limit: ?int<0, max>,
     *         },
     *         sampler: ?ComponentPlugin<SamplerInterface>,
     *         processors: list<ComponentPlugin<SpanProcessorInterface>>,
     *     },
     * } $properties
     */
    public function createPlugin(array $properties, Context $context): SdkBuilder {
        $sdkBuilder = new SdkBuilder();

        $propagator = $properties['propagator']?->create($context) ?? NoopTextMapPropagator::getInstance();
        $sdkBuilder->setPropagator($propagator);

        if ($properties['disabled']) {
            return $sdkBuilder;
        }

        $resource = ResourceInfoFactory::defaultResource()
            ->merge(ResourceInfo::create(
                attributes: Attributes::create($properties['resource']['attributes']),
                schemaUrl: $properties['resource']['schema_url'],
            ));

        $spanProcessors = [];
        foreach ($properties['tracer_provider']['processors'] as $processor) {
            $spanProcessors[] = $processor->create($context);
        }

        $tracerProvider = new TracerProvider(
            spanProcessors: $spanProcessors,
            sampler: $properties['tracer_provider']['sampler']?->create($context) ?? new ParentBased(new AlwaysOnSampler()),
            resource: $resource,
            spanLimits: new SpanLimits(
                attributesFactory: Attributes::factory(
                    attributeCountLimit: $properties['tracer_provider']['limits']['attribute_count_limit']
                        ?? $properties['attribute_limits']['attribute_count_limit'],
                    attributeValueLengthLimit: $properties['tracer_provider']['limits']['attribute_value_length_limit']
                        ?? $properties['attribute_limits']['attribute_value_length_limit'],
                ),
                eventAttributesFactory: Attributes::factory(
                    attributeCountLimit: $properties['tracer_provider']['limits']['event_attribute_count_limit']
                        ?? $properties['tracer_provider']['limits']['attribute_count_limit']
                        ?? $properties['attribute_limits']['attribute_count_limit'],
                    attributeValueLengthLimit: $properties['tracer_provider']['limits']['attribute_value_length_limit']
                        ?? $properties['attribute_limits']['attribute_value_length_limit'],
                ),
                linkAttributesFactory: Attributes::factory(
                    attributeCountLimit: $properties['tracer_provider']['limits']['link_attribute_count_limit']
                        ?? $properties['tracer_provider']['limits']['attribute_count_limit']
                        ?? $properties['attribute_limits']['attribute_count_limit'],
                    attributeValueLengthLimit: $properties['tracer_provider']['limits']['attribute_value_length_limit']
                        ?? $properties['attribute_limits']['attribute_value_length_limit'],
                ),
                eventCountLimit: $properties['tracer_provider']['limits']['event_count_limit'],
                linkCountLimit: $properties['tracer_provider']['limits']['link_count_limit'],
            ),
        );

        $sdkBuilder->setTracerProvider($tracerProvider);

        return $sdkBuilder;
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition {
        $node = new ArrayNodeDefinition('open_telemetry');
        $node
            ->addDefaultsIfNotSet()
            ->ignoreExtraKeys()
            ->children()
                ->scalarNode('file_format')
                    ->isRequired()
                    ->example('0.1')
                    ->validate()->always(Validation::ensureString())->end()
                    ->validate()->ifNotInArray(['0.1'])->thenInvalid('unsupported version')->end()
                ->end()
                ->booleanNode('disabled')->defaultFalse()->end()
                ->append($this->getResourceConfig())
                ->append($this->getAttributeLimitsConfig())
                ->append($registry->component('propagator', TextMapPropagatorInterface::class))
                ->append($this->getTracerProviderConfig($registry))
                # ->append($this->getMeterProviderConfig($registry))
                # ->append($this->getLoggerProviderConfig($registry))
            ->end();

        return $node;
    }

    private function getResourceConfig(): ArrayNodeDefinition {
        $node = new ArrayNodeDefinition('resource');
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('attributes')
                    ->variablePrototype()->end()
                ->end()
                ->scalarNode('schema_url')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
            ->end();

        return $node;
    }

    private function getAttributeLimitsConfig(): ArrayNodeDefinition {
        $node = new ArrayNodeDefinition('attribute_limits');
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->integerNode('attribute_value_length_limit')->min(0)->defaultValue(4096)->end()
                ->integerNode('attribute_count_limit')->min(0)->defaultValue(128)->end()
            ->end();

        return $node;
    }

    private function getTracerProviderConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition {
        $node = new ArrayNodeDefinition('tracer_provider');
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('limits')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('attribute_value_length_limit')->min(0)->defaultNull()->end()
                        ->integerNode('attribute_count_limit')->min(0)->defaultNull()->end()
                        ->integerNode('event_count_limit')->min(0)->defaultValue(128)->end()
                        ->integerNode('link_count_limit')->min(0)->defaultValue(128)->end()
                        ->integerNode('event_attribute_count_limit')->min(0)->defaultNull()->end()
                        ->integerNode('link_attribute_count_limit')->min(0)->defaultNull()->end()
                    ->end()
                ->end()
                ->append($registry->component('sampler', SamplerInterface::class))
                ->append($registry->componentList('processors', SpanProcessorInterface::class))
            ->end()
        ;

        return $node;
    }
}
