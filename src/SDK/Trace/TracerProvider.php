<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use function is_array;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\API\Trace\NoopTracer;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactoryInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use function register_shutdown_function;
use function spl_object_id;
use WeakReference;

final class TracerProvider implements API\TracerProviderInterface
{
    public const DEFAULT_TRACER_NAME = 'io.opentelemetry.contrib.php';

    /** @var array<int, WeakReference<self>>|null */
    private static ?array $tracerProviders = null;

    /** @readonly */
    private TracerSharedState $tracerSharedState;
    private InstrumentationScopeFactoryInterface $instrumentationScopeFactory;

    /** @param list<SpanProcessorInterface>|SpanProcessorInterface|null $spanProcessors */
    public function __construct(
        $spanProcessors = [],
        SamplerInterface $sampler = null,
        ResourceInfo $resource = null,
        SpanLimits $spanLimits = null,
        IdGeneratorInterface $idGenerator = null,
        ?InstrumentationScopeFactoryInterface $instrumentationScopeFactory = null
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
        $this->instrumentationScopeFactory = $instrumentationScopeFactory ?? new InstrumentationScopeFactory(Attributes::factory());

        self::registerShutdownFunction($this);
    }

    public function forceFlush(): ?bool
    {
        return $this->tracerSharedState->getSpanProcessor()->forceFlush();
    }

    /** @inheritDoc */
    public function getTracer(
        string $name = self::DEFAULT_TRACER_NAME,
        ?string $version = null,
        ?string $schemaUrl = null,
        iterable $attributes = []
    ): API\TracerInterface {
        if ($this->tracerSharedState->hasShutdown()) {
            return NoopTracer::getInstance();
        }

        return new Tracer(
            $this->tracerSharedState,
            $this->instrumentationScopeFactory->create($name, $version, $schemaUrl, $attributes),
        );
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

        self::unregisterShutdownFunction($this);

        return $this->tracerSharedState->shutdown();
    }

    public function __destruct()
    {
        $this->shutdown();
    }

    private static function registerShutdownFunction(TracerProvider $tracerProvider): void
    {
        if (self::$tracerProviders === null) {
            register_shutdown_function(static function (): void {
                $tracerProviders = self::$tracerProviders;
                self::$tracerProviders = null;

                // Push tracer provider shutdown to end of queue
                // @phan-suppress-next-line PhanTypeMismatchArgumentInternal
                register_shutdown_function(static function (array $tracerProviders): void {
                    foreach ($tracerProviders as $reference) {
                        if ($tracerProvider = $reference->get()) {
                            $tracerProvider->shutdown();
                        }
                    }
                }, $tracerProviders);
            });
        }

        self::$tracerProviders[spl_object_id($tracerProvider)] = WeakReference::create($tracerProvider);
    }

    private static function unregisterShutdownFunction(TracerProvider $tracerProvider): void
    {
        /** @psalm-suppress PossiblyNullArrayAccess */
        unset(self::$tracerProviders[spl_object_id($tracerProvider)]);
    }
}
