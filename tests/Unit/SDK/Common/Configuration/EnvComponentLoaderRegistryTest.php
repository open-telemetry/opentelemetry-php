<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Configuration;

use InvalidArgumentException;
use LogicException;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoader;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoaderRegistry as EnvComponentLoaderRegistryInterface;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvResolver;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Common\Configuration\EnvComponentLoaderRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EnvComponentLoaderRegistry::class)]
final class EnvComponentLoaderRegistryTest extends TestCase
{
    public function test_register_and_load(): void
    {
        $registry = new EnvComponentLoaderRegistry();

        $loader = new class implements EnvComponentLoader {
            public function load(EnvResolver $env, EnvComponentLoaderRegistryInterface $registry, Context $context): string
            {
                return 'loaded-value';
            }

            public function name(): string
            {
                return 'test_loader';
            }
        };

        $registry->register($loader);

        $env = $this->createMock(EnvResolver::class);
        $result = $registry->load('string', 'test_loader', $env, new Context());

        $this->assertSame('loaded-value', $result);
    }

    public function test_register_returns_self(): void
    {
        $registry = new EnvComponentLoaderRegistry();

        $loader = new class implements EnvComponentLoader {
            public function load(EnvResolver $env, EnvComponentLoaderRegistryInterface $registry, Context $context): string
            {
                return 'value';
            }

            public function name(): string
            {
                return 'fluent_loader';
            }
        };

        $result = $registry->register($loader);
        $this->assertSame($registry, $result);
    }

    public function test_register_duplicate_throws(): void
    {
        $registry = new EnvComponentLoaderRegistry();

        $loader = new class implements EnvComponentLoader {
            public function load(EnvResolver $env, EnvComponentLoaderRegistryInterface $registry, Context $context): string
            {
                return 'value';
            }

            public function name(): string
            {
                return 'dup_loader';
            }
        };

        $registry->register($loader);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Duplicate environment loader');
        $registry->register($loader);
    }

    public function test_load_unknown_throws(): void
    {
        $registry = new EnvComponentLoaderRegistry();
        $env = $this->createMock(EnvResolver::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Loader for string unknown not found');
        $registry->load('string', 'unknown', $env, new Context());
    }

    public function test_load_all(): void
    {
        $registry = new EnvComponentLoaderRegistry();

        $loader1 = new class implements EnvComponentLoader {
            public function load(EnvResolver $env, EnvComponentLoaderRegistryInterface $registry, Context $context): string
            {
                return 'value1';
            }

            public function name(): string
            {
                return 'loader1';
            }
        };

        $loader2 = new class implements EnvComponentLoader {
            public function load(EnvResolver $env, EnvComponentLoaderRegistryInterface $registry, Context $context): string
            {
                return 'value2';
            }

            public function name(): string
            {
                return 'loader2';
            }
        };

        $registry->register($loader1);
        $registry->register($loader2);

        $env = $this->createMock(EnvResolver::class);
        $results = iterator_to_array($registry->loadAll('string', $env, new Context()));

        $this->assertCount(2, $results);
        $this->assertSame('value1', $results[0]);
        $this->assertSame('value2', $results[1]);
    }

    public function test_load_all_empty_type(): void
    {
        $registry = new EnvComponentLoaderRegistry();
        $env = $this->createMock(EnvResolver::class);

        $results = iterator_to_array($registry->loadAll('nonexistent', $env, new Context()));
        $this->assertEmpty($results);
    }
}
