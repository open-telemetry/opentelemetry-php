<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Common\Instrumentation;

use ArrayAccess;
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
use OpenTelemetry\SDK\Common\Util\WeakMap;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class Instrumentation
{
    private ?ContextStorageInterface $contextStorage;

    private string $name;
    private ?string $version;
    private ?string $schemaUrl;
    private iterable $attributes;
    /** @var ArrayAccess<TracerProviderInterface, TracerInterface> */
    private ArrayAccess $tracers;
    /** @var ArrayAccess<MeterProviderInterface, MeterInterface> */
    private ArrayAccess $meters;

    public function __construct(string $name, ?string $version = null, ?string $schemaUrl = null, iterable $attributes = [], ?ContextStorageInterface $contextStorage = null)
    {
        $this->contextStorage = $contextStorage;
        $this->name = $name;
        $this->version = $version;
        $this->schemaUrl = $schemaUrl;
        $this->attributes = $attributes;
        $this->tracers = WeakMap::create();
        $this->meters = WeakMap::create();
    }

    private function get(ContextKeyInterface $contextKey)
    {
        return ($this->contextStorage ?? Context::storage())->current()->get($contextKey);
    }

    public function tracer(): TracerInterface
    {
        static $noop;
        $tracerProvider = $this->get(ContextKeys::tracerProvider()) ?? $noop ??= new NoopTracerProvider();

        return $this->tracers[$tracerProvider] ??= $tracerProvider->getTracer($this->name, $this->version, $this->schemaUrl, $this->attributes);
    }

    public function meter(): MeterInterface
    {
        static $noop;
        $meterProvider = $this->get(ContextKeys::meterProvider()) ?? $noop ??= new NoopMeterProvider();

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
