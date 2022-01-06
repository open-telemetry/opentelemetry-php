<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\AttributesInterface;
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
    private SamplerInterface $root;

    private SamplerInterface $remoteParentSampled;

    private SamplerInterface $remoteParentNotSampled;

    private SamplerInterface $localParentSampled;

    private SamplerInterface $localParentNotSampled;

    /**
     * ParentBased sampler delegates the sampling decision based on the parent context.
     *
     * @param SamplerInterface $root Sampler called for the span with no parent (root span).
     * @param SamplerInterface|null $remoteParentSampled Sampler called for the span with the remote sampled parent. When null, `AlwaysOnSampler` is used.
     * @param SamplerInterface|null $remoteParentNotSampled Sampler called for the span with the remote not sampled parent. When null, `AlwaysOffSampler` is used.
     * @param SamplerInterface|null $localParentSampled Sampler called for the span with local the sampled parent. When null, `AlwaysOnSampler` is used.
     * @param SamplerInterface|null $localParentNotSampled Sampler called for the span with the local not sampled parent. When null, `AlwaysOffSampler` is used.
     */
    public function __construct(
        SamplerInterface $root,
        ?SamplerInterface $remoteParentSampled = null,
        ?SamplerInterface $remoteParentNotSampled = null,
        ?SamplerInterface $localParentSampled = null,
        ?SamplerInterface $localParentNotSampled = null
    ) {
        $this->root = $root;
        $this->remoteParentSampled = $remoteParentSampled ?? new AlwaysOnSampler();
        $this->remoteParentNotSampled = $remoteParentNotSampled ?? new AlwaysOffSampler();
        $this->localParentSampled = $localParentSampled ?? new AlwaysOnSampler();
        $this->localParentNotSampled = $localParentNotSampled ?? new AlwaysOffSampler();
    }

    /**
     * Invokes the respective delegate sampler when parent is set or uses root sampler for the root span.
     * {@inheritdoc}
     */
    public function shouldSample(
        Context $parentContext,
        string $traceId,
        string $spanName,
        int $spanKind,
        AttributesInterface $attributes,
        array $links = []
    ): SamplingResult {
        $parentSpan = Span::fromContext($parentContext);
        $parentSpanContext = $parentSpan->getContext();

        // Invalid parent SpanContext indicates root span is being created
        if (!$parentSpanContext->isValid()) {
            return $this->root->shouldSample(...func_get_args());
        }

        if ($parentSpanContext->isRemote()) {
            return $parentSpanContext->isSampled()
                ? $this->remoteParentSampled->shouldSample(...func_get_args())
                : $this->remoteParentNotSampled->shouldSample(...func_get_args());
        }

        return $parentSpanContext->isSampled()
            ? $this->localParentSampled->shouldSample(...func_get_args())
            : $this->localParentNotSampled->shouldSample(...func_get_args());
    }

    public function getDescription(): string
    {
        return 'ParentBased+' . $this->root->getDescription();
    }
}
