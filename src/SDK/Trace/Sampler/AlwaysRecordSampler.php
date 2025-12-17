<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;

/**
 * This implementation of the SamplerInterface converts `DROP` decisions 
 * from the root sampler into `RECORD_ONLY` decisions, allowing processors 
 * to see all spans without sending them to exporters. This is typically used
 * to enable accurate span-to-metrics processing.
 * Example:
 * ```
 * use OpenTelemetry\SDK\Trace\Sampler\AlwaysRecordSampler;
 * use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
 * 
 * $rootSampler = new AlwaysOffSampler();
 * $sampler = new AlwaysRecordSampler($rootSampler);
 * ```
 */
class AlwaysRecordSampler implements SamplerInterface
{

    /**
     * AlwaysRecord sampler ensures every span is passed to the `SpanProcessor`, 
     * even those that would normally be dropped by the root sampler. It does this
     * by converting `DROP` decisions into `RECORD_ONLY`.
     * 
     * @param SamplerInterface $root The root sampler called for the span.
     */
    public function __construct(
        private readonly SamplerInterface $root,
    ) {}

    /**
     * Overrides the sampling decision from the root sampler to RECORD_ONLY when
     * the root sampler returns DROP.
     * {@inheritdoc}
     */
    #[\Override]
    public function shouldSample(
        ContextInterface $parentContext,
        string $traceId,
        string $spanName,
        int $spanKind,
        AttributesInterface $attributes,
        array $links,
    ): SamplingResult {
        $rootSamplerSamplingResult = $this->root->shouldSample(
            $parentContext,
            $traceId,
            $spanName,
            $spanKind,
            $attributes,
            $links,
        );
        if ($rootSamplerSamplingResult->getDecision() === SamplingResult::DROP) {
            return new SamplingResult(
                SamplingResult::RECORD_ONLY,
                $rootSamplerSamplingResult->getAttributes(),
                $rootSamplerSamplingResult->getTraceState()
            );
        }
        return $rootSamplerSamplingResult;
    }

    #[\Override]
    public function getDescription(): string
    {
        return 'AlwaysRecordSampler+' . $this->root->getDescription();
    }
}
