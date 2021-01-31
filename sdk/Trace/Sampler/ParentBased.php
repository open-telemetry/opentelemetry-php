<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace\Sampler;

use OpenTelemetry\Sdk\Trace\Sampler;
use OpenTelemetry\Sdk\Trace\SamplingResult;
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
        ?API\SpanContext $parentContext,
        string $traceId,
        string $spanId,
        string $spanName,
        int $spanKind,
        ?API\Attributes $attributes = null,
        ?API\Links $links = null
    ): SamplingResult {
        if ($parentContext === null) {
            return $this->root->shouldSample($parentContext, $traceId, $spanId, $spanName, $spanKind, $attributes, $links);
        }

        if ($parentContext->isRemote()) {
            return $parentContext->isSampled()
                ? $this->remoteParentSampled->shouldSample($parentContext, $traceId, $spanId, $spanName, $spanKind, $attributes, $links)
                : $this->remoteParentNotSampled->shouldSample($parentContext, $traceId, $spanId, $spanName, $spanKind, $attributes, $links);
        }

        return $parentContext->isSampled()
            ? $this->localParentSampled->shouldSample($parentContext, $traceId, $spanId, $spanName, $spanKind, $attributes, $links)
            : $this->localParentNotSampled->shouldSample($parentContext, $traceId, $spanId, $spanName, $spanKind, $attributes, $links);
    }

    public function getDescription(): string
    {
        return 'ParentBased';
    }
}
