<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace\Sampler;

use OpenTelemetry\Context\SpanContext;

/**
 * This implementation of the SamplerInterface records with given probability.
 * Example:
 * ```
 * use OpenTelemetry\Trace\Sampler\ProbabilitySampler;
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
        ?SpanContext $parentContext,
        string $traceId,
        string $spanId,
        string $spanName,
        // SpanKind $spanKind,
        array $attributes = [],
        array $links = []
    ): SamplingResult {
        if (null !== $parentContext && $parentContext->isSampled()) {
            return new SamplingResult(SamplingResult::RECORD_AND_SAMPLED);
        }

        // TODO: implement as a function of TraceID when specification is ready
        $decision = (lcg_value() < $this->probability)
            ? SamplingResult::RECORD_AND_SAMPLED
            : SamplingResult::NOT_RECORD;

        return new SamplingResult($decision);
    }

    public function getDescription(): string
    {
        return sprintf('%s{%.6f}', 'ProbabilitySampler', $this->probability);
    }
}
