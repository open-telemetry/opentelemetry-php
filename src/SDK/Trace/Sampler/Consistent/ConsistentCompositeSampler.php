<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

/**
 * Adapts any {@see ComposableInterface} to a {@see \OpenTelemetry\SDK\Trace\SamplerInterface}
 * (the spec's CompositeSampler). The final decision and the TraceState update are made by
 * {@see ConsistentSampler} from the delegate's sampling intent.
 *
 * @experimental
 */
final class ConsistentCompositeSampler extends ConsistentSampler
{
    public function __construct(
        private readonly ComposableInterface $delegate,
    ) {
    }

    #[\Override]
    public function getSamplingIntent(
        ContextInterface $parentContext,
        string $spanName,
        int $spanKind,
        AttributesInterface $attributes,
        array $links,
    ): SamplingIntentInterface {
        return $this->delegate->getSamplingIntent($parentContext, $spanName, $spanKind, $attributes, $links);
    }

    #[\Override]
    public function getDescription(): string
    {
        return $this->delegate->getDescription();
    }
}
