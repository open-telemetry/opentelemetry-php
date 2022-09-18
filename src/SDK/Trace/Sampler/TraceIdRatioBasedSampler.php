<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler;

use InvalidArgumentException;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use OpenTelemetry\SDK\Trace\Span;

/**
 * This implementation of the SamplerInterface records with given probability.
 * Example:
 * ```
 * use OpenTelemetry\API\Trace\TraceIdRatioBasedSampler;
 * $sampler = new TraceIdRatioBasedSampler(0.01);
 * ```
 */
class TraceIdRatioBasedSampler implements SamplerInterface
{
    private float $probability;

    /**
     * TraceIdRatioBasedSampler constructor.
     * @param float $probability Probability float value between 0.0 and 1.0.
     */
    public function __construct(float $probability)
    {
        if ($probability < 0.0 || $probability > 1.0) {
            throw new InvalidArgumentException('probability should be be between 0.0 and 1.0.');
        }
        $this->probability = $probability;
    }

    /**
     * Returns `SamplingResult` based on probability. Respects the parent `SampleFlag`
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
        // TODO: Add config to adjust which spans get sampled (only default from specification is implemented)
        $parentSpan = Span::fromContext($parentContext);
        $parentSpanContext = $parentSpan->getContext();
        $traceState = $parentSpanContext->getTraceState();

        /**
         * Since php can only store up to 63 bit positive integers
         */
        $traceIdLimit = (1 << 60) - 1;
        $lowerOrderBytes = hexdec(substr($traceId, strlen($traceId) - 15, 15));
        $traceIdCondition = $lowerOrderBytes < round($this->probability * $traceIdLimit);
        $decision = $traceIdCondition ? SamplingResult::RECORD_AND_SAMPLE : SamplingResult::DROP;

        return new SamplingResult($decision, [], $traceState);
    }

    public function getDescription(): string
    {
        return sprintf('%s{%.6F}', 'TraceIdRatioBasedSampler', $this->probability);
    }
}
