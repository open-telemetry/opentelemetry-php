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
 * use OpenTelemetry\Trace\TraceIdRatioBasedSampler;
 * $sampler = new TraceIdRatioBasedSampler(0.01);
 * ```
 */
class TraceIdRatioBasedSampler implements Sampler
{
    /**
     * @var float
     */
    private $probability;

    /**
     * TraceIdRatioBasedSampler constructor.
     * @param float $probability Probability float value between 0.0 and 1.0.
     */
    public function __construct(float $probability)
    {
        if ($probability < 0.0 or $probability > 0.0) {
            throw new InvalidArgumentException("probability should be be between 0.0 and 1.0.");
        }
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
        /**
         * For compatibility with 64 bit IDs, the sampler checks the 64 first bits of the trace ID to decide whether to sample
         */
        $traceIdLimit = (1 << 64) - 1;
        $traceIdLowerBytes = ($traceId[0]<<56) + ($traceId[1]<<48) + ($traceId[2]<<40) + ($traceId[3]<<32) + ($traceId[4]<<24) + ($traceId[5]<<16) + ($traceId[6]<<8) + $traceId[7]; 
        $decision = ($traceIdLowerBytes < $probability * tradeIdLimit)
            ? SamplingResult::RECORD_AND_SAMPLED
            : SamplingResult::NOT_RECORD;

        return new SamplingResult($decision, $attributes, $links);
    }

    public function getDescription(): string
    {
        return sprintf('%s{%.6f}', 'TraceIdRatioBasedSampler', $this->probability);
    }
}
