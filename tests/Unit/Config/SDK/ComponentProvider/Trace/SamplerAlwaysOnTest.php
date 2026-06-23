<?php

declare(strict_types=1);

namespace OpenTelemetry\Test\Unit\Config\SDK\ComponentProvider\Trace;

use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SamplerAlwaysOn;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

#[CoversClass(SamplerAlwaysOn::class)]
final class SamplerAlwaysOnTest extends TestCase
{
    public function test_get_config(): void
    {
        $provider = new SamplerAlwaysOn();
        $registry = $this->createMock(ComponentProviderRegistry::class);
        $config = $provider->getConfig($registry, new NodeBuilder());
        $this->assertInstanceOf(ArrayNodeDefinition::class, $config);
    }

    public function test_create_plugin(): void
    {
        $provider = new SamplerAlwaysOn();
        $sampler = $provider->createPlugin([], new Context());
        $this->assertInstanceOf(AlwaysOnSampler::class, $sampler);
    }
}
