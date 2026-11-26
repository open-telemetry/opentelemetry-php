<?php

declare(strict_types=1);

namespace Nevay\SPI;

use Closure;
use Iterator;
use IteratorAggregate;

/**
 * Phan reads `@template P_ of S_` on register() as a bound to a class named S_, so every call fails with
 * PhanTemplateTypeConstraintViolation. This stub replaces vendor/tbachert/spi/src/ServiceLoader.php for phan only,
 * and binds $provider to S_ directly.
 *
 * @template-covariant S of object
 * @implements IteratorAggregate<class-string, S>
 */
final class ServiceLoader implements IteratorAggregate
{
    /**
     * @template S_ of object
     * @param class-string<S_> $service
     * @param class-string<S_> $provider
     */
    public static function register(string $service, string $provider): bool
    {
    }

    /**
     * @template S_ of object
     * @param class-string<S_> $service
     * @return ServiceLoader<S_>
     */
    public static function load(string $service): ServiceLoader
    {
    }

    public function getIterator(): Iterator
    {
    }

    public function reload(): void
    {
    }

    /**
     * @internal
     */
    public static function serviceAvailable(string $service): bool
    {
    }

    /**
     * @internal
     */
    public static function providerAvailable(string $provider, bool $skipRuntimeValidatedRequirements = false): bool
    {
    }

    /**
     * @return array<class-string, list<class-string>>
     * @internal
     */
    public static function collectProviders(Closure $closure, mixed ...$args): array
    {
    }
}
