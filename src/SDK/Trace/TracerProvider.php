<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use function is_array;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\API\Trace\NoopTracer;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactoryInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\SpanSuppression\NoopSuppressionStrategy\NoopSuppressionStrategy;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanSuppressionStrategy;
use WeakMap;

final class TracerProvider implements TracerProviderInterface
{
    private readonly TracerSharedState $tracerSharedState;
    private readonly InstrumentationScopeFactoryInterface $instrumentationScopeFactory;
    private readonly WeakMap $tracers;

    /** @param list<SpanProcessorInterface>|SpanProcessorInterface|null $spanProcessors */
    public function __construct(
        SpanProcessorInterface|array|null $spanProcessors = [],
        ?SamplerInterface $sampler = null,
        ?ResourceInfo $resource = null,
        ?SpanLimits $spanLimits = null,
        ?IdGeneratorInterface $idGenerator = null,
        ?InstrumentationScopeFactoryInterface $instrumentationScopeFactory = null,
        private ?Configurator $configurator = null,
        private SpanSuppressionStrategy $spanSuppressionStrategy = new NoopSuppressionStrategy(),
    ) {
        $spanProcessors ??= [];
        $spanProcessors = is_array($spanProcessors) ? $spanProcessors : [$spanProcessors];
        $resource ??= ResourceInfoFactory::defaultResource();
        $sampler ??= new ParentBased(new AlwaysOnSampler());
        $idGenerator ??= new RandomIdGenerator();
        $spanLimits ??= (new SpanLimitsBuilder())->build();

        $this->tracerSharedState = new TracerSharedState(
            $idGenerator,
            $resource,
            $spanLimits,
            $sampler,
            $spanProcessors
        );
        $this->instrumentationScopeFactory = $instrumentationScopeFactory ?? new InstrumentationScopeFactory(Attributes::factory());
        $this->tracers = new WeakMap();
    }

    #[\Override]
    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return $this->tracerSharedState->getSpanProcessor()->forceFlush($cancellation);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getTracer(
        string $name,
        ?string $version = null,
        ?string $schemaUrl = null,
        iterable $attributes = [],
    ): API\TracerInterface {
        if ($this->tracerSharedState->hasShutdown()) {
            return NoopTracer::getInstance();
        }

        $scope = $this->instrumentationScopeFactory->create($name, $version, $schemaUrl, $attributes);
        $tracer = new Tracer(
            $this->tracerSharedState,
            $scope,
            $this->configurator,
            $this->spanSuppressionStrategy->getSuppressor($name, $version, $schemaUrl),
        );
        $this->tracers->offsetSet($tracer, null);

        return $tracer;
    }

    public function getSampler(): SamplerInterface
    {
        return $this->tracerSharedState->getSampler();
    }

    /**
     * Returns `false` is the provider is already shutdown, otherwise `true`.
     */
    #[\Override]
    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        if ($this->tracerSharedState->hasShutdown()) {
            return true;
        }

        return $this->tracerSharedState->shutdown($cancellation);
    }

    public static function builder(): TracerProviderBuilder
    {
        return new TracerProviderBuilder();
    }

    /**
     * Update the {@link Configurator} for a {@link TracerProvider}, which will
     * reconfigure all tracers created from the provider.
     * @experimental
     */
    #[\Override]
    public function updateConfigurator(Configurator $configurator): void
    {
        $this->configurator = $configurator;

        foreach ($this->tracers as $tracer => $unused) {
            $tracer->updateConfig($configurator);
        }
    }
}
