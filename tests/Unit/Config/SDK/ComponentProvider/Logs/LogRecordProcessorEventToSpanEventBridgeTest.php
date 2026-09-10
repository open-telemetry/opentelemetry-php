<?php

declare(strict_types=1);

namespace Unit\Config\SDK\ComponentProvider\Logs;

use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\ComponentProvider\Logs\LogRecordProcessorEventToSpanEventBridge;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * The EventToSpanEventBridge processor is a stub that will throw until the PHP SDK
 * ships a real implementation. This test documents and guards that behaviour so
 * that a future implementation does not silently break the contract.
 */
#[CoversClass(LogRecordProcessorEventToSpanEventBridge::class)]
final class LogRecordProcessorEventToSpanEventBridgeTest extends TestCase
{
    public function test_create_plugin_throws_runtime_exception(): void
    {
        $provider = new LogRecordProcessorEventToSpanEventBridge();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/event_to_span_event_bridge/');

        $provider->createPlugin([], new Context());
    }

    public function test_get_config_returns_correct_node_name(): void
    {
        $provider = new LogRecordProcessorEventToSpanEventBridge();

        // Use the real Symfony node builder so we verify the spec key is correct
        $treeBuilder = new \Symfony\Component\Config\Definition\Builder\TreeBuilder('root');
        $nodeBuilder = $treeBuilder->getRootNode()->children();

        $node = $provider->getConfig(
            $this->createMock(\OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry::class),
            $nodeBuilder,
        );

        $this->assertSame('event_to_span_event_bridge/development', $node->getNode(true)->getName());
    }
}
