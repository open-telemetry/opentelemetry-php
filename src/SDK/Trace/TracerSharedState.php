<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Metrics\CounterInterface;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Metrics\UpDownCounterInterface;
use OpenTelemetry\API\Trace as API; /** @phan-suppress-current-line PhanUnreferencedUseNormal */
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\SpanProcessor\MultiSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor;
use OpenTelemetry\SemConv\Incubating\Metrics\OtelIncubatingMetrics;
use OpenTelemetry\SemConv\Version;

/**
 * Stores shared state/config between all {@see API\TracerInterface} created via the same {@see API\TracerProviderInterface}.
 */
final class TracerSharedState
{
    private readonly SpanProcessorInterface $spanProcessor;
    private readonly ?CounterInterface $spanStartedCounter;
    private readonly ?UpDownCounterInterface $spanLiveCounter;

    private ?bool $shutdownResult = null;

    public function __construct(
        private readonly IdGeneratorInterface $idGenerator,
        private readonly ResourceInfo $resource,
        private readonly SpanLimits $spanLimits,
        private readonly SamplerInterface $sampler,
        array $spanProcessors,
        ?MeterProviderInterface $meterProvider = null,
    ) {
        $this->spanProcessor = match (count($spanProcessors)) {
            0 => NoopSpanProcessor::getInstance(),
            1 => $spanProcessors[0],
            default => new MultiSpanProcessor(...$spanProcessors),
        };

        if ($meterProvider !== null) {
            $meter = $meterProvider->getMeter('io.opentelemetry.sdk', schemaUrl: Version::VERSION_1_36_0->url());
            $this->spanStartedCounter = $meter->createCounter(
                OtelIncubatingMetrics::OTEL_SDK_SPAN_STARTED,
                '{span}',
                'The number of created spans',
            );
            $this->spanLiveCounter = $meter->createUpDownCounter(
                OtelIncubatingMetrics::OTEL_SDK_SPAN_LIVE,
                '{span}',
                'The number of created spans with recording=true for which the end operation has not been called yet',
            );
        } else {
            $this->spanStartedCounter = null;
            $this->spanLiveCounter = null;
        }
    }

    public function hasShutdown(): bool
    {
        return null !== $this->shutdownResult;
    }

    public function getIdGenerator(): IdGeneratorInterface
    {
        return $this->idGenerator;
    }

    public function getResource(): ResourceInfo
    {
        return $this->resource;
    }

    public function getSpanLimits(): SpanLimits
    {
        return $this->spanLimits;
    }

    public function getSampler(): SamplerInterface
    {
        return $this->sampler;
    }

    public function getSpanProcessor(): SpanProcessorInterface
    {
        return $this->spanProcessor;
    }

    public function getSpanStartedCounter(): ?CounterInterface
    {
        return $this->spanStartedCounter;
    }

    public function getSpanLiveCounter(): ?UpDownCounterInterface
    {
        return $this->spanLiveCounter;
    }

    /**
     * Returns `false` is the provider is already shutdown, otherwise `true`.
     */
    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        return $this->shutdownResult ?? ($this->shutdownResult = $this->spanProcessor->shutdown($cancellation));
    }
}
