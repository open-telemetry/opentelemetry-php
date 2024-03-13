<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use OpenTelemetry\SDK\Trace\Span;

/**
 * Phan seems to struggle with the variadic arguments in the latest version
 * @phan-file-suppress PhanParamTooFewUnpack
 */

/**
 * This implementation of the SamplerInterface that respects parent context's sampling decision
 * and delegates for the root span.
 * Example:
 * ```
 * use OpenTelemetry\API\Trace\ParentBased;
 * use OpenTelemetry\API\Trace\AlwaysOnSampler
 *
 * $rootSampler = new AlwaysOnSampler();
 * $sampler = new ParentBased($rootSampler);
 * ```
 */
class ParentBased implements SamplerInterface
{
    /**
     * ParentBased sampler delegates the sampling decision based on the parent context.
     *
     * @param SamplerInterface $root Sampler called for the span with no parent (root span).
     * @param SamplerInterface $remoteParentSampler Sampler called for the span with the remote sampled parent. When null, `AlwaysOnSampler` is used.
     * @param SamplerInterface $remoteParentNotSampler Sampler called for the span with the remote not sampled parent. When null, `AlwaysOffSampler` is used.
     * @param SamplerInterface $localParentSampler Sampler called for the span with local the sampled parent. When null, `AlwaysOnSampler` is used.
     * @param SamplerInterface $localParentNotSampler Sampler called for the span with the local not sampled parent. When null, `AlwaysOffSampler` is used.
     */
    public function __construct(
        private readonly SamplerInterface $root,
        private readonly SamplerInterface $remoteParentSampler = new AlwaysOnSampler(),
        private readonly SamplerInterface $remoteParentNotSampler = new AlwaysOffSampler(),
        private readonly SamplerInterface $localParentSampler = new AlwaysOnSampler(),
        private readonly SamplerInterface $localParentNotSampler = new AlwaysOffSampler(),
    ) {
    }

    /**
     * Invokes the respective delegate sampler when parent is set or uses root sampler for the root span.
     * {@inheritdoc}
     */
    public function shouldSample(
        ContextInterface $parentContext,
        string $traceId,
        string $spanName,
        int $spanKind,
        AttributesInterface $attributes,
        array $links,
    ): SamplingResult {
        $parentSpan = Span::fromContext($parentContext);
        $parentSpanContext = $parentSpan->getContext();

        // Invalid parent SpanContext indicates root span is being created
        if (!$parentSpanContext->isValid()) {
            return $this->root->shouldSample(...func_get_args());
        }

        if ($parentSpanContext->isRemote()) {
            return $parentSpanContext->isSampled()
                ? $this->remoteParentSampler->shouldSample(...func_get_args())
                : $this->remoteParentNotSampler->shouldSample(...func_get_args());
        }

        return $parentSpanContext->isSampled()
            ? $this->localParentSampler->shouldSample(...func_get_args())
            : $this->localParentNotSampler->shouldSample(...func_get_args());
    }

    public function getDescription(): string
    {
        return 'ParentBased+' . $this->root->getDescription();
    }
}
