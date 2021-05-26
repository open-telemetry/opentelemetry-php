<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace\Sampler;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Sdk\Trace\Sampler;
use OpenTelemetry\Sdk\Trace\SamplingResult;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Trace as API;

/**
 * This implementation of the SamplerInterface that respects parent context's sampling decision
 * and delegates for the root span.
 * Example:
 * ```
 * use OpenTelemetry\Trace\ParentBased;
 * use OpenTelemetry\Trace\AlwaysOnSampler
 *
 * $rootSampler = new AlwaysOnSampler();
 * $sampler = new ParentBased($rootSampler);
 * ```
 */
class ParentBased implements Sampler
{

    /**
     * @var Sampler
     */
    private $root;

    /**
     * @var Sampler
     */
    private $remoteParentSampled;

    /**
     * @var Sampler
     */
    private $remoteParentNotSampled;

    /**
     * @var Sampler
     */
    private $localParentSampled;

    /**
     * @var Sampler
     */
    private $localParentNotSampled;

    /**
     * ParentBased sampler delegates the sampling decision based on the parent context.
     *
     * @param Sampler $root Sampler called for the span with no parent (root span).
     * @param Sampler|null $remoteParentSampled Sampler called for the span with the remote sampled parent. When null, `AlwaysOnSampler` is used.
     * @param Sampler|null $remoteParentNotSampled Sampler called for the span with the remote not sampled parent. When null, `AlwaysOffSampler` is used.
     * @param Sampler|null $localParentSampled Sampler called for the span with local the sampled parent. When null, `AlwaysOnSampler` is used.
     * @param Sampler|null $localParentNotSampled Sampler called for the span with the local not sampled parent. When null, `AlwaysOffSampler` is used.
     */
    public function __construct(
        Sampler $root,
        ?Sampler $remoteParentSampled = null,
        ?Sampler $remoteParentNotSampled = null,
        ?Sampler $localParentSampled = null,
        ?Sampler $localParentNotSampled = null
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
        ?API\Attributes $attributes = null,
        ?API\Links $links = null
    ): SamplingResult {
        $parentSpan = Span::extract($parentContext);
        $parentSpanContext = $parentSpan !== null ? $parentSpan->getContext() : SpanContext::getInvalid();
        
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
        return 'ParentBased';
    }
}
