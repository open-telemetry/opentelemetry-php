<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use OpenTelemetry\SDK\Trace\Span;

/**
 * This implementation of the SamplerInterface always records.
 * Example:
 * ```
 * use OpenTelemetry\Sdk\Trace\AlwaysOnSampler;
 * $sampler = new AlwaysOnSampler();
 * ```
 */
class AlwaysOnSampler implements SamplerInterface
{
    /**
     * Returns true because we always want to sample.
     * {@inheritdoc}
     */
    public function shouldSample(
        ContextInterface $parentContext,
        string $traceId,
        string $spanName,
        int $spanKind,
        AttributesInterface $attributes,
        array $links
    ): SamplingResult {
        $parentSpan = Span::fromContext($parentContext);
        $parentSpanContext = $parentSpan->getContext();
        $traceState = $parentSpanContext->getTraceState();

        return new SamplingResult(
            SamplingResult::RECORD_AND_SAMPLE,
            [],
            $traceState
        );
    }

    public function getDescription(): string
    {
        return 'AlwaysOnSampler';
    }
}
