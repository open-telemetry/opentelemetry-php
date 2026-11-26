<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\Config\ComponentProvider\Trace;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use Override;
use RuntimeException;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<SpanExporterInterface>
 */
final class SpanExporterFailing implements ComponentProvider
{
    #[Override]
    public function createPlugin(array $properties, Context $context): SpanExporterInterface
    {
        return new class() implements SpanExporterInterface {
            #[Override]
            public function export(iterable $batch, ?CancellationInterface $cancellation = null): FutureInterface
            {
                throw new RuntimeException('export failed');
            }

            #[Override]
            public function shutdown(?CancellationInterface $cancellation = null): bool
            {
                return true;
            }

            #[Override]
            public function forceFlush(?CancellationInterface $cancellation = null): bool
            {
                return true;
            }
        };
    }

    #[Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        return $builder->arrayNode('failing');
    }
}
