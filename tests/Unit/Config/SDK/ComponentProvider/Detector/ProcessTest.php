<?php

declare(strict_types=1);

namespace OpenTelemetry\Test\Unit\Config\SDK\ComponentProvider\Detector;

use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\ComponentProvider\Detector\Process;
use OpenTelemetry\SDK\Resource\Detectors\Process as ProcessDetector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

#[CoversClass(Process::class)]
final class ProcessTest extends TestCase
{
    public function test_get_config(): void
    {
        $provider = new Process();
        $registry = $this->createMock(ComponentProviderRegistry::class);
        $config = $provider->getConfig($registry, new NodeBuilder());
        $this->assertInstanceOf(ArrayNodeDefinition::class, $config);
    }

    public function test_create_plugin(): void
    {
        $provider = new Process();
        $detector = $provider->createPlugin([], new Context());
        $this->assertInstanceOf(ProcessDetector::class, $detector);
    }
}
