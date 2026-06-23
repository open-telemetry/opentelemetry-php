<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\ComponentProvider;

use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\ConfigurationRegistry;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\GeneralInstrumentationConfiguration;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\InstrumentationConfiguration;
use OpenTelemetry\Config\SDK\ComponentProvider\InstrumentationConfigurationRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

#[CoversClass(InstrumentationConfigurationRegistry::class)]
final class InstrumentationConfigurationRegistryTest extends TestCase
{
    public function test_get_config(): void
    {
        $provider = new InstrumentationConfigurationRegistry();
        $registry = $this->createMock(ComponentProviderRegistry::class);
        $registry->method('componentMap')->willReturn(new ArrayNodeDefinition('test'));

        $config = $provider->getConfig($registry, new NodeBuilder());
        $this->assertInstanceOf(ArrayNodeDefinition::class, $config);
    }

    public function test_create_plugin_empty_properties(): void
    {
        $provider = new InstrumentationConfigurationRegistry();

        $result = $provider->createPlugin([
            'instrumentation/development' => [
                'php' => [],
                'general' => [],
            ],
        ], new Context());

        $this->assertInstanceOf(ConfigurationRegistry::class, $result);
    }

    public function test_create_plugin_with_php_configurations(): void
    {
        $mockConfig = $this->createMock(InstrumentationConfiguration::class);
        $phpPlugin = $this->createMock(ComponentPlugin::class);
        $phpPlugin->method('create')->willReturn($mockConfig);

        $provider = new InstrumentationConfigurationRegistry();

        $result = $provider->createPlugin([
            'instrumentation/development' => [
                'php' => [$phpPlugin],
                'general' => [],
            ],
        ], new Context());

        $this->assertInstanceOf(ConfigurationRegistry::class, $result);
    }

    public function test_create_plugin_with_general_configurations(): void
    {
        $mockConfig = $this->createMock(GeneralInstrumentationConfiguration::class);
        $generalPlugin = $this->createMock(ComponentPlugin::class);
        $generalPlugin->method('create')->willReturn($mockConfig);

        $provider = new InstrumentationConfigurationRegistry();

        $result = $provider->createPlugin([
            'instrumentation/development' => [
                'php' => [],
                'general' => [$generalPlugin],
            ],
        ], new Context());

        $this->assertInstanceOf(ConfigurationRegistry::class, $result);
    }

    public function test_create_plugin_with_null_sections(): void
    {
        $provider = new InstrumentationConfigurationRegistry();

        $result = $provider->createPlugin([
            'instrumentation/development' => [],
        ], new Context());

        $this->assertInstanceOf(ConfigurationRegistry::class, $result);
    }
}
