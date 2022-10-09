<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Common\Instrumentation;

use ArrayAccess;
use function assert;
use function class_exists;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Metrics\Noop\NoopMeterProvider;
use OpenTelemetry\API\Trace\NoopTracerProvider;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKeyInterface;
use OpenTelemetry\Context\ContextStorageInterface;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use const PHP_VERSION_ID;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class Instrumentation
{
    private ?ContextStorageInterface $contextStorage;

    private string $name;
    private ?string $version;
    private ?string $schemaUrl;
    private iterable $attributes;
    /** @var ArrayAccess<TracerProviderInterface, TracerInterface>|null */
    private ?ArrayAccess $tracers;
    /** @var ArrayAccess<MeterProviderInterface, MeterInterface>|null */
    private ?ArrayAccess $meters;

    public function __construct(string $name, ?string $version = null, ?string $schemaUrl = null, iterable $attributes = [], ?ContextStorageInterface $contextStorage = null)
    {
        $this->contextStorage = $contextStorage;
        $this->name = $name;
        $this->version = $version;
        $this->schemaUrl = $schemaUrl;
        $this->attributes = $attributes;
        $this->tracers = self::createWeakMap();
        $this->meters = self::createWeakMap();
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

    private function get(ContextKeyInterface $contextKey)
    {
        return ($this->contextStorage ?? Context::storage())->current()->get($contextKey);
    }

    public function tracer(): TracerInterface
    {
        static $noop;
        $tracerProvider = $this->get(ContextKeys::tracerProvider()) ?? $noop ??= new NoopTracerProvider();

        if ($this->tracers === null) {
            return $tracerProvider->getTracer($this->name, $this->version, $this->schemaUrl, $this->attributes);
        }

        return $this->tracers[$tracerProvider] ??= $tracerProvider->getTracer($this->name, $this->version, $this->schemaUrl, $this->attributes);
    }

    public function meter(): MeterInterface
    {
        static $noop;
        $meterProvider = $this->get(ContextKeys::meterProvider()) ?? $noop ??= new NoopMeterProvider();

        if ($this->meters === null) {
            return $meterProvider->getMeter($this->name, $this->version, $this->schemaUrl, $this->attributes);
        }

        return $this->meters[$meterProvider] ??= $meterProvider->getMeter($this->name, $this->version, $this->schemaUrl, $this->attributes);
    }

    public function logger(): LoggerInterface
    {
        static $noop;

        return $this->get(ContextKeys::logger()) ?? $noop ??= new NullLogger();
    }

    public function propagator(): TextMapPropagatorInterface
    {
        static $noop;

        return $this->get(ContextKeys::propagator()) ?? $noop ??= new NoopTextMapPropagator();
    }
}
