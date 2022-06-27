<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Adapter\HttpDiscovery;

use Generator;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Strategy\MockClientStrategy;
use OpenTelemetry\SDK\Common\Adapter\HttpDiscovery\MessageFactoryResolver;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use ReflectionClass;

/**
 * @covers \OpenTelemetry\SDK\Common\Adapter\HttpDiscovery\MessageFactoryResolver
 */
class MessageFactoryResolverTest extends TestCase
{
    private const DEPENDENCIES = [
        RequestFactoryInterface::class,
        ResponseFactoryInterface::class,
        ServerRequestFactoryInterface::class,
        StreamFactoryInterface::class,
        UploadedFileFactoryInterface::class,
        UriFactoryInterface::class,
    ];

    public function setUp(): void
    {
        HttpClientDiscovery::prependStrategy(MockClientStrategy::class);
    }

    /**
     * @dataProvider provideDependencies
     */
    public function test_resolve(string $method, object $dependency, array $arguments): void
    {
        $instance = MessageFactoryResolver::create(...$arguments);

        $this->assertSame(
            $dependency,
            $instance->{$method}()
        );
    }

    public function provideDependencies(): Generator
    {
        $dependencies = [];

        foreach (self::DEPENDENCIES as $interface) {
            $dependencies[$this->resolveMethodName($interface)] = $this->createMock($interface);
        }

        foreach ($dependencies as $method => $dependency) {
            yield [$method, $dependency, array_values($dependencies)];
        }
    }

    /**
     *  @psalm-param class-string $interface
     */
    private function resolveMethodName(string $interface): string
    {
        return sprintf(
            'resolve%s',
            str_replace(
                'Interface',
                '',
                (new ReflectionClass($interface))->getShortName()
            )
        );
    }
}
