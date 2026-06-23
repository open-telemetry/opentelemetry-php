<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\ComponentProvider\Instrumentation\General;

use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\GeneralInstrumentationConfiguration;
use OpenTelemetry\API\Instrumentation\Configuration\General\HttpConfig;
use OpenTelemetry\Config\SDK\ComponentProvider\Instrumentation\General\HttpConfigProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

#[CoversClass(HttpConfigProvider::class)]
final class HttpConfigProviderTest extends TestCase
{
    public function test_get_config(): void
    {
        $provider = new HttpConfigProvider();
        $registry = $this->createMock(ComponentProviderRegistry::class);
        $config = $provider->getConfig($registry, new NodeBuilder());
        $this->assertInstanceOf(ArrayNodeDefinition::class, $config);
    }

    public function test_create_plugin(): void
    {
        $provider = new HttpConfigProvider();
        $properties = [
            'client' => [
                'request_captured_headers' => ['Content-Type'],
                'response_captured_headers' => ['X-Request-Id'],
            ],
            'server' => [
                'request_captured_headers' => ['Accept'],
                'response_captured_headers' => ['Content-Length'],
            ],
        ];

        $result = $provider->createPlugin($properties, new Context());
        $this->assertInstanceOf(GeneralInstrumentationConfiguration::class, $result);
        $this->assertInstanceOf(HttpConfig::class, $result);
    }

    public function test_create_plugin_empty_properties(): void
    {
        $provider = new HttpConfigProvider();
        $properties = [];

        $result = $provider->createPlugin($properties, new Context());
        $this->assertInstanceOf(HttpConfig::class, $result);
    }
}
