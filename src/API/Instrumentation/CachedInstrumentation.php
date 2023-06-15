<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation;

use ArrayAccess;
use function assert;
use function class_exists;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use const PHP_VERSION_ID;

/**
 * Provides access to cached {@link TracerInterface} and {@link MeterInterface}
 * instances.
 *
 * Autoinstrumentation should prefer using a {@link CachedInstrumentation}
 * instance over repeatedly obtaining instrumentation instances from
 * {@link Globals}.
 */
final class CachedInstrumentation
{
    private string $name;
    private ?string $version;
    private ?string $schemaUrl;
    private iterable $attributes;
    /** @var ArrayAccess<TracerProviderInterface, TracerInterface>|null */
    private ?ArrayAccess $tracers;
    /** @var ArrayAccess<MeterProviderInterface, MeterInterface>|null */
    private ?ArrayAccess $meters;
    /** @var ArrayAccess<LoggerProviderInterface, LoggerInterface>|null */
    private ?ArrayAccess $loggers;

    public function __construct(string $name, ?string $version = null, ?string $schemaUrl = null, iterable $attributes = [])
    {
        $this->name = $name;
        $this->version = $version;
        $this->schemaUrl = $schemaUrl;
        $this->attributes = $attributes;
        $this->tracers = self::createWeakMap();
        $this->meters = self::createWeakMap();
        $this->loggers = self::createWeakMap();
    }

    private static function createWeakMap(): ?ArrayAccess
    {
        if (PHP_VERSION_ID < 80000) {
            return null;
        }

        /** @phan-suppress-next-line PhanUndeclaredClassReference */
        assert(class_exists(\WeakMap::class, false));
        /** @phan-suppress-next-line PhanUndeclaredClassMethod */
        $map = new \WeakMap();
        assert($map instanceof ArrayAccess);

        return $map;
    }

    public function tracer(): TracerInterface
    {
        $tracerProvider = Globals::tracerProvider();

        if ($this->tracers === null) {
            return $tracerProvider->getTracer($this->name, $this->version, $this->schemaUrl, $this->attributes);
        }

        return $this->tracers[$tracerProvider] ??= $tracerProvider->getTracer($this->name, $this->version, $this->schemaUrl, $this->attributes);
    }

    public function meter(): MeterInterface
    {
        $meterProvider = Globals::meterProvider();

        if ($this->meters === null) {
            return $meterProvider->getMeter($this->name, $this->version, $this->schemaUrl, $this->attributes);
        }

        return $this->meters[$meterProvider] ??= $meterProvider->getMeter($this->name, $this->version, $this->schemaUrl, $this->attributes);
    }
    public function logger(): LoggerInterface
    {
        $loggerProvider = Globals::loggerProvider();

        if ($this->loggers === null) {
            return $loggerProvider->getLogger($this->name, $this->version, $this->schemaUrl, $this->attributes);
        }

        return $this->loggers[$loggerProvider] ??= $loggerProvider->getLogger($this->name, $this->version, $this->schemaUrl, $this->attributes);
    }
}
