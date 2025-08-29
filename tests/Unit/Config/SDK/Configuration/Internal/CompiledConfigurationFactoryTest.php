<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\Configuration\Internal;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\Internal\CompiledConfigurationFactory;
use OpenTelemetry\Config\SDK\Configuration\Internal\ComponentPlugin;
use OpenTelemetry\Config\SDK\Configuration\Internal\ResourceCollection;
use OpenTelemetry\Config\SDK\Configuration\Internal\ResourceTrackable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\NodeInterface;


class CompiledConfigurationFactoryTest extends TestCase
{
    private $mockRootComponent;
    private $mockNode;
    private $mockResourceTrackable;
    private $factory;

    protected function setUp(): void
    {
        $this->mockRootComponent = $this->createMock(ComponentProvider::class);
        $this->mockNode = $this->createMock(NodeInterface::class);
        $this->mockResourceTrackable = $this->createMock(ResourceTrackable::class);
        
        // Set up basic node mock methods
        $this->mockNode->method('getName')->willReturn('root');
        $this->mockNode->method('normalize')->willReturnArgument(0);
        $this->mockNode->method('merge')->willReturnArgument(1);
        $this->mockNode->method('finalize')->willReturnCallback(function ($value) {
            return is_array($value) ? $value : [];
        });
        
        $this->factory = new CompiledConfigurationFactory(
            $this->mockRootComponent,
            $this->mockNode,
            [$this->mockResourceTrackable]
        );
    }

    /**
     * @covers \OpenTelemetry\Config\SDK\Configuration\Internal\CompiledConfigurationFactory::__construct
     */
    public function testConstructor(): void
    {
        $this->assertInstanceOf(CompiledConfigurationFactory::class, $this->factory);
    }

    /**
     * @covers \OpenTelemetry\Config\SDK\Configuration\Internal\CompiledConfigurationFactory::process
     */
    public function testProcessWithResources(): void
    {
        $resources = $this->createMock(ResourceCollection::class);
        $configs = [];
        
        $this->mockResourceTrackable->expects($this->exactly(2))
            ->method('trackResources')
            ->willReturnCallback(function ($arg) use ($resources) {
                static $callCount = 0;
                $callCount++;
                if ($callCount === 1) {
                    $this->assertSame($resources, $arg);
                } else {
                    $this->assertNull($arg);
                }
            });
        
        // Mock the node methods that Processor will call internally
        $this->mockNode->method('getName')->willReturn('root');
        $this->mockNode->method('normalize')->willReturnArgument(0);
        $this->mockNode->method('merge')->willReturnArgument(1);
        $this->mockNode->method('finalize')->willReturnArgument(0);
            
        $result = $this->factory->process($configs, $resources);
        $this->assertInstanceOf(ComponentPlugin::class, $result);
    }

    /**
     * @covers \OpenTelemetry\Config\SDK\Configuration\Internal\CompiledConfigurationFactory::process
     */
    public function testProcessWithoutResources(): void
    {
        $configs = [];
        
        $this->mockResourceTrackable->expects($this->exactly(2))
            ->method('trackResources')
            ->willReturnCallback(function ($arg) {
                static $callCount = 0;
                $callCount++;
                if ($callCount === 1) {
                    $this->assertNull($arg);
                } else {
                    $this->assertNull($arg);
                }
            });
        
        // Mock the node methods that Processor will call internally
        $this->mockNode->method('getName')->willReturn('root');
        $this->mockNode->method('normalize')->willReturnArgument(0);
        $this->mockNode->method('merge')->willReturnArgument(1);
        $this->mockNode->method('finalize')->willReturnArgument(0);
            
        $result = $this->factory->process($configs);
        $this->assertInstanceOf(ComponentPlugin::class, $result);
    }

    /**
     * @covers \OpenTelemetry\Config\SDK\Configuration\Internal\CompiledConfigurationFactory::process
     */
    public function testProcessWithMultipleResourceTrackables(): void
    {
        $resources = $this->createMock(ResourceCollection::class);
        $configs = [];
        $trackable2 = $this->createMock(ResourceTrackable::class);
        
        $this->mockResourceTrackable->expects($this->exactly(2))
            ->method('trackResources');
        $trackable2->expects($this->exactly(2))
            ->method('trackResources');
        
        $mockNode = $this->createMock(NodeInterface::class);
        $mockNode->method('getName')->willReturn('root');
        $mockNode->method('normalize')->willReturnArgument(0);
        $mockNode->method('merge')->willReturnArgument(1);
        $mockNode->method('finalize')->willReturnArgument(0);
        
        $factory = new CompiledConfigurationFactory(
            $this->mockRootComponent,
            $mockNode,
            [$this->mockResourceTrackable, $trackable2]
        );
        $result = $factory->process($configs, $resources);
        $this->assertInstanceOf(ComponentPlugin::class, $result);
    }

    /**
     * @covers \OpenTelemetry\Config\SDK\Configuration\Internal\CompiledConfigurationFactory::process
     */
    public function testProcessWithEmptyResourceTrackables(): void
    {
        $configs = [];
        
        $mockNode = $this->createMock(NodeInterface::class);
        $mockNode->method('getName')->willReturn('root');
        $mockNode->method('normalize')->willReturnArgument(0);
        $mockNode->method('merge')->willReturnArgument(1);
        $mockNode->method('finalize')->willReturnArgument(0);
        
        $factory = new CompiledConfigurationFactory(
            $this->mockRootComponent,
            $mockNode,
            []
        );
        $result = $factory->process($configs);
        $this->assertInstanceOf(ComponentPlugin::class, $result);
    }

    /**
     * @covers \OpenTelemetry\Config\SDK\Configuration\Internal\CompiledConfigurationFactory::process
     */
    public function testProcessWithComplexConfigs(): void
    {
        $configs = [
            'nested' => [
                'key1' => 'value1',
                'key2' => 'value2'
            ],
            'simple' => 'value'
        ];
        
        $this->mockResourceTrackable->expects($this->exactly(2))
            ->method('trackResources')
            ->willReturnCallback(function ($arg) {
                static $callCount = 0;
                $callCount++;
                $this->assertNull($arg);
            });
            
        // Mock the node methods that Processor will call internally
        $this->mockNode->method('getName')->willReturn('root');
        $this->mockNode->method('normalize')->willReturnArgument(0);
        $this->mockNode->method('merge')->willReturnArgument(1);
        $this->mockNode->method('finalize')->willReturnCallback(function ($value) use ($configs) {
            return is_array($value) ? $value : $configs;
        });
            
        $result = $this->factory->process($configs);
        $this->assertInstanceOf(ComponentPlugin::class, $result);
    }
}
