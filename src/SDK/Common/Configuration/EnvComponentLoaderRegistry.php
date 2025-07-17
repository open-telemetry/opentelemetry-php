<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration;

use function array_map;
use function implode;
use InvalidArgumentException;
use LogicException;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoader;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvResolver;
use OpenTelemetry\API\Configuration\Context;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use function sprintf;

/**
 * @internal
 */
final class EnvComponentLoaderRegistry implements \OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoaderRegistry
{
    /**
     * @var array<string, array<string, EnvComponentLoader>>
     */
    private array $loaders = [];

    public function register(EnvComponentLoader $loader): EnvComponentLoaderRegistry
    {
        $name = $loader->name();
        $type = self::loadType($loader);
        if (isset($this->loaders[$type][$name])) {
            throw new LogicException(sprintf('Duplicate environment loader registered for "%s" "%s"', $type, $name));
        }

        $this->loaders[$type][$name] = $loader;

        return $this;
    }

    #[\Override]
    public function load(string $type, string $name, EnvResolver $env, Context $context): mixed
    {
        if (!$loader = $this->loaders[$type][$name] ?? null) {
            throw new InvalidArgumentException(sprintf('Loader for %s %s not found', $type, $name));
        }

        return $loader->load($env, $this, $context);
    }

    public function loadAll(string $type, EnvResolver $env, Context $context): iterable
    {
        foreach ($this->loaders[$type] ?? [] as $loader) {
            yield $loader->load($env, $this, $context);
        }
    }

    private static function loadType(EnvComponentLoader $loader): string
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        if ($returnType = (new ReflectionMethod($loader, 'load'))->getReturnType()) {
            return self::typeToString($returnType);
        }

        return 'mixed';
    }

    /** @phan-suppress PhanUndeclaredMethod */
    private static function typeToString(ReflectionType $type): string
    {
        /** @phpstan-ignore-next-line */
        return match ($type::class) {
            ReflectionNamedType::class => $type->getName(),
            ReflectionUnionType::class => implode('|', array_map(self::typeToString(...), $type->getTypes())),
            ReflectionIntersectionType::class => implode('&', array_map(self::typeToString(...), $type->getTypes())),
        };
    }
}
