<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use function is_array;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\API\Trace\NoopTracer;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Common\Instrumentation\KeyGenerator;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use function register_shutdown_function;

final class TracerProvider implements API\TracerProviderInterface
{
    public const DEFAULT_TRACER_NAME = 'io.opentelemetry.contrib.php';

    private static ?API\TracerInterface $defaultTracer = null;

    /** @var array<string, API\TracerInterface> */
    private ?array $tracers = null;

    /** @readonly */
    private TracerSharedState $tracerSharedState;

    /** @param list<SpanProcessorInterface>|SpanProcessorInterface|null $spanProcessors */
    public function __construct(
        $spanProcessors = [],
        SamplerInterface $sampler = null,
        ResourceInfo $resource = null,
        SpanLimits $spanLimits = null,
        IdGeneratorInterface $idGenerator = null
    ) {
        if (null === $spanProcessors) {
            $spanProcessors = [];
        }

        $spanProcessors = is_array($spanProcessors) ? $spanProcessors : [$spanProcessors];
        $resource = $resource ?? ResourceInfoFactory::defaultResource();
        $sampler = $sampler ?? new ParentBased(new AlwaysOnSampler());
        $idGenerator = $idGenerator ?? new RandomIdGenerator();
        $spanLimits = $spanLimits ?? (new SpanLimitsBuilder())->build();

        $this->tracerSharedState = new TracerSharedState(
            $idGenerator,
            $resource,
            $spanLimits,
            $sampler,
            $spanProcessors
        );

        register_shutdown_function([$this, 'shutdown']);
    }

    public function forceFlush(): ?bool
    {
        return $this->tracerSharedState->getSpanProcessor()->forceFlush();
    }

    /** @inheritDoc */
    public function getTracer(string $name = self::DEFAULT_TRACER_NAME, ?string $version = null, ?string $schemaUrl = null): API\TracerInterface
    {
        if ($this->tracerSharedState->hasShutdown()) {
            return NoopTracer::getInstance();
        }

        $key = KeyGenerator::generateInstanceKey($name, $version, $schemaUrl);

        if (isset($this->tracers[$key]) && $this->tracers[$key] instanceof API\TracerInterface) {
            return $this->tracers[$key];
        }

        $instrumentationScope = new InstrumentationScope($name, $version, $schemaUrl);

        $tracer = new Tracer(
            $this->tracerSharedState,
            $instrumentationScope,
        );
        if (null === self::$defaultTracer) {
            self::$defaultTracer = $tracer;
        }

        return $this->tracers[$key] = $tracer;
    }

    public static function getDefaultTracer(): API\TracerInterface
    {
        if (null === self::$defaultTracer) {
            // TODO log a warning
            return NoopTracer::getInstance();
        }

        return self::$defaultTracer;
    }

    public static function setDefaultTracer(API\TracerInterface $tracer): void
    {
        self::$defaultTracer = $tracer;
    }

    public function getSampler(): SamplerInterface
    {
        return $this->tracerSharedState->getSampler();
    }

    /**
     * Returns `false` is the provider is already shutdown, otherwise `true`.
     */
    public function shutdown(): bool
    {
        if ($this->tracerSharedState->hasShutdown()) {
            return true;
        }

        return $this->tracerSharedState->shutdown();
    }
}
