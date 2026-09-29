<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\Config\ComponentProvider\Trace;

use function count;
use InvalidArgumentException;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use function sprintf;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * The bridge from the `Sampler` schema type into the composable samplers.
 *
 * The node is built with {@see ComponentProviderRegistry::componentMap()} rather than
 * {@see ComponentProviderRegistry::component()}: `component()` installs a validator that
 * replaces the array with a `ComponentPlugin`, which a provider's own top-level node cannot
 * return (`Processor::process()` is typed to return an array). `componentMap()` does not
 * enforce the schema's `minProperties: 1`/`maxProperties: 1`, so that is checked below.
 *
 * Composable sampling is not implemented, so the configured tree is parsed and discarded and
 * an {@see AlwaysOffSampler} is returned — matching the schema's "if no rules match, the span
 * is not sampled" default rather than inventing a sampling decision.
 *
 * @implements ComponentProvider<SamplerInterface>
 */
final class SamplerComposite implements ComponentProvider
{
    #[\Override]
    public function createPlugin(array $properties, Context $context): SamplerInterface
    {
        return new AlwaysOffSampler();
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $registry->componentMap('composite/development', ComposableSamplerInterface::class);
        $node->validate()->always(static function (array $value): array {
            if (count($value) !== 1) {
                throw new InvalidArgumentException(sprintf(
                    'Composite sampler must have exactly one composable sampler defined, got %d',
                    count($value),
                ));
            }

            return $value;
        });

        return $node;
    }
}
