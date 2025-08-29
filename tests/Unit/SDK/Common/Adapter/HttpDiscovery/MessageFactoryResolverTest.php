<?php

declare(strict_types=1);

namesfinal pace OpenTelemetry\Tests\Unit\SDK\Common\Adapter\HttpDiscovery;

use Generator;
use Http\Discovery\Psr18ClientDiscovery;
use Http\Discovery\Strategy\MockClientStrategy;
use Mockery;
use OpenTelemetry\SDK\Common\Adapter\HttpDiscovery\MessageFactoryResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use ReflectionClass;

#[CoversClass(MessageFactoryResolver::class)]
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

    #[\Override]
    public function setUp(): void
    {
        Psr18ClientDiscovery::prependStrategy(MockClientStrategy::class);
    }

    #[DataProvider('provideDependencies')]
    public function test_resolve(string $method, object $dependency, array $arguments): void
    {
        $instance = MessageFactoryResolver::create(...$arguments);

        $this->assertSame(
            $dependency,
            $instance->{$method}()
        );
    }

    public static function provideDependencies(): Generator
    {
        $dependencies = [];

        foreach (self::DEPENDENCIES as $interface) {
            $dependencies[self::resolveMethodName($interface)] = Mockery::mock($interface);
        }

        foreach ($dependencies as $method => $dependency) {
            yield [$method, $dependency, array_values($dependencies)];
        }
    }

    /**
     *  @psalm-param class-string $interface
     */
    private static function resolveMethodName(string $interface): string
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
