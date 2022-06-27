<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Adapter\HttpDiscovery;

use Generator;
use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use OpenTelemetry\SDK\Common\Adapter\HttpDiscovery\DependencyResolver;
use OpenTelemetry\SDK\Common\Http\HttpPlug\Client\ResolverInterface as HttpPlugClientResolverInterface;
use OpenTelemetry\SDK\Common\Http\Psr\Client\ResolverInterface as PsrClientResolverInterface;
use OpenTelemetry\SDK\Common\Http\Psr\Message\FactoryResolverInterface as MessageFactoryResolverInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface as PsrClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use ReflectionClass;

/**
 * @covers \OpenTelemetry\SDK\Common\Adapter\HttpDiscovery\DependencyResolver
 */
class DependencyResolverTest extends TestCase
{
    private const DEPENDENCIES = [
        MessageFactoryResolverInterface::class => [
            RequestFactoryInterface::class,
            ResponseFactoryInterface::class,
            ServerRequestFactoryInterface::class,
            StreamFactoryInterface::class,
            UploadedFileFactoryInterface::class,
            UriFactoryInterface::class,
        ],
        PsrClientResolverInterface::class => [
            PsrClientInterface::class,
        ],
        HttpPlugClientResolverInterface::class => [
            HttpClient::class,
            HttpAsyncClient::class,
        ],
    ];
    private const METHOD_NAME_REPLACEMENTS = [
        MessageFactoryResolverInterface::class => [],
        HttpPlugClientResolverInterface::class => ['Http', 'HttpPlug'],
        PsrClientResolverInterface::class => ['Client', 'PsrClient'],
    ];

    /**
     * @dataProvider provideDependencies
     */
    public function test_resolve(string $method, object $dependency, array $arguments): void
    {
        $instance = DependencyResolver::create(...$arguments);

        $this->assertSame(
            $dependency,
            $instance->{$method}()
        );
    }

    public function provideDependencies(): Generator
    {
        $arguments = [];
        $dependencies = [];

        foreach (self::DEPENDENCIES as $resolverInterface => $interfaces) {
            $resolver = $this->createMock($resolverInterface);

            foreach ($interfaces as $interface) {
                $method = $this->resolveMethodName($interface, self::METHOD_NAME_REPLACEMENTS[$resolverInterface]);
                $dependency = $this->createMock($interface);
                $resolver->method($method)->willReturn($dependency);
                $dependencies[$method] = $dependency;
            }

            $arguments[] = $resolver;
        }

        foreach ($dependencies as $method => $dependency) {
            yield [$method, $dependency, $arguments];
        }
    }

    private function resolveMethodName(string $interface, array $replacements = []): string
    {
        $interface = (new ReflectionClass($interface))->getShortName();

        if (count($replacements) >= 2) {
            $interface = str_replace($replacements[0], $replacements[1], $interface);
        }

        return sprintf(
            'resolve%s',
            str_replace(
                'Interface',
                '',
                $interface
            )
        );
    }
}
