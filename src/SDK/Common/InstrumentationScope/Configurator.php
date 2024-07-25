<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\InstrumentationScope;

use Closure;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Logs\LoggerConfig;
use OpenTelemetry\SDK\Metrics\MeterConfig;
use OpenTelemetry\SDK\Trace\TracerConfig;
use WeakMap;

/**
 * @template T
 */
final class Configurator
{
    /** @var Closure(InstrumentationScopeInterface): T */
    private readonly Closure $factory;
    /** @var WeakMap<InstrumentationScopeInterface, T> */
    private WeakMap $configs;
    /** @var list<ConfiguratorClosure> */
    private array $configurators = [];

    /**
     * @param Closure(InstrumentationScopeInterface): T $factory
     * @psalm-suppress PropertyTypeCoercion
     */
    public function __construct(Closure $factory)
    {
        $this->configs = new WeakMap();
        $this->factory = $factory;
    }

    /**
     * @param Closure(T, InstrumentationScopeInterface): void $closure
     */
    public function with(Closure $closure, ?string $name, ?string $version = null, ?string $schemaUrl = null): self
    {
        $this->configurators[] = $configurator = new ConfiguratorClosure($closure, self::namePattern($name), $version, $schemaUrl);

        foreach ($this->configs as $instrumentationScope => $config) {
            if ($configurator->matches($instrumentationScope)) {
                ($configurator->closure)($config, $instrumentationScope);
            }
        }

        return $this;
    }

    /**
     * @return T
     */
    public function resolve(InstrumentationScopeInterface $instrumentationScope): Config
    {
        if ($config = $this->configs[$instrumentationScope] ?? null) {
            return $config;
        }

        $config = ($this->factory)($instrumentationScope);
        foreach ($this->configurators as $configurator) {
            if ($configurator->matches($instrumentationScope)) {
                ($configurator->closure)($config, $instrumentationScope);
            }
        }

        return $this->configs[$instrumentationScope] ??= $config;
    }

    /**
     * Create a default Configurator for a LoggerConfig
     * @return Configurator<LoggerConfig>
     */
    public static function logger(): self
    {
        return (new Configurator(static fn () => new LoggerConfig()));
    }

    /**
     * Create a default Configurator for a MeterConfig
     * @return Configurator<MeterConfig>
     */
    public static function meter(): self
    {
        return (new Configurator(static fn () => new MeterConfig()));
    }

    /**
     * Create a default Configurator for a TracerConfig
     * @return Configurator<TracerConfig>
     */
    public static function tracer(): self
    {
        return (new Configurator(static fn () => new TracerConfig()));
    }

    private static function namePattern(?string $name): ?string
    {
        return $name !== null
            ? sprintf('/^%s$/', strtr(preg_quote($name, '/'), ['\\?' => '.', '\\*' => '.*']))
            : null;
    }
}
