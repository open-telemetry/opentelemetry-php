<?php

declare(strict_types=1);

namespace OpenTelemetry\Test\Unit\Config\SDK\ComponentProvider\Trace;

use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SamplerAlwaysOff;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

#[CoversClass(SamplerAlwaysOff::class)]
final class SamplerAlwaysOffTest extends TestCase
{
    public function test_get_config(): void
    {
        $provider = new SamplerAlwaysOff();
        $registry = $this->createMock(ComponentProviderRegistry::class);
        $config = $provider->getConfig($registry, new NodeBuilder());
        $this->assertInstanceOf(ArrayNodeDefinition::class, $config);
    }

    public function test_create_plugin(): void
    {
        $provider = new SamplerAlwaysOff();
        $sampler = $provider->createPlugin([], new Context());
        $this->assertInstanceOf(AlwaysOffSampler::class, $sampler);
    }
}
