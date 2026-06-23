<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\ComponentProvider\Instrumentation\General;

use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\GeneralInstrumentationConfiguration;
use OpenTelemetry\API\Instrumentation\Configuration\General\PeerConfig;
use OpenTelemetry\Config\SDK\ComponentProvider\Instrumentation\General\PeerConfigProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

#[CoversClass(PeerConfigProvider::class)]
final class PeerConfigProviderTest extends TestCase
{
    public function test_get_config(): void
    {
        $provider = new PeerConfigProvider();
        $registry = $this->createMock(ComponentProviderRegistry::class);
        $config = $provider->getConfig($registry, new NodeBuilder());
        $this->assertInstanceOf(ArrayNodeDefinition::class, $config);
    }

    public function test_create_plugin(): void
    {
        $provider = new PeerConfigProvider();
        $properties = [
            'service_mapping' => [
                ['peer' => '10.0.0.1', 'service' => 'my-service'],
                ['peer' => '10.0.0.2', 'service' => 'other-service'],
            ],
        ];

        $result = $provider->createPlugin($properties, new Context());
        $this->assertInstanceOf(GeneralInstrumentationConfiguration::class, $result);
        $this->assertInstanceOf(PeerConfig::class, $result);
    }

    public function test_create_plugin_empty_properties(): void
    {
        $provider = new PeerConfigProvider();
        $properties = [];

        $result = $provider->createPlugin($properties, new Context());
        $this->assertInstanceOf(PeerConfig::class, $result);
    }
}
