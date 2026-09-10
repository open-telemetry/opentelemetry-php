<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Logs;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Bridges log records to span events on the active span.
 *
 * Spec key: event_to_span_event_bridge/development.
 *
 * @see https://github.com/open-telemetry/opentelemetry-configuration/blob/main/schema/logger_provider.yaml
 *
 * @implements ComponentProvider<LogRecordProcessorInterface>
 *
 * TODO: implement once the PHP SDK has an EventToSpanEventBridgeLogRecordProcessor.
 */
final class LogRecordProcessorEventToSpanEventBridge implements ComponentProvider
{
    /**
     * @param array{} $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): LogRecordProcessorInterface
    {
        throw new \RuntimeException(
            'event_to_span_event_bridge/development log record processor is not yet implemented in the PHP SDK.',
        );
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        return $builder->arrayNode('event_to_span_event_bridge/development');
    }
}
