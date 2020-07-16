<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace\Sampler;

use OpenTelemetry\Sdk\Trace\Sampler;
use OpenTelemetry\Sdk\Trace\SamplingResult;
use OpenTelemetry\Trace as API;

/**
 * This implementation of the SamplerInterface records with given probability.
 * Example:
 * ```
 * use OpenTelemetry\Trace\ProbabilitySampler;
 * $sampler = new ProbabilitySampler(0.01);
 * ```
 */
class ProbabilitySampler implements Sampler
{
    /**
     * @var float
     */
    private $probability;

    /**
     * ProbabilitySampler constructor.
     * @param float $probability Probability float value between 0.0 and 1.0.
     */
    public function __construct(float $probability)
    {
        $this->probability = $probability;
    }

    /**
     * Returns `SamplingResult` based on probability. Respects the parent `SampleFlag`
     * {@inheritdoc}
     */
    public function shouldSample(
        ?API\SpanContext $parentContext,
        string $traceId,
        string $spanId,
        string $spanName,
        int $spanKind,
        ?API\Attributes $attributes = null,
        ?API\Links $links = null
    ): SamplingResult {
        if (null !== $parentContext && ($parentContext->getTraceFlags() & API\SpanContext::TRACE_FLAG_SAMPLED)) {
            return new SamplingResult(SamplingResult::RECORD_AND_SAMPLED, $attributes, $links);
        }

        // TODO: implement as a function of TraceID when specification is ready
        $decision = (lcg_value() < $this->probability)
            ? SamplingResult::RECORD_AND_SAMPLED
            : SamplingResult::NOT_RECORD;

        return new SamplingResult($decision, $attributes, $links);
    }

    public function getDescription(): string
    {
        return sprintf('%s{%.6f}', 'ProbabilitySampler', $this->probability);
    }
}
